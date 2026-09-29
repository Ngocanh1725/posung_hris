<?php
/**
 * ============================================================
 *  POSUNG HRIS – SalaryProgressionController
 * ============================================================
 *  Phân hệ Quản lý Lịch sử Lương & Nâng bậc nhân sự (Salary Progression)
 *  Timeline lịch sử lương, Biểu đồ Chart.js, Nâng lương hàng loạt & Báo cáo BI
 * ============================================================
 */

class SalaryProgressionController extends Controller
{
    /**
     * Màn hình chính:
     * - Nếu truyền $employee_id: Xem Timeline & Biểu đồ của 1 nhân viên cụ thể
     * - Nếu không truyền: Xem Dashboard quản lý nâng bậc toàn công ty
     */
    public function index(?int $employee_id = null): void
    {
        $this->checkPermission('employee.view');

        $progModel = $this->model('SalaryProgression');
        $empModel = $this->model('Employee');

        if ($employee_id) {
            // MÀN HÌNH TIMELINE & BIỂU ĐỒ 1 NHÂN VIÊN
            $employee = $empModel->getById($employee_id);
            if (!$employee) {
                Session::setFlash('error', 'Không tìm thấy nhân viên.');
                $this->redirect('salaryProgression');
                return;
            }

            $history = $progModel->getByEmployee($employee_id, 'DESC');
            $chartData = $progModel->getChartData($employee_id);
            $currentSalary = $progModel->getCurrentSalary($employee_id);

            $this->view('layouts/header', ['pageTitle' => 'Lịch sử Lương - ' . $employee->full_name]);
            $this->view('salary_progression/timeline', [
                'employee'      => $employee,
                'history'       => $history,
                'chartData'     => $chartData,
                'currentSalary' => $currentSalary,
            ]);
            $this->view('layouts/footer');
        } else {
            // MÀN HÌNH DASHBOARD TOÀN CÔNG TY
            $filters = [
                'search'        => trim($this->getData('search', '')),
                'department_id' => $this->getData('department_id', ''),
                'project_id'    => $this->getData('project_id', ''),
                'year'          => $this->getData('year', date('Y')),
                'reason'        => trim($this->getData('reason', '')),
            ];

            $progressions = $progModel->getAllWithFilters($filters);

            // Lấy danh sách phòng ban và dự án phục vụ lọc
            $db = Database::getInstance();
            $db->query("SELECT id, dept_name FROM departments ORDER BY dept_name ASC");
            $departments = $db->fetchAll();

            $db->query("SELECT id, project_name FROM projects ORDER BY project_name ASC");
            $projects = $db->fetchAll();

            // Thống kê nhanh
            $totalIncreases = count($progressions);
            $totalAmount = 0;
            foreach ($progressions as $p) {
                $totalAmount += (float)($p->increase_amount ?? 0);
            }

            $this->view('layouts/header', ['pageTitle' => 'Quản lý Diễn biến Lương & Nâng bậc']);
            $this->view('salary_progression/index', [
                'progressions'   => $progressions,
                'departments'    => $departments,
                'projects'       => $projects,
                'filters'        => $filters,
                'totalIncreases' => $totalIncreases,
                'totalAmount'    => $totalAmount,
            ]);
            $this->view('layouts/footer');
        }
    }

