<?php
/**
 * ============================================================
 *  POSUNG HRIS – ExpenseClaim Model
 * ============================================================
 *  Quản lý Bảng Quyết toán Chi phí Công tác & Chi phí Dự án
 * ============================================================
 */

require_once APP_ROOT . '/models/BaseModel.php';

class ExpenseClaim extends BaseModel
{
    protected string $table = 'expense_claims';

    /**
     * Lấy danh sách bảng quyết toán chi phí kèm liên kết thông tin
     */
    public function getAllWithRelations(array $filters = []): array
    {
        $sql = "SELECT ec.*,
                       e.full_name AS employee_name,
                       e.emp_code AS employee_code,
                       e.avatar_path,
                       d.dept_name,
                       p.pos_title,
                       prj.project_name,
                       prj.project_code,
                       tr.request_code AS travel_request_code,
                       tr.to_location AS travel_destination,
                       tr.estimated_budget,
                       u.username AS approver_username,
                       ue.full_name AS approver_fullname,
                       up.username AS payer_username,
                       uep.full_name AS payer_fullname,
                       (SELECT COUNT(*) FROM expense_items ei WHERE ei.claim_id = ec.id) AS items_count
                FROM `{$this->table}` ec
                INNER JOIN `employees` e ON ec.employee_id = e.id
                LEFT JOIN `departments` d ON e.department_id = d.id
                LEFT JOIN `positions` p ON e.position_id = p.id
                LEFT JOIN `projects` prj ON ec.project_id = prj.id
                LEFT JOIN `travel_requests` tr ON ec.travel_request_id = tr.id
                LEFT JOIN `users` u ON ec.approved_by = u.id
                LEFT JOIN `employees` ue ON u.employee_id = ue.id
                LEFT JOIN `users` up ON ec.paid_by = up.id
                LEFT JOIN `employees` uep ON up.employee_id = uep.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (ec.claim_code LIKE :search OR e.full_name LIKE :search2 OR e.emp_code LIKE :search3 OR ec.title LIKE :search4)";
            $params['search']  = "%{$filters['search']}%";
            $params['search2'] = "%{$filters['search']}%";
            $params['search3'] = "%{$filters['search']}%";
            $params['search4'] = "%{$filters['search']}%";
        }

        if (!empty($filters['status'])) {
            $sql .= " AND ec.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['category'])) {
            $sql .= " AND ec.category = :category";
            $params['category'] = $filters['category'];
        }

        if (!empty($filters['project_id'])) {
            $sql .= " AND ec.project_id = :project_id";
            $params['project_id'] = (int)$filters['project_id'];
        }

