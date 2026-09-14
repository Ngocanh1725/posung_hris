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

        return $employee;
    }

    /**
     * Danh sách nhân viên kèm lọc đa tiêu chí
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT e.id, e.emp_code, e.full_name, e.phone, e.email,
                       e.employee_type, e.nationality, e.status, e.join_date,
                       e.avatar_path,
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
            $sql .= " AND e.status = :status";
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
            $data['cv_file_path'] = $this->uploadFile($files['cv_file'], 'cvs', $data['emp_code']);
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
            $data['cv_file_path'] = $this->uploadFile($files['cv_file'], 'cvs', $empCode);
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
     * Lấy danh sách giấy tờ sắp hết hạn (Bao gồm Expat Visa/TRC và Chứng chỉ)
     */
    public function getExpiringDocuments(int $days = 60): array
    {
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

        // Sắp xếp gộp theo ngày hết hạn gần nhất
        usort($alerts, function($a, $b) {
            return strtotime($a->expiry_date) - strtotime($b->expiry_date);
        });

        return $alerts;
    }
    
    // Giữ lại hàm cũ để tương thích Dashboard (nếu cần)
    public function countExpats(): int
    {
        $this->db->query("SELECT COUNT(*) AS cnt FROM employees WHERE employee_type = 'Expat' AND status = 'Active'");
        $row = $this->db->fetch();
        return (int) ($row['cnt'] ?? 0);
    }

    /**
     * Kiểm tra CCCD có nằm trong Blacklist không
     */
    public function checkBlacklistIdCard(string $idCard): bool
    {
        $sql = "SELECT id FROM employees WHERE id_card = :ic AND status = 'Blacklisted'";
        $this->db->query($sql, ['ic' => $idCard]);
        return $this->db->fetch() ? true : false;
    }

    /**
     * Cảnh báo nhân viên sắp đến tuổi nghỉ hưu (trong vòng 6 tháng)
     * Nam 60 tuổi 6 tháng (tạm lấy 60.5)
     * Nữ 55 tuổi 8 tháng (tạm lấy 55.6)
     */
    public function getRetiringAlerts(): array
    {
        // Sử dụng DATEDIFF để tính khoảng cách ngày.
        // Giả sử: Nam hưu ở tuổi 60, Nữ hưu ở tuổi 55 (Để đơn giản hóa query)
        $sql = "SELECT id, emp_code, full_name, dob, gender,
                       TIMESTAMPDIFF(YEAR, dob, CURDATE()) as current_age,
                       TIMESTAMPDIFF(MONTH, dob, CURDATE()) as age_months
                FROM employees 
                WHERE status IN ('Active', 'Suspended')
                  AND dob IS NOT NULL
                  AND (
                    (gender = 'Male' AND TIMESTAMPDIFF(MONTH, dob, CURDATE()) >= (60 * 12 - 6)) OR
                    (gender = 'Female' AND TIMESTAMPDIFF(MONTH, dob, CURDATE()) >= (55 * 12 - 6))
                  )";
        $this->db->query($sql);
        return $this->db->fetchAll();
    }
}