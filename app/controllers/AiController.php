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
}
