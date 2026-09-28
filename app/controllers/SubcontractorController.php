<?php
class SubcontractorController extends Controller
{
    public function index(): void
    {
        $this->checkPermission('subcontractor.view');
        
        $model = $this->model('Subcontractor');
        $subs = $model->getAllSubcontractors();

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Nhà Thầu Phụ (Subcontractors)']);
        $this->view('subcontractor/index', ['subs' => $subs]);
        $this->view('layouts/footer');
    }

    public function detail(int $id): void
    {
        $this->checkPermission('subcontractor.view');
        
        $model = $this->model('Subcontractor');
        $workers = $model->getWorkersBySubId($id);
        
        $this->view('layouts/header', ['pageTitle' => 'Chi tiết Tổ đội / Safety Induction']);
        $this->view('subcontractor/detail', ['workers' => $workers, 'sub_id' => $id]);
        $this->view('layouts/footer');
    }

    public function updateInduction(): void
    {
        $this->checkPermission('hse.manage');
        if ($this->isPost()) {
            $worker_id = $this->postData('worker_id');
            $sub_id = $this->postData('sub_id');
            $induction_date = $this->postData('induction_date');
            $status = $this->postData('induction_status');
            
            $db = Database::getInstance();
            $db->query("
                UPDATE sub_workers 
                SET safety_induction_date = :date, induction_status = :status 
                WHERE id = :id
            ", [
                'date' => $induction_date,
                'status' => $status,
                'id' => $worker_id
            ]);
            
            Session::setFlash('success', 'Đã cập nhật trạng thái Safety Induction!');
            $this->redirect('subcontractor/detail/' . $sub_id);
        }
    }
}
