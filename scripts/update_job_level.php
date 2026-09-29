<?php
require 'app/core/Database.php';
require 'config/config.php';
$db = Database::getInstance();

$db->query("UPDATE positions SET job_level = '5' WHERE job_level = 'Director' OR pos_title LIKE '%Giám đốc%'");
$db->query("UPDATE positions SET job_level = '4' WHERE job_level = 'Manager' OR pos_title LIKE 'Trưởng phòng%' OR pos_title = 'Kế toán trưởng' OR pos_title = 'Kỹ Sư Trưởng'");
$db->query("UPDATE positions SET job_level = '3' WHERE job_level = 'Senior' OR pos_title LIKE 'Chuyên gia%'");
$db->query("UPDATE positions SET job_level = '2' WHERE job_level = 'Staff' OR pos_title LIKE 'Chuyên viên%' OR pos_title LIKE 'Kỹ sư%' OR pos_title LIKE 'Kế toán viên' OR pos_title LIKE 'Điều phối viên%'");
$db->query("UPDATE positions SET job_level = '1' WHERE job_level = 'Worker' OR pos_title LIKE 'Thợ%'");
$db->query("UPDATE positions SET job_level = '1' WHERE job_level = '' OR job_level IS NULL OR job_level NOT IN ('1','2','3','4','5')");

echo "Cập nhật dữ liệu cấp bậc thành công.";