        if (!empty($filters['employee_id'])) {
            $sql .= " AND ec.employee_id = :employee_id";
            $params['employee_id'] = (int)$filters['employee_id'];
        }

        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = :department_id";
            $params['department_id'] = (int)$filters['department_id'];
        }

        if (!empty($filters['from_date'])) {
            $sql .= " AND ec.submitted_date >= :from_date";
            $params['from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $sql .= " AND ec.submitted_date <= :to_date";
            $params['to_date'] = $filters['to_date'];
        }

        $sql .= " ORDER BY ec.id DESC";

        $this->db->query($sql, $params);
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Lấy chi tiết 1 bảng quyết toán kèm danh sách các hạng mục chi (items)
     */
    public function getByIdWithDetails(int $id): ?object
    {
        $sql = "SELECT ec.*,
                       e.full_name AS employee_name,
                       e.emp_code AS employee_code,
                       e.avatar_path,
                       e.phone AS employee_phone,
                       e.email AS employee_email,
                       e.bank_account_no,
                       e.bank_name,
                       e.bank_branch,
                       d.dept_name,
                       p.pos_title,
                       prj.project_name,
                       prj.project_code,
                       tr.request_code AS travel_request_code,
                       tr.from_location,
                       tr.to_location,
                       tr.departure_date,
                       tr.return_date,
                       tr.estimated_budget,
                       tr.advance_amount,
                       u.username AS approver_username,
                       ue.full_name AS approver_fullname,
                       up.username AS payer_username,
                       uep.full_name AS payer_fullname
                FROM `{$this->table}` ec
                INNER JOIN `employees` e ON ec.employee_id = e.id
                LEFT JOIN `departments` d ON e.department_id = d.id
                LEFT JOIN `positions` p ON e.position_id = p.id
                LEFT JOIN `projects` prj ON ec.project_id = prj.id
                LEFT JOIN `travel_requests` tr ON ec.travel_request_id = tr.id
                LEFT JOIN `users` u ON ec.approved_by = u.id
                LEFT JOIN `employees` ue ON u.employee_id = ue.id
                LEFT JOIN `users` up ON ec.paid_by = up.id
                LEFT JOIN `employees` uep ON up.employee_id = uep.id
                WHERE ec.id = :id
                LIMIT 1";

        $this->db->query($sql, ['id' => $id]);
        $row = $this->db->fetch();
        if (!$row) {
            return null;
        }

        $claim = (object)$row;

        // Lấy danh sách các khoản chi chi tiết
        require_once APP_ROOT . '/models/ExpenseItem.php';
        $itemModel = new ExpenseItem();
        $claim->items = $itemModel->getByClaimId($id);

        return $claim;
    }

    /**
     * Lấy tất cả bảng quyết toán của 1 nhân viên
     */
    public function getByEmployee(int $employeeId): array
    {
        $sql = "SELECT ec.*, prj.project_name, prj.project_code, tr.request_code as travel_request_code
                FROM `{$this->table}` ec
                LEFT JOIN `projects` prj ON ec.project_id = prj.id
                LEFT JOIN `travel_requests` tr ON ec.travel_request_id = tr.id
                WHERE ec.employee_id = :employee_id
                ORDER BY ec.submitted_date DESC, ec.id DESC";
        $this->db->query($sql, ['employee_id' => $employeeId]);
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Lấy các bảng quyết toán thuộc chuyến công tác
     */
    public function getByTravelRequest(int $travelRequestId): array
    {
        $sql = "SELECT ec.*, 
                       (SELECT COUNT(*) FROM expense_items ei WHERE ei.claim_id = ec.id) as items_count
                FROM `{$this->table}` ec
                WHERE ec.travel_request_id = :tr_id
                ORDER BY ec.id ASC";
        $this->db->query($sql, ['tr_id' => $travelRequestId]);
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Tự động sinh mã bảng quyết toán (VD: EXP-2026-0003)
     */
    public function generateClaimCode(): string
    {
        $year = date('Y');
        $prefix = "EXP-{$year}-";

        $this->db->query(
            "SELECT claim_code FROM `{$this->table}` 
             WHERE claim_code LIKE :prefix 
             ORDER BY id DESC LIMIT 1",
            ['prefix' => "{$prefix}%"]
        );
        $latest = $this->db->fetch();

        if ($latest && preg_match('/EXP-\d{4}-(\d+)/', $latest['claim_code'], $m)) {
            $nextNum = (int)$m[1] + 1;
        } else {
            $nextNum = 1;
        }

        return sprintf('%s%04d', $prefix, $nextNum);
    }

    /**
     * Phê duyệt quyết toán chi phí
     */
    public function approve(int $id, int $approverId): bool
    {
        $claim = $this->find($id);
        if (!$claim || $claim->status !== 'Submitted') {
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
     * Từ chối quyết toán
     */
    public function reject(int $id, int $approverId, string $reason): bool
    {
        $claim = $this->find($id);
        if (!$claim || $claim->status !== 'Submitted') {
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
     * Đánh dấu đã chi trả / thanh toán tiền quyết toán
     */
    public function markPaid(int $id, int $payerId, string $paidDate, string $method = 'Bank Transfer'): bool
    {
        $claim = $this->find($id);
        if (!$claim || $claim->status !== 'Approved') {
            return false;
        }

        $this->db->query(
            "UPDATE `{$this->table}` 
             SET `status` = 'Paid',
                 `paid_date` = :paid_date,
                 `paid_by` = :payer_id,
                 `payment_method` = :method
             WHERE `id` = :id",
            [
                'id'        => $id,
                'paid_date' => $paidDate,
                'payer_id'  => $payerId,
                'method'    => $method
            ]
        );

        return true;
    }

    /**
     * Thống kê tổng hợp số liệu quyết toán
     */
    public function getStats(): array
    {
        $this->db->query(
            "SELECT 
                COUNT(*) AS total_claims,
                COALESCE(SUM(total_amount), 0) AS total_amount,
                COALESCE(SUM(advance_deducted), 0) AS total_advance_deducted,
                COALESCE(SUM(net_payable), 0) AS total_net_payable,
                SUM(CASE WHEN status = 'Submitted' THEN 1 ELSE 0 END) AS submitted_count,
                SUM(CASE WHEN status = 'Approved' THEN 1 ELSE 0 END) AS approved_count,
                SUM(CASE WHEN status = 'Paid' THEN 1 ELSE 0 END) AS paid_count,
                SUM(CASE WHEN status = 'Rejected' THEN 1 ELSE 0 END) AS rejected_count
             FROM `{$this->table}`"
        );
        $row = $this->db->fetch();
        return $row ?: [
            'total_claims'           => 0,
            'total_amount'           => 0,
            'total_advance_deducted' => 0,
            'total_net_payable'      => 0,
            'submitted_count'        => 0,
            'approved_count'         => 0,
            'paid_count'             => 0,
            'rejected_count'         => 0,
        ];
    }

    /**
     * Báo cáo phân bổ chi phí theo Dự án
     */
    public function getSummaryByProject(): array
    {
        $sql = "SELECT prj.id AS project_id,
                       COALESCE(prj.project_name, 'Chi phí Văn phòng / Chung') AS project_name,
                       prj.project_code,
                       COUNT(ec.id) AS claims_count,
                       COALESCE(SUM(ec.total_amount), 0) AS total_amount,
                       COALESCE(SUM(ec.net_payable), 0) AS total_net_payable
                FROM `{$this->table}` ec
                LEFT JOIN `projects` prj ON ec.project_id = prj.id
                WHERE ec.status IN ('Approved', 'Paid')
                GROUP BY prj.id, prj.project_name, prj.project_code
                ORDER BY total_amount DESC";
        $this->db->query($sql);
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Báo cáo phân bổ chi phí theo Danh mục chi phí (Transport, Hotel, Meal, Fuel, Material...)
     */
    public function getSummaryByCategory(): array
    {
        $sql = "SELECT ei.category,
                       COUNT(ei.id) AS item_count,
                       COALESCE(SUM(ei.amount), 0) AS total_amount
                FROM `expense_items` ei
                INNER JOIN `expense_claims` ec ON ei.claim_id = ec.id
                WHERE ec.status IN ('Approved', 'Paid')
                GROUP BY ei.category
                ORDER BY total_amount DESC";
        $this->db->query($sql);
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Báo cáo phân bổ chi phí theo Phòng ban
     */
    public function getSummaryByDepartment(): array
    {
        $sql = "SELECT d.id AS dept_id,
                       COALESCE(d.dept_name, 'Chưa phân bổ') AS dept_name,
                       COUNT(ec.id) AS claims_count,
                       COALESCE(SUM(ec.total_amount), 0) AS total_amount
                FROM `expense_claims` ec
                INNER JOIN `employees` e ON ec.employee_id = e.id
                LEFT JOIN `departments` d ON e.department_id = d.id
                WHERE ec.status IN ('Approved', 'Paid')
                GROUP BY d.id, d.dept_name
                ORDER BY total_amount DESC";
        $this->db->query($sql);
        return json_decode(json_encode($this->db->fetchAll()));
    }
}
