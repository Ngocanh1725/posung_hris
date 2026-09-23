<?php
/**
 * ============================================================
 *  POSUNG HRIS – Offboarding Model
 * ============================================================
 *  Quản lý quy trình thôi việc, hưu trí và biên bản bàn giao.
 * ============================================================
 */

class Offboarding extends BaseModel
{
    protected string $table = 'clearance_checklists';

    /**
     * Xử lý lưu biên bản bàn giao và cập nhật trạng thái nhân viên
     */
    public function processOffboarding(array $data, string $newStatus): bool
    {
        try {
            $this->db->beginTransaction();

            // 1. Lưu biên bản bàn giao (Clearance Checklist)
            // Fields: ppe_returned, tools_returned, account_settled, insurance_closed
            $sql = "INSERT INTO clearance_checklists (employee_id, tools_returned, ppe_returned, account_settled, insurance_closed, notes, status, created_by)
                    VALUES (:emp, :tools, :ppe, :account, :insurance, :notes, 'Completed', :creator)";
            
            $this->db->query($sql, [
                'emp'       => $data['employee_id'],
                'tools'     => $data['tools_returned'] ?? 0,
                'ppe'       => $data['ppe_returned'] ?? 0,
                'account'   => $data['account_settled'] ?? 0,
                'insurance' => $data['insurance_closed'] ?? 0,
                'notes'     => $data['notes'] ?? '',
                'creator'   => $data['created_by']
            ]);

            // 2. Cập nhật trạng thái NV
            $updateSql = "UPDATE employees SET status = :status WHERE id = :id";
            $this->db->query($updateSql, [
                'status' => $newStatus,
                'id'     => $data['employee_id']
            ]);

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Lỗi offboarding: " . $e->getMessage());
            return false;
        }
    }
}
