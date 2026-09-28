<?php
/**
 * POSUNG HRIS - HseCard Model
 * Quản lý chứng chỉ / thẻ An toàn lao động HSE (Nhóm 1-6)
 */

class HseCard extends Model
{
    protected $table = 'hse_safety_cards';

    public function getAllCards()
    {
        $this->db->query("SELECT h.*, e.employee_code, e.full_name 
                          FROM {$this->table} h
                          JOIN employees e ON h.employee_id = e.id
                          ORDER BY h.id DESC");
        return $this->db->fetchAll();
    }

    public function getCardById($id)
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }

    public function getCardsByEmployee($employeeId)
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE employee_id = :eid ORDER BY issue_date DESC");
        $this->db->bind(':eid', $employeeId);
        return $this->db->fetchAll();
    }

    /**
     * Lấy các thẻ sắp hết hạn trong X ngày tới (Mặc định 30 ngày)
     */
    public function getExpiringCards($days = 30)
    {
        $this->db->query("SELECT h.*, e.employee_code, e.full_name 
                          FROM {$this->table} h
                          JOIN employees e ON h.employee_id = e.id
                          WHERE h.expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL :days DAY)
                          AND h.status = 'VALID'
                          ORDER BY h.expiry_date ASC");
        $this->db->bind(':days', $days);
        return $this->db->fetchAll();
    }

    public function createCard($data)
    {
        $this->db->query("INSERT INTO {$this->table} 
            (employee_id, group_type, card_number, issue_date, expiry_date, training_unit, status, scan_attachment_url) 
            VALUES (:eid, :grp, :cnum, :iss, :exp, :train, :stat, :scan)");
        $this->db->bind(':eid', $data['employee_id']);
        $this->db->bind(':grp', $data['group_type']);
        $this->db->bind(':cnum', $data['card_number']);
        $this->db->bind(':iss', $data['issue_date']);
        $this->db->bind(':exp', $data['expiry_date']);
        $this->db->bind(':train', $data['training_unit'] ?? null);
        $this->db->bind(':stat', $data['status'] ?? 'VALID');
        $this->db->bind(':scan', $data['scan_attachment_url'] ?? null);
        return $this->db->execute();
    }

    public function updateCard($id, $data)
    {
        $this->db->query("UPDATE {$this->table} SET 
            employee_id = :eid, group_type = :grp, card_number = :cnum, 
            issue_date = :iss, expiry_date = :exp, training_unit = :train, 
            status = :stat, scan_attachment_url = :scan
            WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':eid', $data['employee_id']);
        $this->db->bind(':grp', $data['group_type']);
        $this->db->bind(':cnum', $data['card_number']);
        $this->db->bind(':iss', $data['issue_date']);
        $this->db->bind(':exp', $data['expiry_date']);
        $this->db->bind(':train', $data['training_unit'] ?? null);
        $this->db->bind(':stat', $data['status'] ?? 'VALID');
        $this->db->bind(':scan', $data['scan_attachment_url'] ?? null);
        return $this->db->execute();
    }

    public function deleteCard($id)
    {
        $this->db->query("DELETE FROM {$this->table} WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
?>
