<?php
/**
 * ============================================================
 *  POSUNG HRIS – Employee Model
 * ============================================================
 *  Quản lý Hồ sơ nhân sự 360 độ, bao gồm thông tin cá nhân,
 *  bằng cấp, chứng chỉ, expat details và tự động sinh mã nhân viên.
 * ============================================================
 */

class Employee extends BaseModel
{
    protected string $table = 'employees';

    /**
     * Tự động sinh mã nhân viên theo định dạng PS-YYYY-XXXX
     * YYYY là năm hiện tại, XXXX là số thứ tự tăng dần
     */
    public function generateEmpCode(): string
    {
        $year = date('Y');
        $prefix = "PS-{$year}-";
        
        $this->db->query(
            "SELECT emp_code FROM {$this->table} 
             WHERE emp_code LIKE :prefix 
             ORDER BY emp_code DESC LIMIT 1",
            ['prefix' => "{$prefix}%"]
        );
        $lastCodeRow = $this->db->fetch();

        if ($lastCodeRow && !empty($lastCodeRow['emp_code'])) {
            // Lấy 4 ký tự cuối (XXXX) và tăng thêm 1
            $lastNumber = (int) substr($lastCodeRow['emp_code'], -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad((string)$newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Lấy thông tin chi tiết nhân viên (360 độ) kèm các bảng liên kết
     */
    public function getById(int $id): ?object
    {
        $sql = "SELECT e.*,
                       d.dept_name,
                       d.branch,
                       p.pos_title,
                       p.job_level,
                       p.description as pos_description,
                       pr.project_name,
                       pr.client_name
                FROM employees e
                LEFT JOIN departments d  ON e.department_id      = d.id
                LEFT JOIN positions p    ON e.position_id        = p.id
                LEFT JOIN projects pr    ON e.current_project_id = pr.id
                WHERE e.id = :id
                LIMIT 1";

        $this->db->query($sql, ['id' => $id]);
        $result = $this->db->fetch();
        
        if (!$result) return null;

        $employee = (object)$result;

        // Lấy thông tin Expat (nếu có)
        $this->db->query("SELECT * FROM expat_details WHERE employee_id = :id", ['id' => $id]);
        $expat = $this->db->fetch();
        $employee->expat = $expat ? (object)$expat : null;

        // Lấy danh sách chứng chỉ
        $this->db->query("SELECT * FROM certificates WHERE employee_id = :id ORDER BY expiry_date DESC", ['id' => $id]);
        $employee->certificates = json_decode(json_encode($this->db->fetchAll()));

        // Lấy lịch sử lương
        $this->db->query("SELECT * FROM salaries WHERE employee_id = :id ORDER BY effective_date DESC", ['id' => $id]);
        $employee->salaries = json_decode(json_encode($this->db->fetchAll()));

        // Lấy lịch sử điều động (Movements)
        $this->db->query(
            "SELECT jm.*, 
                    fp.project_name AS from_project, tp.project_name AS to_project,
                    fd.dept_name AS from_dept, td.dept_name AS to_dept
             FROM job_movements jm
             LEFT JOIN projects fp    ON jm.from_project_id = fp.id
             LEFT JOIN projects tp    ON jm.to_project_id   = tp.id
             LEFT JOIN departments fd ON jm.from_dept_id    = fd.id
             LEFT JOIN departments td ON jm.to_dept_id      = td.id
             WHERE jm.employee_id = :id
             ORDER BY jm.effective_date DESC",
            ['id' => $id]
        );
        $employee->movements = json_decode(json_encode($this->db->fetchAll()));

        // Lấy Khen thưởng / Kỷ luật
        $this->db->query("SELECT * FROM rewards_disciplines WHERE employee_id = :id ORDER BY decision_date DESC", ['id' => $id]);
        $employee->rewards = json_decode(json_encode($this->db->fetchAll()));

        // NEW: Lấy hợp đồng
        $this->db->query("SELECT c.*, t.name as contract_type_name FROM contracts c LEFT JOIN contract_types t ON c.contract_type_id = t.id WHERE c.employee_id = :id ORDER BY c.start_date DESC", ['id' => $id]);
        $employee->contracts = json_decode(json_encode($this->db->fetchAll()));

        // NEW: Lấy nghỉ phép
        $this->db->query("SELECT lr.*, lt.name as leave_type_name FROM leave_requests lr LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id WHERE lr.employee_id = :id ORDER BY lr.start_date DESC", ['id' => $id]);
        $employee->leave_requests = json_decode(json_encode($this->db->fetchAll()));

        // NEW: Lấy người phụ thuộc
        $this->db->query("SELECT * FROM dependents WHERE employee_id = :id ORDER BY id DESC", ['id' => $id]);
        $employee->dependents = json_decode(json_encode($this->db->fetchAll()));

        // NEW: Lấy kinh nghiệm làm việc
        $this->db->query("SELECT * FROM work_experiences WHERE employee_id = :id ORDER BY start_date DESC", ['id' => $id]);
        $employee->work_experiences = json_decode(json_encode($this->db->fetchAll()));

        // NEW: Lấy phụ cấp
        $this->db->query("SELECT ea.*, a.name as allowance_name, a.code as allowance_code, a.type as allowance_type FROM employee_allowances ea JOIN allowances a ON ea.allowance_id = a.id WHERE ea.employee_id = :id", ['id' => $id]);
        $employee->allowances = json_decode(json_encode($this->db->fetchAll()));

        // SPRINT 2: Lấy thông tin PPE (Bảo hộ lao động & Tài sản)
        $this->db->query("SELECT * FROM emp_ppe_issuances WHERE employee_id = :id ORDER BY issue_date DESC, id DESC", ['id' => $id]);
        $employee->ppes = json_decode(json_encode($this->db->fetchAll()));

        return $employee;
    }

    /**
     * SPRINT 2: Kiểm tra trùng lặp CCCD/CMND (trừ user hiện tại nếu đang update)
     */
    public function checkUniqueIdCard(string $idCard, ?int $excludeEmpId = null): bool
    {
        $sql = "SELECT id FROM {$this->table} WHERE id_card_no = :id_card_no";
        $params = ['id_card_no' => $idCard];
        
        if ($excludeEmpId) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeEmpId;
        }

        $this->db->query($sql, $params);
        return $this->db->rowCount() === 0;
    }

    /**
     * Danh sách nhân viên kèm lọc đa tiêu chí
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT e.id, e.emp_code, e.full_name, e.phone, e.email,
                       e.employee_type, e.nationality, e.`status`, e.join_date,
                       e.avatar_path, e.current_project_id, e.department_id,
                       d.dept_name, p.pos_title, pr.project_name
                FROM employees e
                LEFT JOIN departments d  ON e.department_id      = d.id
                LEFT JOIN positions p    ON e.position_id        = p.id
                LEFT JOIN projects pr    ON e.current_project_id = pr.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (e.full_name LIKE :search OR e.emp_code LIKE :search2 OR e.phone LIKE :search3)";
            $params['search']  = "%{$filters['search']}%";
            $params['search2'] = "%{$filters['search']}%";
            $params['search3'] = "%{$filters['search']}%";
        }
        if (!empty($filters['status'])) {
            $sql .= " AND e.`status` = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $sql .= " AND e.employee_type = :type";
            $params['type'] = $filters['type'];
        }
        if (!empty($filters['deptId'])) {
            $sql .= " AND e.department_id = :dept";
            $params['dept'] = $filters['deptId'];
        }
        if (!empty($filters['projectId'])) {
            $sql .= " AND e.current_project_id = :project";
            $params['project'] = $filters['projectId'];
        }
        if (!empty($filters['cert_status'])) {
            if ($filters['cert_status'] === 'expiring') {
                $sql .= " AND e.id IN (SELECT employee_id FROM certificates WHERE expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 60 DAY))";
            } elseif ($filters['cert_status'] === 'expired') {
                $sql .= " AND e.id IN (SELECT employee_id FROM certificates WHERE expiry_date < CURDATE())";
            }
        }

        $sql .= " ORDER BY e.emp_code ASC";
        
        $this->db->query($sql, $params);
        $results = $this->db->fetchAll();

        return json_decode(json_encode($results));
    }

    /**
     * Thêm mới nhân sự (Kèm xử lý upload file và sinh mã tự động)
     */
    public function createWithFiles(array $data, array $files = []): int
    {
        // Tự động sinh mã nếu chưa có
        if (empty($data['emp_code'])) {
            $data['emp_code'] = $this->generateEmpCode();
        }

        // Xử lý upload ảnh đại diện (avatar)
        if (!empty($files['avatar']['name']) && $files['avatar']['error'] === UPLOAD_ERR_OK) {
            $data['avatar_path'] = $this->uploadFile($files['avatar'], 'avatars', $data['emp_code']);
        }

        // Xử lý upload CV/CCCD scan (cv_file)
        if (!empty($files['cv_file']['name']) && $files['cv_file']['error'] === UPLOAD_ERR_OK) {
            $data['cv_file_path'] = $this->uploadFile($files['cv_file'], 'documents', $data['emp_code'] . '_cv');
        }

        // CCCD Mặt trước
        if (!empty($files['id_card_front']['name']) && $files['id_card_front']['error'] === UPLOAD_ERR_OK) {
            $data['id_card_front'] = $this->uploadFile($files['id_card_front'], 'documents', $data['emp_code'] . '_id_front');
        }

        // CCCD Mặt sau
        if (!empty($files['id_card_back']['name']) && $files['id_card_back']['error'] === UPLOAD_ERR_OK) {
            $data['id_card_back'] = $this->uploadFile($files['id_card_back'], 'documents', $data['emp_code'] . '_id_back');
        }

        return $this->create($data); // Gọi hàm create của BaseModel
    }

    /**
     * Cập nhật nhân sự (Kèm xử lý upload file mới)
     */
    public function updateWithFiles(int $id, array $data, array $files = []): bool
    {
        // Lấy thông tin cũ để lấy emp_code cho việc đặt tên file
        $oldEmp = $this->find($id);
        if (!$oldEmp) return false;
        
        $empCode = $oldEmp->emp_code;

        // Upload avatar mới
        if (!empty($files['avatar']['name']) && $files['avatar']['error'] === UPLOAD_ERR_OK) {
            $data['avatar_path'] = $this->uploadFile($files['avatar'], 'avatars', $empCode);
        }

        // Upload CV mới
        if (!empty($files['cv_file']['name']) && $files['cv_file']['error'] === UPLOAD_ERR_OK) {
            $data['cv_file_path'] = $this->uploadFile($files['cv_file'], 'documents', $empCode . '_cv');
        }

        // CCCD Mặt trước
        if (!empty($files['id_card_front']['name']) && $files['id_card_front']['error'] === UPLOAD_ERR_OK) {
            $data['id_card_front'] = $this->uploadFile($files['id_card_front'], 'documents', $empCode . '_id_front');
        }

        // CCCD Mặt sau
        if (!empty($files['id_card_back']['name']) && $files['id_card_back']['error'] === UPLOAD_ERR_OK) {
            $data['id_card_back'] = $this->uploadFile($files['id_card_back'], 'documents', $empCode . '_id_back');
        }

        return $this->update($id, $data);
    }

    /**
     * Helper: Hàm xử lý upload file an toàn
     */
    private function uploadFile(array $file, string $subDir, string $prefix): string
    {
        $uploadDir = __DIR__ . '/../../public/uploads/' . $subDir . '/';
        
        // Tạo thư mục nếu chưa tồn tại
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        // Đổi tên file để tránh trùng lặp: PS-2026-0001_time.ext
        $newName = $prefix . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . $newName;

        if (move_uploaded_file($file['tmp_name'], $destPath)) {
            // Trả về đường dẫn tương đối để lưu vào DB (Dùng trong the <img> src)
            return 'uploads/' . $subDir . '/' . $newName;
        }

        return '';
    }

    /**
     * Lấy danh sách giấy tờ sắp hết hạn (Bao gồm Expat Visa/TRC/WP, Chứng chỉ và Hợp đồng)
     */
    public function checkExpiringDocuments(int $days = 60): array
    {
        // Tự động vô hiệu hóa Hợp đồng đã quá hạn
        $this->db->query("UPDATE contracts SET `status` = 'expired' WHERE end_date < CURDATE() AND `status` = 'active'");
        
        $alerts = [];
        
        // 1. Quét Chứng chỉ (Certificates)
        $this->db->query(
            "SELECT c.id, c.cert_name AS doc_name, c.cert_type AS doc_type, c.expiry_date,
                    e.emp_code, e.full_name, e.id AS employee_id, 'Certificate' AS source
             FROM certificates c
             JOIN employees e ON c.employee_id = e.id
             WHERE c.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
             ORDER BY c.expiry_date ASC",
            ['days' => $days]
        );
        $certs = $this->db->fetchAll();
        foreach ($certs as $c) $alerts[] = (object)$c;

        // 2. Quét Visa Expat
        $this->db->query(
            "SELECT ex.id, 'Visa' AS doc_name, 'Visa' AS doc_type, ex.visa_expiry AS expiry_date,
                    e.emp_code, e.full_name, e.id AS employee_id, 'Expat' AS source
             FROM expat_details ex
             JOIN employees e ON ex.employee_id = e.id
             WHERE ex.visa_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)"
             , ['days' => $days]
        );
        $visas = $this->db->fetchAll();
        foreach ($visas as $v) $alerts[] = (object)$v;

        // 3. Quét TRC Expat
        $this->db->query(
            "SELECT ex.id, 'Thẻ Tạm Trú (TRC)' AS doc_name, 'TRC' AS doc_type, ex.trc_expiry AS expiry_date,
                    e.emp_code, e.full_name, e.id AS employee_id, 'Expat' AS source
             FROM expat_details ex
             JOIN employees e ON ex.employee_id = e.id
             WHERE ex.trc_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)"
             , ['days' => $days]
        );
        $trcs = $this->db->fetchAll();
        foreach ($trcs as $t) $alerts[] = (object)$t;

        // 4. Quét WP Expat
        $this->db->query(
            "SELECT ex.id, 'Giấy Phép LĐ (WP)' AS doc_name, 'WP' AS doc_type, ex.work_permit_expiry AS expiry_date,
                    e.emp_code, e.full_name, e.id AS employee_id, 'Expat' AS source
             FROM expat_details ex
             JOIN employees e ON ex.employee_id = e.id
             WHERE ex.work_permit_expiry BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)"
             , ['days' => $days]
        );
        $wps = $this->db->fetchAll();
        foreach ($wps as $w) $alerts[] = (object)$w;

        // 5. Quét Hợp Đồng (Contracts)
        $this->db->query(
            "SELECT c.id, 'Hợp đồng lao động' AS doc_name, 'Contract' AS doc_type, c.end_date AS expiry_date,
                    e.emp_code, e.full_name, e.id AS employee_id, 'Contract' AS source
             FROM contracts c
             JOIN employees e ON c.employee_id = e.id
             WHERE c.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
               AND c.`status` = 'active'"
             , ['days' => $days]
        );
        $contracts = $this->db->fetchAll();
        foreach ($contracts as $cnt) $alerts[] = (object)$cnt;

        // Sắp xếp gộp theo ngày hết hạn gần nhất
        usort($alerts, function($a, $b) {
            return strtotime($a->expiry_date) - strtotime($b->expiry_date);
        });

        // Xử lý cờ hết hạn (Expired <= 0, Red <= 15, Orange <= 30, Yellow <= 60)
        $now = time();
        foreach ($alerts as &$a) {
            $diffDays = (strtotime($a->expiry_date) - $now) / (60 * 60 * 24);
            $a->days_left = floor($diffDays);
            
            if ($a->days_left <= 0) {
                $a->alert_level = 'purple';
                $a->alert_label = 'Hết hạn';
            } elseif ($a->days_left <= 15) {
                $a->alert_level = 'red';
                $a->alert_label = 'Khẩn cấp';
            } elseif ($a->days_left <= 30) {
                $a->alert_level = 'orange';
                $a->alert_label = 'Cảnh báo';
            } else {
                $a->alert_level = 'yellow';
                $a->alert_label = 'Nhắc nhở';
            }
        }

        return $alerts;
    }
    
