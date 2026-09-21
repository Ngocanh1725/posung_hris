<?php
/**
 * ============================================================
 *  POSUNG HRIS – Timesheet Model
 * ============================================================
 */

class Timesheet extends BaseModel
{
    protected string $table = 'timesheets';

    /**
     * Đồng bộ dữ liệu chấm công từ API
     */
    public function syncData(array $payload): int
    {
        $count = 0;
        foreach ($payload as $log) {
            $empCode = $log['employee_code'] ?? '';
            $timestamp = $log['timestamp'] ?? '';
            $deviceIp = $log['device_ip'] ?? '';
            $projectId = $log['project_id'] ?? null;
            $verificationType = $log['verification_type'] ?? '';

            if (!$empCode || !$timestamp) continue;

            // Khớp mã nhân viên
            $this->db->query("SELECT id FROM employees WHERE emp_code = :code", ['code' => $empCode]);
            $emp = $this->db->fetch();
            if (!$emp) continue;
            
            $employeeId = $emp['id'];
            $workDate = date('Y-m-d', strtotime($timestamp));
            $timeTime = date('H:i:s', strtotime($timestamp));

            // Kiểm tra xem đã có bản ghi trong ngày chưa
            $this->db->query("SELECT * FROM timesheets WHERE employee_id = :emp AND work_date = :date AND project_id = :proj", [
                'emp' => $employeeId,
                'date' => $workDate,
                'proj' => $projectId
            ]);
            $existing = $this->db->fetch();

            if ($existing) {
                // Đã có, tiến hành check-out hoặc cập nhật check-out
                if ($existing['status'] === 'Locked') continue; // Không sửa nếu đã khóa

                // Logic: nếu check_in lớn hơn time này thì đổi lại, hoặc update check_out
                $checkIn = $existing['check_in'];
                $checkOut = $existing['check_out'];
                
                if (!$checkIn || $timeTime < $checkIn) {
                    $checkIn = $timeTime;
                }
                if (!$checkOut || $timeTime > $checkOut) {
                    $checkOut = $timeTime;
                }

                // Tính toán loại ca, OT...
                $calc = $this->calculateShiftAndOT($checkIn, $checkOut, $workDate);

                $sql = "UPDATE timesheets SET check_in = :in, check_out = :out, shift_type = :shift, ot_hours = :ot, sync_status = 'synced', device_ip = :ip, verification_type = :ver, updated_at = NOW() 
                        WHERE id = :id";
                $this->db->query($sql, [
                    'in' => $checkIn,
                    'out' => $checkOut,
                    'shift' => $calc['shift_type'],
                    'ot' => $calc['ot_hours'],
                    'ip' => $deviceIp,
                    'ver' => $verificationType,
                    'id' => $existing['id']
                ]);
            } else {
                // Tạo mới check-in
                $calc = $this->calculateShiftAndOT($timeTime, $timeTime, $workDate); // Chưa có check-out
                
                $sql = "INSERT INTO timesheets (employee_id, project_id, work_date, check_in, check_out, shift_type, ot_hours, status, sync_status, device_ip, verification_type)
                        VALUES (:emp, :proj, :date, :in, :out, :shift, :ot, 'Approved', 'synced', :ip, :ver)";
                $this->db->query($sql, [
                    'emp' => $employeeId,
                    'proj' => $projectId,
                    'date' => $workDate,
                    'in' => $timeTime,
                    'out' => clone $timeTime ? $timeTime : null, // Mới check-in thì in và out như nhau hoặc out null
                    'shift' => $calc['shift_type'],
                    'ot' => 0,
                    'ip' => $deviceIp,
                    'ver' => $verificationType
                ]);
            }
            $count++;
        }
        return $count;
    }

