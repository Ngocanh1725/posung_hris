<?php
/**
 * ============================================================
 *  POSUNG HRIS – Notification Service
 * ============================================================
 *  Dịch vụ quản lý trung tâm thông báo tự động (Notification Center)
 *  - Gửi thông báo tức thời (In-app notification)
 *  - Xử lý quét các sự kiện tự động theo lịch (Cron/Scheduled events):
 *    + Hợp đồng sắp hết hạn (30/15/7 ngày)
 *    + Chứng chỉ/HSE Card sắp hết hạn (60/30 ngày)
 *    + Visa/Work Permit chuyên gia sắp hết hạn (90/60/30 ngày)
 *    + Chúc mừng sinh nhật nhân viên
 *    + Thử việc sắp hết hạn (15/7 ngày)
 *    + Nhắc duyệt đơn nghỉ phép / OT
 *    + Thông báo phiếu lương đã phê duyệt
 * ============================================================
 */

class NotificationService
{
    /**
     * Gửi một thông báo tới một người dùng cụ thể
     *
     * @param int         $userId  ID của user nhận
     * @param string      $type    system | reminder | approval | alert
     * @param string      $title   Tiêu đề thông báo
     * @param string      $message Nội dung chi tiết
     * @param string|null $link    Đường dẫn chuyển hướng (URL tương đối hoặc tuyệt đối)
     * @param bool        $preventDuplicate Kiểm tra trùng lặp trong ngày
     * @return bool
     */
    public static function send(int $userId, string $type, string $title, string $message, ?string $link = null, bool $preventDuplicate = false): bool
    {
        try {
            $db = Database::getInstance();

            // Kiểm tra chống spam thông báo trùng lặp trong ngày
            if ($preventDuplicate) {
                $db->query(
                    "SELECT id FROM notifications 
                     WHERE user_id = :uid 
                       AND title = :title 
                       AND DATE(created_at) = CURDATE() 
                     LIMIT 1",
                    ['uid' => $userId, 'title' => $title]
                );
                if ($db->fetch()) {
                    return false; // Đã gửi trong ngày rồi
                }
            }

            $validTypes = ['system', 'reminder', 'approval', 'alert'];
            if (!in_array($type, $validTypes, true)) {
                $type = 'system';
            }

            $db->query(
                "INSERT INTO notifications (user_id, type, title, message, link, is_read, created_at)
                 VALUES (:user_id, :type, :title, :message, :link, 0, NOW())",
                [
                    'user_id' => $userId,
                    'type'    => $type,
                    'title'   => $title,
                    'message' => $message,
                    'link'    => $link
                ]
            );

            return true;
        } catch (Exception $e) {
            error_log("NotificationService::send error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gửi thông báo tới nhân viên dựa vào employee_id
     */
    public static function sendToEmployee(int $employeeId, string $type, string $title, string $message, ?string $link = null, bool $preventDuplicate = false): bool
    {
        try {
            $db = Database::getInstance();
            $db->query("SELECT id FROM users WHERE employee_id = :emp_id LIMIT 1", ['emp_id' => $employeeId]);
            $u = $db->fetch();
            if ($u) {
                return self::send((int)$u['id'], $type, $title, $message, $link, $preventDuplicate);
            }
            return false;
        } catch (Exception $e) {
            error_log("NotificationService::sendToEmployee error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Gửi thông báo tới tất cả người dùng thuộc các role chỉ định (ví dụ: 'admin', 'hr_manager', 'site_manager')
     */
    public static function sendToRoles(array|string $roles, string $type, string $title, string $message, ?string $link = null, bool $preventDuplicate = false): int
    {
        try {
            $db = Database::getInstance();
            if (is_string($roles)) {
                $roleArray = array_map('trim', explode(',', $roles));
            } else {
                $roleArray = $roles;
            }

            if (empty($roleArray)) return 0;

            $placeholders = str_repeat('?,', count($roleArray) - 1) . '?';
            $sql = "SELECT DISTINCT u.id 
                    FROM users u
                    JOIN roles r ON u.role_id = r.id
                    WHERE (r.code IN ($placeholders) OR u.role_id = 1) 
                      AND u.status = 'active'";
            
            $db->query($sql, array_values($roleArray));
            $users = $db->fetchAll();

            $count = 0;
            foreach ($users as $u) {
                if (self::send((int)$u['id'], $type, $title, $message, $link, $preventDuplicate)) {
                    $count++;
                }
            }

            return $count;
        } catch (Exception $e) {
            error_log("NotificationService::sendToRoles error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Gửi thông báo tới toàn bộ người dùng active trong hệ thống
     */
    public static function sendToAll(string $type, string $title, string $message, ?string $link = null, bool $preventDuplicate = false): int
    {
        try {
            $db = Database::getInstance();
            $db->query("SELECT id FROM users WHERE status = 'active'");
            $users = $db->fetchAll();
            $count = 0;
            foreach ($users as $u) {
                if (self::send((int)$u['id'], $type, $title, $message, $link, $preventDuplicate)) {
                    $count++;
                }
            }
            return $count;
        } catch (Exception $e) {
            error_log("NotificationService::sendToAll error: " . $e->getMessage());
            return 0;
        }
    }

    // ══════════════════════════════════════════════════════════
    //  CÁC EVENT TỰ ĐỘNG (SCHEDULED REMINDERS)
    // ══════════════════════════════════════════════════════════

    /**
     * Chạy toàn bộ các quy tắc nhắc nhở tự động
     * (Thường được gọi bởi Cron Job hàng ngày hoặc Admin trigger thủ công)
     */
    public static function runScheduledReminders(): array
    {
        $summary = [
            'contract_reminders'   => 0,
            'hse_reminders'        => 0,
            'visa_reminders'       => 0,
            'birthdays'            => 0,
            'probation_reminders'  => 0,
            'evaluation_reminders' => 0,
            'total_created'        => 0
        ];

        $summary['contract_reminders']   = self::checkExpiringContracts();
        $summary['hse_reminders']        = self::checkExpiringHseAndCertificates();
        $summary['visa_reminders']       = self::checkExpiringVisaAndWorkPermits();
        $summary['birthdays']            = self::checkEmployeeBirthdays();
        $summary['probation_reminders']  = self::checkExpiringProbations();
        $summary['evaluation_reminders'] = self::checkEvaluationDeadlines();

        $summary['total_created'] = array_sum($summary);
        return $summary;
    }

    /**
     * 1. Hợp đồng sắp hết hạn (30, 15, 7 ngày trước)
     */
    public static function checkExpiringContracts(): int
    {
        $db = Database::getInstance();
        $intervals = [30, 15, 7];
        $created = 0;

        foreach ($intervals as $days) {
            $targetDate = date('Y-m-d', strtotime("+{$days} days"));

            $sql = "SELECT c.*, e.emp_code, e.full_name, e.department_id, ct.name as contract_type_name
                    FROM contracts c
                    JOIN employees e ON c.employee_id = e.id
                    LEFT JOIN contract_types ct ON c.contract_type_id = ct.id
                    WHERE c.status = 'Active' 
                      AND c.end_date = :target_date";
            
            $db->query($sql, ['target_date' => $targetDate]);
            $expiringContracts = $db->fetchAll();

            foreach ($expiringContracts as $c) {
                $expiryFormatted = date('d/m/Y', strtotime($c['end_date']));
                $title = "Hợp đồng lao động hết hạn trong {$days} ngày ({$c['emp_code']})";
                $msg = "Hợp đồng của nhân viên {$c['full_name']} ({$c['emp_code']}) sẽ hết hạn vào ngày {$expiryFormatted} (còn {$days} ngày). Vui lòng xem xét gia hạn hoặc thanh lý.";
                $link = 'contract';

                // Gửi cho HR & Admin
                self::sendToRoles(['super_admin', 'admin', 'hr_manager', 'cb_staff', 'qtvp'], 'reminder', $title, $msg, $link, true);

                // Gửi thông báo cho chính nhân viên (nếu có tài khoản)
                $empMsg = "Hợp đồng lao động của bạn sẽ hết hạn vào ngày {$expiryFormatted} (còn {$days} ngày). Phòng Nhân sự sẽ liên hệ ký gia hạn.";
                self::sendToEmployee((int)$c['employee_id'], 'reminder', "Nhắc nhở: Hợp đồng của bạn sắp hết hạn", $empMsg, 'ess/myContracts', true);

                $created++;
            }
        }

        return $created;
    }

    /**
     * 2. Chứng chỉ / Thẻ an toàn HSE sắp hết hạn (60, 30 ngày)
     */
    public static function checkExpiringHseAndCertificates(): int
    {
        $db = Database::getInstance();
        $intervals = [60, 30];
        $created = 0;

        foreach ($intervals as $days) {
            $targetDate = date('Y-m-d', strtotime("+{$days} days"));

            // A. Quét hse_safety_cards
            $db->query(
                "SELECT sc.*, e.emp_code, e.full_name 
                 FROM hse_safety_cards sc
                 JOIN employees e ON sc.employee_id = e.id
                 WHERE sc.expiry_date = :target_date",
                ['target_date' => $targetDate]
            );
            $cards = $db->fetchAll();

            foreach ($cards as $card) {
                $expFormatted = date('d/m/Y', strtotime($card['expiry_date']));
                $title = "Thẻ An toàn HSE sắp hết hạn trong {$days} ngày ({$card['emp_code']})";
                $msg = "Thẻ an toàn {$card['group_type']} (Số thẻ: {$card['card_number']}) của nhân viên {$card['full_name']} sẽ hết hạn vào ngày {$expFormatted}. Cần sắp xếp sát hạch gia hạn thẻ.";
                $link = 'hse/safetyCards';

                self::sendToRoles(['super_admin', 'admin', 'hr_manager', 'site_manager'], 'alert', $title, $msg, $link, true);
                self::sendToEmployee((int)$card['employee_id'], 'alert', "Thẻ An toàn HSE của bạn sắp hết hạn", $msg, 'ess/profile', true);
                $created++;
            }

            // B. Quét employee_certificates
            $db->query(
                "SELECT ec.*, e.emp_code, e.full_name 
                 FROM employee_certificates ec
                 JOIN employees e ON ec.employee_id = e.id
                 WHERE ec.expiry_date = :target_date",
                ['target_date' => $targetDate]
            );
            $certs = $db->fetchAll();

            foreach ($certs as $cert) {
                $expFormatted = date('d/m/Y', strtotime($cert['expiry_date']));
                $title = "Chứng chỉ chuyên môn sắp hết hạn trong {$days} ngày ({$cert['emp_code']})";
                $msg = "Chứng chỉ {$cert['certificate_name']} của nhân viên {$cert['full_name']} sẽ hết hạn vào ngày {$expFormatted}.";
                $link = 'employee/show/' . $cert['employee_id'];

                self::sendToRoles(['super_admin', 'admin', 'hr_manager'], 'alert', $title, $msg, $link, true);
                self::sendToEmployee((int)$cert['employee_id'], 'alert', "Chứng chỉ của bạn sắp hết hạn", $msg, 'ess/profile', true);
                $created++;
            }
        }

        return $created;
    }

    /**
     * 3. Visa / Work Permit sắp hết hạn (90, 60, 30 ngày)
     */
    public static function checkExpiringVisaAndWorkPermits(): int
    {
        $db = Database::getInstance();
        $intervals = [90, 60, 30];
        $created = 0;

        foreach ($intervals as $days) {
            $targetDate = date('Y-m-d', strtotime("+{$days} days"));

            // Quét trong bảng employees (các cột work_permit_expiry, passport_expiry, trc_expiry)
            $db->query(
                "SELECT id, emp_code, full_name, work_permit_no, work_permit_expiry, passport_no, passport_expiry, trc_no, trc_expiry
                 FROM employees
                 WHERE status = 'Active' 
                   AND (work_permit_expiry = :td1 OR trc_expiry = :td2 OR passport_expiry = :td3)",
                ['td1' => $targetDate, 'td2' => $targetDate, 'td3' => $targetDate]
            );
            $expats = $db->fetchAll();

            foreach ($expats as $ep) {
                $expDate = $ep['work_permit_expiry'] ?? ($ep['trc_expiry'] ?? $ep['passport_expiry']);
                $expFormatted = date('d/m/Y', strtotime($expDate));
                $title = "Giấy phép LĐ / Visa sắp hết hạn trong {$days} ngày ({$ep['emp_code']})";
                $msg = "Giấy phép lao động/Visa/TRC của chuyên gia {$ep['full_name']} ({$ep['emp_code']}) sẽ hết hạn vào ngày {$expFormatted} (còn {$days} ngày). Cần làm thủ tục gia hạn với cơ quan quản lý xuất nhập cảnh.";
                $link = 'employee/show/' . $ep['id'];

                self::sendToRoles(['super_admin', 'admin', 'hr_manager'], 'alert', $title, $msg, $link, true);
                self::sendToEmployee((int)$ep['id'], 'alert', "Giấy tờ cư trú / Giấy phép lao động sắp hết hạn", $msg, 'ess/profile', true);
                $created++;
            }
        }

        return $created;
    }

    /**
     * 4. Sinh nhật nhân viên (Hôm nay)
     */
    public static function checkEmployeeBirthdays(): int
    {
        $db = Database::getInstance();
        $todayMonth = date('n');
        $todayDay = date('j');
        $created = 0;

        $db->query(
            "SELECT e.id, e.emp_code, e.full_name, e.birth_date, d.dept_name
             FROM employees e
             LEFT JOIN departments d ON e.department_id = d.id
             WHERE e.status = 'Active'
               AND MONTH(e.birth_date) = :m 
               AND DAY(e.birth_date) = :d",
            ['m' => $todayMonth, 'd' => $todayDay]
        );
        $birthdayEmployees = $db->fetchAll();

        foreach ($birthdayEmployees as $emp) {
            // A. Gửi thông báo chúc mừng tới chính nhân viên
            $selfTitle = "🎉 Chúc mừng sinh nhật bạn!";
            $selfMsg = "Công ty Posung E&C kính chúc bạn một sinh nhật thật nhiều niềm vui, sức khỏe dồi dào và gặt hái nhiều thành công rực rỡ!";
            self::sendToEmployee((int)$emp['id'], 'system', $selfTitle, $selfMsg, 'ess/dashboard', true);

            // B. Gửi thông báo tới HR & Công ty để tổ chức
            $deptStr = !empty($emp['dept_name']) ? " - Phòng " . $emp['dept_name'] : '';
            $companyTitle = "🎂 Sinh nhật hôm nay: {$emp['full_name']}{$deptStr}";
            $companyMsg = "Hôm nay là sinh nhật của CBNV {$emp['full_name']} ({$emp['emp_code']}). Hãy cùng gửi lời chúc mừng sinh nhật tốt đẹp nhất!";
            self::sendToRoles(['super_admin', 'admin', 'hr_manager', 'qtvp'], 'system', $companyTitle, $companyMsg, 'employee/show/' . $emp['id'], true);

            $created++;
        }

        return $created;
    }

    /**
     * 5. Thử việc sắp hết hạn (15, 7 ngày trước)
     */
    public static function checkExpiringProbations(): int
    {
        $db = Database::getInstance();
        $intervals = [15, 7];
        $created = 0;

        foreach ($intervals as $days) {
            $targetDate = date('Y-m-d', strtotime("+{$days} days"));

            // Quét nhân viên có official_date hoặc kết thúc hợp đồng thử việc đúng targetDate
            $db->query(
                "SELECT e.id, e.emp_code, e.full_name, e.join_date, e.official_date, d.dept_name
                 FROM employees e
                 LEFT JOIN departments d ON e.department_id = d.id
                 WHERE e.status = 'Probation' 
                   AND e.official_date = :target_date",
                ['target_date' => $targetDate]
            );
            $probations = $db->fetchAll();

            foreach ($probations as $pb) {
                $expFormatted = date('d/m/Y', strtotime($pb['official_date']));
                $title = "Hết hạn thử việc trong {$days} ngày ({$pb['emp_code']})";
                $msg = "Nhân viên thử việc {$pb['full_name']} ({$pb['emp_code']}) sẽ kết thúc giai đoạn thử việc vào ngày {$expFormatted}. Vui lòng thực hiện đánh giá thử việc để chuyển ký HĐLĐ chính thức.";
                $link = 'employee/show/' . $pb['id'];

                self::sendToRoles(['super_admin', 'admin', 'hr_manager', 'site_manager'], 'reminder', $title, $msg, $link, true);
                $created++;
            }
        }

        return $created;
    }

    /**
     * 6. Nhắc nhở hạn chót Kỳ Đánh Giá Hiệu Suất 360° & KRA
     */
    public static function checkEvaluationDeadlines(): int
    {
        $db = Database::getInstance();
        $created = 0;
        $intervals = [7, 3, 1];

        // Lấy các chu kỳ đang Active
        $db->query("SELECT * FROM evaluation_periods WHERE status = 'Active'");
        $activePeriods = $db->fetchAll();

        foreach ($activePeriods as $p) {
            $pid = (int)$p['id'];
            $endDate = $p['end_date'];
            $daysLeft = (int)((strtotime($endDate) - strtotime(date('Y-m-d'))) / 86400);

            if (in_array($daysLeft, $intervals, true)) {
                $endFormatted = date('d/m/Y', strtotime($endDate));
                $pName = $p['name'];

                // 1. Nhắc nhở nhân viên chưa hoàn tất tự đánh giá
                $db->query(
                    "SELECT e.id, e.full_name FROM employees e
                     WHERE e.status != 'Resigned'
                       AND (:dept IS NULL OR e.department_id = :dept)
                       AND e.id NOT IN (
                           SELECT employee_id FROM performance_reviews_360 
                           WHERE period_id = :pid AND relationship = 'Self' AND status = 'Submitted'
                       )",
                    ['dept' => $p['department_id'], 'pid' => $pid]
                );
                $pendingEmps = $db->fetchAll();

                foreach ($pendingEmps as $pe) {
                    $title = "Hạn chót đánh giá 360° còn {$daysLeft} ngày!";
                    $msg = "Chu kỳ [{$pName}] sẽ kết thúc vào ngày {$endFormatted}. Vui lòng hoàn thành tự đánh giá KRA của bạn trước thời hạn.";
                    if (self::sendToEmployee((int)$pe['id'], 'reminder', $title, $msg, "evaluation/submitSelfReview/{$pid}/{$pe['id']}", true)) {
                        $created++;
                    }
                }

                // 2. Nhắc nhở đồng nghiệp có bài Peer Review chưa hoàn thành
                $db->query(
                    "SELECT r.reviewer_id, e.full_name AS target_name
                     FROM performance_reviews_360 r
                     JOIN employees e ON r.employee_id = e.id
                     WHERE r.period_id = :pid AND r.relationship = 'Peer' AND r.status = 'Pending'",
                    ['pid' => $pid]
                );
                $pendingPeers = $db->fetchAll();

                foreach ($pendingPeers as $pp) {
                    $title = "Nhắc nhở: Đánh giá đồng nghiệp 360° (còn {$daysLeft} ngày)";
                    $msg = "Chu kỳ [{$pName}] sắp kết thúc vào ngày {$endFormatted}. Bạn có yêu cầu đánh giá chéo đồng nghiệp {$pp['target_name']} đang chờ hoàn thành.";
                    if (self::sendToEmployee((int)$pp['reviewer_id'], 'reminder', $title, $msg, "ess/performance", true)) {
                        $created++;
                    }
                }
            }
        }

        return $created;
    }

    /**
     * 7. Thông báo đơn nghỉ phép cần phê duyệt (Approval)
     */
    public static function notifyLeavePending(int $leaveRequestId): bool
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "SELECT lr.*, e.emp_code, e.full_name, e.direct_manager_id, lt.name as leave_type_name
                 FROM leave_requests lr
                 JOIN employees e ON lr.employee_id = e.id
                 JOIN leave_types lt ON lr.leave_type_id = lt.id
                 WHERE lr.id = :id LIMIT 1",
                ['id' => $leaveRequestId]
            );
            $leave = $db->fetch();
            if (!$leave) return false;

            $startFmt = date('d/m/Y', strtotime($leave['start_date']));
            $endFmt = date('d/m/Y', strtotime($leave['end_date']));
            $title = "Đơn xin nghỉ phép cần duyệt: {$leave['full_name']}";
            $msg = "Nhân viên {$leave['full_name']} ({$leave['emp_code']}) vừa nộp đơn {$leave['leave_type_name']} từ {$startFmt} đến {$endFmt} ({$leave['total_days']} ngày).";
            $link = 'leave';

            // Gửi cho người quản lý trực tiếp (nếu có)
            if (!empty($leave['direct_manager_id'])) {
                self::sendToEmployee((int)$leave['direct_manager_id'], 'approval', $title, $msg, $link);
            }

            // Gửi cho Admin & HR Manager
            self::sendToRoles(['super_admin', 'admin', 'hr_manager'], 'approval', $title, $msg, $link);
            return true;
        } catch (Exception $e) {
            error_log("NotificationService::notifyLeavePending error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * 7. Thông báo phiếu lương đã được phê duyệt gửi cho toàn bộ nhân viên trong kỳ
     */
    public static function notifyPayrollPeriodApproved(int $month, int $year): int
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "SELECT DISTINCT employee_id 
                 FROM payrolls 
                 WHERE month = :m AND year = :y AND status = 'Approved'",
                ['m' => $month, 'y' => $year]
            );
            $payrolls = $db->fetchAll();

            $title = "Phiếu lương tháng {$month}/{$year} đã sẵn sàng";
            $msg = "Phiếu lương tháng {$month}/{$year} của bạn đã được Phòng C&B và Ban Giám đốc phê duyệt. Vui lòng truy cập Cổng ESS để tra cứu và in phiếu lương.";
            $link = "ess/payslip?period={$month}-{$year}";

            $count = 0;
            foreach ($payrolls as $p) {
                if (!empty($p['employee_id'])) {
                    if (self::sendToEmployee((int)$p['employee_id'], 'approval', $title, $msg, $link, true)) {
                        $count++;
                    }
                }
            }

            return $count;
        } catch (Exception $e) {
            error_log("NotificationService::notifyPayrollPeriodApproved error: " . $e->getMessage());
            return 0;
        }
    }

    // ══════════════════════════════════════════════════════════
    //  QUẢN LÝ THÔNG BÁO CHO USER (GET / READ)
    // ══════════════════════════════════════════════════════════

    /**
     * Lấy số lượng thông báo chưa đọc của user
     */
    public static function getUnreadCount(int $userId): int
    {
        try {
            $db = Database::getInstance();
            $db->query("SELECT COUNT(*) as cnt FROM notifications WHERE user_id = :uid AND is_read = 0", ['uid' => $userId]);
            return (int)($db->fetch()['cnt'] ?? 0);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Lấy danh sách thông báo mới nhất cho dropdown chuông thông báo
     */
    public static function getRecentNotifications(int $userId, int $limit = 8): array
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "SELECT * FROM notifications 
                 WHERE user_id = :uid 
                 ORDER BY is_read ASC, created_at DESC 
                 LIMIT " . (int)$limit,
                ['uid' => $userId]
            );
            return $db->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    /**
     * Đánh dấu một thông báo là đã đọc
     */
    public static function markAsRead(int $id, int $userId): bool
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "UPDATE notifications 
                 SET is_read = 1, read_at = NOW() 
                 WHERE id = :id AND user_id = :uid",
                ['id' => $id, 'uid' => $userId]
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Đánh dấu toàn bộ thông báo của user là đã đọc
     */
    public static function markAllAsRead(int $userId): bool
    {
        try {
            $db = Database::getInstance();
            $db->query(
                "UPDATE notifications 
                 SET is_read = 1, read_at = NOW() 
                 WHERE user_id = :uid AND is_read = 0",
                ['uid' => $userId]
            );
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
