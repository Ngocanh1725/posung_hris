<?php
/**
 * ============================================================
 *  POSUNG HRIS – LeaveHoliday Model
 * ============================================================
 *  Quản lý Lịch nghỉ Lễ / Tết / Ngày kỷ niệm công ty và
 *  Công cụ tự động tính toán ngày làm việc thực tế (trừ Weekend + Holiday).
 * ============================================================
 */

class LeaveHoliday extends BaseModel
{
    protected string $table = 'leave_holidays';

    /**
     * Lấy danh sách ngày nghỉ lễ theo năm
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
     * Lấy các ngày lễ sắp tới (từ hôm nay trở đi)
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
     * Kiểm tra một ngày cụ thể có phải là ngày lễ không
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
     * Lấy danh sách ngày lễ rơi vào khoảng thời gian [startDate, endDate]
     */
    public function getHolidaysBetween(string $startDate, string $endDate, ?int $deptId = null): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE date BETWEEN :start AND :end";
        $params = ['start' => $startDate, 'end' => $endDate];

        if ($deptId !== null) {
            $sql .= " AND (applies_to_department_id IS NULL OR applies_to_department_id = :dept_id)";
            $params['dept_id'] = $deptId;
        }

        $sql .= " ORDER BY date ASC";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * ĐỘNG CƠ TỰ ĐỘNG TÍNH SỐ NGÀY NGHỈ THỰC TẾ (WORKING DAYS)
     * Chuẩn Frappe HRMS / Việt Nam:
     * - Trừ các ngày Chủ Nhật (Sunday = 0)
     * - Trừ các ngày Nghỉ Lễ Quốc Gia & Nghỉ Lễ Công Ty (Holidays)
     * - Không trừ trùng lặp (ví dụ ngày Lễ rơi vào Chủ Nhật)
     *
     * @param string $startDate 'Y-m-d'
     * @param string $endDate   'Y-m-d'
     * @param int|null $deptId  Phòng ban nhân viên
     * @param bool $excludeSundaysOnly true: chỉ trừ Chủ Nhật; false: trừ cả T7 và CN
     * @return array
     */
    public function calculateWorkingDays(string $startDate, string $endDate, ?int $deptId = null, bool $excludeSundaysOnly = true): array
    {
        $start = strtotime($startDate);
        $end = strtotime($endDate);

        if ($start > $end) {
            return [
                'total_calendar_days' => 0,
                'working_days'        => 0,
                'weekend_days'        => 0,
                'holiday_days'        => 0,
                'holidays'            => [],
                'breakdown'           => []
            ];
        }

        // 1. Nạp trước toàn bộ ngày lễ trong khoảng
        $holidays = $this->getHolidaysBetween($startDate, $endDate, $deptId);
        $holidayDates = [];
        foreach ($holidays as $h) {
            $holidayDates[$h['date']] = $h['name'];
        }

        $totalCalendarDays = 0;
        $workingDays = 0;
        $weekendDays = 0;
        $holidayDays = 0;
        $breakdown = [];

        $current = $start;
        while ($current <= $end) {
            $totalCalendarDays++;
            $curDateStr = date('Y-m-d', $current);
            $dayOfWeek = (int)date('w', $current); // 0 = Sunday, 6 = Saturday

            $isWeekend = false;
            if ($excludeSundaysOnly) {
                // POSUNG: Công trường & khối văn phòng nghỉ Chủ nhật
                if ($dayOfWeek === 0) {
                    $isWeekend = true;
                }
            } else {
                if ($dayOfWeek === 0 || $dayOfWeek === 6) {
                    $isWeekend = true;
                }
            }

            $isHoliday = isset($holidayDates[$curDateStr]);

            if ($isHoliday) {
                $holidayDays++;
                $breakdown[] = [
                    'date'   => $curDateStr,
                    'type'   => 'Holiday',
                    'reason' => $holidayDates[$curDateStr]
                ];
            } elseif ($isWeekend) {
                $weekendDays++;
                $breakdown[] = [
                    'date'   => $curDateStr,
                    'type'   => 'Weekend',
                    'reason' => ($dayOfWeek === 0 ? 'Chủ Nhật' : 'Thứ Bảy')
                ];
            } else {
                $workingDays++;
                $breakdown[] = [
                    'date'   => $curDateStr,
                    'type'   => 'Working',
                    'reason' => 'Ngày làm việc tính phép'
                ];
            }

            $current = strtotime('+1 day', $current);
        }

        return [
            'total_calendar_days' => $totalCalendarDays,
            'working_days'        => (float)$workingDays,
            'weekend_days'        => $weekendDays,
            'holiday_days'        => $holidayDays,
            'holidays'            => $holidays,
            'breakdown'           => $breakdown
        ];
    }

    /**
     * Thêm ngày nghỉ lễ mới
     */
    public function addHoliday(array $data): int
    {
        $sql = "INSERT INTO {$this->table} 
                (name, date, type, is_recurring, applies_to_department_id, description, created_at)
                VALUES 
                (:name, :date, :type, :is_recurring, :dept_id, :desc, NOW())";
        
        $this->db->query($sql, [
            'name'         => $data['name'],
            'date'         => $data['date'],
            'type'         => $data['type'] ?? 'National',
            'is_recurring' => !empty($data['is_recurring']) ? 1 : 0,
            'dept_id'      => !empty($data['applies_to_department_id']) ? (int)$data['applies_to_department_id'] : null,
            'desc'         => $data['description'] ?? null
        ]);

        return (int)$this->db->lastInsertId();
    }

    /**
     * Cập nhật ngày nghỉ lễ
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

        return $this->db->query($sql, [
            'name'         => $data['name'],
            'date'         => $data['date'],
            'type'         => $data['type'] ?? 'National',
            'is_recurring' => !empty($data['is_recurring']) ? 1 : 0,
            'dept_id'      => !empty($data['applies_to_department_id']) ? (int)$data['applies_to_department_id'] : null,
            'desc'         => $data['description'] ?? null,
            'id'           => $id
        ]);
    }

    /**
     * Xóa ngày nghỉ lễ
     */
    public function deleteHoliday(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->query($sql, ['id' => $id]);
    }
}
