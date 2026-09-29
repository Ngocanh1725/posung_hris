<?php
/**
 * ============================================================
 *  POSUNG HRIS – Workflow & Approval Model
 * ============================================================
 *  Quản lý dữ liệu cho Động cơ Phê duyệt (Approval Engine)
 *  - Quản lý quy trình duyệt (approval_flows)
 *  - Quản lý yêu cầu phê duyệt đa phân hệ (approval_requests)
 *  - Quản lý yêu cầu sửa hồ sơ (profile_change_requests)
 *  - Tương thích ngược với các yêu cầu E-Sign & Kanban cũ
 * ============================================================
 */

class Workflow extends BaseModel
{
    /**
     * Lấy danh sách tất cả các quy trình duyệt (Approval Flows)
     */
    public function getFlows(): array
    {
        $this->db->query("SELECT * FROM approval_flows ORDER BY id ASC");
        $flows = $this->db->fetchAll();

        foreach ($flows as &$flow) {
            $flow['steps_array'] = !empty($flow['steps']) ? json_decode($flow['steps'], true) : [];
            $flow['total_steps'] = is_array($flow['steps_array']) ? count($flow['steps_array']) : 0;
        }

        return $flows;
    }

    /**
     * Lấy thông tin một flow theo ID
     */
    public function getFlowById(int $id): ?array
    {
        $this->db->query("SELECT * FROM approval_flows WHERE id = :id LIMIT 1", ['id' => $id]);
        $flow = $this->db->fetch();
        if ($flow) {
            $flow['steps_array'] = !empty($flow['steps']) ? json_decode($flow['steps'], true) : [];
        }
        return $flow ?: null;
    }

    /**
     * Lưu hoặc cập nhật quy trình duyệt
     */
    public function saveFlow(array $data): int
    {
        $name = trim($data['name'] ?? '');
        $module = trim($data['module'] ?? '');
        $description = trim($data['description'] ?? '');
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;
        $steps = is_array($data['steps'] ?? null) ? json_encode($data['steps'], JSON_UNESCAPED_UNICODE) : ($data['steps'] ?? '[]');

        if (!empty($data['id'])) {
            $this->db->query(
                "UPDATE approval_flows 
                 SET name = :name, module = :mod, description = :desc, steps = :steps, is_active = :act, updated_at = NOW() 
                 WHERE id = :id",
                [
                    'name'  => $name,
                    'mod'   => $module,
                    'desc'  => $description,
                    'steps' => $steps,
                    'act'   => $isActive,
                    'id'    => (int)$data['id']
                ]
            );
            return (int)$data['id'];
        } else {
            $this->db->query(
                "INSERT INTO approval_flows (name, module, description, steps, is_active, created_at)
                 VALUES (:name, :mod, :desc, :steps, :act, NOW())",
                [
                    'name'  => $name,
                    'mod'   => $module,
                    'desc'  => $description,
                    'steps' => $steps,
                    'act'   => $isActive
                ]
            );
            return (int)$this->db->lastInsertId();
        }
    }

    /**
     * Xóa một quy trình duyệt
     */
    public function deleteFlow(int $id): bool
    {
        $this->db->query("DELETE FROM approval_flows WHERE id = :id", ['id' => $id]);
        return true;
    }

    /**
     * Lấy danh sách các yêu cầu đang chờ duyệt (Hộp duyệt tập trung)
     */
    public function getPendingApprovals(?string $module = null, ?int $userId = null): array
    {
        return WorkflowService::getPendingApprovals($module, $userId);
    }

    /**
     * Thống kê số lượng đơn chờ duyệt theo module
     */
    public function getPendingCounts(): array
    {
        return WorkflowService::getPendingCounts();
    }

    /**
     * Lấy danh sách yêu cầu thay đổi hồ sơ (Profile Change Requests)
     */
    public function getProfileChanges(?string $status = null, ?int $empId = null): array
    {
        return WorkflowService::getProfileChanges($status, $empId);
    }

    /**
     * Lấy chi tiết một yêu cầu thay đổi hồ sơ
     */
    public function getProfileChangeById(int $id): ?array
    {
        $this->db->query(
            "SELECT pcr.*, 
                    e.emp_code, e.full_name as employee_name, e.avatar_path,
                    d.dept_name, pos.pos_title,
                    u_req.username as requester_name,
                    IFNULL(e_rev.full_name, u_rev.username) as reviewer_name,
                    ar.id as approval_request_id,
                    ar.current_level
             FROM profile_change_requests pcr
             JOIN employees e ON pcr.employee_id = e.id
             LEFT JOIN departments d ON e.department_id = d.id
             LEFT JOIN positions pos ON e.position_id = pos.id
             LEFT JOIN users u_req ON pcr.requested_by = u_req.id
             LEFT JOIN users u_rev ON pcr.reviewed_by = u_rev.id
             LEFT JOIN employees e_rev ON u_rev.employee_id = e_rev.id
             LEFT JOIN approval_requests ar ON (ar.module = 'profile_change' AND ar.record_id = pcr.id)
             WHERE pcr.id = :id LIMIT 1",
            ['id' => $id]
        );
        $res = $this->db->fetch();
        return $res ?: null;
    }

    /**
     * Lấy danh sách yêu cầu do người dùng tạo
     */
    public function getMyRequests(int $empId): array
    {
        // 1. Lấy từ approval_requests
        $this->db->query("
            SELECT ar.*, 
                   af.name as flow_name,
                   af.steps as flow_steps,
                   u.username as creator_name
            FROM approval_requests ar
            LEFT JOIN approval_flows af ON ar.flow_id = af.id
            LEFT JOIN users u ON ar.created_by = u.id
            WHERE u.employee_id = :e OR ar.created_by = (SELECT id FROM users WHERE employee_id = :e2 LIMIT 1)
            ORDER BY ar.id DESC
            LIMIT 50
        ", ['e' => $empId, 'e2' => $empId]);
        $reqs = $this->db->fetchAll();

        foreach ($reqs as &$r) {
            $r['details'] = WorkflowService::getRequestDetails($r['module'], (int)$r['record_id']);
        }

        // Nếu chưa có trong approval_requests thì fallback sang bảng requests cũ
        if (empty($reqs)) {
            $this->db->query("
                SELECT r.*, w.name as workflow_name, w.code as workflow_code 
                FROM requests r
                JOIN workflows w ON r.workflow_id = w.id
                WHERE r.emp_id = :e
                ORDER BY r.id DESC
                LIMIT 50
            ", ['e' => $empId]);
            return $this->db->fetchAll();
        }

        return $reqs;
    }

    /**
     * Lịch sử phê duyệt của một yêu cầu
     */
    public function getApprovalActions(int $requestId): array
    {
        $this->db->query(
            "SELECT aa.*, IFNULL(e.full_name, u.username) as user_name, u.username 
             FROM approval_actions aa
             JOIN users u ON aa.user_id = u.id
             LEFT JOIN employees e ON u.employee_id = e.id
             WHERE aa.request_id = :rid
             ORDER BY aa.id ASC",
            ['rid' => $requestId]
        );
        return $this->db->fetchAll();
    }

    /**
     * Kiểm tra mã PIN E-Sign
     */
    public function verifyEsignPin(int $userId, string $pin): bool
    {
        $this->db->query("SELECT esign_pin FROM users WHERE id = :uid LIMIT 1", ['uid' => $userId]);
        $user = $this->db->fetch();
        $validPin = ($user && !empty($user['esign_pin'])) ? $user['esign_pin'] : 'posung@123';
        return ($pin === $validPin);
    }
}
