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
        $sixtyDays = date('Y-m-d', strtotime('+60 days'));

        $sql = "
            SELECT e.full_name, e.emp_code, c.card_number as doc_number, c.expiry_date as expiry, 
                   CONCAT('Thẻ An Toàn ', c.group_type) as type, 'Nhân viên' as role
            FROM hse_safety_cards c
            JOIN employees e ON c.employee_id = e.id
            WHERE e.status = 'Active' AND c.expiry_date BETWEEN :t1 AND :t2
            
            UNION ALL
            
            SELECT e.full_name, e.emp_code, ec.license_no as doc_number, ec.expiry_date as expiry, 
                   ec.certificate_name as type, 'Nhân viên' as role
            FROM employee_certificates ec
            JOIN employees e ON ec.employee_id = e.id
            WHERE e.status = 'Active' AND ec.expiry_date BETWEEN :t3 AND :t4
            
            UNION ALL
            
            SELECT full_name, worker_code as emp_code, qr_code as doc_number, hse_expiry_date as expiry, 
                   'Thẻ CHTP' as type, 'Thầu phụ' as role
            FROM sub_workers
            WHERE is_active = 1 AND hse_expiry_date BETWEEN :t5 AND :t6
            
            ORDER BY expiry ASC
        ";

        $this->db->query($sql, [
            't1' => $today, 't2' => $sixtyDays,
            't3' => $today, 't4' => $sixtyDays,
            't5' => $today, 't6' => $sixtyDays
        ]);
        return $this->db->fetchAll();
    }

    public function getEngineersMissingCerts(): array
    {
        // Giả sử position_id của Chỉ huy trưởng và Giám sát nằm trong list (ví dụ: 5, 6, 7)
        $this->db->query("
            SELECT e.id, e.emp_code, e.full_name, p.pos_title, proj.project_name
            FROM employees e
            JOIN positions p ON e.position_id = p.id
            LEFT JOIN projects proj ON e.current_project_id = proj.id
            WHERE e.status = 'Active' 
              AND (p.pos_title LIKE '%Chỉ huy trưởng%' OR p.pos_title LIKE '%Giám sát%')
              AND e.id NOT IN (
                  SELECT employee_id FROM employee_certificates 
                  WHERE certificate_name LIKE '%Hành nghề%' AND (expiry_date IS NULL OR expiry_date >= CURDATE())
              )
        ");
        return $this->db->fetchAll();
    }

    public function getAllSafetyCards(?int $projectId = null): array
    {
        $sql = "
            SELECT c.*, e.emp_code, e.full_name, p.pos_title, proj.project_name
            FROM hse_safety_cards c
            JOIN employees e ON c.employee_id = e.id
            LEFT JOIN positions p ON e.position_id = p.id
            LEFT JOIN projects proj ON e.current_project_id = proj.id
            WHERE 1=1
        ";
        $params = [];
        if ($projectId) {
            $sql .= " AND e.current_project_id = :proj";
            $params['proj'] = $projectId;
        }
        $sql .= " ORDER BY c.expiry_date ASC";
        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    public function saveSafetyCard(array $data): bool
    {
        if (!empty($data['id'])) {
            $this->db->query(
                "UPDATE hse_safety_cards SET employee_id = :emp, group_type = :grp, card_number = :num, issue_date = :iss, expiry_date = :exp, training_unit = :unit, status = :stat WHERE id = :id",
                [
                    'emp' => $data['employee_id'], 'grp' => $data['group_type'], 'num' => $data['card_number'],
                    'iss' => $data['issue_date'], 'exp' => $data['expiry_date'], 'unit' => $data['training_unit'],
                    'stat' => $data['status'] ?? 'VALID', 'id' => $data['id']
                ]
            );
        } else {
            $this->db->query(
                "INSERT INTO hse_safety_cards (employee_id, group_type, card_number, issue_date, expiry_date, training_unit, status) 
                 VALUES (:emp, :grp, :num, :iss, :exp, :unit, :stat)",
                [
                    'emp' => $data['employee_id'], 'grp' => $data['group_type'], 'num' => $data['card_number'],
                    'iss' => $data['issue_date'], 'exp' => $data['expiry_date'], 'unit' => $data['training_unit'],
                    'stat' => $data['status'] ?? 'VALID'
                ]
            );
        }
        return true;
    }
}
