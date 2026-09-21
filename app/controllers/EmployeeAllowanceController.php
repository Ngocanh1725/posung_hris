<?php
/**
 * ============================================================
 *  POSUNG HRIS – EmployeeAllowanceController
 * ============================================================
 */

class EmployeeAllowanceController extends Controller
{
    public function __construct()
    {
        Session::checkPermission(['Admin', 'HR_Manager', 'Project_Manager']);
    }

    /**
     * Gán phụ cấp cho nhân viên
     */
    public function store(): void
    {
        if ($this->isPost()) {
            $employeeId = (int)$this->postData('employee_id');
            $allowanceId = (int)$this->postData('allowance_id');
            $effectiveDate = $this->postData('effective_date');
            
            // Số tiền tùy chỉnh nếu có
            $amountRaw = $this->postData('amount', '');
            $amount = $amountRaw !== '' ? (float)str_replace(',', '', $amountRaw) : null;

            $model = $this->model('EmployeeAllowance');

            // Kiểm tra xem đã được gán chưa (tránh trùng lặp)
            $existing = $model->where(['employee_id' => $employeeId, 'allowance_id' => $allowanceId]);
            if (!empty($existing)) {
                Session::setFlash('error', 'Nhân viên này đã được gán phụ cấp này rồi!');
            } else {
                $data = [
                    'employee_id' => $employeeId,
                    'allowance_id' => $allowanceId,
                    'effective_date' => $effectiveDate,
                    'amount' => $amount
                ];
                $model->create($data);
                Session::setFlash('success', 'Đã gán phụ cấp thành công!');
            }

            $this->redirect('employee/detail/' . $employeeId);
        }
    }

    /**
     * Gỡ bỏ phụ cấp
     */
    public function delete(int $id): void
    {
        $model = $this->model('EmployeeAllowance');
        $record = $model->find($id);
        if ($record) {
            $model->delete($id);
            Session::setFlash('success', 'Đã gỡ bỏ phụ cấp khỏi nhân sự.');
            $this->redirect('employee/detail/' . $record->employee_id);
        } else {
            Session::setFlash('error', 'Không tìm thấy phụ cấp.');
            $this->redirect('employee');
        }
    }
}
