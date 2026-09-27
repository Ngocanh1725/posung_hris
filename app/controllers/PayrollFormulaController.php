<?php
/**
 * ============================================================
 *  POSUNG HRIS – PayrollFormulaController
 * ============================================================
 *  Quản lý công thức tính lương động
 * ============================================================
 */

class PayrollFormulaController extends Controller
{
    public function index(): void
    {
        $this->checkPermission('payroll.calculate');

        $formulaModel = $this->model('PayrollFormula');
        $db = Database::getInstance();
        $db->query("SELECT * FROM payroll_formulas ORDER BY id DESC");
        $formulas = json_decode(json_encode($db->fetchAll()));

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Công thức Lương']);
        $this->view('payroll/formulas', [
            'formulas' => $formulas
        ]);
        $this->view('layouts/footer');
    }

    public function store(): void
    {
        $this->checkPermission('payroll.calculate');
        if ($this->isPost()) {
            $data = [
                'formula_name' => $this->postData('formula_name'),
                'formula_code' => $this->postData('formula_code'),
                'expression'   => $this->postData('expression'),
                'is_active'    => (int) $this->postData('is_active')
            ];

            $db = Database::getInstance();
            $db->query("INSERT INTO payroll_formulas (formula_name, formula_code, expression, is_active) VALUES (:name, :code, :expr, :active)", [
                'name' => $data['formula_name'],
                'code' => $data['formula_code'],
                'expr' => $data['expression'],
                'active' => $data['is_active']
            ]);

            Session::setFlash('success', 'Đã thêm công thức lương mới thành công.');
            $this->redirect('payrollformula');
        }
    }

    public function update(): void
    {
        $this->checkPermission('payroll.calculate');
        if ($this->isPost()) {
            $id = (int) $this->postData('id');
            $data = [
                'formula_name' => $this->postData('formula_name'),
                'formula_code' => $this->postData('formula_code'),
                'expression'   => $this->postData('expression'),
                'is_active'    => (int) $this->postData('is_active'),
                'id' => $id
            ];

            $db = Database::getInstance();
            $db->query("UPDATE payroll_formulas SET formula_name = :name, formula_code = :code, expression = :expr, is_active = :active WHERE id = :id", [
                'name' => $data['formula_name'],
                'code' => $data['formula_code'],
                'expr' => $data['expression'],
                'active' => $data['is_active'],
                'id' => $data['id']
            ]);

            Session::setFlash('success', 'Đã cập nhật công thức lương.');
            $this->redirect('payrollformula');
        }
    }

    public function delete(int $id): void
    {
        $this->checkPermission('payroll.calculate');
        $db = Database::getInstance();
        $db->query("DELETE FROM payroll_formulas WHERE id = :id", ['id' => $id]);
        Session::setFlash('success', 'Đã xóa công thức.');
        $this->redirect('payrollformula');
    }
}
