<?php
/**
 * ============================================================
 *  POSUNG HRIS – TravelRequest Model
 * ============================================================
 *  Quản lý Đề xuất & Lịch trình Công tác Cán bộ Nhân viên
 * ============================================================
 */

require_once APP_ROOT . '/models/BaseModel.php';

class TravelRequest extends BaseModel
{
    protected string $table = 'travel_requests';

    /**
     * Lấy danh sách đề xuất công tác kèm liên kết thông tin nhân viên, dự án, người duyệt
     */
    public function getAllWithRelations(array $filters = []): array
    {
        $sql = "SELECT tr.*,
                       e.full_name AS employee_name,
                       e.emp_code AS employee_code,
                       e.avatar_path,
                       d.dept_name,
                       p.pos_title,
                       prj.project_name,
                       prj.project_code,
                       u.username AS approver_username,
                       ue.full_name AS approver_fullname,
                       (SELECT COUNT(*) FROM expense_claims ec WHERE ec.travel_request_id = tr.id) AS claims_count,
                       (SELECT COALESCE(SUM(ec.total_amount), 0) FROM expense_claims ec WHERE ec.travel_request_id = tr.id) AS claimed_amount
                FROM `{$this->table}` tr
                INNER JOIN `employees` e ON tr.employee_id = e.id
                LEFT JOIN `departments` d ON e.department_id = d.id
                LEFT JOIN `positions` p ON e.position_id = p.id
                LEFT JOIN `projects` prj ON tr.project_id = prj.id
                LEFT JOIN `users` u ON tr.approved_by = u.id
                LEFT JOIN `employees` ue ON u.employee_id = ue.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (tr.request_code LIKE :search OR e.full_name LIKE :search2 OR e.emp_code LIKE :search3 OR tr.purpose LIKE :search4 OR tr.to_location LIKE :search5)";
            $params['search']  = "%{$filters['search']}%";
            $params['search2'] = "%{$filters['search']}%";
            $params['search3'] = "%{$filters['search']}%";
            $params['search4'] = "%{$filters['search']}%";
            $params['search5'] = "%{$filters['search']}%";
        }

        if (!empty($filters['status'])) {
            $sql .= " AND tr.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['project_id'])) {
            $sql .= " AND tr.project_id = :project_id";
            $params['project_id'] = (int)$filters['project_id'];
        }

        if (!empty($filters['employee_id'])) {
            $sql .= " AND tr.employee_id = :employee_id";
            $params['employee_id'] = (int)$filters['employee_id'];
        }

        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = :department_id";
            $params['department_id'] = (int)$filters['department_id'];
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND tr.departure_date >= :from_date";
            $params['from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND tr.return_date <= :to_date";
            $params['to_date'] = $filters['to_date'];
        }

        $sql .= " ORDER BY tr.id DESC";

        $this->db->query($sql, $params);
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Lấy chi tiết 1 đề xuất công tác kèm các bảng quyết toán liên quan
     */
    public function getByIdWithDetails(int $id): ?object
    {
        $sql = "SELECT tr.*,
                       e.full_name AS employee_name,
                       e.emp_code AS employee_code,
                       e.avatar_path,
                       e.phone AS employee_phone,
                       e.email AS employee_email,
                       d.dept_name,
                       p.pos_title,
                       prj.project_name,
                       prj.project_code,
                       prj.location AS project_location,
                       u.username AS approver_username,
                       ue.full_name AS approver_fullname
                FROM `{$this->table}` tr
                INNER JOIN `employees` e ON tr.employee_id = e.id
                LEFT JOIN `departments` d ON e.department_id = d.id
                LEFT JOIN `positions` p ON e.position_id = p.id
                LEFT JOIN `projects` prj ON tr.project_id = prj.id
                LEFT JOIN `users` u ON tr.approved_by = u.id
                LEFT JOIN `employees` ue ON u.employee_id = ue.id
                WHERE tr.id = :id
                LIMIT 1";

        $this->db->query($sql, ['id' => $id]);
        $row = $this->db->fetch();
        if (!$row) {
            return null;
        }

        $travel = (object)$row;

        // Lấy danh sách các bảng quyết toán phát sinh từ đợt công tác này
        require_once APP_ROOT . '/models/ExpenseClaim.php';
        $claimModel = new ExpenseClaim();
        $travel->claims = $claimModel->getByTravelRequest($id);

        return $travel;
    }

