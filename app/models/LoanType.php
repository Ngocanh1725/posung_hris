<?php
/**
 * ============================================================
 *  POSUNG HRIS – LoanType Model
 * ============================================================
 *  Quản lý Danh mục Loại Khoản vay & Tạm ứng Nhân viên
 * ============================================================
 */

require_once APP_ROOT . '/models/BaseModel.php';

class LoanType extends BaseModel
{
    protected string $table = 'loan_types';

    /**
     * Lấy danh sách các loại khoản vay đang áp dụng (Active)
     */
    public function getActiveTypes(): array
    {
        $this->db->query("SELECT * FROM `{$this->table}` WHERE is_active = 1 ORDER BY id ASC");
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Kiểm tra trùng mã loại khoản vay
     */
    public function checkCodeExists(string $code, ?int $excludeId = null): bool
    {
        $sql = "SELECT id FROM `{$this->table}` WHERE type_code = :code";
        $params = ['code' => $code];
        if ($excludeId !== null) {
            $sql .= " AND id != :id";
            $params['id'] = $excludeId;
        }
        $this->db->query($sql, $params);
        return (bool)$this->db->fetch();
    }
}