    // Giữ lại hàm cũ để tương thích Dashboard (nếu cần)
    public function countExpats(): int
    {
        $this->db->query("SELECT COUNT(*) AS cnt FROM employees WHERE employee_type = 'Expat' AND `status` = 'Active'");
        $row = $this->db->fetch();
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * Kiểm tra CCCD có nằm trong Blacklist không
     */
    public function checkBlacklistIdCard(string $idCard): bool
    {
        $sql = "SELECT id FROM employees WHERE id_card_no = :ic AND `status` = 'Blacklisted'";
        $this->db->query($sql, ['ic' => $idCard]);
        return $this->db->fetch() ? true : false;
    }

    /**
     * Cảnh báo nhân viên sắp đến tuổi nghỉ hưu (trong vòng 12 tháng)
     * Bộ luật LĐ mới: Nam tiến tới 62 tuổi, Nữ tiến tới 60 tuổi
     */
    public function getRetiringAlerts(): array
    {
        // Nam: 62 tuổi (62 * 12 = 744 tháng)
        // Nữ: 60 tuổi (60 * 12 = 720 tháng)
        // Cảnh báo khi còn cách ngưỡng từ 6 - 12 tháng (hoặc đã quá hạn)
        $sql = "SELECT id, emp_code, full_name, birth_date, gender,
                       TIMESTAMPDIFF(YEAR, birth_date, CURDATE()) as current_age,
                       TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) as age_months,
                       (CASE 
                            WHEN gender = 'Male' THEN (62 * 12) - TIMESTAMPDIFF(MONTH, birth_date, CURDATE())
                            ELSE (60 * 12) - TIMESTAMPDIFF(MONTH, birth_date, CURDATE())
                        END) as months_to_retire
                FROM employees 
                WHERE `status` IN ('Active', 'Suspended')
                  AND birth_date IS NOT NULL
                  AND (
                    (gender = 'Male' AND TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) >= (62 * 12 - 12)) OR
                    (gender = 'Female' AND TIMESTAMPDIFF(MONTH, birth_date, CURDATE()) >= (60 * 12 - 12))
                  )
                ORDER BY months_to_retire ASC";
        $this->db->query($sql);
        return $this->db->fetchAll();
    }

    // --- RELATIONSHIP METHODS ---

    public function getWorkHistories(int $employeeId): array
    {
        $this->db->query("SELECT * FROM work_histories WHERE employee_id = :id ORDER BY from_date DESC", ['id' => $employeeId]);
        return $this->db->fetchAll();
    }
    
    public function getSalaryProgressions(int $employeeId): array
    {
        $this->db->query("SELECT * FROM salaries WHERE employee_id = :id ORDER BY effective_date DESC", ['id' => $employeeId]);
        return $this->db->fetchAll();
    }
    
    public function getAppointments(int $employeeId): array
    {
        $this->db->query("SELECT * FROM appointments WHERE employee_id = :id ORDER BY effective_date DESC", ['id' => $employeeId]);
        return $this->db->fetchAll();
    }
    
    public function getCertificates(int $employeeId): array
    {
        $this->db->query("SELECT * FROM certificates WHERE employee_id = :id ORDER BY expiry_date DESC", ['id' => $employeeId]);
        return $this->db->fetchAll();
    }
    
    public function getFamilyMembers(int $employeeId): array
    {
        $this->db->query("SELECT * FROM family_members WHERE employee_id = :id", ['id' => $employeeId]);
        return $this->db->fetchAll();
    }
    
    public function getPpeItems(int $employeeId): array
    {
        $this->db->query("SELECT * FROM emp_ppe_issuances WHERE employee_id = :id ORDER BY issue_date DESC", ['id' => $employeeId]);
        return $this->db->fetchAll();
    }

    /**
     * Thêm bản ghi phụ (Generic)
     */
    public function addRelatedRecord(string $table, array $data): int
    {
        // Simple insert
        $columns = array_keys($data);
        $holders = array_map(fn($c) => ":{$c}", $columns);
        $sql = sprintf(
            "INSERT INTO `%s` (`%s`) VALUES (%s)",
            $table,
            implode('`, `', $columns),
            implode(', ', $holders)
        );
        $this->db->query($sql, $data);
        return (int) $this->db->lastInsertId();
    }

    /**
     * Xóa bản ghi phụ (Generic)
     */
    public function deleteRelatedRecord(string $table, int $id, int $employeeId): bool
    {
        $this->db->query(
            "DELETE FROM `{$table}` WHERE id = :id AND employee_id = :emp_id",
            ['id' => $id, 'emp_id' => $employeeId]
        );
        return $this->db->rowCount() > 0;
    }

    /**
     * Lấy danh sách NV sắp nghỉ hưu
     */
    public function getRetirementAlerts(int $months = 12): array
    {
        $retireAgeMale = 62; // Tuổi hưu nam (theo Luật 2019 lộ trình 2026)
        $retireAgeFemale = 60; // Tuổi hưu nữ

        $sql = "SELECT e.id, e.emp_code, e.full_name, e.gender, e.birth_date as dob, e.join_date, e.phone,
                       d.dept_name, p.pos_title,
                       TIMESTAMPDIFF(YEAR, e.birth_date, CURDATE()) as current_age,
                       TIMESTAMPDIFF(YEAR, e.join_date, CURDATE()) as seniority,
                       CASE 
                           WHEN e.gender = 'Male' THEN DATE_ADD(e.birth_date, INTERVAL {$retireAgeMale} YEAR)
                           ELSE DATE_ADD(e.birth_date, INTERVAL {$retireAgeFemale} YEAR)
                       END as retirement_date,
                       TIMESTAMPDIFF(MONTH, CURDATE(), CASE 
                           WHEN e.gender = 'Male' THEN DATE_ADD(e.birth_date, INTERVAL {$retireAgeMale} YEAR)
                           ELSE DATE_ADD(e.birth_date, INTERVAL {$retireAgeFemale} YEAR)
                       END) as months_left
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                WHERE e.`status` IN ('Active','Probation')
                AND e.birth_date IS NOT NULL
                HAVING retirement_date <= DATE_ADD(CURDATE(), INTERVAL :months MONTH)
                ORDER BY retirement_date ASC";

        $this->db->query($sql, ['months' => $months]);
        return $this->db->fetchAll();
    }

    /**
     * Tìm kiếm nâng cao
     */
    public function searchAdvanced(array $filters): array
    {
        $sql = "SELECT e.id, e.emp_code, e.full_name, e.gender, e.birth_date as dob, e.join_date, e.phone, e.email, e.id_card_no,
                       e.employee_type, e.`status`,
                       d.dept_name, p.pos_title, proj.project_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN projects proj ON e.current_project_id = proj.id
                WHERE 1=1";
        $params = [];

        // Search text
        if (!empty($filters['search'])) {
            $sql .= " AND (e.full_name LIKE :search OR e.emp_code LIKE :search OR e.phone LIKE :search OR e.id_card_no LIKE :search OR e.email LIKE :search)";
            $params['search'] = "%{$filters['search']}%";
        }
        if (!empty($filters['department_id'])) {
            $sql .= " AND e.department_id = :dept";
            $params['dept'] = $filters['department_id'];
        }
        if (!empty($filters['project_id'])) {
            $sql .= " AND e.current_project_id = :proj";
            $params['proj'] = $filters['project_id'];
        }
        if (!empty($filters['position_id'])) {
            $sql .= " AND e.position_id = :pos";
            $params['pos'] = $filters['position_id'];
        }
        if (!empty($filters['employee_type'])) {
            $sql .= " AND e.employee_type = :etype";
            $params['etype'] = $filters['employee_type'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND e.`status` = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['gender'])) {
            $sql .= " AND e.gender = :gender";
            $params['gender'] = $filters['gender'];
        }
        // Date ranges
        if (!empty($filters['join_date_from'])) {
            $sql .= " AND e.join_date >= :jfrom";
            $params['jfrom'] = $filters['join_date_from'];
        }
        if (!empty($filters['join_date_to'])) {
            $sql .= " AND e.join_date <= :jto";
            $params['jto'] = $filters['join_date_to'];
        }
        if (!empty($filters['dob_from'])) {
            $sql .= " AND e.birth_date >= :dobfrom";
            $params['dobfrom'] = $filters['dob_from'];
        }
        if (!empty($filters['dob_to'])) {
            $sql .= " AND e.birth_date <= :dobto";
            $params['dobto'] = $filters['dob_to'];
        }
        // Lương
        if (!empty($filters['salary_from'])) {
            $sql .= " AND e.base_salary >= :salfrom";
            $params['salfrom'] = $filters['salary_from'];
        }
        if (!empty($filters['salary_to'])) {
            $sql .= " AND e.base_salary <= :salto";
            $params['salto'] = $filters['salary_to'];
        }

        $sql .= " ORDER BY e.emp_code ASC LIMIT 500";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }
}