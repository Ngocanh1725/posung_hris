<?php
/**
 * ============================================================
 *  POSUNG HRIS – Reward & Discipline Model
 * ============================================================
 *  Quản lý Khen thưởng và Kỷ luật, bao gồm xử lý Blacklist
 *  nếu vi phạm an toàn lao động (HSE).
 * ============================================================
 */

class RewardDiscipline extends BaseModel
{
    protected string $table = 'rewards_disciplines';

    /**
     * Lấy danh sách khen thưởng / kỷ luật
     */
    public function getAllRecords(): array
    {
        $sql = "SELECT rd.*, e.emp_code, e.full_name, e.status as emp_status, p.pos_title
                FROM rewards_disciplines rd
                JOIN employees e ON rd.employee_id = e.id
                LEFT JOIN positions p ON e.position_id = p.id
                ORDER BY rd.created_at DESC";
        $this->db->query($sql);
        return $this->db->fetchAll();
    }

    /**
     * Tạo quyết định khen thưởng / kỷ luật
     */
    public function createRecord(array $data): bool
    {
        try {
            $this->db->beginTransaction();

            $sql = "INSERT INTO rewards_disciplines (employee_id, type, decision_number, decision_date, title, amount, reason, is_safety_violation)
                    VALUES (:emp, :type, :dnum, :ddate, :title, :amount, :reason, :safety)";
            
            $this->db->query($sql, [
                'emp'    => $data['employee_id'],
                'type'   => $data['type'],
                'dnum'   => $data['decision_number'],
                'ddate'  => $data['decision_date'],
                'title'  => $data['title'],
                'amount' => $data['amount'] ?? 0,
                'reason' => $data['reason'] ?? '',
                'safety' => $data['is_safety_violation'] ?? 0
            ]);

            // Nếu là Kỷ luật và Vi phạm an toàn -> Cập nhật trạng thái NV thành Blacklisted
            if ($data['type'] === 'Discipline' && !empty($data['is_safety_violation'])) {
                $updateEmpSql = "UPDATE employees SET status = 'Blacklisted', notes = CONCAT(IFNULL(notes,''), '\n[BLACKLISTED] Vi phạm HSE: ', :title) WHERE id = :emp_id";
                $this->db->query($updateEmpSql, [
                    'title'  => $data['title'],
                    'emp_id' => $data['employee_id']
                ]);
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Lỗi tạo quyết định KTKL: " . $e->getMessage());
            return false;
        }
    }
}
