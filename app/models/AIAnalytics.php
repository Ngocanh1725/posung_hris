<?php
/**
 * ============================================================
 *  POSUNG HRIS – AIAnalytics Model (Hệ Chuyên Gia)
 * ============================================================
 *  Hệ thống AI chuyên gia (Rule-based Expert System) nội bộ:
 *  - Phân tích và dự báo nhân sự (Nghỉ việc, Thuyên chuyển).
 *  - Phân tích nhu cầu tuyển dụng (Dựa trên tỷ lệ nghỉ việc, quy mô dự án).
 *  - Phân tích nhu cầu đào tạo (Dựa trên vi phạm HSE, hiệu suất - nếu có).
 *  - Cảnh báo rủi ro (Blacklist, thiếu hụt nhân sự khẩn cấp).
 * ============================================================
 */

class AIAnalytics extends BaseModel
{
    /**
     * Chạy toàn bộ luồng phân tích của Hệ chuyên gia
     */
    public function runFullAnalysis(): array
    {
        return [
            'turnover_risk'    => $this->analyzeTurnoverRisk(),
            'recruitment_need' => $this->analyzeRecruitmentNeeds(),
            'training_need'    => $this->analyzeTrainingNeeds(),
            'safety_alerts'    => $this->analyzeSafetyAlerts(),
            'last_run'         => date('Y-m-d H:i:s')
        ];
    }

    /**
     * 1. Phân tích Rủi ro nghỉ việc (Turnover Risk)
     * Dựa trên: Kỷ luật nhiều, thuyên chuyển liên tục, mức lương so với trung bình, thời gian làm việc.
     */
    private function analyzeTurnoverRisk(): array
    {
        $risks = [];
        
        // Tiêu chí 1: Nhân sự có nhiều hơn 1 lần kỷ luật trong 6 tháng qua
        $this->db->query(
            "SELECT e.id, e.emp_code, e.full_name, COUNT(rd.id) as discipline_count
             FROM employees e
             JOIN rewards_disciplines rd ON e.id = rd.employee_id
             WHERE rd.type = 'Discipline' AND rd.decision_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
             AND e.status IN ('Active', 'Probation')
             GROUP BY e.id
             HAVING discipline_count >= 2"
        );
        foreach ($this->db->fetchAll() as $row) {
            $risks[] = [
                'emp_code' => $row['emp_code'],
                'full_name' => $row['full_name'],
                'risk_level' => 'High',
                'reason' => "Bị kỷ luật {$row['discipline_count']} lần trong 6 tháng qua.",
                'recommendation' => 'Cần quản lý trực tiếp gặp mặt (1-on-1) để tìm hiểu vấn đề tư tưởng/kỹ năng.'
            ];
        }

        // Tiêu chí 2: Thuyên chuyển > 2 lần trong năm
        $this->db->query(
            "SELECT e.id, e.emp_code, e.full_name, COUNT(jm.id) as transfer_count
             FROM employees e
             JOIN job_movements jm ON e.id = jm.employee_id
             WHERE jm.effective_date >= DATE_SUB(CURDATE(), INTERVAL 1 YEAR)
             AND e.status IN ('Active', 'Probation')
             GROUP BY e.id
             HAVING transfer_count >= 3"
        );
        foreach ($this->db->fetchAll() as $row) {
            // Check trùng
            $exists = false;
            foreach ($risks as &$r) {
                if ($r['emp_code'] === $row['emp_code']) {
                    $r['reason'] .= " Thuyên chuyển liên tục ({$row['transfer_count']} lần/năm).";
                    $r['risk_level'] = 'Critical';
                    $exists = true;
                    break;
                }
            }
            if (!$exists) {
                $risks[] = [
                    'emp_code' => $row['emp_code'],
                    'full_name' => $row['full_name'],
                    'risk_level' => 'Medium',
                    'reason' => "Thuyên chuyển liên tục ({$row['transfer_count']} lần trong năm qua).",
                    'recommendation' => 'Đánh giá lại sự phù hợp vị trí, tránh gây xáo trộn tâm lý nhân viên.'
                ];
            }
        }

        return $risks;
    }

