<?php
/**
 * ============================================================
 *  POSUNG HRIS – Reward & Discipline Model (Upgraded)
 * ============================================================
 *  Quản lý Khen thưởng và Kỷ luật đầy đủ quy trình:
 *  - CRUD với trường thông tin mở rộng
 *  - Lọc đa tiêu chí
 *  - Quy trình phê duyệt
 *  - Thống kê báo cáo
 *  - In quyết định
 *  - HSE Blacklist
 * ============================================================
 */

class RewardDiscipline extends BaseModel
{
    protected string $table = 'rewards_disciplines';

    /**
     * Lấy danh sách khen thưởng / kỷ luật (có lọc)
     */
    public function getAllRecords(array $filters = []): array
    {
        $sql = "SELECT rd.*, e.emp_code, e.full_name, e.status as emp_status, 
                       p.pos_title, d.dept_name, proj.project_name,
                       u.username AS approver_name
                FROM rewards_disciplines rd
                JOIN employees e ON rd.employee_id = e.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN departments d ON COALESCE(rd.department_id, e.department_id) = d.id
                LEFT JOIN projects proj ON COALESCE(rd.project_id, e.current_project_id) = proj.id
                LEFT JOIN users u ON rd.approved_by = u.id
                WHERE 1=1";
        
        $params = [];

        // Lọc theo loại (Reward/Discipline)
        if (!empty($filters['type'])) {
            $sql .= " AND rd.type = :type";
            $params['type'] = $filters['type'];
        }

        // Lọc theo trạng thái QĐ
        if (!empty($filters['status'])) {
            $sql .= " AND rd.status = :status";
            $params['status'] = $filters['status'];
        }

        // Lọc theo phòng ban
        if (!empty($filters['department_id'])) {
            $sql .= " AND (rd.department_id = :dept OR e.department_id = :dept2)";
            $params['dept'] = $filters['department_id'];
            $params['dept2'] = $filters['department_id'];
        }

        // Lọc theo dự án
        if (!empty($filters['project_id'])) {
            $sql .= " AND (rd.project_id = :proj OR e.current_project_id = :proj2)";
            $params['proj'] = $filters['project_id'];
            $params['proj2'] = $filters['project_id'];
        }

        // Lọc theo thời gian
        if (!empty($filters['from_date'])) {
            $sql .= " AND rd.decision_date >= :from_date";
            $params['from_date'] = $filters['from_date'];
        }
        if (!empty($filters['to_date'])) {
            $sql .= " AND rd.decision_date <= :to_date";
            $params['to_date'] = $filters['to_date'];
        }

        // Lọc theo từ khóa
        if (!empty($filters['search'])) {
            $sql .= " AND (e.full_name LIKE :search OR e.emp_code LIKE :search2 
                          OR rd.title LIKE :search3 OR rd.decision_number LIKE :search4)";
            $params['search']  = "%{$filters['search']}%";
            $params['search2'] = "%{$filters['search']}%";
            $params['search3'] = "%{$filters['search']}%";
            $params['search4'] = "%{$filters['search']}%";
        }

        // Lọc chỉ vi phạm HSE
        if (!empty($filters['safety_only'])) {
            $sql .= " AND rd.is_safety_violation = 1";
        }

        $sql .= " ORDER BY rd.decision_date DESC, rd.created_at DESC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Lấy chi tiết 1 quyết định
     */
    public function getRecordById(int $id): ?object
    {
        $sql = "SELECT rd.*, e.emp_code, e.full_name, e.status as emp_status, e.dob, e.phone,
                       e.id_card_no, e.join_date, e.employee_type,
                       p.pos_title, d.dept_name, d.dept_code,
                       proj.project_name, proj.project_code,
                       u.username AS approver_name
                FROM rewards_disciplines rd
                JOIN employees e ON rd.employee_id = e.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN departments d ON COALESCE(rd.department_id, e.department_id) = d.id
                LEFT JOIN projects proj ON COALESCE(rd.project_id, e.current_project_id) = proj.id
                LEFT JOIN users u ON rd.approved_by = u.id
                WHERE rd.id = :id";

        $this->db->query($sql, ['id' => $id]);
        $row = $this->db->fetch();
        return $row ? (object)$row : null;
    }

    /**
     * Tạo quyết định khen thưởng / kỷ luật (Nâng cấp đầy đủ trường)
     */
    public function createRecord(array $data): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO rewards_disciplines 
                    (employee_id, department_id, project_id, type, reward_form, discipline_form,
                     decision_number, decision_date, title, amount, reason, 
                     is_safety_violation, authority_level, proposed_by, status)
                    VALUES 
                    (:emp, :dept, :proj, :type, :reward_form, :discipline_form,
                     :dnum, :ddate, :title, :amount, :reason, 
                     :safety, :authority, :proposed_by, :status)";
            
            $this->db->query($sql, [
                'emp'             => $data['employee_id'],
                'dept'            => $data['department_id'] ?? null,
                'proj'            => $data['project_id'] ?? null,
                'type'            => $data['type'],
                'reward_form'     => $data['reward_form'] ?? null,
                'discipline_form' => $data['discipline_form'] ?? null,
                'dnum'            => $data['decision_number'] ?? null,
                'ddate'           => $data['decision_date'] ?? null,
                'title'           => $data['title'],
                'amount'          => $data['amount'] ?? 0,
                'reason'          => $data['reason'] ?? '',
                'safety'          => $data['is_safety_violation'] ?? 0,
                'authority'       => $data['authority_level'] ?? null,
                'proposed_by'     => $data['proposed_by'] ?? null,
                'status'          => $data['status'] ?? 'Approved'
            ]);

