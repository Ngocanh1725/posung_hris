<?php
/**
 * ============================================================
 *  POSUNG HRIS – Timesheet Model
 * ============================================================
 *  Quản lý dữ liệu chấm công hàng ngày của nhân viên.
 * ============================================================
 */

class Timesheet extends BaseModel
{
    protected string $table = 'timesheets';

    /**
     * Mô phỏng: Nhập dữ liệu chấm công từ máy chấm công (hoặc file Excel)
     * 
     * @param array $logData Mảng chứa dữ liệu chấm công từng ngày của NV
     * @return int Số bản ghi đã chèn thành công
     */
    public function importFromMachine(array $logData): int
    {
        $count = 0;
        foreach ($logData as $log) {
            // Xác định shift_type cơ bản (Trong thực tế cần bộ xử lý phức tạp hơn)
            $shiftType = $log['shift_type'] ?? 'Day';
            $isCleanroom = !empty($log['is_cleanroom']) ? 1 : 0;
            $otHours = isset($log['ot_hours']) ? (float)$log['ot_hours'] : 0.0;
            
            try {
                // Sử dụng INSERT IGNORE (hoặc ON DUPLICATE KEY UPDATE)
                // Dựa trên unique key uq_ts_emp_date (employee_id, work_date)
                $sql = "INSERT INTO timesheets (employee_id, project_id, work_date, check_in, check_out, shift_type, is_cleanroom, ot_hours, status)
                        VALUES (:emp, :proj, :date, :in, :out, :shift, :cr, :ot, 'Approved')
                        ON DUPLICATE KEY UPDATE 
                            check_in = VALUES(check_in), check_out = VALUES(check_out),
                            shift_type = VALUES(shift_type), is_cleanroom = VALUES(is_cleanroom),
                            ot_hours = VALUES(ot_hours)";
                            
                $this->db->query($sql, [
                    'emp'   => $log['employee_id'],
                    'proj'  => $log['project_id'] ?? null,
                    'date'  => $log['work_date'],
                    'in'    => $log['check_in'] ?? null,
                    'out'   => $log['check_out'] ?? null,
                    'shift' => $shiftType,
                    'cr'    => $isCleanroom,
                    'ot'    => $otHours
                ]);
                $count++;
            } catch (Exception $e) {
                error_log("Lỗi import chấm công: " . $e->getMessage());
            }
        }
        return $count;
    }

