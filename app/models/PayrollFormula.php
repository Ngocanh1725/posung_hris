<?php
class PayrollFormula extends BaseModel
{
    protected string $table = 'payroll_formulas';

    /**
     * Lấy các items (chi tiết công thức) của một formula
     *
     * @param int $formulaId
     * @return array
     */
    public function getItems(int $formulaId): array
    {
        $sql = "SELECT * FROM payroll_formula_items WHERE formula_id = :fid ORDER BY sort_order ASC, id ASC";
        $this->db->query($sql, ['fid' => $formulaId]);
        return $this->db->fetchAll();
    }
}
