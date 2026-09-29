<?php
/**
 * ============================================================
 *  POSUNG HRIS – Helper / Middleware AuditLogger
 * ============================================================
 *  Chuyên trách ghi nhật ký hoạt động của toàn bộ người dùng
 *  trong hệ thống (Tạo, Sửa, Xóa, Đăng nhập, Đăng xuất, Backup, Restore...)
 * ============================================================
 */

class AuditLogger
{
    /**
     * Ghi một bản ghi nhật ký kiểm toán (Audit Log)
     * 
     * @param string $action Hành động: create, update, delete, login, logout, view, export, backup, restore
     * @param string $module Tên module bị tác động: employee, payroll, insurance, contract, auth, system...
     * @param mixed $recordId ID của bản ghi bị tác động
     * @param mixed $oldValues Dữ liệu cũ trước khi thay đổi (array, object hoặc string)
     * @param mixed $newValues Dữ liệu mới sau khi thay đổi (array, object hoặc string)
     * @param string|null $description Mô tả chi tiết hành động
     * @return bool
     */
    public static function log(
        string $action, 
        string $module, 
        mixed $recordId = null, 
        mixed $oldValues = null, 
        mixed $newValues = null, 
        ?string $description = null
    ): bool {
        try {
            $db = Database::getInstance();

            $userId = Session::isLoggedIn() ? Session::userId() : null;
            $userName = Session::isLoggedIn() ? (Session::userFullName() ?? (Session::get('username') ?? 'Admin')) : 'Khách vãng lai';

            // Lấy IP client
            $ipAddress = $_SERVER['HTTP_CLIENT_IP'] 
                      ?? $_SERVER['HTTP_X_FORWARDED_FOR'] 
                      ?? $_SERVER['REMOTE_ADDR'] 
                      ?? '127.0.0.1';
            if (str_contains($ipAddress, ',')) {
                $ipAddress = trim(explode(',', $ipAddress)[0]);
            }

            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown User-Agent';

            // Mã hóa dữ liệu JSON nếu là array hoặc object
            $oldJson = null;
            if ($oldValues !== null) {
                $oldJson = is_string($oldValues) ? $oldValues : json_encode($oldValues, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }

            $newJson = null;
            if ($newValues !== null) {
                $newJson = is_string($newValues) ? $newValues : json_encode($newValues, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            }

            // Tự động tạo description nếu chưa có
            if (empty($description)) {
                $descMap = [
                    'create'  => "Thêm mới bản ghi #{$recordId} trong phân hệ {$module}",
                    'update'  => "Cập nhật bản ghi #{$recordId} trong phân hệ {$module}",
                    'delete'  => "Xóa bản ghi #{$recordId} khỏi phân hệ {$module}",
                    'login'   => "Đăng nhập vào hệ thống",
                    'logout'  => "Đăng xuất khỏi hệ thống",
                    'backup'  => "Tạo bản sao lưu CSDL: {$recordId}",
                    'restore' => "Phục hồi CSDL từ bản sao lưu: {$recordId}",
                    'export'  => "Xuất báo cáo / dữ liệu trong phân hệ {$module}",
                ];
                $description = $descMap[$action] ?? "Thực hiện thao tác {$action} trên phân hệ {$module}";
            }

            $sql = "INSERT INTO audit_logs 
                    (user_id, user_name, `action`, `module`, record_id, description, old_values, new_values, ip_address, user_agent, created_at)
                    VALUES 
                    (:uid, :uname, :act, :mod, :rid, :desc, :old, :new, :ip, :ua, NOW())";

            $db->query($sql, [
                'uid'   => $userId,
                'uname' => $userName,
                'act'   => $action,
                'mod'   => $module,
                'rid'   => $recordId !== null ? (string)$recordId : null,
                'desc'  => $description,
                'old'   => $oldJson,
                'new'   => $newJson,
                'ip'    => $ipAddress,
                'ua'    => mb_substr($userAgent, 0, 500, 'UTF-8'),
            ]);

            return true;
        } catch (Throwable $e) {
            error_log("AuditLogger Error: " . $e->getMessage());
            return false;
        }
    }
}
