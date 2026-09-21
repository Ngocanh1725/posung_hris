<?php
/**
 * ============================================================
 *  POSUNG HRIS – Recruitment Model
 * ============================================================
 *  Quản lý Yêu cầu Tuyển dụng và Hồ sơ Ứng viên.
 *  - CRUD Yêu cầu tuyển dụng
 *  - CRUD Hồ sơ ứng viên
 *  - Quy trình phỏng vấn
 *  - Chuyển đổi ứng viên → nhân viên
 *  - Thống kê tuyển dụng
 * ============================================================
 */

class Recruitment extends BaseModel
{
    protected string $table = 'recruitment_requests';

    /**
     * Sinh mã yêu cầu tuyển dụng: TD-YYYY-XXXX
     */
    public function generateRequestCode(): string
    {
        $year = date('Y');
        $prefix = "TD-{$year}-";
        $this->db->query(
            "SELECT request_code FROM recruitment_requests WHERE request_code LIKE :prefix ORDER BY request_code DESC LIMIT 1",
            ['prefix' => "{$prefix}%"]
        );
        $last = $this->db->fetch();
        $num = $last ? ((int)substr($last['request_code'], -4) + 1) : 1;
        return $prefix . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
    }

    // ══════════════════════════════════════════════════════════
    //  YÊU CẦU TUYỂN DỤNG
    // ══════════════════════════════════════════════════════════

