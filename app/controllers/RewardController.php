<?php
/**
 * ============================================================
 *  POSUNG HRIS – RewardController
 * ============================================================
 */

class RewardController extends Controller
{
    public function index(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        $rewardModel = $this->model('RewardDiscipline');
        $records = $rewardModel->getAllRecords();

        $employeeModel = $this->model('Employee');
        $employees = $employeeModel->getAll(); // Để fill dropdown form

        $this->view('layouts/header', ['pageTitle' => 'Thi đua & Kỷ luật (HSE)']);
        $this->view('reward/index', [
            'records' => $records,
            'employees' => $employees
        ]);
        $this->view('layouts/footer');
    }

    public function store(): void
    {
        Session::checkPermission(['Admin', 'HR_Manager']);

        if ($this->isPost()) {
            $data = [
                'employee_id'         => (int)$this->postData('employee_id'),
                'type'                => $this->postData('type'),
                'decision_number'     => $this->postData('decision_number'),
                'decision_date'       => $this->postData('decision_date'),
                'title'               => $this->postData('title'),
                'amount'              => (float)$this->postData('amount', 0),
                'reason'              => $this->postData('reason'),
                'is_safety_violation' => $this->postData('is_safety_violation') ? 1 : 0
            ];

            $rewardModel = $this->model('RewardDiscipline');
            if ($rewardModel->createRecord($data)) {
                if ($data['type'] === 'Discipline' && $data['is_safety_violation']) {
                    Session::setFlash('success', 'Đã lưu Kỷ luật & Khóa Blacklist nhân sự thành công!');
                } else {
                    Session::setFlash('success', 'Đã lưu Quyết định thành công!');
                }
            } else {
                Session::setFlash('error', 'Có lỗi xảy ra khi lưu Quyết định.');
            }

            $this->redirect('reward/index');
        }
    }
}
