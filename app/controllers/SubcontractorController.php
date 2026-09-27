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
        
        $this->view('layouts/header', ['pageTitle' => 'Chi tiết Tổ đội']);
        $this->view('subcontractor/detail', ['workers' => $workers, 'sub_id' => $id]);
        $this->view('layouts/footer');
    }
}
