<?php
/**
 * ============================================================
 *  POSUNG HRIS – Model Permission (Quyền hạn)
 * ============================================================
 */

class Permission
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Lấy danh sách tất cả các quyền được gom nhóm theo Module.
     */
    public function getPermissionsGroupedByModule(): array
    {
        $this->db->query(
            "SELECT * FROM permissions ORDER BY module_code ASC, id ASC"
        );
        $permissions = $this->db->fetchAll();

        $grouped = [];
        foreach ($permissions as $perm) {
            $modCode = $perm['module_code'];
            if (!isset($grouped[$modCode])) {
                $grouped[$modCode] = [
                    'module_code' => $modCode,
                    'permissions' => []
                ];
            }
            $grouped[$modCode]['permissions'][] = $perm;
        }

        return array_values($grouped);
    }

    /**
     * Lấy toàn bộ quyền thực tế của một user (Quyền từ Vai trò + Quyền từ Tùy biến).
     */
    public function getUserEffectivePermissions(int $userId): array
    {
        $this->db->query(
            "SELECT DISTINCT p.*
             FROM permissions p
             JOIN users u ON u.id = :user_id
             LEFT JOIN role_permissions rp ON p.id = rp.permission_id AND rp.role_id = u.role_id
             LEFT JOIN user_permissions up ON p.id = up.permission_id AND up.user_id = u.id
             WHERE (rp.role_id IS NOT NULL AND (up.is_granted IS NULL OR up.is_granted = 1))
                OR (up.is_granted = 1)",
            ['user_id' => $userId]
        );
        
        return $this->db->fetchAll();
    }

    /**
     * Lưu danh sách quyền được cấp cho một user (Lưu vào bảng user_permissions).
     * Mảng $permissions = [ 'permission_id' => 1 (granted) hoặc 0 (denied) ]
     */
    public function saveUserPermissions(int $userId, array $permissions): bool
    {
        try {
            $this->db->beginTransaction();

            // Lấy role hiện tại của User
            $this->db->query(
                "SELECT u.role_id, r.level 
                 FROM users u 
                 LEFT JOIN roles r ON u.role_id = r.id 
                 WHERE u.id = :uid",
                ['uid' => $userId]
            );
            $userRole = $this->db->fetch();

            if ($userRole && $userRole['level'] == 1) {
                // Nếu là Super Admin (level 1) thì không cần lưu vào user_permissions
                $this->db->rollBack();
                return true; 
            }

            // Xóa tất cả quyền cũ của user (đặt lại tùy biến)
            $this->db->query(
                "DELETE FROM user_permissions WHERE user_id = :uid",
                ['uid' => $userId]
            );

            // Chèn quyền mới
            if (!empty($permissions)) {
                foreach ($permissions as $permId => $isGranted) {
                    $this->db->query(
                        "INSERT INTO user_permissions (user_id, permission_id, is_granted)
                         VALUES (:uid, :pid, :granted)",
                        [
                            'uid'     => $userId,
                            'pid'     => $permId,
                            'granted' => $isGranted
                        ]
                    );
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }
}
