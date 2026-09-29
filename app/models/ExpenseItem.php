<?php
/**
 * ============================================================
 *  POSUNG HRIS – ExpenseItem Model
 * ============================================================
 *  Quản lý Chi tiết Từng Khoản chi trong Bảng Quyết toán
 * ============================================================
 */

require_once APP_ROOT . '/models/BaseModel.php';

class ExpenseItem extends BaseModel
{
    protected string $table = 'expense_items';

    /**
     * Lấy danh sách các khoản chi của 1 bảng quyết toán
     */
    public function getByClaimId(int $claimId): array
    {
        $this->db->query(
            "SELECT * FROM `{$this->table}` 
             WHERE claim_id = :claim_id 
             ORDER BY expense_date ASC, id ASC",
            ['claim_id' => $claimId]
        );
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Thêm hàng loạt khoản chi cho bảng quyết toán
     */
    public function insertBatch(int $claimId, array $items): bool
    {
        if (empty($items)) {
            return false;
        }

        $sql = "INSERT INTO `{$this->table}` 
                (claim_id, description, category, amount, expense_date, receipt_path, notes)
                VALUES 
                (:claim_id, :description, :category, :amount, :expense_date, :receipt_path, :notes)";

        foreach ($items as $item) {
            $this->db->query($sql, [
                'claim_id'     => $claimId,
                'description'  => trim($item['description'] ?? ''),
                'category'     => $item['category'] ?? 'Other',
                'amount'       => (float)($item['amount'] ?? 0),
                'expense_date' => $item['expense_date'] ?? date('Y-m-d'),
                'receipt_path' => $item['receipt_path'] ?? null,
                'notes'        => trim($item['notes'] ?? '') ?: null,
            ]);
        }

        return true;
    }
}