    /**
     * Danh sách yêu cầu tuyển dụng (có lọc)
     */
    public function getRequests(array $filters = []): array
    {
        $sql = "SELECT rr.*, d.dept_name, d.dept_code, p.pos_title, proj.project_name,
                       u1.full_name AS requester_name, u2.full_name AS approver_name,
                       (SELECT COUNT(*) FROM candidates c WHERE c.request_id = rr.id) AS candidate_count
                FROM recruitment_requests rr
                LEFT JOIN departments d ON rr.department_id = d.id
                LEFT JOIN positions p ON rr.position_id = p.id
                LEFT JOIN projects proj ON rr.project_id = proj.id
                LEFT JOIN users u1 ON rr.requested_by = u1.id
                LEFT JOIN users u2 ON rr.approved_by = u2.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND rr.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['department_id'])) {
            $sql .= " AND rr.department_id = :dept";
            $params['dept'] = $filters['department_id'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (rr.request_code LIKE :s1 OR rr.description LIKE :s2)";
            $params['s1'] = "%{$filters['search']}%";
            $params['s2'] = "%{$filters['search']}%";
        }

        $sql .= " ORDER BY rr.created_at DESC";
        $this->db->query($sql, $params);
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Chi tiết 1 yêu cầu tuyển dụng
     */
    public function getRequestById(int $id): ?object
    {
        $sql = "SELECT rr.*, d.dept_name, d.dept_code, p.pos_title, proj.project_name,
                       u1.full_name AS requester_name, u2.full_name AS approver_name
                FROM recruitment_requests rr
                LEFT JOIN departments d ON rr.department_id = d.id
                LEFT JOIN positions p ON rr.position_id = p.id
                LEFT JOIN projects proj ON rr.project_id = proj.id
                LEFT JOIN users u1 ON rr.requested_by = u1.id
                LEFT JOIN users u2 ON rr.approved_by = u2.id
                WHERE rr.id = :id";
        $this->db->query($sql, ['id' => $id]);
        $row = $this->db->fetch();
        if (!$row) return null;

        $obj = (object)$row;

        // Lấy danh sách ứng viên
        $this->db->query(
            "SELECT * FROM candidates WHERE request_id = :id ORDER BY created_at DESC",
            ['id' => $id]
        );
        $obj->candidates = json_decode(json_encode($this->db->fetchAll()));

        return $obj;
    }

    /**
     * Tạo yêu cầu tuyển dụng
     */
    public function createRequest(array $data): int
    {
        $data['request_code'] = $this->generateRequestCode();

        $sql = "INSERT INTO recruitment_requests 
                (request_code, department_id, position_id, project_id, quantity, reason, urgency,
                 description, requirements, salary_range_from, salary_range_to, benefits,
                 work_location, deadline, requested_by, status, notes)
                VALUES 
                (:code, :dept, :pos, :proj, :qty, :reason, :urgency,
                 :desc, :req, :sal_from, :sal_to, :benefits,
                 :location, :deadline, :requested_by, :status, :notes)";

        $this->db->query($sql, [
            'code'         => $data['request_code'],
            'dept'         => $data['department_id'] ?: null,
            'pos'          => $data['position_id'] ?: null,
            'proj'         => $data['project_id'] ?: null,
            'qty'          => $data['quantity'] ?? 1,
            'reason'       => $data['reason'] ?? 'Expansion',
            'urgency'      => $data['urgency'] ?? 'Normal',
            'desc'         => $data['description'] ?? null,
            'req'          => $data['requirements'] ?? null,
            'sal_from'     => $data['salary_range_from'] ?: null,
            'sal_to'       => $data['salary_range_to'] ?: null,
            'benefits'     => $data['benefits'] ?? null,
            'location'     => $data['work_location'] ?? null,
            'deadline'     => $data['deadline'] ?: null,
            'requested_by' => $data['requested_by'] ?? null,
            'status'       => $data['status'] ?? 'Draft',
            'notes'        => $data['notes'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Phê duyệt yêu cầu tuyển dụng
     */
    public function approveRequest(int $id, int $approverId): bool
    {
        $this->db->query(
            "UPDATE recruitment_requests SET status = 'Approved', approved_by = :approver WHERE id = :id AND status IN ('Draft','Pending')",
            ['approver' => $approverId, 'id' => $id]
        );
        return $this->db->rowCount() > 0;
    }

    // ══════════════════════════════════════════════════════════
    //  ỨNG VIÊN
    // ══════════════════════════════════════════════════════════

    /**
     * Danh sách ứng viên (có lọc)
     */
    public function getCandidates(array $filters = []): array
    {
        $sql = "SELECT c.*, rr.request_code, rr.description as request_desc, 
                       p.pos_title, d.dept_name
                FROM candidates c
                LEFT JOIN recruitment_requests rr ON c.request_id = rr.id
                LEFT JOIN positions p ON rr.position_id = p.id
                LEFT JOIN departments d ON rr.department_id = d.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['request_id'])) {
            $sql .= " AND c.request_id = :req_id";
            $params['req_id'] = $filters['request_id'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND c.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (c.full_name LIKE :s1 OR c.phone LIKE :s2 OR c.email LIKE :s3)";
            $params['s1'] = "%{$filters['search']}%";
            $params['s2'] = "%{$filters['search']}%";
            $params['s3'] = "%{$filters['search']}%";
        }

        $sql .= " ORDER BY c.created_at DESC";
        $this->db->query($sql, $params);
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Chi tiết ứng viên
     */
    public function getCandidateById(int $id): ?object
    {
        $sql = "SELECT c.*, rr.request_code, rr.description as request_desc,
                       p.pos_title, d.dept_name
                FROM candidates c
                LEFT JOIN recruitment_requests rr ON c.request_id = rr.id
                LEFT JOIN positions p ON rr.position_id = p.id
                LEFT JOIN departments d ON rr.department_id = d.id
                WHERE c.id = :id";
        $this->db->query($sql, ['id' => $id]);
        $row = $this->db->fetch();
        return $row ? (object)$row : null;
    }

    /**
     * Thêm ứng viên
     */
    public function addCandidate(array $data): int
    {
        // Kiểm tra Cảnh báo Blacklist HSE
        if (!empty($data['id_card'])) {
            $sqlCheck = "SELECT rd.id, rd.reason, rd.discipline_form 
                         FROM rewards_disciplines rd
                         JOIN employees e ON rd.employee_id = e.id
                         WHERE e.id_card = :id_card
                           AND rd.type = 'Discipline'
                           AND rd.status = 'Approved'
                           AND (rd.discipline_form LIKE '%Buộc thôi việc%' OR rd.discipline_form LIKE '%Sa thải%' OR rd.reason LIKE '%HSE%' OR rd.reason LIKE '%An toàn%')";
            $this->db->query($sqlCheck, ['id_card' => $data['id_card']]);
            $blacklist = $this->db->fetch();

            if ($blacklist) {
                $data['is_blacklisted'] = 1;
                $data['blacklist_reason'] = 'Vi phạm HSE/Kỷ luật nặng: ' . ($blacklist['discipline_form'] ?: $blacklist['reason']);
                $data['status'] = 'rejected';
            }
        }

        $sql = "INSERT INTO candidates 
                (request_id, full_name, dob, gender, phone, email, address, id_card,
                 highest_degree, major, university, graduation_year, years_experience,
                 current_company, current_position, expected_salary, cv_file_path,
                 front_id_card_path, back_id_card_path, cert_file_path,
                 source, skills, languages, status, is_blacklisted, blacklist_reason, notes)
                VALUES 
                (:req, :name, :dob, :gender, :phone, :email, :addr, :id_card,
                 :degree, :major, :uni, :grad_year, :exp,
                 :company, :position, :salary, :cv,
                 :front_id, :back_id, :cert_file,
                 :source, :skills, :langs, :status, :is_bl, :bl_reason, :notes)";

        $this->db->query($sql, [
            'req'       => $data['request_id'] ?: null,
            'name'      => $data['full_name'],
            'dob'       => $data['dob'] ?: null,
            'gender'    => $data['gender'] ?? 'Male',
            'phone'     => $data['phone'] ?? null,
            'email'     => $data['email'] ?? null,
            'addr'      => $data['address'] ?? null,
            'id_card'   => $data['id_card'] ?? null,
            'degree'    => $data['highest_degree'] ?? null,
            'major'     => $data['major'] ?? null,
            'uni'       => $data['university'] ?? null,
            'grad_year' => $data['graduation_year'] ?: null,
            'exp'       => $data['years_experience'] ?? 0,
            'company'   => $data['current_company'] ?? null,
            'position'  => $data['current_position'] ?? null,
            'salary'    => $data['expected_salary'] ?: null,
            'cv'        => $data['cv_file_path'] ?? null,
            'front_id'  => $data['front_id_card_path'] ?? null,
            'back_id'   => $data['back_id_card_path'] ?? null,
            'cert_file' => $data['cert_file_path'] ?? null,
            'source'    => $data['source'] ?? null,
            'skills'    => $data['skills'] ?? null,
            'langs'     => $data['languages'] ?? null,
            'status'    => $data['status'] ?? 'received',
            'is_bl'     => $data['is_blacklisted'] ?? 0,
            'bl_reason' => $data['blacklist_reason'] ?? null,
            'notes'     => $data['notes'] ?? null,
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Cập nhật trạng thái ứng viên
     */
    public function updateCandidateStatus(int $id, string $status, array $extra = []): bool
    {
        $setClauses = ['status = :status'];
        $params = ['status' => $status, 'id' => $id];

        foreach (['interview_date','interview_location','interview_result','interview_score',
                   'interviewer_name','interviewer_notes','final_decision','offer_salary',
                   'offer_date','start_date','rejection_reason'] as $field) {
            if (array_key_exists($field, $extra)) {
                $setClauses[] = "$field = :$field";
                $params[$field] = $extra[$field];
            }
        }

        $sql = "UPDATE candidates SET " . implode(', ', $setClauses) . " WHERE id = :id";
        $this->db->query($sql, $params);
        return $this->db->rowCount() > 0;
    }

    /**
     * Chuyển đổi ứng viên thành nhân viên
     */
    public function convertToEmployee(int $candidateId): ?int
    {
        $candidate = $this->getCandidateById($candidateId);
        if (!$candidate || $candidate->status !== 'Offer') return null;

        try {
            $this->db->beginTransaction();

            // Tạo nhân viên mới
            require_once APP_ROOT . '/models/Employee.php';
            $empModel = new Employee();

            $empData = [
                'full_name'     => $candidate->full_name,
                'dob'           => $candidate->dob,
                'gender'        => $candidate->gender,
                'phone'         => $candidate->phone,
                'email'         => $candidate->email,
                'address'       => $candidate->address,
                'id_card'       => $candidate->id_card,
                'highest_degree'=> $candidate->highest_degree,
                'status'        => 'Probation',
                'join_date'     => $candidate->start_date ?? date('Y-m-d'),
                'employee_type' => 'Direct_Worker',
                'notes'         => "Chuyển từ ứng viên #{$candidateId} - YCTD: {$candidate->request_code}"
            ];

            // Lấy department/position từ yêu cầu tuyển dụng
            if ($candidate->request_id) {
                $req = $this->getRequestById($candidate->request_id);
                if ($req) {
                    $empData['department_id'] = $req->department_id;
                    $empData['position_id'] = $req->position_id;
                    $empData['current_project_id'] = $req->project_id;
                }
            }

            $empId = $empModel->createWithFiles($empData, []);

            // Cập nhật trạng thái ứng viên
            $this->db->query(
                "UPDATE candidates SET status = 'Hired', converted_employee_id = :emp WHERE id = :id",
                ['emp' => $empId, 'id' => $candidateId]
            );

            // Cập nhật hired_count cho yêu cầu tuyển dụng
            if ($candidate->request_id) {
                $this->db->query(
                    "UPDATE recruitment_requests SET hired_count = hired_count + 1 WHERE id = :id",
                    ['id' => $candidate->request_id]
                );
                // Auto-close nếu đủ số lượng
                $this->db->query(
                    "UPDATE recruitment_requests SET status = 'Closed' WHERE id = :id AND hired_count >= quantity",
                    ['id' => $candidate->request_id]
                );
            }

            $this->db->commit();
            return $empId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Lỗi convert candidate → employee: " . $e->getMessage());
            return null;
        }
    }

    /**
     * Thống kê tuyển dụng
     */
    public function getStatistics(array $filters = []): array
    {
        $stats = [];
        $year = $filters['year'] ?? date('Y');

        // Tổng yêu cầu theo trạng thái
        $this->db->query("SELECT status, COUNT(*) as total, SUM(quantity) as total_qty, SUM(hired_count) as total_hired FROM recruitment_requests WHERE YEAR(created_at) = :y GROUP BY status", ['y' => $year]);
        $stats['requests_by_status'] = $this->db->fetchAll();

        // Tổng ứng viên theo trạng thái
        $this->db->query("SELECT status, COUNT(*) as total FROM candidates WHERE YEAR(created_at) = :y GROUP BY status", ['y' => $year]);
        $stats['candidates_by_status'] = $this->db->fetchAll();

        // Theo nguồn
        $this->db->query("SELECT COALESCE(source, 'Khác') as source, COUNT(*) as total FROM candidates WHERE YEAR(created_at) = :y GROUP BY source ORDER BY total DESC", ['y' => $year]);
        $stats['by_source'] = $this->db->fetchAll();

        // Tỷ lệ tuyển thành công
        $this->db->query("SELECT COUNT(*) as total FROM candidates WHERE YEAR(created_at) = :y", ['y' => $year]);
        $totalCandidates = $this->db->fetch()['total'] ?? 0;
        $this->db->query("SELECT COUNT(*) as total FROM candidates WHERE status = 'Hired' AND YEAR(created_at) = :y", ['y' => $year]);
        $hiredCount = $this->db->fetch()['total'] ?? 0;
        $stats['success_rate'] = $totalCandidates > 0 ? round(($hiredCount / $totalCandidates) * 100, 1) : 0;
        $stats['total_candidates'] = $totalCandidates;
        $stats['hired_count'] = $hiredCount;

        // Theo tháng
        $this->db->query(
            "SELECT MONTH(created_at) as month, COUNT(*) as total, SUM(CASE WHEN status='Hired' THEN 1 ELSE 0 END) as hired
             FROM candidates WHERE YEAR(created_at) = :y GROUP BY MONTH(created_at) ORDER BY month",
            ['y' => $year]
        );
        $stats['by_month'] = $this->db->fetchAll();

        return $stats;
    }
}