    /**
     * Form tạo quyết định nâng bậc cho 1 nhân viên
     */
    public function create(int $employee_id): void
    {
        $this->checkPermission('employee.edit');

        $empModel = $this->model('Employee');
        $employee = $empModel->getById($employee_id);

        if (!$employee) {
            Session::setFlash('error', 'Không tìm thấy nhân viên.');
            $this->redirect('salaryProgression');
            return;
        }

        $progModel = $this->model('SalaryProgression');
        $currentSalary = $progModel->getCurrentSalary($employee_id);

        $this->view('layouts/header', ['pageTitle' => 'Đề xuất Nâng bậc Lương - ' . $employee->full_name]);
        $this->view('salary_progression/create', [
            'employee'      => $employee,
            'currentSalary' => $currentSalary,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Xử lý lưu quyết định nâng bậc
     */
    public function store(): void
    {
        $this->checkPermission('employee.edit');

        if (!$this->isPost()) {
            $this->redirect('salaryProgression');
            return;
        }

        $employeeId = (int)$this->postData('employee_id');
        $newSalary = (float)str_replace([',', '.'], '', $this->postData('new_salary', '0'));
        $effectiveDate = $this->postData('effective_date', date('Y-m-d'));
        $reason = trim($this->postData('reason', 'Nâng bậc lương định kỳ'));
        $decisionNumber = trim($this->postData('decision_number', ''));
        $decisionDate = $this->postData('decision_date') ?: null;
        $notes = trim($this->postData('notes', ''));

        if ($newSalary <= 0) {
            Session::setFlash('error', 'Mức lương mới phải lớn hơn 0.');
            $this->redirect('salaryProgression/create/' . $employeeId);
            return;
        }

        $progModel = $this->model('SalaryProgression');
        $oldSalary = $progModel->getCurrentSalary($employeeId);

        $res = $progModel->createProgression([
            'employee_id'     => $employeeId,
            'old_salary'      => $oldSalary,
            'new_salary'      => $newSalary,
            'effective_date'  => $effectiveDate,
            'reason'          => $reason,
            'decision_number' => $decisionNumber,
            'decision_date'   => $decisionDate,
            'approved_by'     => Session::userId(),
            'notes'           => $notes,
        ]);

        if ($res) {
            Session::setFlash('success', "Quyết định nâng lương đã được lưu và cập nhật thành công.");
            $this->redirect('salaryProgression/index/' . $employeeId);
        } else {
            Session::setFlash('error', 'Đã xảy ra lỗi khi lưu quyết định nâng lương.');
            $this->redirect('salaryProgression/create/' . $employeeId);
        }
    }

    /**
     * Nâng lương hàng loạt (Batch Increase)
     */
    public function batchIncrease(): void
    {
        $this->checkPermission('employee.edit');

        $progModel = $this->model('SalaryProgression');
        $empModel = $this->model('Employee');

        if ($this->isPost()) {
            $employeeIds = $this->postData('employee_ids', []);
            $increaseType = $this->postData('increase_type', 'percent');
            $increaseValue = (float)$this->postData('increase_value', 0);
            $effectiveDate = $this->postData('effective_date', date('Y-m-d'));
            $reason = trim($this->postData('reason', 'Điều chỉnh lương hàng loạt'));
            $decisionNumber = trim($this->postData('decision_number', ''));
            $decisionDate = $this->postData('decision_date') ?: null;
            $notes = trim($this->postData('notes', ''));

            if (empty($employeeIds)) {
                Session::setFlash('error', 'Vui lòng chọn ít nhất 1 nhân viên để áp dụng tăng lương.');
                $this->redirect('salaryProgression/batchIncrease');
                return;
            }

            if ($increaseValue <= 0) {
                Session::setFlash('error', 'Giá trị mức tăng phải lớn hơn 0.');
                $this->redirect('salaryProgression/batchIncrease');
                return;
            }

            $result = $progModel->processBatchIncrease([
                'employee_ids'    => $employeeIds,
                'increase_type'   => $increaseType,
                'increase_value'  => $increaseValue,
                'effective_date'  => $effectiveDate,
                'reason'          => $reason,
                'decision_number' => $decisionNumber,
                'decision_date'   => $decisionDate,
                'approved_by'     => Session::userId(),
                'notes'           => $notes,
            ]);

            if ($result['success']) {
                $increaseFormatted = number_format($result['total_cost_increase'], 0, ',', '.') . ' ₫/tháng';
                Session::setFlash('success', "{$result['message']} (Tổng quỹ lương tăng thêm: {$increaseFormatted})");
                $this->redirect('salaryProgression');
            } else {
                Session::setFlash('error', $result['message']);
                $this->redirect('salaryProgression/batchIncrease');
            }
            return;
        }

        // Lấy danh sách nhân viên đang làm việc kèm mức lương hiện tại
        $deptId = $this->getData('department_id', '');
        $projId = $this->getData('project_id', '');

        $sql = "SELECT e.id, e.emp_code, e.full_name, e.department_id, e.current_project_id,
                       d.dept_name, p.pos_title,
                       COALESCE(cur_sal.base_salary, 0) AS current_salary
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN (
                    SELECT s1.employee_id, s1.base_salary
                    FROM salaries s1
                    INNER JOIN (
                        SELECT employee_id, MAX(effective_date) AS max_date, MAX(id) AS max_id
                        FROM salaries
                        GROUP BY employee_id
                    ) s2 ON s1.employee_id = s2.employee_id AND s1.id = s2.max_id
                ) cur_sal ON e.id = cur_sal.employee_id
                WHERE e.status != 'Resigned' AND e.status != 'Terminated'";

        $params = [];
        if (!empty($deptId)) {
            $sql .= " AND e.department_id = :dept_id";
            $params['dept_id'] = (int)$deptId;
        }
        if (!empty($projId)) {
            $sql .= " AND e.current_project_id = :proj_id";
            $params['proj_id'] = (int)$projId;
        }

        $sql .= " ORDER BY d.dept_name ASC, e.emp_code ASC";

        $db = Database::getInstance();
        $db->query($sql, $params);
        $employees = $db->fetchAll();

        // Lấy danh sách phòng ban & dự án cho bộ lọc
        $db->query("SELECT id, dept_name FROM departments ORDER BY dept_name ASC");
        $departments = $db->fetchAll();

        $db->query("SELECT id, project_name FROM projects ORDER BY project_name ASC");
        $projects = $db->fetchAll();

        $this->view('layouts/header', ['pageTitle' => 'Nâng lương Hàng loạt (Batch Increase)']);
        $this->view('salary_progression/batch_increase', [
            'employees'   => $employees,
            'departments' => $departments,
            'projects'    => $projects,
            'deptId'      => $deptId,
            'projId'      => $projId,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Báo cáo Biến động lương theo Phòng ban / Dự án (Report & BI)
     */
    public function report(): void
    {
        $this->checkPermission('employee.view');

        $progModel = $this->model('SalaryProgression');
        $year = (int)$this->getData('year', date('Y'));

        $reportData = $progModel->getProgressionReport(['year' => $year]);

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo Biến động Lương & Chi phí Nhân sự']);
        $this->view('salary_progression/report', $reportData);
        $this->view('layouts/footer');
    }

    /**
     * AJAX endpoint trả về dữ liệu biểu đồ
     */
    public function ajaxChartData(int $employee_id): void
    {
        $progModel = $this->model('SalaryProgression');
        $chartData = $progModel->getChartData($employee_id);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($chartData);
        exit;
    }
}
