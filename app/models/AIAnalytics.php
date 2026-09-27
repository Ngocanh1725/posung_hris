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
            'attrition_risk'   => $this->analyzeAttritionRisk(),
            'recruitment_need' => $this->analyzeRecruitmentNeeds(),
            'training_need'    => $this->analyzeTrainingNeeds(),
            'safety_alerts'    => $this->analyzeSafetyAlerts(),
            'skill_gaps'       => $this->evaluateAllSkillGaps(),
            'project_skill_gaps'=> $this->predictSkillGaps(),
            'staffing_preds'   => $this->predictStaffingNeeds(),
            'flight_risks'     => $this->getFlightRisks(),
            'last_run'         => date('Y-m-d H:i:s')
        ];
    }

    /**
     * SPRINT 6: Ma trận Năng lực Tiêu chuẩn (Mock/Hardcoded cho Demo)
     */
    const SKILL_MATRIX = [
        'Kỹ sư M&E Cao cấp' => [
            'ME_Certificate' => 35,
            'HSE_Group3' => 25,
            'BIM_Cert' => 25,
            'Other' => 15
        ],
        'Chỉ huy trưởng' => [
            'Other' => 30, // Tạm map Chứng chỉ quản lý
            'HSE_Group3' => 25,
            'BIM_Cert' => 25,
            'ME_Certificate' => 20
        ],
        'Thợ hàn 6G' => [
            'Welding_6G' => 60,
            'HSE_Group3' => 30,
            'Fire_Safety' => 10
        ]
    ];

    /**
     * SPRINT 6: Đánh giá Khoảng trống Kỹ năng cho 1 Nhân sự
     */
    public function evaluateSkillGap(int $employeeId, string $posTitle): array
    {
        if (!isset(self::SKILL_MATRIX[$posTitle])) {
            return [
                'match_index' => 100, // Nếu không có trong ma trận, mặc định đủ điều kiện
                'recommendations' => [],
                'details' => []
            ];
        }

        $matrix = self::SKILL_MATRIX[$posTitle];
        $matchIndex = 0;
        $recommendations = [];
        $details = [];

        // Lấy chứng chỉ còn hạn của nhân viên
        $this->db->query("SELECT cert_type, cert_name, expiry_date FROM certificates WHERE employee_id = :emp AND (expiry_date IS NULL OR expiry_date >= CURDATE())", ['emp' => $employeeId]);
        $certs = $this->db->fetchAll();
        
        $hasCerts = [];
        foreach ($certs as $c) {
            $hasCerts[$c['cert_type']] = true;
        }

        foreach ($matrix as $requiredCert => $weight) {
            if (isset($hasCerts[$requiredCert])) {
                $matchIndex += $weight;
                $details[$requiredCert] = ['status' => 'Đạt', 'weight' => $weight];
            } else {
                $details[$requiredCert] = ['status' => 'Thiếu', 'weight' => $weight];
                $certNameMapping = [
                    'ME_Certificate' => 'Chứng chỉ Hành nghề Cơ điện',
                    'HSE_Group3' => 'Thẻ An toàn Nhóm 3',
                    'BIM_Cert' => 'Chứng chỉ BIM',
                    'Welding_6G' => 'Chứng chỉ Thợ hàn 6G',
                    'Fire_Safety' => 'Chứng chỉ PCCC',
                    'Other' => 'Chứng chỉ chuyên môn khác'
                ];
                $cName = $certNameMapping[$requiredCert] ?? $requiredCert;
                $recommendations[] = "Đề xuất cử đi đào tạo bổ sung/gia hạn $cName (Trọng số $weight%).";
            }
        }

        return [
            'match_index' => $matchIndex,
            'recommendations' => $matchIndex < 75 ? $recommendations : [],
            'details' => $details
        ];
    }

    /**
     * Đánh giá Skill Gaps cho phòng kỹ thuật/thi công để lên biểu đồ Radar
     */
    public function evaluateAllSkillGaps(): array
    {
        $this->db->query("SELECT e.id, p.pos_title, d.dept_name, e.full_name
                          FROM employees e 
                          JOIN positions p ON e.position_id = p.id
                          LEFT JOIN departments d ON e.department_id = d.id
                          WHERE e.`status` = 'Active' AND p.pos_title IN ('Kỹ sư M&E Cao cấp', 'Chỉ huy trưởng', 'Thợ hàn 6G')");
        
        $employees = $this->db->fetchAll();
        $gaps = [];
        
        // Nhóm theo Position để tính average match index
        $posStats = [];

        foreach ($employees as $emp) {
            $gap = $this->evaluateSkillGap($emp['id'], $emp['pos_title']);
            if (!isset($posStats[$emp['pos_title']])) {
                $posStats[$emp['pos_title']] = ['total' => 0, 'count' => 0];
            }
            $posStats[$emp['pos_title']]['total'] += $gap['match_index'];
            $posStats[$emp['pos_title']]['count']++;

            if ($gap['match_index'] < 75) {
                $gaps[] = [
                    'full_name' => $emp['full_name'],
                    'position' => $emp['pos_title'],
                    'match_index' => $gap['match_index'],
                    'recommendations' => $gap['recommendations']
                ];
            }
        }

        $radarData = [];
        foreach ($posStats as $pos => $stat) {
            $radarData['labels'][] = $pos;
            $radarData['data'][] = round($stat['total'] / $stat['count'], 1);
        }

        return [
            'radar_data' => $radarData,
            'individual_gaps' => $gaps
        ];
    }

    public function predictStaffingNeeds(): array
    {
        // 1. Phân tích các dự án sắp khởi công hoặc đang thi công
        $this->db->query("SELECT id, project_code, project_name, start_date, end_date, headcount_budget FROM projects WHERE `status` IN ('Planning', 'In_Progress')");
        $projects = $this->db->fetchAll();

        $predictions = [];
        $today = new DateTime();

        foreach ($projects as $p) {
            $requiredTotal = (int) $p['headcount_budget'];
            if ($requiredTotal <= 0) continue;

            // Số lượng hiện đang gán cho dự án
            $this->db->query("SELECT COUNT(*) as current_staff FROM employees WHERE current_project_id = :pid AND `status` = 'Active'", ['pid' => $p['id']]);
            $currentStaff = (int) $this->db->fetch()['current_staff'];

            // Dự báo hao hụt (Flight risk + Contract expiry) tại dự án này
            $this->db->query("SELECT COUNT(*) as expiring 
                              FROM contracts c 
                              JOIN employees e ON c.employee_id = e.id 
                              WHERE e.current_project_id = :pid AND c.end_date <= DATE_ADD(CURDATE(), INTERVAL 60 DAY)", ['pid' => $p['id']]);
            $expiringCount = (int) $this->db->fetch()['expiring'];
            
            // Tỷ lệ nghỉ việc tự nhiên (giả định 2% quân số)
            $attritionCount = ceil($currentStaff * 0.02);

            $availableReady = $currentStaff - $expiringCount - $attritionCount;
            
            // Tính toán thiếu hụt
            $shortage = $requiredTotal - $availableReady;
            
            if ($shortage > 0) {
                // Xác định số ngày còn lại đến khi khởi công
                $startDateStr = $p['start_date'] ?: 'now';
                $start = new DateTime($startDateStr);
                $daysToStart = $today->diff($start)->format("%r%a");
                
                $budget = $shortage * 15000000; // Giả định trung bình 15tr/người
                $budgetStr = number_format($budget, 0, ',', '.') . " VNĐ/tháng";

                $msg = "";
                if ($daysToStart > 0 && $daysToStart <= 60) {
                    $msg = "Dự án {$p['project_name']} bắt đầu sau $daysToStart ngày nữa. Đề xuất mở đợt tuyển dụng khẩn cấp.";
                } else {
                    $msg = "Dự án đang thiếu $shortage nhân sự so với định biên ($requiredTotal).";
                }

                $msg .= " Ngân sách lương ước tính: $budgetStr.";

                $predictions[] = [
                    'project_id' => $p['id'],
                    'project_name' => $p['project_name'],
                    'required' => $requiredTotal,
                    'available' => max(0, $availableReady),
                    'shortage' => $shortage,
                    'recommendation' => $msg
                ];
            }
        }

        return $predictions;
    }

    public function predictSkillGaps(): array
    {
        $insights = [];

        // Lấy danh sách YCTD đang mở (cần kỹ năng đặc thù)
        $this->db->query("SELECT rr.id, p.project_name, pos.pos_title, rr.quantity,
                                 (SELECT COUNT(*) FROM candidates c WHERE c.request_id = rr.id AND c.status = 'Hired') as hired_count
                          FROM recruitment_requests rr
                          JOIN projects p ON rr.project_id = p.id
                          JOIN positions pos ON rr.position_id = pos.id
                          WHERE rr.status IN ('Approved', 'In_Progress') 
                          AND (pos.pos_title LIKE '%Thợ hàn%' OR pos.pos_title LIKE '%Kỹ sư M&E%')");
        $requests = $this->db->fetchAll();

        foreach ($requests as $req) {
            $shortage = $req['quantity'] - $req['hired_count'];
            if ($shortage <= 0) continue;

            $certNeeded = '';
            if (strpos(mb_strtoupper($req['pos_title'], 'UTF-8'), '6G') !== false) {
                $certNeeded = 'Welding_6G';
            }

            if ($certNeeded === 'Welding_6G') {
                $this->db->query("SELECT COUNT(*) as trainable 
                                  FROM employees e 
                                  JOIN positions p2 ON e.position_id = p2.id
                                  WHERE p2.pos_title LIKE '%Thợ hàn%' AND e.status = 'Active'
                                  AND e.id NOT IN (
                                      SELECT employee_id FROM certificates 
                                      WHERE cert_type = 'Welding_6G' AND (expiry_date IS NULL OR expiry_date >= CURDATE())
                                  )");
                $trainable = (int) ($this->db->fetch()['trainable'] ?? 0);
                
                if ($trainable > 0) {
                    $upgrades = min($shortage, $trainable);
                    $insights[] = [
                        'project' => $req['project_name'],
                        'issue' => "Dự án {$req['project_name']} thiếu hụt {$shortage} {$req['pos_title']} (Cần Thợ hàn 6G).",
                        'recommendation' => "Đào tạo nâng cấp {$upgrades} thợ hàn hiện tại lên 6G để lấp khoảng trống thay vì tuyển mới.",
                        'severity' => 'Medium',
                        'action' => 'CreateTraining',
                        'action_label' => 'Tự động lập danh sách tham gia'
                    ];
                } else {
                    $insights[] = [
                        'project' => $req['project_name'],
                        'issue' => "Dự án {$req['project_name']} thiếu hụt {$shortage} {$req['pos_title']}.",
                        'recommendation' => "Không có nhân sự nội bộ khả dụng để nâng bậc. Yêu cầu tạo Yêu cầu tuyển dụng ngay.",
                        'severity' => 'High',
                        'action' => 'CreateRecruitment',
                        'action_label' => 'Tạo Yêu Cầu Tuyển Dụng'
                    ];
                }
            }
        }

        // Kiểm tra Thiếu thẻ HSE Safety Card cho toàn bộ dự án đang thi công
        $this->db->query("SELECT p.id, p.project_name, COUNT(e.id) as total_staff 
                          FROM projects p
                          JOIN employees e ON p.id = e.current_project_id
                          WHERE p.status IN ('Planning', 'In_Progress') AND e.status = 'Active'
                          GROUP BY p.id");
        $activeProjects = $this->db->fetchAll();

        foreach ($activeProjects as $proj) {
            $totalStaff = (int)$proj['total_staff'];
            if ($totalStaff == 0) continue;

            // Đếm số người có chứng chỉ An toàn lao động (HSE_Safety_Card) còn hạn
            $this->db->query("SELECT COUNT(DISTINCT e.id) as certified
                              FROM employees e
                              JOIN certificates c ON e.id = c.employee_id
                              WHERE e.current_project_id = :pid AND e.status = 'Active'
                              AND c.cert_type = 'HSE_Safety' AND (c.expiry_date IS NULL OR c.expiry_date >= CURDATE())", ['pid' => $proj['id']]);
            $certified = (int)($this->db->fetch()['certified'] ?? 0);
            
            // Yêu cầu: Tối thiểu 1 người có chứng chỉ HSE trên công trường, hoặc 10% quân số nếu dự án > 10 người.
            $requiredHse = $totalStaff > 10 ? ceil($totalStaff * 0.1) : 1;
            
            if ($certified < $requiredHse) {
                $shortage = $requiredHse - $certified;
                $insights[] = [
                    'project' => $proj['project_name'],
                    'issue' => "Dự án {$proj['project_name']} thiếu hụt {$shortage} nhân sự có Thẻ An toàn HSE (Quy định: $requiredHse người, hiện có: $certified).",
                    'recommendation' => "Tổ chức huấn luyện cấp Thẻ An toàn (HSE) khẩn cấp cho $shortage nhân sự thuộc dự án.",
                    'severity' => 'High',
                    'action' => 'CreateTraining',
                    'action_label' => 'Tự động lập danh sách tham gia'
                ];
            }
        }
        
        return $insights;
    }

    /**
     * SPRINT 9: Đánh giá điểm rủi ro nghỉ việc (Turnover Risk Score)
     */
    public function analyzeAttritionRisk(): array
    {
        $risks = [];
        
        $this->db->query("SELECT e.id, e.emp_code, e.full_name, p.pos_title, e.join_date, e.birth_date, e.gender,
                                 s.base_salary, s.remote_allowance, proj.project_name
                          FROM employees e
                          LEFT JOIN positions p ON e.position_id = p.id
                          LEFT JOIN salaries s ON e.id = s.employee_id
                          LEFT JOIN projects proj ON e.current_project_id = proj.id
                          WHERE e.`status` = 'Active'");
        $employees = $this->db->fetchAll();

        $this->db->query("SELECT AVG(base_salary) as avg_salary FROM salaries WHERE base_salary > 0");
        $avgSalary = (float)($this->db->fetch()['avg_salary'] ?? 0);

        $this->db->query("SELECT employee_id, SUM(ot_hours) as total_ot FROM timesheets WHERE work_date >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH) GROUP BY employee_id");
        $otData = [];
        foreach ($this->db->fetchAll() as $row) {
            $otData[$row['employee_id']] = (float)$row['total_ot'];
        }

        foreach ($employees as $emp) {
            $score = 0;
            $reasons = [];

            // 1. OT quá tải
            $otHours = $otData[$emp['id']] ?? 0;
            if ($otHours > 80) {
                $score += 45;
                $reasons[] = "Tăng ca quá tải ({$otHours}h / 3 tháng).";
            }

            // 2. Lương thấp
            $salary = (float)$emp['base_salary'];
            if ($salary > 0 && $salary < ($avgSalary * 0.7)) {
                $score += 35;
                $reasons[] = "Lương quá thấp so với mặt bằng (Cảnh báo).";
            }

            // 3. Dự án xa (vùng sâu)
            if ((float)$emp['remote_allowance'] > 0) {
                $score += 25;
                $reasons[] = "Đang làm việc tại dự án vùng sâu ({$emp['project_name']}).";
            }

            $level = 'Thấp';
            if ($score >= 75) $level = 'Cao';
            elseif ($score >= 40) $level = 'Trung bình';

            if ($score >= 75) {
                $risks[] = [
                    'emp_code' => $emp['emp_code'],
                    'full_name' => $emp['full_name'],
                    'position' => $emp['pos_title'],
                    'score' => $score,
                    'level' => $level,
                    'reasons' => implode(" ", $reasons)
                ];
            }
        }
        
        usort($risks, fn($a, $b) => $b['score'] <=> $a['score']);
        return array_slice($risks, 0, 15);
    }

    /**
     * SPRINT 6: Phân tích Rủi ro nghỉ việc (Flight Risk) qua OT và Hợp đồng
     */
    public function getFlightRisks(): array
    {
        $risks = [];

        // Tiêu chí 1: Hợp đồng sắp hết hạn (Trong 30 ngày)
        $this->db->query("SELECT e.id, e.emp_code, e.full_name, p.pos_title, c.end_date 
                          FROM contracts c 
                          JOIN employees e ON c.employee_id = e.id 
                          LEFT JOIN positions p ON e.position_id = p.id
                          WHERE c.end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                          AND c.`status` = 'Active' AND e.`status` = 'Active'");
        $expiringContracts = $this->db->fetchAll();

        foreach ($expiringContracts as $emp) {
            $risks[$emp['id']] = [
                'emp_code' => $emp['emp_code'],
                'full_name' => $emp['full_name'],
                'position' => $emp['pos_title'],
                'reason' => "HĐLĐ sắp hết hạn vào ngày " . date('d/m/Y', strtotime($emp['end_date'])),
                'level' => 'High'
            ];
        }

        // Tiêu chí 2: OT quá 60h liên tục (Giả lập truy vấn Timesheets hoặc Payrolls tháng gần nhất)
        // Lưu ý: OT có thể được lưu trong bảng payrolls
        $this->db->query("SELECT p.employee_id, e.emp_code, e.full_name, pos.pos_title, 
                                 (p.ot_pay / (p.net_salary + 1)) as ot_ratio
                          FROM payrolls p
                          JOIN employees e ON p.employee_id = e.id
                          LEFT JOIN positions pos ON e.position_id = pos.id
                          WHERE p.month = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) 
                          AND p.year = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH))
                          AND p.ot_pay > 0");
        $heavyOT = $this->db->fetchAll();

        foreach ($heavyOT as $emp) {
            // Nếu tỷ lệ OT > 30% thu nhập -> rủi ro Burnout (hoặc hardcode dựa vào tổng giờ)
            if ($emp['ot_ratio'] > 0.3) {
                if (isset($risks[$emp['employee_id']])) {
                    $risks[$emp['employee_id']]['reason'] .= " + Rủi ro Burnout (Tỷ lệ OT cao).";
                    $risks[$emp['employee_id']]['level'] = 'Critical';
                } else {
                    $risks[$emp['employee_id']] = [
                        'emp_code' => $emp['emp_code'],
                        'full_name' => $emp['full_name'],
                        'position' => $emp['pos_title'],
                        'reason' => "Rủi ro Burnout (Tỷ lệ tiền OT chiếm > 30% thu nhập tháng trước).",
                        'level' => 'Medium'
                    ];
                }
            }
        }

        return array_values($risks);
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
             AND e.`status` IN ('Active', 'Probation')
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
             AND e.`status` IN ('Active', 'Probation')
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
     */
    private function analyzeRecruitmentNeeds(): array
    {
        $needs = [];

        // Kiểm tra các YCTD quá hạn hoặc tỷ lệ lấp đầy thấp
        $this->db->query(
            "SELECT CONCAT('YCTD-', rr.id) AS request_code, d.dept_name, p.pos_title, rr.quantity, 
                    (SELECT COUNT(*) FROM candidates c WHERE c.request_id = rr.id AND c.`status` = 'Hired') AS hired_count, 
                    DATE_ADD(rr.created_at, INTERVAL 30 DAY) AS deadline
             FROM recruitment_requests rr
             LEFT JOIN departments d ON rr.department_id = d.id
             LEFT JOIN positions p ON rr.position_id = p.id
             WHERE rr.`status` IN ('approved', 'recruiting')
             HAVING hired_count < rr.quantity"
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
     */
    private function analyzeTrainingNeeds(): array
    {
        $needs = [];

        // Đào tạo hội nhập: Số lượng nhân viên Probation
        $this->db->query("SELECT COUNT(*) as probation_count FROM employees WHERE `status` = 'Probation'");
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

        // 1. Cảnh báo nhân viên vi phạm HSE (Blacklist) nhưng vẫn đang Active
        $this->db->query(
            "SELECT e.emp_code, e.full_name, rd.decision_date, rd.title
             FROM employees e
             JOIN rewards_disciplines rd ON e.id = rd.employee_id
             WHERE rd.is_safety_violation = 1 AND e.`status` IN ('Active', 'Probation')
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

        // 2. Chứng chỉ HSE/PCCC hết hạn
        $this->db->query("SELECT e.emp_code, e.full_name, c.cert_type, c.certificate_name, c.expiry_date 
                          FROM certificates c
                          JOIN employees e ON c.employee_id = e.id
                          WHERE e.`status` = 'Active' 
                          AND c.cert_type IN ('HSE_Group3', 'Fire_Safety')
                          AND c.expiry_date IS NOT NULL 
                          AND c.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
        foreach ($this->db->fetchAll() as $row) {
            $isExpired = (strtotime($row['expiry_date']) < time());
            $alerts[] = [
                'type' => 'Cert_Expiry',
                'severity' => $isExpired ? 'Critical' : 'Warning',
                'message' => "Chứng chỉ {$row['certificate_name']} của {$row['full_name']} (" . $row['emp_code'] . ") " . ($isExpired ? "ĐÃ HẾT HẠN" : "sắp hết hạn") . " vào ngày " . date('d/m/Y', strtotime($row['expiry_date'])) . ".",
                'action' => 'Yêu cầu gia hạn/thi lại chứng chỉ ngay lập tức.'
            ];
        }

        // 3. Visa / Work Permit Expat sắp hết hạn
        $this->db->query("SELECT emp_code, full_name, passport_expiry, work_permit_expiry, trc_expiry 
                          FROM employees 
                          WHERE is_expat = 1 AND `status` = 'Active'");
        foreach ($this->db->fetchAll() as $row) {
            $checkDates = [
                'Passport' => $row['passport_expiry'],
                'Work Permit' => $row['work_permit_expiry'],
                'TRC' => $row['trc_expiry']
            ];
            foreach ($checkDates as $docName => $date) {
                if ($date && strtotime($date) <= strtotime('+60 days')) {
                    $isExpired = (strtotime($date) < time());
                    $alerts[] = [
                        'type' => 'Expat_Doc_Expiry',
                        'severity' => $isExpired ? 'Critical' : 'Warning',
                        'message' => "$docName của chuyên gia {$row['full_name']} ({$row['emp_code']}) " . ($isExpired ? "ĐÃ HẾT HẠN" : "sắp hết hạn") . " vào ngày " . date('d/m/Y', strtotime($date)) . ".",
                        'action' => "Liên hệ đối tác/Sở LĐTBXH để gia hạn $docName."
                    ];
                }
            }
        }

        return $alerts;
    }
}
