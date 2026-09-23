<?php
/**
 * ============================================================
 *  POSUNG HRIS – CategoryController
 * ============================================================
 *  Quản lý danh mục chung: Phòng ban, Chức vụ, Loại Hợp đồng, 
 *  Loại Nghỉ phép, Phụ cấp.
 * ============================================================
 */

class CategoryController extends Controller
{
    public function __construct()
    {
        // Chỉ Admin và HR Manager mới được quản lý danh mục
        $this->checkPermission('category.manage');
    }

    public function index(): void
    {
        $this->view('layouts/header', ['pageTitle' => 'Quản lý Danh mục']);
        $this->view('category/index');
        $this->view('layouts/footer');
    }

    /**
     * CRUD Phòng Ban (Departments)
     */
    public function departments(): void
    {
        $deptModel = $this->model('Department');
        $employeeModel = $this->model('Employee');
        
        if ($this->isPost()) {
            // Chuyển textarea functions thành JSON array (mỗi dòng 1 chức năng)
            $functionsRaw = $this->postData('functions', '');
            $functionsArr = array_filter(array_map('trim', explode("\n", $functionsRaw)));
            $functionsJson = !empty($functionsArr) ? json_encode(array_values($functionsArr), JSON_UNESCAPED_UNICODE) : null;

            $data = [
                'dept_code'        => $this->postData('dept_code'),
                'dept_name'        => $this->postData('dept_name'),
                'branch'           => $this->postData('branch', 'Hanoi_HQ'),
                'description'      => $this->postData('description'),
                'functions'        => $functionsJson,
                'parent_id'        => $this->postData('parent_id') ?: null,
                'manager_id'       => $this->postData('manager_id') ?: null,
                'phone'            => $this->postData('phone') ?: null,
                'email'            => $this->postData('email') ?: null,
                'office_location'  => $this->postData('office_location') ?: null,
                'established_date' => $this->postData('established_date') ?: null,
                'dept_type'        => $this->postData('dept_type', 'Department'),
                'status'           => $this->postData('status', 'Active'),
                'sort_order'       => (int)$this->postData('sort_order', 0),
            ];
            $id = (int)$this->postData('id');
            if ($id > 0) {
                $deptModel->update($id, $data);
                Session::setFlash('success', 'Cập nhật Phòng ban thành công');
            } else {
                $deptModel->create($data);
                Session::setFlash('success', 'Thêm mới Phòng ban thành công');
            }
            $this->redirect('category/departments');
            return;
        }

        $departments = $deptModel->all('dept_code ASC');
        // Lấy danh sách NV để gán Trưởng phòng
        $db = Database::getInstance();
        $db->query("SELECT id, full_name, emp_code FROM employees WHERE status IN ('Active','Probation') ORDER BY full_name ASC");
        $employees = $db->fetchAll();

        $this->view('layouts/header', ['pageTitle' => 'Danh mục Phòng ban']);
        $this->view('category/departments', [
            'departments' => $departments,
            'employees' => json_decode(json_encode($employees))
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Xóa Phòng Ban
     */
    public function delete_department(int $id): void
    {
        $deptModel = $this->model('Department');
        try {
            $deptModel->delete($id);
            Session::setFlash('success', 'Đã xóa phòng ban');
        } catch (Exception $e) {
            Session::setFlash('error', 'Không thể xóa phòng ban đang có nhân viên hoặc phòng ban con.');
        }
        $this->redirect('category/departments');
    }

    /**
     * CRUD Chức vụ (Positions)
     */
    public function positions(): void
    {
        $posModel = $this->model('Position');
        
        if ($this->isPost()) {
            $data = [
                'pos_code'       => $this->postData('pos_code'),
                'pos_title'      => $this->postData('pos_title'),
                'description'    => $this->postData('description'),
                'allowance_rate' => $this->postData('allowance_rate', 0),
                'job_level'      => $this->postData('job_level', 1)
            ];
            $id = (int)$this->postData('id');
            if ($id > 0) {
                $posModel->update($id, $data);
                Session::setFlash('success', 'Cập nhật Chức vụ thành công');
            } else {
                $posModel->create($data);
                Session::setFlash('success', 'Thêm mới Chức vụ thành công');
            }
            $this->redirect('category/positions');
            return;
        }

        $positions = $posModel->all('job_level DESC, pos_code ASC');

        $this->view('layouts/header', ['pageTitle' => 'Danh mục Chức vụ']);
        $this->view('category/positions', ['positions' => $positions]);
        $this->view('layouts/footer');
    }

    /**
     * CRUD Các danh mục khác (Loại Hợp đồng, Loại Phép, Phụ cấp)
     * Rút gọn dùng chung 1 logic
     */
    public function manage(string $type): void
    {
        $modelMap = [
            'contract_types' => ['model' => 'ContractType', 'title' => 'Loại Hợp đồng', 'fields' => ['name', 'duration_months']],
            'leave_types'    => ['model' => 'LeaveType', 'title' => 'Loại Nghỉ phép', 'fields' => ['name', 'days_per_year', 'is_paid']],
            'allowances'     => ['model' => 'Allowance', 'title' => 'Phụ cấp', 'fields' => ['code', 'name', 'type', 'amount', 'is_taxable']]
        ];

        if (!isset($modelMap[$type])) {
            $this->redirect('category');
            return;
        }

        $config = $modelMap[$type];
        $model = $this->model($config['model']);

        if ($this->isPost()) {
            $data = [];
            foreach ($config['fields'] as $f) {
                $data[$f] = $this->postData($f) !== '' ? $this->postData($f) : null;
            }
            $id = (int)$this->postData('id');
            if ($id > 0) {
                $model->update($id, $data);
                Session::setFlash('success', "Cập nhật {$config['title']} thành công");
            } else {
                $model->create($data);
                Session::setFlash('success', "Thêm mới {$config['title']} thành công");
            }
            $this->redirect("category/manage/{$type}");
            return;
        }

        $items = $model->all();

        $this->view('layouts/header', ['pageTitle' => "Danh mục " . $config['title']]);
        $this->view("category/{$type}", [
            'items' => $items,
            'title' => $config['title']
        ]);
        $this->view('layouts/footer');
    }
}
