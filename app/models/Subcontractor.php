<?php
class Subcontractor extends BaseModel
{
    protected string $table = 'subcontractors';

    public function getAllSubcontractors(): array
    {
        $this->db->query("
            SELECT s.*, 
                   (SELECT COUNT(*) FROM sub_workers WHERE sub_id = s.id AND is_active = 1) as total_workers,
                   (SELECT COUNT(*) FROM sub_workers WHERE sub_id = s.id AND is_active = 1 AND has_hse_cert = 1) as certified_workers
            FROM {$this->table} s
            ORDER BY s.id DESC
        ");
        return $this->db->fetchAll();
    }

    public function getWorkersBySubId(int $subId): array
    {
        $this->db->query("SELECT * FROM sub_workers WHERE sub_id = :id ORDER BY id DESC", ['id' => $subId]);
        return $this->db->fetchAll();
    }

    public function createSubcontractor(array $data): int
    {
        $this->db->query("INSERT INTO {$this->table} (sub_code, sub_name, contact_person, phone) VALUES (:code, :name, :cp, :ph)", [
            'code' => $data['sub_code'],
            'name' => $data['sub_name'],
            'cp' => $data['contact_person'],
            'ph' => $data['phone']
        ]);
        return $this->db->lastInsertId();
    }
}
