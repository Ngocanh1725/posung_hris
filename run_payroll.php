<?php
require_once 'app/config/config.php';
require_once 'app/core/Database.php';
require_once 'app/core/Model.php';
require_once 'app/models/Payroll.php';

$payroll = new Payroll();
$count = $payroll->calculateMonthlyPayroll(9, 2026);
echo "Done! Calculated for $count employees.";
?>
