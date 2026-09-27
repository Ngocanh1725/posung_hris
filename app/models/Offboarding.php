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
     * Xử lý lưu thông tin nghỉ việc / nghỉ hưu và cập nhật trạng thái nhân viên
     */
    public function processOffboarding(array $data, string $newStatus): bool
    {
        try {
            $this->db->beginTransaction();

            // 1. Lưu thông tin nghỉ việc (bảng offboardings)
            $sql = "INSERT INTO offboardings (employee_id, resignation_date, last_working_date, reason, type, asset_returned, is_hse_violation, `status`)
                    VALUES (:emp, :res_date, :last_date, :reason, :type, :asset, :hse, 'Completed')";
            
            $this->db->query($sql, [
                'emp'       => $data['employee_id'],
                'res_date'  => $data['resignation_date'] ?? date('Y-m-d'),
                'last_date' => $data['last_working_date'] ?? date('Y-m-d'),
                'reason'    => $data['reason'] ?? '',
                'type'      => $data['type'] ?? 'Voluntary',
                'asset'     => $data['asset_returned'] ?? 0,
                'hse'       => $data['is_hse_violation'] ?? 0
            ]);

            // Nếu vi phạm HSE, có thể cần ghi thêm vào rewards_disciplines
            if (!empty($data['is_hse_violation'])) {
                $newStatus = 'Blacklisted'; // Ghi đè trạng thái nếu vi phạm nghiêm trọng
            }

            // 2. Cập nhật trạng thái NV
            $updateSql = "UPDATE employees SET `status` = :status WHERE id = :id";
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
