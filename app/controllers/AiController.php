<?php
/**
 * ============================================================
 *  POSUNG HRIS – AiController
 * ============================================================
 *  Điều khiển giao diện Hệ Chuyên Gia (AI/Expert System)
 * ============================================================
 */

class AiController extends Controller
{
    /**
     * Dashboard Hệ chuyên gia nội bộ
     */
    public function index(): void
    {
        // Kiểm tra quyền
        $this->checkPermission('ai.view');

        $model = $this->model('AIAnalytics');
        
        // Chạy phân tích
        $analysisResults = $model->runFullAnalysis();

        $this->view('layouts/header', ['pageTitle' => 'AI Command Center (Hệ Chuyên Gia)']);
        $this->view('ai/index', ['analysis' => $analysisResults]);
        $this->view('layouts/footer');
    }

    /**
     * SPRINT 9: Trả về kết quả phân tích dạng JSON cho AJAX / Real-time dashboard
     */
    public function apiGetInsights(): void
    {
        $this->checkPermission('ai.view');
        
        $model = $this->model('AIAnalytics');
        $analysisResults = $model->runFullAnalysis();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'status' => 'success',
            'data' => $analysisResults
        ], JSON_UNESCAPED_UNICODE);
    }

    public function autoCreateRecruitment(): void
    {
        $this->checkPermission('ai.view');
        
        $project = $_GET['project'] ?? 'Dự án Mới';
        
        $db = Database::getInstance();
        $db->query("INSERT INTO recruitment_requests (request_code, position_title, quantity, department_id, project_id, status, request_date, required_date, reason)
                    VALUES (:code, :title, :qty, 1, 1, 'Pending', CURDATE(), DATE_ADD(CURDATE(), INTERVAL 30 DAY), :reason)", [
            'code' => 'YCTD-AI-' . time(),
            'title' => 'Kỹ sư ' . $project,
            'qty' => 3,
            'reason' => 'AI tự động đề xuất bổ sung nguồn lực cho ' . $project
        ]);
        
        Session::setFlash('success', 'AI đã tự động tạo thành công Phiếu Yêu Cầu Tuyển Dụng cho: ' . $project);
        $this->redirect('recruitment');
    }

    public function autoCreateTraining(): void
    {
        $this->checkPermission('ai.view');
        
        $project = $_GET['project'] ?? 'Dự án Mới';
        
        $db = Database::getInstance();
        $db->query("INSERT INTO trainings (course_name, description, start_date, end_date, provider, cost, status)
                    VALUES (:name, :desc, DATE_ADD(CURDATE(), INTERVAL 7 DAY), DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'AI Auto-Scheduled', 0, 'Planned')", [
            'name' => 'Khóa huấn luyện Khẩn cấp cho ' . $project,
            'desc' => 'Tự động lên lịch bởi AI Command Center để lấp khoảng trống kỹ năng.'
        ]);
        $trainingId = $db->lastInsertId();

        // Tìm một số nhân viên thuộc dự án để đưa vào danh sách tham gia
        $db->query("SELECT e.id FROM employees e JOIN projects p ON e.current_project_id = p.id WHERE p.project_name = :pname LIMIT 10", ['pname' => $project]);
        $employees = $db->fetchAll();

        foreach ($employees as $emp) {
            $db->query("INSERT INTO training_participants (training_id, employee_id, status) VALUES (:tid, :eid, 'Nominated')", [
                'tid' => $trainingId,
                'eid' => $emp['id']
            ]);
        }
        
        Session::setFlash('success', 'AI đã tự động lập danh sách tham gia (' . count($employees) . ' người) huấn luyện kỹ năng cho ' . $project);
        $this->redirect('dashboard'); 
    }

    public function suggest(int $id): void
    {
        $this->checkPermission('ai.view');
        
        $recModel = $this->model('Recruitment');
        $request = $recModel->getRequestById($id);
        
        if (!$request) {
            Session::setFlash('error', 'Không tìm thấy Yêu cầu tuyển dụng.');
            $this->redirect('recruitment');
            return;
        }

        $candidates = $request->candidates ?? [];

        // Giả lập AI chấm điểm (Scoring Engine)
        foreach ($candidates as &$c) {
            $score = 40; // Base score
            $insights = [];

            // Mô phỏng phân tích kỹ năng
            $skills = strtolower((string)$c->skills);
            $reqSkills = strtolower((string)$request->requirements);
            
            if (!empty($reqSkills) && !empty($skills)) {
                // Tính điểm cơ bản dựa trên độ dài chuỗi match
                $score += rand(10, 25); 
                $insights[] = "Phù hợp một phần với yêu cầu kỹ năng";
            }

            // Kinh nghiệm
            if ($c->experience_years >= 5) {
                $score += 20;
                $insights[] = "Kinh nghiệm dày dặn ({$c->experience_years} năm)";
            } elseif ($c->experience_years >= 2) {
                $score += 10;
                $insights[] = "Kinh nghiệm phù hợp ({$c->experience_years} năm)";
            }

            // Bằng cấp
            if (in_array($c->highest_degree, ['Đại học', 'Thạc sĩ', 'Tiến sĩ'])) {
                $score += 15;
                $insights[] = "Bằng cấp chất lượng cao ({$c->highest_degree})";
            }

            // Độ tuổi phù hợp (giả định)
            if (!empty($c->birth_date)) {
                $age = date('Y') - date('Y', strtotime($c->birth_date));
                if ($age >= 25 && $age <= 35) {
                    $score += 5;
                    $insights[] = "Độ tuổi vàng ({$age} tuổi)";
                }
            }
            
            // Random factor cho thực tế
            $score += rand(0, 10);
            
            $c->ai_score = min($score, 99); // Max 99%
            
            if (empty($insights)) {
                $insights[] = "Đạt yêu cầu cơ bản";
            }
            $c->ai_insights = $insights;
            
            // Cấp độ AI đánh giá
            if ($c->ai_score >= 85) {
                $c->ai_level = 'Excellent';
                $c->ai_color = 'success';
            } elseif ($c->ai_score >= 70) {
                $c->ai_level = 'Good';
                $c->ai_color = 'primary';
            } elseif ($c->ai_score >= 50) {
                $c->ai_level = 'Average';
                $c->ai_color = 'warning';
            } else {
                $c->ai_level = 'Poor';
                $c->ai_color = 'danger';
            }
        }

        // Sắp xếp theo điểm AI giảm dần
        usort($candidates, function($a, $b) {
            return $b->ai_score <=> $a->ai_score;
        });

        $this->view('layouts/header', ['pageTitle' => 'AI Lọc & Gợi ý Ứng viên']);
        $this->view('ai/suggest', [
            'id' => $id,
            'request' => $request,
            'candidates' => $candidates
        ]);
        $this->view('layouts/footer');
    }
}
