<?php
/**
 * ============================================================
 *  POSUNG HRIS – ContractController
 * ============================================================
 *  Quản lý vòng đời Hợp đồng lao động của Nhân sự.
 * ============================================================
 */

class ContractController extends Controller
{
    public function __construct()
    {
        // Yêu cầu đăng nhập và quyền (HR_Manager hoặc Admin)
        Session::checkPermission(['Admin', 'HR_Manager']);
    }

    /**
     * Danh sách tất cả hợp đồng
     */
    public function index(): void
    {
        $contractModel = $this->model('Contract');
        // Giả sử có hàm lấy toàn bộ hợp đồng kèm tên nhân viên
        $db = Database::getInstance();
        $sql = "SELECT c.*, e.emp_code, e.full_name, ct.name as contract_type_name
                FROM contracts c
                JOIN employees e ON c.employee_id = e.id
                LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
                ORDER BY c.start_date DESC";
        $db->query($sql);
        $contracts = $db->fetchAll();

        // Lấy danh sách hợp đồng sắp hết hạn (30 ngày)
        $expiringContracts = $contractModel->getExpiring(30);

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Hợp đồng']);
        $this->view('contract/index', [
            'contracts' => $contracts,
            'expiringContracts' => $expiringContracts
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Hiển thị danh sách hợp đồng của 1 nhân viên cụ thể
     */
    public function employee(int $employeeId): void
    {
        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->find($employeeId);
        if (!$employee) {
            Session::setFlash('error', 'Không tìm thấy nhân viên.');
            $this->redirect('employee');
            return;
        }

        $contractModel = $this->model('Contract');
        $contracts = $contractModel->getByEmployee($employeeId);
        
        $contractTypeModel = $this->model('ContractType');
        $contractTypes = $contractTypeModel->all();
        
        $formulaModel = $this->model('PayrollFormula');
        $formulas = $formulaModel->where(['is_active' => 1]);

        $this->view('layouts/header', ['pageTitle' => 'Hợp đồng - ' . h($employee->full_name)]);
        $this->view('contract/employee', [
            'employee' => $employee,
            'contracts' => $contracts,
            'contractTypes' => $contractTypes,
            'formulas' => $formulas
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Thêm mới / Ký mới hợp đồng
     */
    public function store(): void
    {
        if ($this->isPost()) {
            $employeeId = (int)$this->postData('employee_id');
            $typeId = (int)$this->postData('contract_type_id');
            
            // Xử lý status
            $status = $this->postData('status', 'Active');
            
            $data = [
                'employee_id' => $employeeId,
                'contract_type_id' => $typeId,
                'contract_number' => $this->postData('contract_number'),
                'start_date' => $this->postData('start_date'),
                'end_date' => $this->postData('end_date') ?: null,
                'basic_salary' => (float)str_replace(',', '', $this->postData('basic_salary', '0')),
                'insurance_salary' => (float)str_replace(',', '', $this->postData('insurance_salary', '0')),
                'payroll_formula_id' => $this->postData('payroll_formula_id') ?: null,
                'status' => $status,
                'note' => $this->postData('note')
            ];

            $contractModel = $this->model('Contract');
            $db = Database::getInstance();

            try {
                $db->beginTransaction();

                // Lưu hợp đồng
                $contractId = $contractModel->create($data);

                // Cập nhật trạng thái nhân viên (VD: Probation -> Active) 
                // Tùy theo loại hợp đồng (ví dụ Type có name là 'Thử việc')
                $contractTypeModel = $this->model('ContractType');
                $type = $contractTypeModel->find($typeId);
                
                $employeeModel = $this->model('Employee');
                $emp = $employeeModel->find($employeeId);

                if ($emp && $type) {
                    $newEmpStatus = null;
                    $isProbation = stripos($type->name, 'Thử việc') !== false;
                    
                    if ($isProbation) {
                        $newEmpStatus = 'Probation';
                    } else if ($status === 'Active') {
                        $newEmpStatus = 'Active';
                        // Cập nhật ngày lên chính thức nếu chưa có
                        if (empty($emp->official_date)) {
                            $employeeModel->update($employeeId, ['official_date' => $data['start_date']]);
                        }
                    }

                    if ($newEmpStatus && $emp->status !== $newEmpStatus) {
                        $employeeModel->update($employeeId, ['status' => $newEmpStatus]);
                    }
                }

                $db->commit();
                Session::setFlash('success', 'Đã ký hợp đồng mới thành công!');
            } catch (Exception $e) {
                $db->rollBack();
                Session::setFlash('error', 'Lỗi khi ký hợp đồng: ' . $e->getMessage());
            }

            $this->redirect('contract/employee/' . $employeeId);
        }
    }

    /**
     * Cập nhật / Chấm dứt hợp đồng
     */
    public function update(int $id): void
    {
        if ($this->isPost()) {
            $contractModel = $this->model('Contract');
            $contract = $contractModel->find($id);
            if (!$contract) {
                $this->redirect('contract');
                return;
            }

            $data = [
                'contract_type_id' => $this->postData('contract_type_id'),
                'contract_number' => $this->postData('contract_number'),
                'start_date' => $this->postData('start_date'),
                'end_date' => $this->postData('end_date') ?: null,
                'basic_salary' => (float)str_replace(',', '', $this->postData('basic_salary', '0')),
                'insurance_salary' => (float)str_replace(',', '', $this->postData('insurance_salary', '0')),
                'payroll_formula_id' => $this->postData('payroll_formula_id') ?: null,
                'status' => $this->postData('status'),
                'note' => $this->postData('note')
            ];

            $contractModel->update($id, $data);
            Session::setFlash('success', 'Cập nhật hợp đồng thành công!');
            $this->redirect('contract/employee/' . $contract->employee_id);
        }
    }

    /**
     * In Hợp đồng (Bản in)
     */
    public function print(int $id): void
    {
        $db = Database::getInstance();
        $sql = "SELECT c.*, e.emp_code, e.full_name, e.dob, e.id_card, e.id_card_date, e.id_card_place, e.address, e.pos_title, e.dept_name, ct.name as contract_type_name
                FROM contracts c
                JOIN employees e ON c.employee_id = e.id
                LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
                WHERE c.id = :id";
        $db->query($sql, ['id' => $id]);
        $contract = $db->fetch();

        if (!$contract) {
            die("Không tìm thấy hợp đồng");
        }

        $this->view('contract/print_contract', ['contract' => (object)$contract]);
    }
}
