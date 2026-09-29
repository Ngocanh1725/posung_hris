<?php
/**
 * ============================================================
 *  POSUNG HRIS – Holiday Model
 * ============================================================
 *  Quản lý Lịch Ngày Lễ / Tết / Ngày kỷ niệm toàn công ty.
 *  Tích hợp tính công, nghỉ phép và Calendar View.
 * ============================================================
 */

class Holiday extends BaseModel
{
    protected string $table = 'holidays';

    /**
     * Lấy danh sách ngày lễ theo năm (hoặc tất cả)
     */
    public function getHolidays(?int $year = null, ?int $deptId = null): array
    {
        $year = $year ?: (int)date('Y');
        
        $sql = "SELECT h.*, d.dept_name
                FROM {$this->table} h
                LEFT JOIN departments d ON h.applies_to_department_id = d.id
                WHERE (YEAR(h.date) = :year OR h.is_recurring = 1)";
        $params = ['year' => $year];

        if ($deptId !== null) {
            $sql .= " AND (h.applies_to_department_id IS NULL OR h.applies_to_department_id = :dept_id)";
            $params['dept_id'] = $deptId;
        }

        $sql .= " ORDER BY h.date ASC";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Lấy chi tiết một ngày lễ
     */
    public function getById(int $id): ?array
    {
        $sql = "SELECT h.*, d.dept_name 
                FROM {$this->table} h
                LEFT JOIN departments d ON h.applies_to_department_id = d.id
                WHERE h.id = :id LIMIT 1";
        $this->db->query($sql, ['id' => $id]);
        $row = $this->db->fetch();
        return $row ?: null;
    }

    /**
     * Lấy các ngày lễ sắp tới
     */
    public function getUpcomingHolidays(int $limit = 5): array
    {
        $today = date('Y-m-d');
        $sql = "SELECT * FROM {$this->table} 
                WHERE date >= :today 
                ORDER BY date ASC 
                LIMIT {$limit}";
        $this->db->query($sql, ['today' => $today]);
        return $this->db->fetchAll();
    }

    /**
     * Kiểm tra một ngày cụ thể có phải ngày lễ
     */
    public function isHoliday(string $date, ?int $deptId = null): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE date = :d";
        $params = ['d' => $date];

        if ($deptId !== null) {
            $sql .= " AND (applies_to_department_id IS NULL OR applies_to_department_id = :dept_id)";
            $params['dept_id'] = $deptId;
        }

        $sql .= " LIMIT 1";
        $this->db->query($sql, $params);
        $res = $this->db->fetch();
        return $res ?: null;
    }

    /**
     * Thêm mới ngày lễ (đồng bộ cả holidays và leave_holidays)
     */
    public function addHoliday(array $data): int
    {
        $sql = "INSERT INTO {$this->table} 
                (name, date, type, is_recurring, applies_to_department_id, description, created_at)
                VALUES 
                (:name, :date, :type, :is_recurring, :dept_id, :desc, NOW())";
        
        $params = [
            'name'         => $data['name'],
            'date'         => $data['date'],
            'type'         => $data['type'] ?? 'National',
            'is_recurring' => !empty($data['is_recurring']) ? 1 : 0,
            'dept_id'      => !empty($data['applies_to_department_id']) ? (int)$data['applies_to_department_id'] : null,
            'desc'         => $data['description'] ?? null
        ];

        $this->db->query($sql, $params);
        $newId = (int)$this->db->lastInsertId();

        // Đồng bộ vào leave_holidays nếu bảng đó tồn tại
        try {
            $this->db->query(
                "INSERT IGNORE INTO leave_holidays (name, date, type, is_recurring, applies_to_department_id, description, created_at)
                 VALUES (:name, :date, :type, :is_recurring, :dept_id, :desc, NOW())",
                $params
            );
        } catch (Exception $e) {
            // Không chặn nếu leave_holidays có cấu trúc khác
        }

        return $newId;
    }

