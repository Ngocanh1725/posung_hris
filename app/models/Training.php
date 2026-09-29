<?php
/**
 * ============================================================
 *  POSUNG HRIS – Training Model (Phân hệ Đào tạo & L&D)
 * ============================================================
 *  Quản lý các Khóa đào tạo (trainings), Học viên tham gia
 *  (training_participants) và Quá trình đào tạo CBNV (emp_trainings)
 * ============================================================
 */

class Training extends BaseModel
{
    protected string $table = 'trainings';

    // ══════════════════════════════════════════════════════════
    //  1. QUẢN LÝ KHÓA ĐÀO TẠO (trainings)
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy danh sách khóa đào tạo kèm thông tin mở rộng và bộ lọc
     */
    public function getAll(array $filters = []): array
    {
        $sql = "SELECT t.*, 
                       d.dept_name,
                       d.dept_code,
                       COUNT(tp.id) AS participant_count,
                       SUM(CASE WHEN tp.result = 'Passed' THEN 1 ELSE 0 END) AS passed_count,
                       SUM(CASE WHEN tp.result = 'Failed' THEN 1 ELSE 0 END) AS failed_count
                FROM `{$this->table}` t
                LEFT JOIN departments d ON t.department_id = d.id
                LEFT JOIN training_participants tp ON t.id = tp.training_id
                WHERE t.deleted_at IS NULL";

        $params = [];

        if (!empty($filters['year'])) {
            $sql .= " AND (YEAR(t.start_date) = :year OR YEAR(t.end_date) = :year)";
            $params['year'] = (int)$filters['year'];
        }

        if (!empty($filters['department_id'])) {
            $sql .= " AND t.department_id = :dept_id";
            $params['dept_id'] = (int)$filters['department_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND t.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (t.course_name LIKE :search OR t.provider LIKE :search OR t.location LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " GROUP BY t.id ORDER BY t.start_date DESC, t.id DESC";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Lấy chi tiết 1 khóa đào tạo kèm thông tin phòng ban & thống kê
     */
    public function getById(int $id): ?object
    {
        $sql = "SELECT t.*, 
                       d.dept_name,
                       d.dept_code,
                       COUNT(tp.id) AS participant_count,
                       SUM(CASE WHEN tp.result = 'Passed' THEN 1 ELSE 0 END) AS passed_count,
                       SUM(CASE WHEN tp.result = 'Failed' THEN 1 ELSE 0 END) AS failed_count
                FROM `{$this->table}` t
                LEFT JOIN departments d ON t.department_id = d.id
                LEFT JOIN training_participants tp ON t.id = tp.training_id
                WHERE t.id = :id AND t.deleted_at IS NULL
                GROUP BY t.id
                LIMIT 1";

        $this->db->query($sql, ['id' => $id]);
        $row = $this->db->fetch();
        return $row ? (object)$row : null;
    }

    /**
     * Tạo mới khóa đào tạo
     */
    public function createCourse(array $data): int
    {
        return $this->create([
            'course_name'      => $data['course_name'],
            'description'      => $data['description'] ?? null,
            'start_date'       => !empty($data['start_date']) ? $data['start_date'] : null,
            'end_date'         => !empty($data['end_date']) ? $data['end_date'] : null,
            'provider'         => $data['provider'] ?? null,
            'department_id'    => !empty($data['department_id']) ? (int)$data['department_id'] : null,
            'cost'             => !empty($data['cost']) ? (float)$data['cost'] : 0,
            'max_participants' => !empty($data['max_participants']) ? (int)$data['max_participants'] : 0,
            'location'         => $data['location'] ?? null,
            'status'           => $data['status'] ?? 'Planning',
        ]);
    }

    /**
     * Cập nhật khóa đào tạo
     */
    public function updateCourse(int $id, array $data): bool
    {
        return $this->update($id, [
            'course_name'      => $data['course_name'],
            'description'      => $data['description'] ?? null,
            'start_date'       => !empty($data['start_date']) ? $data['start_date'] : null,
            'end_date'         => !empty($data['end_date']) ? $data['end_date'] : null,
            'provider'         => $data['provider'] ?? null,
            'department_id'    => !empty($data['department_id']) ? (int)$data['department_id'] : null,
            'cost'             => !empty($data['cost']) ? (float)$data['cost'] : 0,
            'max_participants' => !empty($data['max_participants']) ? (int)$data['max_participants'] : 0,
            'location'         => $data['location'] ?? null,
            'status'           => $data['status'] ?? 'Planning',
        ]);
    }

    /**
     * Xóa mềm (Soft delete)
     */
    public function softDelete(int $id): bool
    {
        $this->db->query(
            "UPDATE `{$this->table}` SET deleted_at = NOW() WHERE id = :id",
            ['id' => $id]
        );
        return $this->db->rowCount() > 0;
    }

    /**
     * Thống kê KPI tổng quan đào tạo
     */
    public function getStats(): array
    {
        $this->db->query("SELECT 
            COUNT(*) AS total_courses,
            SUM(CASE WHEN `status` = 'Planning' THEN 1 ELSE 0 END) AS planning_courses,
            SUM(CASE WHEN `status` = 'In_Progress' THEN 1 ELSE 0 END) AS in_progress_courses,
            SUM(CASE WHEN `status` = 'Completed' THEN 1 ELSE 0 END) AS completed_courses,
            COALESCE(SUM(cost), 0) AS total_budget
            FROM `{$this->table}` WHERE deleted_at IS NULL");
        $courseStats = $this->db->fetch() ?: [];

        $this->db->query("SELECT 
            COUNT(*) AS total_participants,
            SUM(CASE WHEN result = 'Passed' THEN 1 ELSE 0 END) AS total_passed,
            SUM(CASE WHEN result = 'Failed' THEN 1 ELSE 0 END) AS total_failed
            FROM training_participants tp
            JOIN trainings t ON tp.training_id = t.id
            WHERE t.deleted_at IS NULL");
        $partStats = $this->db->fetch() ?: [];

        return array_merge($courseStats, $partStats);
    }

    /**
     * Lấy các năm có khóa đào tạo để phục vụ lọc
     */
    public function getYears(): array
    {
        $this->db->query("SELECT DISTINCT YEAR(start_date) as y FROM `{$this->table}` WHERE start_date IS NOT NULL AND deleted_at IS NULL ORDER BY y DESC");
        $years = [];
        foreach ($this->db->fetchAll() as $row) {
            if (!empty($row['y'])) $years[] = (int)$row['y'];
        }
        $currentYear = (int)date('Y');
        if (!in_array($currentYear, $years, true)) {
            array_unshift($years, $currentYear);
        }
        return $years;
    }

    // ══════════════════════════════════════════════════════════
    //  2. QUẢN LÝ HỌC VIÊN THAM GIA (training_participants)
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy danh sách học viên tham gia khóa đào tạo
     */
    public function getParticipants(int $trainingId): array
    {
        $sql = "SELECT tp.*,
                       e.emp_code,
                       e.full_name,
                       e.phone,
                       e.email,
                       e.avatar_path,
                       e.employee_type,
                       d.dept_name,
                       p.pos_title,
                       pr.project_name
                FROM training_participants tp
                JOIN employees e ON tp.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN projects pr ON e.current_project_id = pr.id
                WHERE tp.training_id = :tid
                ORDER BY e.full_name ASC";

        $this->db->query($sql, ['tid' => $trainingId]);
        return $this->db->fetchAll();
    }

    /**
     * Thêm danh sách nhân viên vào khóa đào tạo (bỏ qua trùng)
     */
    public function addParticipants(int $trainingId, array $employeeIds): int
    {
        if (empty($employeeIds)) return 0;

        $inserted = 0;
        foreach ($employeeIds as $empId) {
            $empId = (int)$empId;
            if ($empId <= 0) continue;

            // Kiểm tra đã có chưa
            $this->db->query(
                "SELECT id FROM training_participants WHERE training_id = :tid AND employee_id = :eid",
                ['tid' => $trainingId, 'eid' => $empId]
            );
            if ($this->db->fetch()) {
                continue;
            }

            $this->db->query(
                "INSERT INTO training_participants (training_id, employee_id, status, result, created_at)
                 VALUES (:tid, :eid, 'Registered', 'Pending', NOW())",
                ['tid' => $trainingId, 'eid' => $empId]
            );
            $inserted++;
        }

        return $inserted;
    }

    /**
     * Xóa 1 học viên khỏi khóa đào tạo
     */
    public function removeParticipant(int $trainingId, int $employeeId): bool
    {
        // Xóa liên kết trong emp_trainings nếu có
        $this->db->query(
            "DELETE FROM emp_trainings WHERE training_id = :tid AND employee_id = :eid",
            ['tid' => $trainingId, 'eid' => $employeeId]
        );

        $this->db->query(
            "DELETE FROM training_participants WHERE training_id = :tid AND employee_id = :eid",
            ['tid' => $trainingId, 'eid' => $employeeId]
        );
        return $this->db->rowCount() > 0;
    }

    /**
     * Cập nhật kết quả đào tạo của 1 học viên & đồng bộ sang hồ sơ CBNV (emp_trainings)
     */
    public function updateParticipantResult(int $trainingId, int $employeeId, array $data): bool
    {
        $score = $data['score'] !== '' && $data['score'] !== null ? (float)$data['score'] : null;
        $result = !empty($data['result']) ? $data['result'] : 'Pending';
        $certNo = !empty($data['certificate_no']) ? trim($data['certificate_no']) : null;
        $status = !empty($data['status']) ? $data['status'] : ($result === 'Passed' ? 'Completed' : 'Attending');
        $completedDate = !empty($data['completed_date']) ? $data['completed_date'] : null;
        $notes = $data['notes'] ?? null;

        // Cập nhật training_participants
        $this->db->query(
            "UPDATE training_participants 
             SET score = :score,
                 result = :result,
                 certificate_no = :cert,
                 status = :status,
                 completed_date = :cdate,
                 notes = :notes
             WHERE training_id = :tid AND employee_id = :eid",
            [
                'score'  => $score,
                'result' => $result,
                'cert'   => $certNo,
                'status' => $status,
                'cdate'  => $completedDate,
                'notes'  => $notes,
                'tid'    => $trainingId,
                'eid'    => $employeeId,
            ]
        );

        // Lấy thông tin khóa học để đồng bộ sang emp_trainings
        $course = $this->getById($trainingId);
        if ($course) {
            $this->db->query(
                "SELECT id FROM emp_trainings WHERE training_id = :tid AND employee_id = :eid",
                ['tid' => $trainingId, 'eid' => $employeeId]
            );
            $existing = $this->db->fetch();

            if ($existing) {
                // Update
                $this->db->query(
                    "UPDATE emp_trainings 
                     SET training_name = :tname,
                         training_type = 'Internal',
                         institution = :inst,
                         from_date = :fdate,
                         to_date = :tdate,
                         result = :result,
                         certificate_no = :cert,
                         notes = :notes
                     WHERE id = :id",
                    [
                        'tname'  => $course->course_name,
                        'inst'   => $course->provider ?: 'POSUNG HRIS Internal',
                        'fdate'  => $course->start_date,
                        'tdate'  => $completedDate ?: $course->end_date,
                        'result' => $result,
                        'cert'   => $certNo,
                        'notes'  => $notes,
                        'id'     => $existing['id']
                    ]
                );
            } else {
                // Insert
                $this->db->query(
                    "INSERT INTO emp_trainings 
                     (employee_id, training_id, training_name, training_type, institution, from_date, to_date, result, certificate_no, notes, created_at)
                     VALUES (:eid, :tid, :tname, 'Internal', :inst, :fdate, :tdate, :result, :cert, :notes, NOW())",
                    [
                        'eid'    => $employeeId,
                        'tid'    => $trainingId,
                        'tname'  => $course->course_name,
                        'inst'   => $course->provider ?: 'POSUNG HRIS Internal',
                        'fdate'  => $course->start_date,
                        'tdate'  => $completedDate ?: $course->end_date,
                        'result' => $result,
                        'cert'   => $certNo,
                        'notes'  => $notes,
                    ]
                );
            }
        }

        return true;
    }

    /**
     * Lấy danh sách nhân viên khả dụng để thêm vào khóa học (loại trừ đã có)
     */
    public function getAvailableEmployees(int $trainingId, array $filters = []): array
    {
        $sql = "SELECT e.id, e.emp_code, e.full_name, e.employee_type,
                       d.dept_name, p.pos_title, pr.project_name
                FROM employees e
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN projects pr ON e.current_project_id = pr.id
                WHERE e.status != 'Resigned'
                  AND e.id NOT IN (
                      SELECT employee_id FROM training_participants WHERE training_id = :tid
                  )";

        $params = ['tid' => $trainingId];

        if (!empty($filters['dept_id'])) {
            $sql .= " AND e.department_id = :dept_id";
            $params['dept_id'] = (int)$filters['dept_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (e.full_name LIKE :search OR e.emp_code LIKE :search)";
            $params['search'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY e.full_name ASC LIMIT 100";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    // ══════════════════════════════════════════════════════════
    //  3. QUÁ TRÌNH ĐÀO TẠO THEO NHÂN VIÊN (emp_trainings)
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy toàn bộ lịch sử đào tạo của một nhân viên
     * (Bao gồm cả các khóa nội bộ tham gia và các văn bằng/chứng chỉ tự khai báo)
     */
    public function getByEmployee(int $employeeId): array
    {
        $sql = "SELECT et.id,
                       et.training_id,
                       et.training_name,
                       et.training_type,
                       et.institution,
                       et.from_date,
                       et.to_date,
                       et.result,
                       et.certificate_no,
                       et.notes,
                       et.created_at,
                       t.course_name,
                       t.provider,
                       t.cost,
                       t.location,
                       tp.score,
                       tp.status AS participant_status
                FROM emp_trainings et
                LEFT JOIN trainings t ON et.training_id = t.id
                LEFT JOIN training_participants tp ON (et.training_id = tp.training_id AND et.employee_id = tp.employee_id)
                WHERE et.employee_id = :eid
                ORDER BY COALESCE(et.from_date, et.created_at) DESC";

        $this->db->query($sql, ['eid' => $employeeId]);
        $list = $this->db->fetchAll();

        // Kiểm tra xem có khóa học nào nhân viên tham gia trong training_participants mà chưa có trong emp_trainings không
        $this->db->query(
            "SELECT tp.training_id, tp.status, tp.score, tp.result, tp.certificate_no, tp.completed_date, tp.notes,
                    t.course_name, t.provider, t.start_date, t.end_date, t.cost, t.location
             FROM training_participants tp
             JOIN trainings t ON tp.training_id = t.id
             WHERE tp.employee_id = :eid
               AND t.deleted_at IS NULL
               AND tp.training_id NOT IN (
                   SELECT COALESCE(training_id, 0) FROM emp_trainings WHERE employee_id = :eid2
               )
             ORDER BY t.start_date DESC",
            ['eid' => $employeeId, 'eid2' => $employeeId]
        );
        $additional = $this->db->fetchAll();

        foreach ($additional as $add) {
            $list[] = [
                'id'                 => 'tp_' . $add['training_id'],
                'training_id'        => $add['training_id'],
                'training_name'      => $add['course_name'],
                'training_type'      => 'Internal',
                'institution'        => $add['provider'] ?: 'POSUNG HRIS Internal',
                'from_date'          => $add['start_date'],
                'to_date'            => $add['completed_date'] ?: $add['end_date'],
                'result'             => $add['result'] ?: 'Pending',
                'certificate_no'     => $add['certificate_no'],
                'notes'              => $add['notes'],
                'created_at'         => null,
                'course_name'        => $add['course_name'],
                'provider'           => $add['provider'],
                'cost'               => $add['cost'],
                'location'           => $add['location'],
                'score'              => $add['score'],
                'participant_status' => $add['status'],
            ];
        }

        return $list;
    }

    /**
     * Thêm bản ghi đào tạo thủ công cho nhân viên
     */
    public function addEmployeeRecord(array $data): int
    {
        $this->db->query(
            "INSERT INTO emp_trainings 
             (employee_id, training_name, training_type, institution, from_date, to_date, result, certificate_no, notes, created_at)
             VALUES (:eid, :tname, :ttype, :inst, :fdate, :tdate, :result, :cert, :notes, NOW())",
            [
                'eid'    => (int)$data['employee_id'],
                'tname'  => $data['training_name'],
                'ttype'  => $data['training_type'] ?? 'External',
                'inst'   => $data['institution'] ?? null,
                'fdate'  => !empty($data['from_date']) ? $data['from_date'] : null,
                'tdate'  => !empty($data['to_date']) ? $data['to_date'] : null,
                'result' => $data['result'] ?? 'Completed',
                'cert'   => $data['certificate_no'] ?? null,
                'notes'  => $data['notes'] ?? null,
            ]
        );
        return (int)$this->db->lastInsertId();
    }

    /**
     * Xóa bản ghi đào tạo cá nhân
     */
    public function deleteEmployeeRecord(int $id, int $employeeId): bool
    {
        $this->db->query(
            "DELETE FROM emp_trainings WHERE id = :id AND employee_id = :eid",
            ['id' => $id, 'eid' => $employeeId]
        );
        return $this->db->rowCount() > 0;
    }
}