    /**
     * Tổng hợp dữ liệu chấm công của 1 nhân viên trong 1 tháng
     * Để truyền vào Payroll Engine.
     *
     * Chuyển đổi:
     * - Day: 1.0 ngày
     * - Night: 1.3 ngày
     * - Sunday: 2.0 ngày
     * - Holiday: 3.0 ngày
     *
     * @return array [
     *   'actual_days' => tổng ngày công quy đổi,
     *   'ot_day_hours' => giờ OT ban ngày,
     *   'ot_night_hours' => giờ OT ban đêm,
     *   'ot_sunday_hours' => giờ OT ngày nghỉ,
     *   'cleanroom_days' => số ngày làm phòng sạch
     * ]
     */
    public function getMonthlySummary(int $employeeId, int $month, int $year): array
    {
        // Chú ý: Ở hệ thống thực tế, OT_Night và OT_Sunday phải được nhập riêng hoặc tính dựa trên check_out
        // Để đơn giản hóa, ta coi OT trong ca Day là OT Day, OT trong ca Night là OT Night.
        
        $sql = "SELECT 
                    SUM(CASE WHEN shift_type = 'Day' THEN 1 ELSE 0 END) as day_shifts,
                    SUM(CASE WHEN shift_type = 'Night' THEN 1 ELSE 0 END) as night_shifts,
                    SUM(CASE WHEN shift_type = 'Sunday' THEN 1 ELSE 0 END) as sunday_shifts,
                    SUM(CASE WHEN shift_type = 'Holiday' THEN 1 ELSE 0 END) as holiday_shifts,
                    
                    SUM(CASE WHEN shift_type = 'Day' THEN ot_hours ELSE 0 END) as ot_day_hours,
                    SUM(CASE WHEN shift_type = 'Night' THEN ot_hours ELSE 0 END) as ot_night_hours,
                    SUM(CASE WHEN shift_type = 'Sunday' OR shift_type = 'Holiday' THEN ot_hours ELSE 0 END) as ot_sunday_hours,
                    
                    SUM(is_cleanroom) as cleanroom_days
                FROM timesheets
                WHERE employee_id = :emp 
                  AND MONTH(work_date) = :m 
                  AND YEAR(work_date) = :y
                  AND status = 'Approved'";
                  
        $this->db->query($sql, ['emp' => $employeeId, 'm' => $month, 'y' => $year]);
        $row = $this->db->fetch();

        if (!$row || $row['day_shifts'] === null) {
            return [
                'actual_days'     => 0.0,
                'ot_day_hours'    => 0.0,
                'ot_night_hours'  => 0.0,
                'ot_sunday_hours' => 0.0,
                'cleanroom_days'  => 0
            ];
        }

        // Quy đổi ra ngày công chuẩn
        $actualDays = ($row['day_shifts'] * 1.0) 
                    + ($row['night_shifts'] * 1.3) 
                    + ($row['sunday_shifts'] * 2.0)
                    + ($row['holiday_shifts'] * 3.0);

        return [
            'actual_days'     => round($actualDays, 2),
            'ot_day_hours'    => (float) $row['ot_day_hours'],
            'ot_night_hours'  => (float) $row['ot_night_hours'],
            'ot_sunday_hours' => (float) $row['ot_sunday_hours'],
            'cleanroom_days'  => (int) $row['cleanroom_days']
        ];
    }

    /**
     * Lấy toàn bộ ma trận chấm công trong tháng (phục vụ hiển thị lưới grid)
     * Trả về mảng 2 chiều: array[employee_id][day_of_month] = 'X' / 'N' / ...
     */
    public function getMonthlyGrid(int $month, int $year, ?int $projectId = null): array
    {
        $sql = "SELECT t.*, e.emp_code, e.full_name, p.pos_title, d.dept_name
                FROM timesheets t
                JOIN employees e ON t.employee_id = e.id
                LEFT JOIN positions p ON e.position_id = p.id
                LEFT JOIN departments d ON e.department_id = d.id
                WHERE MONTH(t.work_date) = :m AND YEAR(t.work_date) = :y";
        
        $params = ['m' => $month, 'y' => $year];

        if ($projectId) {
            $sql .= " AND t.project_id = :proj";
            $params['proj'] = $projectId;
        }

        $this->db->query($sql, $params);
        $records = $this->db->fetchAll();

        // Xây dựng Grid
        $grid = [];
        foreach ($records as $r) {
            $empId = $r['employee_id'];
            if (!isset($grid[$empId])) {
                $grid[$empId] = [
                    'employee_id' => $empId,
                    'emp_code'    => $r['emp_code'],
                    'full_name'   => $r['full_name'],
                    'pos_title'   => $r['pos_title'],
                    'dept_name'   => $r['dept_name'],
                    'days'        => array_fill(1, 31, '') // 1->31
                ];
            }

            $day = (int) date('d', strtotime($r['work_date']));
            
            // Ký hiệu
            $symbol = 'X';
            if ($r['shift_type'] === 'Night') $symbol = 'N';
            if ($r['shift_type'] === 'Sunday') $symbol = 'CN';
            if ($r['shift_type'] === 'Holiday') $symbol = 'L';
            if ($r['is_cleanroom']) $symbol = 'CR';
            
            // Cộng thêm nếu có OT
            if ((float)$r['ot_hours'] > 0) {
                $symbol .= '<br><small class="text-danger">+'.(float)$r['ot_hours'].'</small>';
            }

            $grid[$empId]['days'][$day] = $symbol;
        }

        return $grid;
    }
}