    /**
     * Cập nhật ngày lễ
     */
    public function updateHoliday(int $id, array $data): bool
    {
        $sql = "UPDATE {$this->table} 
                SET name = :name, 
                    date = :date, 
                    type = :type, 
                    is_recurring = :is_recurring, 
                    applies_to_department_id = :dept_id, 
                    description = :desc 
                WHERE id = :id";

        $params = [
            'name'         => $data['name'],
            'date'         => $data['date'],
            'type'         => $data['type'] ?? 'National',
            'is_recurring' => !empty($data['is_recurring']) ? 1 : 0,
            'dept_id'      => !empty($data['applies_to_department_id']) ? (int)$data['applies_to_department_id'] : null,
            'desc'         => $data['description'] ?? null,
            'id'           => $id
        ];

        $this->db->query($sql, $params);

        // Đồng bộ cập nhật leave_holidays theo date
        try {
            $this->db->query(
                "UPDATE leave_holidays 
                 SET name = :name, type = :type, is_recurring = :is_recurring, 
                     applies_to_department_id = :dept_id, description = :desc 
                 WHERE date = :date",
                [
                    'name'         => $data['name'],
                    'date'         => $data['date'],
                    'type'         => $data['type'] ?? 'National',
                    'is_recurring' => !empty($data['is_recurring']) ? 1 : 0,
                    'dept_id'      => !empty($data['applies_to_department_id']) ? (int)$data['applies_to_department_id'] : null,
                    'desc'         => $data['description'] ?? null
                ]
            );
        } catch (Exception $e) {}

        return true;
    }

    /**
     * Xóa ngày lễ
     */
    public function deleteHoliday(int $id): bool
    {
        $h = $this->getById($id);
        $date = $h ? $h['date'] : null;

        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        $this->db->query($sql, ['id' => $id]);

        if ($date) {
            try {
                $this->db->query("DELETE FROM leave_holidays WHERE date = :d", ['d' => $date]);
            } catch (Exception $e) {}
        }

        return true;
    }

