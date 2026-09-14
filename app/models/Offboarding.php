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
            $sql = "INSERT INTO clearance_checklists (employee_id, ppe_returned, tools_returned, id_card_returned, laptop_returned, notes, status, created_by)
                    VALUES (:emp, :ppe, :tools, :id_card, :laptop, :notes, 'Completed', :creator)";
            
            $this->db->query($sql, [
                'emp'     => $data['employee_id'],
                'ppe'     => $data['ppe_returned'],
                'tools'   => $data['tools_returned'],
                'id_card' => $data['id_card_returned'],
                'laptop'  => $data['laptop_returned'],
                'notes'   => $data['notes'],
                'creator' => $data['created_by']
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
