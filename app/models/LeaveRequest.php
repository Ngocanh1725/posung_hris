<?php
/**
 * ============================================================
 *  POSUNG HRIS – LeaveRequest Model
 * ============================================================
 *  Quản lý đơn xin nghỉ phép, kiểm tra chồng đơn (Overlap),
 *  tích hợp lịch sự kiện và đồng bộ quỹ phép (Leave Allocation).
 * ============================================================
 */

class LeaveRequest extends BaseModel
{
    protected string $table = 'leave_requests';

    /**
     * Lấy danh sách đơn xin nghỉ theo nhân viên
     */
    public function getByEmployee(int $employeeId, ?int $year = null): array
    {
        $sql = "SELECT lr.*, lr.total_days as days, lt.name as leave_type_name, lt.is_paid, lt.code as leave_type_code,
                       COALESCE(u.username, 'Quản lý') as approver_name
                FROM {$this->table} lr
                JOIN leave_types lt ON lr.leave_type_id = lt.id
                LEFT JOIN users u ON lr.approved_by = u.id
                WHERE lr.employee_id = :emp_id";
        
        $params = ['emp_id' => $employeeId];

        if ($year) {
            $sql .= " AND YEAR(lr.start_date) = :y";
            $params['y'] = $year;
        }

        $sql .= " ORDER BY lr.start_date DESC";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Lấy danh sách đơn xin nghỉ chờ duyệt
     */
    public function getPending(?int $deptId = null): array
    {
        $sql = "SELECT lr.*, lr.total_days as days, e.emp_code, e.full_name, e.avatar_path,
                       d.dept_name, lt.name as leave_type_name, lt.is_paid, lt.code as leave_type_code
                FROM {$this->table} lr
                JOIN employees e ON lr.employee_id = e.id
                JOIN leave_types lt ON lr.leave_type_id = lt.id
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE lr.`status` = 'Pending'";
        
        $params = [];
        if ($deptId) {
            $sql .= " AND e.department_id = :dept_id";
            $params['dept_id'] = $deptId;
        }

        $sql .= " ORDER BY lr.created_at ASC";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * KIỂM TRA CHỒNG ĐƠN (OVERLAPPING LEAVE REQUEST VALIDATION)
     * Trả về bản ghi đơn trùng lặp nếu phát hiện giao thoa thời gian
     */
    public function checkOverlap(int $employeeId, string $startDate, string $endDate, ?int $excludeId = null): ?array
    {
        $sql = "SELECT lr.*, lt.name as leave_type_name
                FROM {$this->table} lr
                JOIN leave_types lt ON lr.leave_type_id = lt.id
                WHERE lr.employee_id = :emp_id
                  AND lr.status NOT IN ('Rejected', 'Cancelled')
                  AND NOT (lr.end_date < :start OR lr.start_date > :end)";

        $params = [
            'emp_id' => $employeeId,
            'start'  => $startDate,
            'end'    => $endDate
        ];

        if ($excludeId) {
            $sql .= " AND lr.id != :exc_id";
            $params['exc_id'] = $excludeId;
        }

        $sql .= " LIMIT 1";
        $this->db->query($sql, $params);
        $row = $this->db->fetch();
        return $row ?: null;
    }

    /**
     * Lấy danh sách các đơn nghỉ phép đã duyệt để hiển thị lên Lịch (Calendar View)
     */
    public function getApprovedCalendarEvents(int $year, ?int $month = null, ?int $deptId = null): array
    {
        $sql = "SELECT lr.id, lr.start_date, lr.end_date, lr.total_days, lr.reason,
                       e.emp_code, e.full_name, d.dept_name,
                       lt.name as leave_type_name, lt.code as leave_type_code, lt.is_paid
                FROM {$this->table} lr
                JOIN employees e ON lr.employee_id = e.id
                JOIN leave_types lt ON lr.leave_type_id = lt.id
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE lr.status = 'Approved'
                  AND (YEAR(lr.start_date) = :year1 OR YEAR(lr.end_date) = :year2)";

        $params = ['year1' => $year, 'year2' => $year];

        if ($month) {
            $sql .= " AND (MONTH(lr.start_date) = :m1 OR MONTH(lr.end_date) = :m2)";
            $params['m1'] = $month;
            $params['m2'] = $month;
        }

        if ($deptId) {
            $sql .= " AND e.department_id = :dept_id";
            $params['dept_id'] = $deptId;
        }

        $sql .= " ORDER BY lr.start_date ASC";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Phê duyệt đơn xin nghỉ phép
     */
    public function approveRequest(int $id, int $approverId, ?string $note = 'Đồng ý'): bool
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1", ['id' => $id]);
        $req = $this->db->fetch();
        if (!$req) return false;

        $sql = "UPDATE {$this->table} 
                SET status = 'Approved', 
                    approved_by = :approver, 
                    approved_at = NOW(), 
                    approver_note = :note 
                WHERE id = :id";
        
        $ok = $this->db->query($sql, [
            'approver' => $approverId,
            'note'     => $note,
            'id'       => $id
        ]);

        if ($ok) {
            // Tự động đồng bộ số dư phép trong bảng leave_allocations
            $year = (int)date('Y', strtotime($req['start_date']));
            require_once APP_ROOT . '/models/LeaveAllocation.php';
            $allocModel = new LeaveAllocation();
            $allocModel->syncUsedDays((int)$req['employee_id'], (int)$req['leave_type_id'], $year);
        }

        return $ok;
    }

    /**
     * Từ chối đơn xin nghỉ phép
     */
    public function rejectRequest(int $id, int $approverId, ?string $note = 'Từ chối'): bool
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id LIMIT 1", ['id' => $id]);
        $req = $this->db->fetch();
        if (!$req) return false;

        $sql = "UPDATE {$this->table} 
                SET status = 'Rejected', 
                    approved_by = :approver, 
                    approved_at = NOW(), 
                    approver_note = :note 
                WHERE id = :id";

        $ok = $this->db->query($sql, [
            'approver' => $approverId,
            'note'     => $note,
            'id'       => $id
        ]);

        if ($ok) {
            // Đồng bộ lại phòng trường hợp từ chối đơn từng được duyệt
            $year = (int)date('Y', strtotime($req['start_date']));
            require_once APP_ROOT . '/models/LeaveAllocation.php';
            $allocModel = new LeaveAllocation();
            $allocModel->syncUsedDays((int)$req['employee_id'], (int)$req['leave_type_id'], $year);
        }

        return $ok;
    }
}