    /**
     * Import danh sách ngày lễ chuẩn Việt Nam cho một năm
     */
    public function importYearlyTemplate(int $year): int
    {
        // Danh sách ngày lễ tiêu chuẩn theo Bộ Luật Lao Động Việt Nam
        // Đối với Tết Âm Lịch và Giỗ Tổ Hùng Vương, có tính toán gần đúng theo năm
        $templates = [
            // Dương lịch
            [
                'name' => "Tết Dương Lịch {$year}",
                'date' => sprintf('%04d-01-01', $year),
                'type' => 'National',
                'is_recurring' => 1,
                'description' => 'Nghỉ Tết Dương Lịch theo Bộ luật Lao động'
            ],
            // 30/4 & 1/5
            [
                'name' => 'Ngày Giải phóng Miền Nam (30/4)',
                'date' => sprintf('%04d-04-30', $year),
                'type' => 'National',
                'is_recurring' => 1,
                'description' => 'Kỷ niệm Ngày Giải phóng miền Nam, thống nhất đất nước'
            ],
            [
                'name' => 'Ngày Quốc tế Lao động (1/5)',
                'date' => sprintf('%04d-05-01', $year),
                'type' => 'National',
                'is_recurring' => 1,
                'description' => 'Kỷ niệm Ngày Quốc tế Lao động'
            ],
            // Ngày thành lập công ty POSUNG E&C
            [
                'name' => 'Ngày Thành Lập POSUNG E&C',
                'date' => sprintf('%04d-08-18', $year),
                'type' => 'Company',
                'is_recurring' => 1,
                'description' => 'Kỷ niệm ngày truyền thống thành lập Tập đoàn Xây dựng POSUNG'
            ],
            // Quốc khánh 2/9
            [
                'name' => 'Quốc khánh Nước CHXHCN Việt Nam',
                'date' => sprintf('%04d-09-02', $year),
                'type' => 'National',
                'is_recurring' => 1,
                'description' => 'Kỷ niệm ngày Quốc khánh 2/9'
            ],
            [
                'name' => 'Nghỉ liền kề Quốc khánh',
                'date' => sprintf('%04d-09-03', $year),
                'type' => 'National',
                'is_recurring' => 0,
                'description' => 'Nghỉ liền kề Quốc khánh theo quy định hàng năm'
            ],
        ];

        // Lịch Âm Lịch cho các năm phổ biến (2025, 2026, 2027)
        $lunarHolidays = [
            2025 => [
                ['name' => 'Tết Nguyên Đán 2025 (28 Tết)', 'date' => '2025-01-27', 'type' => 'National', 'desc' => 'Kỳ nghỉ Tết Nguyên Đán Ất Tỵ'],
                ['name' => 'Tết Nguyên Đán 2025 (29 Tết)', 'date' => '2025-01-28', 'type' => 'National', 'desc' => 'Kỳ nghỉ Tết Nguyên Đán Ất Tỵ'],
                ['name' => 'Tết Nguyên Đán 2025 (Mùng 1)', 'date' => '2025-01-29', 'type' => 'National', 'desc' => 'Kỳ nghỉ Tết Nguyên Đán Ất Tỵ'],
                ['name' => 'Tết Nguyên Đán 2025 (Mùng 2)', 'date' => '2025-01-30', 'type' => 'National', 'desc' => 'Kỳ nghỉ Tết Nguyên Đán Ất Tỵ'],
                ['name' => 'Tết Nguyên Đán 2025 (Mùng 3)', 'date' => '2025-01-31', 'type' => 'National', 'desc' => 'Kỳ nghỉ Tết Nguyên Đán Ất Tỵ'],
                ['name' => 'Giỗ Tổ Hùng Vương (10/3 ÂL)', 'date' => '2025-04-07', 'type' => 'National', 'desc' => 'Giỗ Tổ Hùng Vương'],
            ],
            2026 => [
                ['name' => 'Tết Nguyên Đán 2026 (28 Tết)', 'date' => '2026-02-15', 'type' => 'National', 'desc' => 'Kỳ nghỉ Tết Nguyên Đán Bính Ngọ 2026'],
                ['name' => 'Tết Nguyên Đán 2026 (29 Tết)', 'date' => '2026-02-16', 'type' => 'National', 'desc' => 'Kỳ nghỉ Tết Nguyên Đán Bính Ngọ 2026'],
                ['name' => 'Tết Nguyên Đán 2026 (Mùng 1)', 'date' => '2026-02-17', 'type' => 'National', 'desc' => 'Kỳ nghỉ Tết Nguyên Đán Bính Ngọ 2026'],
                ['name' => 'Tết Nguyên Đán 2026 (Mùng 2)', 'date' => '2026-02-18', 'type' => 'National', 'desc' => 'Kỳ nghỉ Tết Nguyên Đán Bính Ngọ 2026'],
                ['name' => 'Tết Nguyên Đán 2026 (Mùng 3)', 'date' => '2026-02-19', 'type' => 'National', 'desc' => 'Kỳ nghỉ Tết Nguyên Đán Bính Ngọ 2026'],
                ['name' => 'Tết Nguyên Đán 2026 (Mùng 4)', 'date' => '2026-02-20', 'type' => 'National', 'desc' => 'Kỳ nghỉ Tết Nguyên Đán Bính Ngọ 2026'],
                ['name' => 'Tết Nguyên Đán 2026 (Mùng 5)', 'date' => '2026-02-21', 'type' => 'National', 'desc' => 'Kỳ nghỉ Tết Nguyên Đán Bính Ngọ 2026'],
                ['name' => 'Giỗ Tổ Hùng Vương (10/3 ÂL)', 'date' => '2026-04-26', 'type' => 'National', 'desc' => 'Lễ Giỗ tổ Hùng Vương (Chủ Nhật)'],
                ['name' => 'Nghỉ bù Giỗ Tổ Hùng Vương', 'date' => '2026-04-27', 'type' => 'National', 'desc' => 'Nghỉ bù Giỗ tổ Hùng Vương'],
            ],
            2027 => [
                ['name' => 'Tết Nguyên Đán 2027 (29 Tết)', 'date' => '2027-02-05', 'type' => 'National', 'desc' => 'Kỳ nghỉ Tết Nguyên Đán Đinh Mùi'],
                ['name' => 'Tết Nguyên Đán 2027 (30 Tết)', 'date' => '2027-02-06', 'type' => 'National', 'desc' => 'Kỳ nghỉ Tết Nguyên Đán Đinh Mùi'],
                ['name' => 'Tết Nguyên Đán 2027 (Mùng 1)', 'date' => '2027-02-07', 'type' => 'National', 'desc' => 'Kỳ nghỉ Tết Nguyên Đán Đinh Mùi'],
                ['name' => 'Tết Nguyên Đán 2027 (Mùng 2)', 'date' => '2027-02-08', 'type' => 'National', 'desc' => 'Kỳ nghỉ Tết Nguyên Đán Đinh Mùi'],
                ['name' => 'Tết Nguyên Đán 2027 (Mùng 3)', 'date' => '2027-02-09', 'type' => 'National', 'desc' => 'Kỳ nghỉ Tết Nguyên Đán Đinh Mùi'],
                ['name' => 'Giỗ Tổ Hùng Vương (10/3 ÂL)', 'date' => '2027-04-16', 'type' => 'National', 'desc' => 'Giỗ Tổ Hùng Vương'],
            ]
        ];

        if (isset($lunarHolidays[$year])) {
            foreach ($lunarHolidays[$year] as $lh) {
                $templates[] = [
                    'name' => $lh['name'],
                    'date' => $lh['date'],
                    'type' => $lh['type'],
                    'is_recurring' => 0,
                    'description' => $lh['desc']
                ];
            }
        }

        $inserted = 0;
        foreach ($templates as $item) {
            $existing = $this->isHoliday($item['date']);
            if (!$existing) {
                $this->addHoliday($item);
                $inserted++;
            }
        }

        return $inserted;
    }