            // Nếu là Kỷ luật và Vi phạm an toàn -> Cập nhật trạng thái NV thành Blacklisted
            if ($data['type'] === 'Discipline' && !empty($data['is_safety_violation'])) {
                // Đổi trạng thái nhân sự
                $updateEmpSql = "UPDATE employees SET status = 'Blacklisted', 
                                 notes = CONCAT(IFNULL(notes,''), '\n[BLACKLISTED] Vi phạm HSE: ', :title) 
                                 WHERE id = :emp_id";
                $this->db->query($updateEmpSql, [
                    'title'  => $data['title'],
                    'emp_id' => $data['employee_id']
                ]);
                
                // Lấy CCCD và Tên để chèn vào hse_blacklists
                $this->db->query("SELECT id_card_no, full_name FROM employees WHERE id = :emp_id", ['emp_id' => $data['employee_id']]);
                $empInfo = $this->db->fetch();
                if ($empInfo && !empty($empInfo['id_card_no'])) {
                    // Chèn hoặc bỏ qua nếu đã tồn tại
                    $insertBlSql = "INSERT IGNORE INTO hse_blacklists (id_card_no, full_name, reason, created_at)
                                    VALUES (:ic, :fn, :rs, NOW())";
                    $this->db->query($insertBlSql, [
                        'ic' => $empInfo['id_card_no'],
                        'fn' => $empInfo['full_name'],
                        'rs' => "Vi phạm HSE: " . $data['title']
                    ]);
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Lỗi tạo quyết định KTKL: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Phê duyệt quyết định
     */
    public function approveRecord(int $id, int $approverId): bool
    {
        try {
            $this->db->query(
                "UPDATE rewards_disciplines 
                 SET status = 'Approved', approved_by = :approver, approved_date = CURDATE()
                 WHERE id = :id AND status IN ('Draft','Pending')",
                ['approver' => $approverId, 'id' => $id]
            );
            return $this->db->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Lỗi phê duyệt KTKL: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Từ chối quyết định
     */
    public function rejectRecord(int $id, int $approverId): bool
    {
        try {
            $this->db->query(
                "UPDATE rewards_disciplines 
                 SET status = 'Rejected', approved_by = :approver, approved_date = CURDATE()
                 WHERE id = :id AND status IN ('Draft','Pending')",
                ['approver' => $approverId, 'id' => $id]
            );
            return $this->db->rowCount() > 0;
        } catch (Exception $e) {
            error_log("Lỗi từ chối KTKL: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Thống kê khen thưởng / kỷ luật
     */
    public function getStatistics(array $filters = []): array
    {
        $stats = [];

        // Tổng số theo loại
        $sql = "SELECT type, COUNT(*) as total, SUM(amount) as total_amount 
                FROM rewards_disciplines WHERE 1=1";
        $params = [];
        if (!empty($filters['year'])) {
            $sql .= " AND YEAR(decision_date) = :year";
            $params['year'] = $filters['year'];
        }
        $sql .= " GROUP BY type";
        $this->db->query($sql, $params);
        $stats['by_type'] = $this->db->fetchAll();

        // Theo tháng trong năm
        $year = $filters['year'] ?? date('Y');
        $this->db->query(
            "SELECT MONTH(decision_date) as month, type, COUNT(*) as total, SUM(amount) as total_amount
             FROM rewards_disciplines 
             WHERE YEAR(decision_date) = :year
             GROUP BY MONTH(decision_date), type
             ORDER BY month",
            ['year' => $year]
        );
        $stats['by_month'] = $this->db->fetchAll();

        // Theo phòng ban (Top 10)
        $this->db->query(
            "SELECT d.dept_name, rd.type, COUNT(*) as total
             FROM rewards_disciplines rd
             JOIN employees e ON rd.employee_id = e.id
             LEFT JOIN departments d ON COALESCE(rd.department_id, e.department_id) = d.id
             WHERE YEAR(rd.decision_date) = :year
             GROUP BY d.id, rd.type
             ORDER BY total DESC
             LIMIT 10",
            ['year' => $year]
        );
        $stats['by_department'] = $this->db->fetchAll();

        // Theo hình thức khen thưởng
        $this->db->query(
            "SELECT reward_form, COUNT(*) as total, SUM(amount) as total_amount
             FROM rewards_disciplines 
             WHERE type = 'Reward' AND YEAR(decision_date) = :year AND reward_form IS NOT NULL
             GROUP BY reward_form
             ORDER BY total DESC",
            ['year' => $year]
        );
        $stats['reward_forms'] = $this->db->fetchAll();

        // Theo hình thức kỷ luật
        $this->db->query(
            "SELECT discipline_form, COUNT(*) as total, SUM(is_safety_violation) as safety_count
             FROM rewards_disciplines 
             WHERE type = 'Discipline' AND YEAR(decision_date) = :year AND discipline_form IS NOT NULL
             GROUP BY discipline_form
             ORDER BY total DESC",
            ['year' => $year]
        );
        $stats['discipline_forms'] = $this->db->fetchAll();

        // Tổng số vi phạm HSE
        $this->db->query(
            "SELECT COUNT(*) as total FROM rewards_disciplines 
             WHERE is_safety_violation = 1 AND YEAR(decision_date) = :year",
            ['year' => $year]
        );
        $stats['hse_violations'] = $this->db->fetch()['total'] ?? 0;

        return $stats;
    }
}