    /**
     * 2. Phân tích Nhu cầu Tuyển dụng
     * Dựa trên: Yêu cầu TD đang mở + tỷ lệ lấp đầy + dự án mới.
     */
    private function analyzeRecruitmentNeeds(): array
    {
        $needs = [];

        // Kiểm tra các YCTD quá hạn hoặc tỷ lệ lấp đầy thấp
        $this->db->query(
            "SELECT rr.request_code, d.dept_name, p.pos_title, rr.quantity, rr.hired_count, rr.deadline
             FROM recruitment_requests rr
             LEFT JOIN departments d ON rr.department_id = d.id
             LEFT JOIN positions p ON rr.position_id = p.id
             WHERE rr.status IN ('Approved', 'In_Progress')
             AND rr.hired_count < rr.quantity"
        );
        foreach ($this->db->fetchAll() as $row) {
            $remaining = $row['quantity'] - $row['hired_count'];
            $isOverdue = (strtotime($row['deadline']) < time());
            
            $needs[] = [
                'department' => $row['dept_name'] ?? 'Toàn công ty',
                'position' => $row['pos_title'],
                'urgency' => $isOverdue ? 'Critical' : 'High',
                'analysis' => "Thiếu {$remaining} nhân sự cho vị trí {$row['pos_title']} (Mã YCTD: {$row['request_code']}). " . 
                              ($isOverdue ? "Đã QUÁ HẠN tuyển dụng (" . date('d/m/Y', strtotime($row['deadline'])) . ")." : ""),
                'recommendation' => $isOverdue 
                    ? 'Đề xuất: Sử dụng dịch vụ Headhunt hoặc tăng ngân sách đăng tin tuyển dụng gấp.'
                    : 'Tiếp tục đẩy mạnh các kênh tuyển dụng nội bộ (Referral).'
            ];
        }

        return $needs;
    }

    /**
     * 3. Phân tích Nhu cầu Đào tạo
     * Dựa trên: Nhân viên mới (Probation) cần hội nhập, hoặc vi phạm quy trình/HSE.
     */
    private function analyzeTrainingNeeds(): array
    {
        $needs = [];

        // Đào tạo hội nhập: Số lượng nhân viên Probation
        $this->db->query("SELECT COUNT(*) as probation_count FROM employees WHERE status = 'Probation'");
        $probationCount = $this->db->fetch()['probation_count'] ?? 0;
        
        if ($probationCount > 0) {
            $needs[] = [
                'target_group' => 'Nhân viên mới (Thử việc)',
                'count' => $probationCount,
                'training_type' => 'Đào tạo Hội nhập (Onboarding) & An toàn LĐ cơ bản',
                'priority' => 'High',
                'recommendation' => "Có {$probationCount} nhân sự đang thử việc. Cần tổ chức lớp Hội nhập ngay trong tháng để đảm bảo nắm rõ nội quy và văn hóa Po Sung."
            ];
        }

        // Đào tạo lại HSE: Dựa trên vi phạm HSE
        $this->db->query(
            "SELECT d.dept_name, COUNT(rd.id) as hse_violations
             FROM rewards_disciplines rd
             JOIN employees e ON rd.employee_id = e.id
             LEFT JOIN departments d ON e.department_id = d.id
             WHERE rd.is_safety_violation = 1 AND rd.decision_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
             GROUP BY d.id
             HAVING hse_violations >= 2"
        );
        foreach ($this->db->fetchAll() as $row) {
            $needs[] = [
                'target_group' => "Phòng/Ban: {$row['dept_name']}",
                'count' => $row['hse_violations'],
                'training_type' => 'Đào tạo lại An toàn Lao động (HSE Refresher)',
                'priority' => 'Critical',
                'recommendation' => "Phát hiện {$row['hse_violations']} vụ vi phạm HSE trong 3 tháng qua. BẮT BUỘC tổ chức Re-training (Đào tạo lại) về An toàn cho toàn bộ công nhân bộ phận này."
            ];
        }

        return $needs;
    }

    /**
     * 4. Cảnh báo rủi ro Hệ thống (Safety & Blacklist)
     */
    private function analyzeSafetyAlerts(): array
    {
        $alerts = [];

        // Cảnh báo nhân viên vi phạm HSE (Blacklist) nhưng vẫn đang Active
        $this->db->query(
            "SELECT e.emp_code, e.full_name, rd.decision_date, rd.title
             FROM employees e
             JOIN rewards_disciplines rd ON e.id = rd.employee_id
             WHERE rd.is_safety_violation = 1 AND e.status IN ('Active', 'Probation')
             ORDER BY rd.decision_date DESC"
        );
        foreach ($this->db->fetchAll() as $row) {
            $alerts[] = [
                'type' => 'HSE_Blacklist_Active',
                'severity' => 'Critical',
                'message' => "Nhân viên {$row['full_name']} ({$row['emp_code']}) vi phạm HSE nghiêm trọng ngày " . date('d/m/Y', strtotime($row['decision_date'])) . " (\"{$row['title']}\") nhưng vẫn đang ở trạng thái Active.",
                'action' => 'Yêu cầu HR/HSE xem xét đình chỉ công tác hoặc chấm dứt HĐLĐ theo quy chế Công ty.'
            ];
        }

        return $alerts;
    }
}