    private function calculateShiftAndOT(string $checkIn, string $checkOut, string $date): array
    {
        $shiftType = 'Day';
        $otHours = 0.0;
        
        $dayOfWeek = date('w', strtotime($date));
        if ($dayOfWeek == 0) { // Sunday
            $shiftType = 'Sunday';
        }
        
        // Ca đêm: 21:00 - 05:00. Để đơn giản, ta kiểm tra giờ check_in
        $hourIn = (int)date('H', strtotime($checkIn));
        if ($hourIn >= 21 || $hourIn < 5) {
            if ($shiftType !== 'Sunday') $shiftType = 'Night';
        }

        // Tính OT:
        if ($checkIn !== $checkOut) {
            $timeIn = strtotime($checkIn);
            $timeOut = strtotime($checkOut);
            
            // Nếu check_out sau 17:30 thì bắt đầu tính OT
            $otStart = strtotime('17:30:00');
            if ($timeOut > $otStart) {
                // Tính giờ OT
                $otSeconds = $timeOut - max($timeIn, $otStart);
                $otHours = round($otSeconds / 3600, 1);
            }
        }
        
        return [
            'shift_type' => $shiftType,
            'ot_hours' => $otHours
        ];
    }

    public function lockTimesheet(int $month, int $year, int $projectId): bool
    {
        try {
            $this->db->query(
                "UPDATE timesheets SET status = 'Locked' WHERE MONTH(work_date) = :m AND YEAR(work_date) = :y AND project_id = :p AND status != 'Locked'",
                ['m' => $month, 'y' => $year, 'p' => $projectId]
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    public function getMonthlyGrid(int $month, int $year, ?int $projectId = null): array
    {
        $params = ['m' => $month, 'y' => $year];
        $projectFilter = "";
        if ($projectId) {
            $projectFilter = " AND t.project_id = :p ";
            $params['p'] = $projectId;
        }

        $sql = "SELECT t.*, e.emp_code, e.full_name 
                FROM timesheets t
                JOIN employees e ON t.employee_id = e.id
                WHERE MONTH(t.work_date) = :m AND YEAR(t.work_date) = :y $projectFilter
                ORDER BY e.emp_code, t.work_date";
                
        $this->db->query($sql, $params);
        $records = $this->db->fetchAll();

        $grid = [];
        foreach ($records as $row) {
            $empId = $row['employee_id'];
            if (!isset($grid[$empId])) {
                $grid[$empId] = [
                    'employee' => $row,
                    'days' => array_fill(1, 31, null),
                    'total_actual' => 0,
                    'total_ot150' => 0,
                    'total_ot200' => 0,
                    'total_cr' => 0
                ];
            }
            
            $day = (int)date('d', strtotime($row['work_date']));
            $symbol = 'X';
            if ($row['shift_type'] === 'Night') $symbol = 'Đ';
            if ($row['ot_hours'] > 0) $symbol = 'OT';
            // P = Nghỉ phép (đơn giản hóa)
            
            $grid[$empId]['days'][$day] = $symbol;
            $grid[$empId]['total_actual'] += 1;
            
            if ($row['shift_type'] === 'Day') {
                $grid[$empId]['total_ot150'] += $row['ot_hours'];
            } elseif ($row['shift_type'] === 'Night' || $row['shift_type'] === 'Sunday') {
                $grid[$empId]['total_ot200'] += $row['ot_hours'];
            }
            
            if ($row['is_cleanroom']) {
                $grid[$empId]['total_cr'] += 1;
            }
        }
        
        return $grid;
    }

    public function getMonthlySummary(int $employeeId, int $month, int $year): array
    {
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
                  AND status = 'Locked'";
                  
        $this->db->query($sql, ['emp' => $employeeId, 'm' => $month, 'y' => $year]);
        $row = $this->db->fetch();

        if (!$row || $row['day_shifts'] === null) {
            return [
                'actual_days' => 0,
                'ot_day_hours' => 0,
                'ot_night_hours' => 0,
                'ot_sunday_hours' => 0,
                'cleanroom_days' => 0
            ];
        }

        $actualDays = (float)$row['day_shifts'] + ((float)$row['night_shifts'] * 1.3) + ((float)$row['sunday_shifts'] * 2.0) + ((float)$row['holiday_shifts'] * 3.0);

        return [
            'actual_days' => round($actualDays, 1),
            'ot_day_hours' => (float)$row['ot_day_hours'],
            'ot_night_hours' => (float)$row['ot_night_hours'],
            'ot_sunday_hours' => (float)$row['ot_sunday_hours'],
            'cleanroom_days' => (int)$row['cleanroom_days']
        ];
    }
}
