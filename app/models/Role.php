<?php
/**
 * ============================================================
 *  POSUNG HRIS – Model Role (Vai trò)
 * ============================================================
 */

class Role
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Lấy danh sách tất cả các vai trò
     */
    public function getAllRoles(): array
    {
        $this->db->query(
            "SELECT * FROM roles ORDER BY level ASC, id ASC"
        );
        return $this->db->fetchAll();
    }

    /**
     * Lấy thông tin chi tiết một vai trò theo ID
     */
    public function getRoleById(int $roleId): ?array
    {
        $this->db->query(
            "SELECT * FROM roles WHERE id = :id LIMIT 1",
            ['id' => $roleId]
        );
        $role = $this->db->fetch();
        return $role ?: null;
    }

    /**
     * Lấy danh sách các quyền (Permissions) thuộc về một Vai trò (Role)
     */
    public function getRolePermissions(int $roleId): array
    {
        $this->db->query(
            "SELECT p.* 
             FROM permissions p
             JOIN role_permissions rp ON p.id = rp.permission_id
             WHERE rp.role_id = :role_id",
            ['role_id' => $roleId]
        );
        return $this->db->fetchAll();
    }
}
