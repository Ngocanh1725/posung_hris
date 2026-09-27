<?php
class HseController extends Controller
{
    public function index(): void
    {
        $this->checkPermission('hse.view');
        
        $model = $this->model('Hse');
        $projectStats = $model->getProjectIncidentStats();
        $expiringCerts = $model->getExpiringCerts();

        $this->view('layouts/header', ['pageTitle' => 'Quản lý HSE & An toàn Lao động']);
        $this->view('hse/index', [
            'projectStats' => $projectStats,
            'expiringCerts' => $expiringCerts
        ]);
        $this->view('layouts/footer');
    }
}
