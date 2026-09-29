<?php
/**
 * ============================================================
 *  POSUNG HRIS – HolidayController
 * ============================================================
 *  Quản lý Lịch Nghỉ Lễ / Tết / Ngày Kỷ niệm và Calendar View
 *  kết hợp Lịch Nghỉ Phép Nhân viên toàn Công ty.
 * ============================================================
 */

class HolidayController extends Controller
{
    private Holiday $holidayModel;

    public function __construct()
    {
        $this->holidayModel = $this->model('Holiday');
    }

    /**
     * Màn hình Calendar & Danh sách Ngày nghỉ Lễ
     * URL: /holiday hoặc /holiday/index
     */
    public function index(): void
    {
        $this->checkPermission('leave.view');

        $year   = (int)$this->getData('year', date('Y'));
        $deptId = $this->getData('dept_id') ? (int)$this->getData('dept_id') : null;

        $holidays = $this->holidayModel->getHolidays($year, $deptId);
        $departments = $this->model('Department')->all('dept_name ASC');

        // Thống kê ngày lễ
        $nationalCount = 0;
        $companyCount = 0;
        $recurringCount = 0;
        foreach ($holidays as $h) {
            if ($h['type'] === 'National') $nationalCount++;
            if ($h['type'] === 'Company') $companyCount++;
            if (!empty($h['is_recurring'])) $recurringCount++;
        }

        $isManager = Session::isAdmin() || Session::isHR() || Session::hasPermission('leave.manage');

        $this->view('layouts/header', ['pageTitle' => "Lịch Nghỉ Lễ & Phép Năm {$year}"]);
        $this->view('holiday/index', [
            'year'           => $year,
            'deptId'         => $deptId,
            'holidays'       => $holidays,
            'departments'    => $departments,
            'nationalCount'  => $nationalCount,
            'companyCount'   => $companyCount,
            'recurringCount' => $recurringCount,
            'totalCount'     => count($holidays),
            'isManager'      => $isManager
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Thêm mới ngày lễ
     * POST /holiday/store
     */
    public function store(): void
    {
        $this->checkManagerAccess();

        if ($this->isPost()) {
            $name        = trim($this->postData('name', ''));
            $date        = trim($this->postData('date', ''));
            $type        = $this->postData('type', 'National');
            $isRecurring = (int)$this->postData('is_recurring', 0);
            $deptId      = $this->postData('applies_to_department_id') ?: null;
            $description = trim($this->postData('description', ''));

            if (empty($name) || empty($date)) {
                Session::setFlash('error', 'Vui lòng nhập tên ngày lễ và ngày nghỉ.');
                $this->redirect('holiday');
                return;
            }

            try {
                $this->holidayModel->addHoliday([
                    'name'                     => $name,
                    'date'                     => $date,
                    'type'                     => $type,
                    'is_recurring'             => $isRecurring,
                    'applies_to_department_id' => $deptId,
                    'description'              => $description
                ]);

                $year = date('Y', strtotime($date));
                Session::setFlash('success', "Đã thêm ngày nghỉ lễ '{$name}' thành công.");
                $this->redirect("holiday?year={$year}");
                return;
            } catch (Exception $e) {
                Session::setFlash('error', 'Lỗi khi thêm ngày lễ: ' . $e->getMessage());
                $this->redirect('holiday');
                return;
            }
        }
    }

    /**
     * Cập nhật ngày lễ
     * POST /holiday/update/{id}
     */
    public function update(int $id): void
    {
        $this->checkManagerAccess();

        if ($this->isPost()) {
            $name        = trim($this->postData('name', ''));
            $date        = trim($this->postData('date', ''));
            $type        = $this->postData('type', 'National');
            $isRecurring = (int)$this->postData('is_recurring', 0);
            $deptId      = $this->postData('applies_to_department_id') ?: null;
            $description = trim($this->postData('description', ''));

            if (empty($name) || empty($date)) {
                Session::setFlash('error', 'Vui lòng nhập đầy đủ tên và ngày lễ.');
                $this->redirect('holiday');
                return;
            }

            try {
                $this->holidayModel->updateHoliday($id, [
                    'name'                     => $name,
                    'date'                     => $date,
                    'type'                     => $type,
                    'is_recurring'             => $isRecurring,
                    'applies_to_department_id' => $deptId,
                    'description'              => $description
                ]);

                $year = date('Y', strtotime($date));
                Session::setFlash('success', "Cập nhật ngày lễ '{$name}' thành công.");
                $this->redirect("holiday?year={$year}");
                return;
            } catch (Exception $e) {
                Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
                $this->redirect('holiday');
                return;
            }
        }
    }

    /**
     * Xóa ngày lễ
     * POST /holiday/delete/{id}
     */
    public function delete(int $id): void
    {
        $this->checkManagerAccess();

        if ($this->isPost()) {
            try {
                $this->holidayModel->deleteHoliday($id);
                Session::setFlash('success', 'Đã xóa ngày nghỉ lễ thành công.');
            } catch (Exception $e) {
                Session::setFlash('error', 'Lỗi khi xóa ngày lễ: ' . $e->getMessage());
            }
            $this->redirect('holiday');
            return;
        }
    }

    /**
     * Import nhanh danh sách ngày lễ chuẩn Việt Nam cho một năm
     * POST /holiday/import
     */
    public function import(): void
    {
        $this->checkManagerAccess();

        if ($this->isPost()) {
            $year = (int)$this->postData('year', date('Y'));
            if ($year < 2020 || $year > 2050) {
                Session::setFlash('error', 'Năm không hợp lệ.');
                $this->redirect('holiday');
                return;
            }

            try {
                $inserted = $this->holidayModel->importYearlyTemplate($year);
                Session::setFlash('success', "Đã import thành công {$inserted} ngày nghỉ lễ theo lịch Việt Nam năm {$year}.");
                $this->redirect("holiday?year={$year}");
                return;
            } catch (Exception $e) {
                Session::setFlash('error', 'Lỗi import: ' . $e->getMessage());
                $this->redirect('holiday');
                return;
            }
        }
    }

    /**
     * API JSON trả về sự kiện cho Calendar UI (Holidays + Approved Leaves)
     * GET /holiday/apiEvents
     */
    public function apiEvents(): void
    {
        $startDate = $this->getData('start', date('Y-01-01'));
        $endDate   = $this->getData('end', date('Y-12-31'));
        $deptId    = $this->getData('dept_id') ? (int)$this->getData('dept_id') : null;

        $events = $this->holidayModel->getCalendarEvents($startDate, $endDate, $deptId);

        $this->json($events);
    }

    /**
     * Kiểm tra quyền quản trị HR/Admin
     */
    private function checkManagerAccess(): void
    {
        if (!Session::isAdmin() && !Session::isHR() && !Session::hasPermission('leave.manage')) {
            Session::setFlash('error', 'Bạn không có quyền thực hiện thao tác này.');
            $this->redirect('holiday');
            exit;
        }
    }
}