    /**
     * Lấy danh sách các chuyến công tác của 1 nhân viên
     */
    public function getByEmployee(int $employeeId): array
    {
        $sql = "SELECT tr.*, prj.project_name, prj.project_code
                FROM `{$this->table}` tr
                LEFT JOIN `projects` prj ON tr.project_id = prj.id
                WHERE tr.employee_id = :employee_id
                ORDER BY tr.departure_date DESC, tr.id DESC";
        $this->db->query($sql, ['employee_id' => $employeeId]);
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Tự động sinh mã đề xuất công tác (VD: TR-2026-0004)
     */
    public function generateRequestCode(): string
    {
        $year = date('Y');
        $prefix = "TR-{$year}-";

        $this->db->query(
            "SELECT request_code FROM `{$this->table}` 
             WHERE request_code LIKE :prefix 
             ORDER BY id DESC LIMIT 1",
            ['prefix' => "{$prefix}%"]
        );
        $latest = $this->db->fetch();

        if ($latest && preg_match('/TR-\d{4}-(\d+)/', $latest['request_code'], $m)) {
            $nextNum = (int)$m[1] + 1;
        } else {
            $nextNum = 1;
        }

        return sprintf('%s%04d', $prefix, $nextNum);
    }

    /**
     * Phê duyệt đề xuất công tác
     */
    public function approve(int $id, int $approverId): bool
    {
        $req = $this->find($id);
        if (!$req || $req->status !== 'Pending') {
            return false;
        }

        $this->db->query(
            "UPDATE `{$this->table}` 
             SET `status` = 'Approved',
                 `approved_by` = :approver_id,
                 `approved_date` = NOW()
             WHERE `id` = :id",
            ['id' => $id, 'approver_id' => $approverId]
        );

        return true;
    }

    /**
     * Từ chối đề xuất công tác
     */
    public function reject(int $id, int $approverId, string $reason): bool
    {
        $req = $this->find($id);
        if (!$req || $req->status !== 'Pending') {
            return false;
        }

        $this->db->query(
            "UPDATE `{$this->table}` 
             SET `status` = 'Rejected',
                 `approved_by` = :approver_id,
                 `approved_date` = NOW(),
                 `rejected_reason` = :reason
             WHERE `id` = :id",
            ['id' => $id, 'approver_id' => $approverId, 'reason' => $reason]
        );

        return true;
    }

    /**
     * Đánh dấu đã hoàn thành công tác
     */
    public function complete(int $id): bool
    {
        $this->db->query(
            "UPDATE `{$this->table}` SET `status` = 'Completed' WHERE `id` = :id",
            ['id' => $id]
        );
        return true;
    }

    /**
     * Thống kê tổng quan đề xuất công tác
     */
    public function getStats(): array
    {
        $this->db->query(
            "SELECT 
                COUNT(*) AS total_requests,
                COALESCE(SUM(estimated_budget), 0) AS total_budget,
                COALESCE(SUM(advance_amount), 0) AS total_advance,
                SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) AS pending_count,
                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved_count,
                SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) AS completed_count,
                SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected_count
             FROM `{$this->table}`"
        );
        $row = $this->db->fetch();
        return $row ?: [
            'total_requests'  => 0,
            'total_budget'    => 0,
            'total_advance'   => 0,
            'pending_count'   => 0,
            'approved_count'  => 0,
            'completed_count' => 0,
            'rejected_count'  => 0,
        ];
    }
}
