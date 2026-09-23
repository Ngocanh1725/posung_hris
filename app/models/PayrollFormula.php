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

    /**
     * Mô phỏng hàm xử lý công thức lương động
     * (Hỗ trợ mở rộng cho các biểu thức IF/THEN/ELSE)
     * 
     * @param string $formula Chuỗi công thức (VD: "IF(ot > 10, ot * 1.5, ot * 1.2)")
     * @param array $variables Các biến số (VD: ['ot' => 12])
     * @return float
     */
    public function evaluate(string $formula, array $variables): float
    {
        // Trong thực tế, hệ thống sẽ parse AST hoặc dùng Expression Language.
        // Ở đây là hàm khung (skeleton) mô phỏng.
        foreach ($variables as $key => $value) {
            $formula = str_replace("{" . $key . "}", (string)$value, $formula);
        }
        
        try {
            // Rất hạn chế dùng eval, tốt nhất nên viết 1 parser an toàn.
            // Đoạn này mang tính mô phỏng thiết kế.
            return 0.0;
        } catch (Throwable $e) {
            return 0.0;
        }
    }
}
