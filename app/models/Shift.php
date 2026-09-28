<?php
/**
 * POSUNG HRIS - Shift Model
 * Quản lý danh mục ca làm việc (Shift)
 */

class Shift extends Model
{
    protected $table = 'shifts';

    public function getAllShifts()
    {
        $this->db->query("SELECT * FROM {$this->table} ORDER BY id ASC");
        return $this->db->fetchAll();
    }

    public function getActiveShifts()
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE status = 'Active' ORDER BY start_time ASC");
        return $this->db->fetchAll();
    }

    public function getShiftById($id)
    {
        $this->db->query("SELECT * FROM {$this->table} WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->fetch();
    }

    public function createShift($data)
    {
        $this->db->query("INSERT INTO {$this->table} 
            (shift_code, shift_name, start_time, end_time, break_start, break_end, is_night_shift, ot_rate_default, is_split_shift, description, status) 
            VALUES (:sc, :sn, :st, :et, :bs, :be, :in, :or, :is, :desc, :stat)");
        $this->db->bind(':sc', $data['shift_code']);
        $this->db->bind(':sn', $data['shift_name']);
        $this->db->bind(':st', $data['start_time']);
        $this->db->bind(':et', $data['end_time']);
        $this->db->bind(':bs', empty($data['break_start']) ? null : $data['break_start']);
        $this->db->bind(':be', empty($data['break_end']) ? null : $data['break_end']);
        $this->db->bind(':in', $data['is_night_shift'] ?? 0);
        $this->db->bind(':or', $data['ot_rate_default'] ?? 1.50);
        $this->db->bind(':is', $data['is_split_shift'] ?? 0);
        $this->db->bind(':desc', $data['description'] ?? null);
        $this->db->bind(':stat', $data['status'] ?? 'Active');
        return $this->db->execute();
    }

    public function updateShift($id, $data)
    {
        $this->db->query("UPDATE {$this->table} SET 
            shift_code = :sc, shift_name = :sn, start_time = :st, end_time = :et, 
            break_start = :bs, break_end = :be, is_night_shift = :in, ot_rate_default = :or, 
            is_split_shift = :is, description = :desc, status = :stat 
            WHERE id = :id");
        $this->db->bind(':id', $id);
        $this->db->bind(':sc', $data['shift_code']);
        $this->db->bind(':sn', $data['shift_name']);
        $this->db->bind(':st', $data['start_time']);
        $this->db->bind(':et', $data['end_time']);
        $this->db->bind(':bs', empty($data['break_start']) ? null : $data['break_start']);
        $this->db->bind(':be', empty($data['break_end']) ? null : $data['break_end']);
        $this->db->bind(':in', $data['is_night_shift'] ?? 0);
        $this->db->bind(':or', $data['ot_rate_default'] ?? 1.50);
        $this->db->bind(':is', $data['is_split_shift'] ?? 0);
        $this->db->bind(':desc', $data['description'] ?? null);
        $this->db->bind(':stat', $data['status'] ?? 'Active');
        return $this->db->execute();
    }

    public function deleteShift($id)
    {
        $this->db->query("DELETE FROM {$this->table} WHERE id = :id");
        $this->db->bind(':id', $id);
        return $this->db->execute();
    }
}
?>
