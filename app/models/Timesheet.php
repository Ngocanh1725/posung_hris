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
     * Đồng bộ dữ liệu chấm công từ API (Máy Edge FaceID tại công trường)
     */
    public function importEdgeData(array $records): int
    {
        if (empty($records)) return 0;
        
        // Tối ưu N+1: Lấy trước toàn bộ id của employees có trong mảng records
        $empCodes = array_map(function($log) {
            return $log['Employee ID'] ?? ($log['employee_code'] ?? ($log['ma_nhan_vien'] ?? ''));
        }, $records);
        $empCodes = array_unique(array_filter($empCodes));
        
        $employeeCache = [];
        if (!empty($empCodes)) {
            $placeholders = str_repeat('?,', count($empCodes) - 1) . '?';
            $this->db->query("SELECT id, emp_code FROM employees WHERE emp_code IN ($placeholders)", array_values($empCodes));
            foreach ($this->db->fetchAll() as $row) {
                $employeeCache[$row['emp_code']] = $row['id'];
            }
        }

        $count = 0;
        
        $this->db->beginTransaction();
        try {
            foreach ($records as $log) {
                $empCode = $log['Employee ID'] ?? ($log['employee_code'] ?? ($log['ma_nhan_vien'] ?? ''));
                $timestamp = $log['Timestamp'] ?? ($log['timestamp'] ?? '');
                $inOut = $log['In/Out'] ?? ($log['in_out'] ?? 'IN');
                $deviceId = $log['Device ID'] ?? ($log['device_id'] ?? '');
                $isCleanroom = $log['Cleanroom Flag'] ?? ($log['is_cleanroom'] ?? 0);
                $projectId = $log['project_id'] ?? null;
                $verificationType = $log['verification_type'] ?? '';

                if (!$empCode || !$timestamp) continue;

                // Lấy id từ cache
                if (!isset($employeeCache[$empCode])) continue;
                
                $employeeId = $employeeCache[$empCode];
                $workDate = date('Y-m-d', strtotime($timestamp));
                $timeTime = date('H:i:s', strtotime($timestamp));

                // Kiểm tra xem đã có bản ghi trong ngày chưa
                $this->db->query("SELECT * FROM timesheets WHERE employee_id = :emp AND work_date = :date AND (project_id = :proj OR project_id IS NULL)", [
                    'emp' => $employeeId,
                    'date' => $workDate,
                    'proj' => $projectId
                ]);
                $existing = $this->db->fetch();

                if ($existing) {
                    if ($existing['status'] === 'Locked') continue;

                    $checkIn = $existing['check_in'];
                    $checkOut = $existing['check_out'];
                    
                    if ($inOut === 'IN' && $checkIn === $timeTime) continue;
                    if ($inOut === 'OUT' && $checkOut === $timeTime) continue;
                    
                    if ($inOut === 'IN') {
                        if (!$checkIn || $timeTime < $checkIn) {
                            $checkIn = $timeTime;
                        }
                    } else {
                        if (!$checkOut || $timeTime > $checkOut) {
                            $checkOut = $timeTime;
                        }
                    }

                    $calc = $this->calculateShiftAndOT($checkIn, $checkOut, $workDate);

                    $sql = "UPDATE timesheets SET check_in = :in, check_out = :out, shift_type = :shift, ot_hours = :ot, is_cleanroom = :cr, sync_status = 'synced', device_ip = :ip, verification_type = :ver, updated_at = NOW() 
                            WHERE id = :id";
                    $this->db->query($sql, [
                        'in' => $checkIn,
                        'out' => $checkOut,
                        'shift' => $calc['shift_type'],
                        'ot' => $calc['ot_hours'],
                        'cr' => max($isCleanroom, $existing['is_cleanroom']),
                        'ip' => $deviceId,
                        'ver' => $verificationType,
                        'id' => $existing['id']
                    ]);
                } else {
                    $checkIn = ($inOut === 'IN') ? $timeTime : null;
                    $checkOut = ($inOut === 'OUT') ? $timeTime : null;
                    $calc = $this->calculateShiftAndOT($checkIn, $checkOut, $workDate);
                    
                    $sql = "INSERT INTO timesheets (employee_id, project_id, work_date, check_in, check_out, shift_type, ot_hours, is_cleanroom, `status`, sync_status, device_ip, verification_type)
                            VALUES (:emp, :proj, :date, :in, :out, :shift, :ot, :cr, 'Approved', 'synced', :ip, :ver)";
                    $this->db->query($sql, [
                        'emp' => $employeeId,
                        'proj' => $projectId,
                        'date' => $workDate,
                        'in' => $checkIn,
                        'out' => $checkOut,
                        'shift' => $calc['shift_type'],
                        'ot' => 0,
                        'cr' => $isCleanroom,
                        'ip' => $deviceId,
                        'ver' => $verificationType
                    ]);
                }
                $count++;
            }
            $this->db->commit();
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Error in importEdgeData: " . $e->getMessage());
        }
        return $count;
    }

    /**
     * Lưu dữ liệu chấm công thủ công (Từ giao diện web)
     */
    public function saveManualTimesheet(int $employeeId, string $workDate, ?string $checkIn, ?string $checkOut, int $isCleanroom = 0): bool
    {
        $this->db->query("SELECT id, `status` FROM timesheets WHERE employee_id = :emp AND work_date = :date", [
            'emp' => $employeeId,
            'date' => $workDate
        ]);
        $existing = $this->db->fetch();

        if ($existing && $existing['status'] === 'Locked') {
            return false;
        }

        $calc = $this->calculateShiftAndOT($checkIn, $checkOut, $workDate);

        if ($existing) {
            $sql = "UPDATE timesheets 
                    SET check_in = :in, check_out = :out, shift_type = :shift, ot_hours = :ot, 
                        is_cleanroom = :cr, sync_status = 'manual', verification_type = 'HR_Manual', updated_at = NOW() 
                    WHERE id = :id";
            $this->db->query($sql, [
                'in' => $checkIn,
                'out' => $checkOut,
                'shift' => $calc['shift_type'],
                'ot' => $calc['ot_hours'],
                'cr' => $isCleanroom,
                'id' => $existing['id']
            ]);
        } else {
            $this->db->query("SELECT current_project_id FROM employees WHERE id = :emp", ['emp' => $employeeId]);
            $emp = $this->db->fetch();
            $projectId = $emp['current_project_id'] ?? null;

            $sql = "INSERT INTO timesheets (employee_id, project_id, work_date, check_in, check_out, shift_type, ot_hours, is_cleanroom, `status`, sync_status, verification_type)
                    VALUES (:emp, :proj, :date, :in, :out, :shift, :ot, :cr, 'Approved', 'manual', 'HR_Manual')";
            $this->db->query($sql, [
                'emp' => $employeeId,
                'proj' => $projectId,
                'date' => $workDate,
                'in' => $checkIn,
                'out' => $checkOut,
                'shift' => $calc['shift_type'],
                'ot' => $calc['ot_hours'],
                'cr' => $isCleanroom
            ]);
        }
        return true;
    }

    /**
     * Lưu dữ liệu chấm công từ GPS Mobile
     */
    public function saveGpsCheckin(int $employeeId, int $projectId, string $workDate, string $time, float $lat, float $lng, int $distance, string $inOut): bool
    {
        $this->db->query("SELECT id, `status`, check_in, check_out FROM timesheets WHERE employee_id = :emp AND work_date = :date AND project_id = :proj", [
            'emp' => $employeeId,
            'date' => $workDate,
            'proj' => $projectId
        ]);
        $existing = $this->db->fetch();

        if ($existing && $existing['status'] === 'Locked') {
            return false;
        }

        $checkIn = $existing['check_in'] ?? null;
        $checkOut = $existing['check_out'] ?? null;

        if ($inOut === 'IN') {
            if (!$checkIn || $time < $checkIn) $checkIn = $time;
        } else {
            if (!$checkOut || $time > $checkOut) $checkOut = $time;
        }

        $calc = $this->calculateShiftAndOT($checkIn, $checkOut, $workDate);

        if ($existing) {
            $sql = "UPDATE timesheets 
                    SET check_in = :in, check_out = :out, shift_type = :shift, ot_hours = :ot, 
                        checkin_lat = :lat, checkin_long = :lng, checkin_distance_m = :dist, 
                        checkin_device_type = 'SITE_GPS', sync_status = 'synced', verification_type = 'Mobile_GPS', 
                        approval_status = 'PENDING_FOREMAN', updated_at = NOW() 
                    WHERE id = :id";
            $this->db->query($sql, [
                'in' => $checkIn, 'out' => $checkOut, 'shift' => $calc['shift_type'], 'ot' => $calc['ot_hours'],
                'lat' => $lat, 'lng' => $lng, 'dist' => $distance, 'id' => $existing['id']
            ]);
        } else {
            $sql = "INSERT INTO timesheets (employee_id, project_id, work_date, check_in, check_out, shift_type, ot_hours, 
                    checkin_lat, checkin_long, checkin_distance_m, checkin_device_type, `status`, approval_status, sync_status, verification_type)
                    VALUES (:emp, :proj, :date, :in, :out, :shift, :ot, :lat, :lng, :dist, 'SITE_GPS', 'Pending', 'PENDING_FOREMAN', 'synced', 'Mobile_GPS')";
            $this->db->query($sql, [
                'emp' => $employeeId, 'proj' => $projectId, 'date' => $workDate,
                'in' => $checkIn, 'out' => $checkOut, 'shift' => $calc['shift_type'], 'ot' => $calc['ot_hours'],
                'lat' => $lat, 'lng' => $lng, 'dist' => $distance
            ]);
        }
        return true;
    }

    public function updateApprovalStatus(array $ids, string $status): bool
    {
        if (empty($ids)) return false;
        $in = implode(',', array_map('intval', $ids));
        
        // Cập nhật trạng thái duyệt cấp công trường, nếu duyệt cuối cùng (HR) thì update luôn status = Approved
        $finalStatus = ($status === 'APPROVED') ? 'Approved' : 'Pending';
        if ($status === 'REJECTED') $finalStatus = 'Pending';
        
        $this->db->query("UPDATE timesheets SET approval_status = :astat, `status` = :fstat, updated_at = NOW() WHERE id IN ($in)", [
            'astat' => $status, 'fstat' => $finalStatus
        ]);
        return true;
    }

    private function calculateShiftAndOT(?string $checkIn, ?string $checkOut, string $date): array
    {
        $shiftType = 'Day';
        $otHours = 0.0;
        
        $dayOfWeek = date('w', strtotime($date));
        if ($dayOfWeek == 0) { // Sunday
            $shiftType = 'Sunday';
        }
        
        // Ca đêm: 21:00 - 05:00. Để đơn giản, ta kiểm tra giờ check_in
        if ($checkIn) {
            $hourIn = (int)date('H', strtotime($checkIn));
            if ($hourIn >= 21 || $hourIn < 5) {
                if ($shiftType !== 'Sunday') $shiftType = 'Night';
            }
        }

        // Tính OT:
        if ($checkIn && $checkOut && $checkIn !== $checkOut) {
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
                "UPDATE timesheets SET `status` = 'Locked' WHERE MONTH(work_date) = :m AND YEAR(work_date) = :y AND project_id = :p AND `status` != 'Locked'",
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

        $sql = "SELECT t.*, e.emp_code, e.full_name, e.department_id, d.dept_name, p.pos_title
                FROM timesheets t
                JOIN employees e ON t.employee_id = e.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
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
                  AND `status` = 'Locked'";
                  
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

    public function getMonthlySummaryByProject(int $employeeId, int $month, int $year): array
    {
        $sql = "SELECT 
                    project_id,
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
                  AND `status` = 'Locked'
                GROUP BY project_id";
                  
        $this->db->query($sql, ['emp' => $employeeId, 'm' => $month, 'y' => $year]);
        $rows = $this->db->fetchAll();

        $summaries = [];
        foreach ($rows as $row) {
            if ($row['day_shifts'] === null) continue;
            
            $actualDays = (float)$row['day_shifts'] + ((float)$row['night_shifts'] * 1.3) + ((float)$row['sunday_shifts'] * 2.0) + ((float)$row['holiday_shifts'] * 3.0);
            
            $summaries[] = [
                'project_id' => $row['project_id'],
                'actual_days' => round($actualDays, 1),
                'ot_day_hours' => (float)$row['ot_day_hours'],
                'ot_night_hours' => (float)$row['ot_night_hours'],
                'ot_sunday_hours' => (float)$row['ot_sunday_hours'],
                'cleanroom_days' => (int)$row['cleanroom_days']
            ];
        }

        return $summaries;
    }
}