    /**
     * Lấy toàn bộ sự kiện cho Calendar View (Ngày lễ + Đơn nghỉ phép đã duyệt)
     */
    public function getCalendarEvents(string $startDate, string $endDate, ?int $deptId = null): array
    {
        $events = [];

        // 1. Ngày Lễ
        $sqlHolidays = "SELECT * FROM {$this->table} WHERE date BETWEEN :start AND :end";
        $paramsH = ['start' => $startDate, 'end' => $endDate];
        if ($deptId !== null) {
            $sqlHolidays .= " AND (applies_to_department_id IS NULL OR applies_to_department_id = :dept_id)";
            $paramsH['dept_id'] = $deptId;
        }
        $sqlHolidays .= " ORDER BY date ASC";
        $this->db->query($sqlHolidays, $paramsH);
        $holidays = $this->db->fetchAll();

        foreach ($holidays as $h) {
            $isCompany = ($h['type'] === 'Company');
            $events[] = [
                'id'          => 'holiday_' . $h['id'],
                'holiday_id'  => $h['id'],
                'title'       => ($isCompany ? '🏢 ' : '🇻🇳 ') . $h['name'],
                'start'       => $h['date'],
                'end'         => $h['date'],
                'allDay'      => true,
                'color'       => $isCompany ? '#d97706' : '#dc2626',
                'textColor'   => '#ffffff',
                'type'        => 'Holiday',
                'subType'     => $h['type'],
                'description' => $h['description'] ?? '',
                'recurring'   => (bool)$h['is_recurring']
            ];
        }

        // 2. Nghỉ phép đã duyệt (Approved Leave Requests)
        $sqlLeaves = "SELECT lr.id, lr.start_date, lr.end_date, lr.total_days, lr.reason,
                             e.full_name, e.emp_code, e.id as employee_id,
                             lt.name as leave_type_name, lt.code as leave_type_code
                      FROM leave_requests lr
                      JOIN employees e ON lr.employee_id = e.id
                      LEFT JOIN leave_types lt ON lr.leave_type_id = lt.id
                      WHERE lr.status = 'Approved'
                        AND lr.start_date <= :end 
                        AND lr.end_date >= :start";
        $paramsL = ['start' => $startDate, 'end' => $endDate];

        if ($deptId !== null) {
            $sqlLeaves .= " AND e.department_id = :dept_id";
            $paramsL['dept_id'] = $deptId;
        }

        $sqlLeaves .= " ORDER BY lr.start_date ASC";
        $this->db->query($sqlLeaves, $paramsL);
        $leaves = $this->db->fetchAll();

        foreach ($leaves as $lv) {
            // FullCalendar / Calendar Grid: end date
            $endDateObj = new DateTime($lv['end_date']);
            $endDateObj->modify('+1 day');

            $color = match($lv['leave_type_code'] ?? '') {
                'AL'    => '#2563eb',
                'SL'    => '#10b981',
                'ML'    => '#ec4899',
                'CL'    => '#f59e0b',
                default => '#6366f1'
            };

            $events[] = [
                'id'          => 'leave_' . $lv['id'],
                'leave_id'    => $lv['id'],
                'title'       => '🏖️ ' . $lv['emp_code'] . ' - ' . $lv['full_name'] . ' (' . ($lv['leave_type_name'] ?? 'Nghỉ phép') . ')',
                'start'       => $lv['start_date'],
                'end'         => $endDateObj->format('Y-m-d'),
                'allDay'      => true,
                'color'       => $color,
                'textColor'   => '#ffffff',
                'type'        => 'Leave',
                'employee'    => $lv['full_name'],
                'emp_code'    => $lv['emp_code'],
                'days'        => (float)$lv['total_days'],
                'reason'      => $lv['reason'] ?? ''
            ];
        }

        return $events;
    }
}
