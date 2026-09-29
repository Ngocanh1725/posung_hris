<?php
/**
 * ============================================================
 *  POSUNG HRIS – LeaveAllocation Model
 * ============================================================
 *  Quản lý quỹ phép năm theo chuẩn Frappe HRMS Leave Allocation:
 *  - Phân bổ định ngạch phép năm theo nhân viên / loại phép / năm
 *  - Tự động tính tỷ lệ tháng làm việc (Proration) cho nhân viên mới
 *  - Tự động cộng ngày thâm niên (+1 ngày / 5 năm - BLLĐ 2019 Điều 114)
 *  - Chuyển tiếp phép dư năm cũ (Carry Forward) kèm hạn sử dụng
 *  - Kiểm soát số dư khả dụng (Remaining Balance)
 * ============================================================
 */

class LeaveAllocation extends BaseModel
{
    protected string $table = 'leave_allocations';

    /**
     * Lấy danh sách phân bổ phép theo năm kèm bộ lọc
     */
    public function getAllocations(int $year, ?int $deptId = null, ?string $search = null, int $limit = 100, int $offset = 0): array
    {
        $sql = "SELECT la.*, e.emp_code, e.full_name, e.avatar_path, e.join_date,
                       d.dept_name, p.pos_title, lt.name as leave_type_name, lt.code as leave_type_code,
                       lt.max_carry_forward, lt.carry_forward_expiry_months
                FROM {$this->table} la
                JOIN employees e ON la.employee_id = e.id
                JOIN leave_types lt ON la.leave_type_id = lt.id
                LEFT JOIN departments d ON e.department_id = d.id
                LEFT JOIN positions p ON e.position_id = p.id
                WHERE la.year = :year";
        
        $params = ['year' => $year];

        if ($deptId) {
            $sql .= " AND e.department_id = :dept_id";
            $params['dept_id'] = $deptId;
        }

        if (!empty($search)) {
            $sql .= " AND (e.full_name LIKE :search1 OR e.emp_code LIKE :search2)";
            $params['search1'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY d.id ASC, e.emp_code ASC LIMIT {$limit} OFFSET {$offset}";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Đếm tổng số bản ghi phân bổ theo điều kiện lọc
     */
    public function countAllocations(int $year, ?int $deptId = null, ?string $search = null): int
    {
        $sql = "SELECT COUNT(*) as total
                FROM {$this->table} la
                JOIN employees e ON la.employee_id = e.id
                WHERE la.year = :year";
        
        $params = ['year' => $year];

        if ($deptId) {
            $sql .= " AND e.department_id = :dept_id";
            $params['dept_id'] = $deptId;
        }

        if (!empty($search)) {
            $sql .= " AND (e.full_name LIKE :search1 OR e.emp_code LIKE :search2)";
            $params['search1'] = '%' . $search . '%';
            $params['search2'] = '%' . $search . '%';
        }

        $this->db->query($sql, $params);
        $res = $this->db->fetch();
        return (int)($res['total'] ?? 0);
    }

    /**
     * Thống kê tổng quan quỹ phép của cả công ty theo năm
     */
    public function getYearStats(int $year): array
    {
        $sql = "SELECT 
                    COUNT(DISTINCT employee_id) as total_employees,
                    COALESCE(SUM(entitled_days), 0) as total_entitled,
                    COALESCE(SUM(carried_forward_days), 0) as total_carried,
                    COALESCE(SUM(used_days), 0) as total_used,
                    COALESCE(SUM(remaining_days), 0) as total_remaining
                FROM {$this->table}
                WHERE year = :year AND leave_type_id = 1";
        
        $this->db->query($sql, ['year' => $year]);
        return $this->db->fetch() ?: [
            'total_employees' => 0,
            'total_entitled' => 0,
            'total_carried' => 0,
            'total_used' => 0,
            'total_remaining' => 0
        ];
    }

    /**
     * Lấy phân bổ cụ thể của nhân viên theo năm và loại phép
     */
    public function getByEmployeeAndYear(int $employeeId, int $year, int $leaveTypeId = 1): ?array
    {
        $sql = "SELECT la.*, lt.name as leave_type_name, lt.allow_negative, lt.max_continuous_days
                FROM {$this->table} la
                JOIN leave_types lt ON la.leave_type_id = lt.id
                WHERE la.employee_id = :emp_id AND la.year = :year AND la.leave_type_id = :type_id
                LIMIT 1";
        $this->db->query($sql, [
            'emp_id' => $employeeId,
            'year' => $year,
            'type_id' => $leaveTypeId
        ]);
        $row = $this->db->fetch();
        return $row ?: null;
    }

    /**
     * Lấy số dư chi tiết tất cả các loại phép của nhân viên trong năm (Balance API)
     */
    public function getEmployeeBalances(int $employeeId, ?int $year = null): array
    {
        $year = $year ?: (int)date('Y');

        // Lấy danh sách tất cả leave_types
        $this->db->query("SELECT * FROM leave_types ORDER BY id ASC");
        $leaveTypes = $this->db->fetchAll();

        // Lấy các allocations hiện có
        $sql = "SELECT * FROM {$this->table} WHERE employee_id = :emp_id AND year = :year";
        $this->db->query($sql, ['emp_id' => $employeeId, 'year' => $year]);
        $allocMap = [];
        foreach ($this->db->fetchAll() as $a) {
            $allocMap[$a['leave_type_id']] = $a;
        }

        // Lấy số ngày đang chờ duyệt (Pending) của từng loại
        $sqlPending = "SELECT leave_type_id, COALESCE(SUM(total_days), 0) as pending_days
                       FROM leave_requests 
                       WHERE employee_id = :emp_id 
                         AND status = 'Pending' 
                         AND YEAR(start_date) = :year
                       GROUP BY leave_type_id";
        $this->db->query($sqlPending, ['emp_id' => $employeeId, 'year' => $year]);
        $pendingMap = [];
        foreach ($this->db->fetchAll() as $p) {
            $pendingMap[$p['leave_type_id']] = (float)$p['pending_days'];
        }

        // Lấy số ngày đã duyệt (Approved) của từng loại
        $sqlUsed = "SELECT leave_type_id, COALESCE(SUM(total_days), 0) as used_days
                    FROM leave_requests 
                    WHERE employee_id = :emp_id 
                      AND status = 'Approved' 
                      AND YEAR(start_date) = :year
                    GROUP BY leave_type_id";
        $this->db->query($sqlUsed, ['emp_id' => $employeeId, 'year' => $year]);
        $usedMap = [];
        foreach ($this->db->fetchAll() as $u) {
            $usedMap[$u['leave_type_id']] = (float)$u['used_days'];
        }

        $result = [];
        foreach ($leaveTypes as $lt) {
            $typeId = (int)$lt['id'];
            $alloc = $allocMap[$typeId] ?? null;

            $entitled = $alloc ? (float)$alloc['entitled_days'] : (float)($lt['max_days_per_year'] ?? 0);
            $carried = $alloc ? (float)$alloc['carried_forward_days'] : 0.0;
            $used = $usedMap[$typeId] ?? ($alloc ? (float)$alloc['used_days'] : 0.0);
            $pending = $pendingMap[$typeId] ?? 0.0;

            $totalQuota = $entitled + $carried;
            $remaining = max(0.0, $totalQuota - $used);
            $available = max(0.0, $remaining - $pending);

            // Nghỉ không lương hoặc cho phép âm
            if (!empty($lt['allow_negative']) || (int)$lt['is_paid'] === 0) {
                $available = 999.0;
                $remaining = 999.0;
            }

            $result[] = [
                'leave_type_id'        => $typeId,
                'name'                 => $lt['name'],
                'code'                 => $lt['code'],
                'is_paid'              => (int)$lt['is_paid'],
                'entitled_days'        => $entitled,
                'carried_forward_days' => $carried,
                'total_quota'          => $totalQuota,
                'used_days'            => $used,
                'pending_days'         => $pending,
                'remaining_days'       => $remaining,
                'available_days'       => $available,
                'allow_negative'       => (bool)($lt['allow_negative'] ?? 0),
                'max_continuous_days'  => (int)($lt['max_continuous_days'] ?? 0),
                'effective_to'         => $alloc['effective_to'] ?? null
            ];
        }

        return $result;
    }

    /**
     * Tự động phân bổ quỹ phép năm cho tất cả nhân viên (Frappe HRMS Auto Allocation)
     * - Proration theo ngày vào làm nếu vào làm trong năm
     * - Seniority: +1 ngày cho mỗi 5 năm thâm niên làm việc
     */
    public function autoAllocate(int $year, int $creatorId = 1): array
    {
        $this->db->query("SELECT id, emp_code, full_name, join_date, status FROM employees WHERE status = 'Active'");
        $employees = $this->db->fetchAll();

        // Lấy loại phép năm (ID = 1 hoặc code = 'NP')
        $this->db->query("SELECT * FROM leave_types WHERE code = 'NP' OR id = 1 LIMIT 1");
        $annualLeaveType = $this->db->fetch();
        $leaveTypeId = $annualLeaveType ? (int)$annualLeaveType['id'] : 1;
        $baseDays = $annualLeaveType ? (float)$annualLeaveType['max_days_per_year'] : 12.0;

        $createdCount = 0;
        $updatedCount = 0;

        foreach ($employees as $emp) {
            $empId = (int)$emp['id'];
            $joinDate = $emp['join_date'] ?? null;

            // 1. Tính toán số ngày được hưởng
            $entitledDays = $baseDays;

            if (!empty($joinDate)) {
                $joinYear = (int)date('Y', strtotime($joinDate));
                $joinMonth = (int)date('m', strtotime($joinDate));

                if ($joinYear === $year) {
                    // Vào làm trong năm -> Proration
                    $monthsWorked = 12 - $joinMonth + 1;
                    $entitledDays = round(($monthsWorked / 12.0) * $baseDays, 1);
                } elseif ($joinYear < $year) {
                    // Thâm niên: Mỗi 5 năm + 1 ngày
                    $refDate = $year . '-01-01';
                    $yearsWorked = floor((strtotime($refDate) - strtotime($joinDate)) / (365.25 * 86400));
                    $seniorityBonus = ($yearsWorked >= 5) ? floor($yearsWorked / 5) : 0;
                    $entitledDays = $baseDays + $seniorityBonus;
                }
            }

            // 2. Lấy số ngày đã sử dụng trong năm đó
            $sqlUsed = "SELECT COALESCE(SUM(total_days), 0) as used_days
                        FROM leave_requests
                        WHERE employee_id = :emp_id 
                          AND leave_type_id = :type_id 
                          AND status = 'Approved' 
                          AND YEAR(start_date) = :year";
            $this->db->query($sqlUsed, [
                'emp_id' => $empId,
                'type_id' => $leaveTypeId,
                'year' => $year
            ]);
            $usedDays = (float)$this->db->fetch()['used_days'];

            // 3. Kiểm tra xem đã có bản ghi chưa
            $this->db->query(
                "SELECT id, carried_forward_days FROM {$this->table} WHERE employee_id = :emp AND leave_type_id = :type AND year = :y LIMIT 1",
                ['emp' => $empId, 'type' => $leaveTypeId, 'y' => $year]
            );
            $existing = $this->db->fetch();

            $carriedDays = $existing ? (float)$existing['carried_forward_days'] : 0.0;
            $remainingDays = max(0.0, ($entitledDays + $carriedDays) - $usedDays);

            if ($existing) {
                $this->db->query(
                    "UPDATE {$this->table} 
                     SET entitled_days = :entitled, 
                         used_days = :used, 
                         remaining_days = :remaining,
                         updated_at = NOW()
                     WHERE id = :id",
                    [
                        'entitled'  => $entitledDays,
                        'used'      => $usedDays,
                        'remaining' => $remainingDays,
                        'id'        => $existing['id']
                    ]
                );
                $updatedCount++;
            } else {
                $this->db->query(
                    "INSERT INTO {$this->table} 
                     (employee_id, leave_type_id, year, entitled_days, carried_forward_days, used_days, remaining_days, effective_from, effective_to, created_by, notes, created_at)
                     VALUES 
                     (:emp, :type, :y, :entitled, 0.0, :used, :remaining, :eff_from, :eff_to, :creator, :notes, NOW())",
                    [
                        'emp'       => $empId,
                        'type'      => $leaveTypeId,
                        'y'         => $year,
                        'entitled'  => $entitledDays,
                        'used'      => $usedDays,
                        'remaining' => $remainingDays,
                        'eff_from'  => "{$year}-01-01",
                        'eff_to'    => "{$year}-12-31",
                        'creator'   => $creatorId,
                        'notes'     => "Phân bổ tự động năm {$year} (Frappe HRMS engine)"
                    ]
                );
                $createdCount++;
            }
        }

        return [
            'total_processed' => count($employees),
            'created'         => $createdCount,
            'updated'         => $updatedCount
        ];
    }

    /**
     * Chuyển phép tồn năm cũ sang năm mới (Carry Forward)
     * Tuân thủ quy định max_carry_forward và carry_forward_expiry_months của loại phép
     */
    public function carryForward(int $fromYear, int $toYear, int $creatorId = 1): array
    {
        // 1. Lấy thông tin cấu hình của loại Phép năm
        $this->db->query("SELECT * FROM leave_types WHERE code = 'NP' OR id = 1 LIMIT 1");
        $lt = $this->db->fetch();
        $leaveTypeId = $lt ? (int)$lt['id'] : 1;
        $maxCarryForward = $lt ? (float)$lt['max_carry_forward'] : 5.0;
        $expiryMonths = $lt ? (int)$lt['carry_forward_expiry_months'] : 3;

        // Ngày hết hạn của phép chuyển tiếp (thường là 31/03 của năm mới)
        $expiryDate = date('Y-m-d', strtotime("{$toYear}-01-01 + {$expiryMonths} months - 1 day"));

        // 2. Lấy danh sách nhân viên có phép còn thừa trong năm cũ
        $sql = "SELECT employee_id, remaining_days 
                FROM {$this->table} 
                WHERE year = :from_year AND leave_type_id = :type_id AND remaining_days > 0";
        $this->db->query($sql, ['from_year' => $fromYear, 'type_id' => $leaveTypeId]);
        $oldAllocations = $this->db->fetchAll();

        $carriedCount = 0;
        $totalDaysCarried = 0.0;

        foreach ($oldAllocations as $item) {
            $empId = (int)$item['employee_id'];
            $rem = (float)$item['remaining_days'];
            
            // Giới hạn số ngày chuyển tối đa theo chính sách
            $daysToCarry = min($rem, $maxCarryForward);
            if ($daysToCarry <= 0) continue;

            // Kiểm tra phân bổ ở năm mới
            $this->db->query(
                "SELECT id, entitled_days, used_days FROM {$this->table} WHERE employee_id = :emp AND leave_type_id = :type AND year = :y LIMIT 1",
                ['emp' => $empId, 'type' => $leaveTypeId, 'y' => $toYear]
            );
            $targetAlloc = $this->db->fetch();

            if ($targetAlloc) {
                $newRemaining = (float)$targetAlloc['entitled_days'] + $daysToCarry - (float)$targetAlloc['used_days'];
                $this->db->query(
                    "UPDATE {$this->table} 
                     SET carried_forward_days = :carried, 
                         remaining_days = :rem,
                         notes = CONCAT(COALESCE(notes, ''), ' | Chuyển {$daysToCarry} ngày từ năm {$fromYear} (Hạn: {$expiryDate})'),
                         updated_at = NOW()
                     WHERE id = :id",
                    [
                        'carried' => $daysToCarry,
                        'rem'     => $newRemaining,
                        'id'      => $targetAlloc['id']
                    ]
                );
            } else {
                // Tạo mới nếu chưa có phân bổ năm mới
                $this->db->query(
                    "INSERT INTO {$this->table} 
                     (employee_id, leave_type_id, year, entitled_days, carried_forward_days, used_days, remaining_days, effective_from, effective_to, created_by, notes, created_at)
                     VALUES 
                     (:emp, :type, :y, 12.0, :carried, 0.0, :rem, :eff_from, :eff_to, :creator, :notes, NOW())",
                    [
                        'emp'      => $empId,
                        'type'     => $leaveTypeId,
                        'y'        => $toYear,
                        'carried'  => $daysToCarry,
                        'rem'      => 12.0 + $daysToCarry,
                        'eff_from' => "{$toYear}-01-01",
                        'eff_to'   => "{$toYear}-12-31",
                        'creator'  => $creatorId,
                        'notes'    => "Khởi tạo kèm {$daysToCarry} ngày chuyển tiếp từ năm {$fromYear} (Hạn: {$expiryDate})"
                    ]
                );
            }

            $carriedCount++;
            $totalDaysCarried += $daysToCarry;
        }

        return [
            'carried_count'      => $carriedCount,
            'total_days_carried' => $totalDaysCarried,
            'expiry_date'        => $expiryDate
        ];
    }

    /**
     * Tự động đồng bộ số ngày đã sử dụng và còn lại khi đơn nghỉ phép thay đổi trạng thái
     */
    public function syncUsedDays(int $employeeId, int $leaveTypeId, int $year): void
    {
        // 1. Tính tổng ngày Approved
        $sql = "SELECT COALESCE(SUM(total_days), 0) as total_used
                FROM leave_requests 
                WHERE employee_id = :emp_id 
                  AND leave_type_id = :type_id 
                  AND status = 'Approved' 
                  AND YEAR(start_date) = :year";
        $this->db->query($sql, [
            'emp_id'  => $employeeId,
            'type_id' => $leaveTypeId,
            'year'    => $year
        ]);
        $row = $this->db->fetch();
        $usedDays = (float)($row['total_used'] ?? 0.0);

        // 2. Cập nhật vào leave_allocations
        $this->db->query(
            "SELECT id, entitled_days, carried_forward_days FROM {$this->table} 
             WHERE employee_id = :emp_id AND leave_type_id = :type_id AND year = :year LIMIT 1",
            ['emp_id' => $employeeId, 'type_id' => $leaveTypeId, 'year' => $year]
        );
        $alloc = $this->db->fetch();

        if ($alloc) {
            $totalQuota = (float)$alloc['entitled_days'] + (float)$alloc['carried_forward_days'];
            $remaining = max(0.0, $totalQuota - $usedDays);

            $this->db->query(
                "UPDATE {$this->table} 
                 SET used_days = :used, remaining_days = :remaining, updated_at = NOW() 
                 WHERE id = :id",
                [
                    'used'      => $usedDays,
                    'remaining' => $remaining,
                    'id'        => $alloc['id']
                ]
            );
        }
    }
}
