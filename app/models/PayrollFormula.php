<?php
class PayrollFormula extends BaseModel
{
    protected string $table = 'payroll_formulas';

    /**
     * Lấy danh sách các công thức đang active dưới dạng key-value
     */
    public function getAllFormulas(): array
    {
        $sql = "SELECT formula_code, expression FROM payroll_formulas WHERE is_active = 1";
        $this->db->query($sql);
        $result = $this->db->fetchAll();
        
        $formulas = [];
        foreach ($result as $f) {
            $formulas[$f['formula_code']] = $f['expression'];
        }
        return $formulas;
    }

    public function evaluate(string $formula, array $variables): float
    {
        // Thay thế các biến số (VD: 'ot' -> 12, '{base_salary}' -> 5000000)
        uksort($variables, function($a, $b) {
            return strlen($b) - strlen($a);
        });

        foreach ($variables as $key => $value) {
            $val = is_numeric($value) ? (string)$value : "0";
            // Thay thế cả dạng {key} và key thuần túy
            $formula = str_ireplace("{" . $key . "}", $val, $formula);
            $formula = preg_replace('/\b' . preg_quote($key, '/') . '\b/i', $val, $formula);
        }
        
        $formula = $this->resolveIfStatements($formula);
        return $this->evaluateMath($formula);
    }

    private function resolveIfStatements(string $expression): string
    {
        // Xử lý các biểu thức IF(condition, true_val, false_val)
        while (preg_match('/IF\s*\(([^,]+),([^,]+),([^)]+)\)/i', $expression, $matches)) {
            $condition = $matches[1];
            $trueVal = $matches[2];
            $falseVal = $matches[3];
            
            $condResult = $this->evaluateCondition($condition);
            $replaceVal = $condResult ? $trueVal : $falseVal;
            
            $expression = str_replace($matches[0], $replaceVal, $expression);
        }
        return $expression;
    }

    private function evaluateCondition(string $condition): bool
    {
        if (preg_match('/(.+)(>|<|>=|<=|==|!=)(.+)/', $condition, $matches)) {
            $left = $this->evaluateMath($matches[1]);
            $right = $this->evaluateMath($matches[3]);
            $op = trim($matches[2]);
            
            switch ($op) {
                case '>': return $left > $right;
                case '<': return $left < $right;
                case '>=': return $left >= $right;
                case '<=': return $left <= $right;
                case '==': return $left == $right;
                case '!=': return $left != $right;
            }
        }
        return false;
    }

    private function evaluateMath(string $expression): float
    {
        $expression = str_replace(' ', '', $expression);
        if ($expression === '') return 0.0;
        
        // Tính ngoặc
        while (preg_match('/\(([^()]+)\)/', $expression)) {
            $expression = preg_replace_callback('/\(([^()]+)\)/', function($m) {
                return $this->evaluateMath($m[1]);
            }, $expression);
        }

        // Tính nhân chia
        while (preg_match('/(\-?\d+(?:\.\d+)?)([\*\/])(\-?\d+(?:\.\d+)?)/', $expression)) {
            $expression = preg_replace_callback('/(\-?\d+(?:\.\d+)?)([\*\/])(\-?\d+(?:\.\d+)?)/', function($m) {
                $left = (float)$m[1];
                $right = (float)$m[3];
                return $m[2] === '*' ? ($left * $right) : ($right != 0 ? $left / $right : 0);
            }, $expression, 1);
        }

        // Tính cộng trừ
        $expression = str_replace('--', '+', $expression);
        $expression = str_replace('+-', '-', $expression);
        
        while (preg_match('/(\-?\d+(?:\.\d+)?)([\+\-])(\d+(?:\.\d+)?)/', $expression)) {
            $expression = preg_replace_callback('/(\-?\d+(?:\.\d+)?)([\+\-])(\d+(?:\.\d+)?)/', function($m) {
                $left = (float)$m[1];
                $right = (float)$m[3];
                return $m[2] === '+' ? ($left + $right) : ($left - $right);
            }, $expression, 1);
        }
        
        return (float)$expression;
    }
}
