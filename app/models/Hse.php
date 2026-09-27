<?php
class Hse extends BaseModel
{
    public function getProjectIncidentStats(): array
    {
        // Thống kê tai nạn, vi phạm theo dự án
        $this->db->query("
            SELECT p.id as project_id, p.project_name as name, 
                   COUNT(i.id) as incident_count,
                   (SELECT COUNT(v.id) FROM hse_violations v WHERE v.project_id = p.id) as violation_count
            FROM projects p
            LEFT JOIN hse_incidents i ON p.id = i.project_id
            GROUP BY p.id
            ORDER BY incident_count DESC, violation_count DESC
        ");
        return $this->db->fetchAll();
    }

    public function getExpiringCerts(): array
    {
        $today = date('Y-m-d');
        $thirtyDays = date('Y-m-d', strtotime('+30 days'));

        // Cảnh báo thẻ an toàn nhân viên (samsung / amkor)
        $this->db->query("
            SELECT full_name, emp_code, hse_card_number, hse_card_expiry_samsung as expiry, 'Samsung' as type, 'Nhân viên' as role
            FROM employees 
            WHERE status = 'Active' AND hse_card_expiry_samsung BETWEEN :t1 AND :t2
            UNION ALL
            SELECT full_name, worker_code as emp_code, qr_code as hse_card_number, hse_expiry_date as expiry, 'An Toàn' as type, 'Thầu phụ' as role
            FROM sub_workers
            WHERE is_active = 1 AND hse_expiry_date BETWEEN :t3 AND :t4
            ORDER BY expiry ASC
        ", [
            't1' => $today, 't2' => $thirtyDays,
            't3' => $today, 't4' => $thirtyDays
        ]);
        return $this->db->fetchAll();
    }
}
