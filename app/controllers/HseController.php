<?php
class HseController extends Controller
{
    public function index(): void
    {
        $this->checkPermission('hse.view');
        
        $model = $this->model('Hse');
        $projectStats = $model->getProjectIncidentStats();
        $expiringCerts = $model->getExpiringCerts();
        $missingCerts = $model->getEngineersMissingCerts();

        $this->view('layouts/header', ['pageTitle' => 'Quản lý HSE & An toàn Lao động']);
        $this->view('hse/index', [
            'projectStats' => $projectStats,
            'expiringCerts' => $expiringCerts,
            'missingCerts' => $missingCerts
        ]);
        $this->view('layouts/footer');
    }

    public function cards(): void
    {
        $this->checkPermission('hse.view');
        $model = $this->model('Hse');
        $projectId = $this->getData('project_id') ? (int) $this->getData('project_id') : null;
        
        $cards = $model->getAllSafetyCards($projectId);
        
        $projectModel = $this->model('Project');
        $projects = $projectModel->getActiveProjects();

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Thẻ An toàn (Nhóm 1-6)']);
        $this->view('hse/cards', [
            'cards' => $cards,
            'projects' => $projects,
            'currentProject' => $projectId
        ]);
        $this->view('layouts/footer');
    }

    public function saveCard(): void
    {
        $this->checkPermission('hse.manage');
        if ($this->isPost()) {
            $data = [
                'id' => $this->postData('id'),
                'employee_id' => $this->postData('employee_id'),
                'group_type' => $this->postData('group_type'),
                'card_number' => $this->postData('card_number'),
                'issue_date' => $this->postData('issue_date'),
                'expiry_date' => $this->postData('expiry_date'),
                'training_unit' => $this->postData('training_unit'),
                'status' => 'VALID'
            ];
            
            if (strtotime($data['expiry_date']) < time()) {
                $data['status'] = 'EXPIRED';
            } elseif (strtotime($data['expiry_date']) < strtotime('+60 days')) {
                $data['status'] = 'EXPIRING_SOON';
            }

            $model = $this->model('Hse');
            $model->saveSafetyCard($data);
            
            Session::setFlash('success', 'Đã lưu thông tin thẻ an toàn.');
            $this->redirect('hse/cards');
        }
    }
}
