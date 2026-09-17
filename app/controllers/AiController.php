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
        Session::checkPermission(['Admin', 'HR_Manager']);

        $model = $this->model('AIAnalytics');
        
        // Chạy phân tích
        $analysisResults = $model->runFullAnalysis();

        $this->view('layouts/header', ['pageTitle' => 'Hệ Chuyên gia (HR Expert System)']);
        $this->view('ai/index', ['analysis' => $analysisResults]);
        $this->view('layouts/footer');
    }
}
