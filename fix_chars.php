<?php
require_once 'app/core/Database.php';
require_once 'config/config.php';
$db = Database::getInstance();

$db->query("UPDATE employees SET full_name='Lê Minh Đức', address='78 Hoàng Văn Thụ, TP Thái Nguyên' WHERE id=4");
$db->query("UPDATE employees SET full_name='Đỗ Quốc Cường', address='15 Cổ Loa, Đông Anh, Hà Nội' WHERE id=7");
$db->query("UPDATE employees SET full_name='Lương Thị Mai', address='20 Trần Duy Hưng, Cầu Giấy, Hà Nội' WHERE id=8");

$db->query("UPDATE users SET full_name='Quản trị viên' WHERE id=1");
$db->query("UPDATE users SET full_name='Nguyễn Thị Hương' WHERE id=2");
$db->query("UPDATE users SET full_name='Park Joon Hyuk' WHERE id=3");
$db->query("UPDATE users SET full_name='Trần Văn Tùng' WHERE id=4");

$db->query("UPDATE positions SET pos_title='Trưởng phòng / Manager' WHERE id=3");
$db->query("UPDATE positions SET pos_title='Chỉ huy trưởng Dự án / PM' WHERE id=5");
$db->query("UPDATE positions SET pos_title='Kỹ sư M&E' WHERE id=6");
$db->query("UPDATE positions SET pos_title='Kỹ sư BIM / Thiết kế' WHERE id=7");
$db->query("UPDATE positions SET pos_title='Kỹ sư An toàn (HSE)' WHERE id=8");
$db->query("UPDATE positions SET pos_title='Thợ ống nước (Pipe Fitter)' WHERE id=12");

$db->query("UPDATE projects SET project_name='Nhà máy Amkor Technology - Gói M&E Phase 2', location='KCN Yên Phong, Bắc Ninh' WHERE id=1");
$db->query("UPDATE projects SET project_name='Samsung SEHC - Hệ thống HVAC & Phòng sạch', location='KCN Yên Bình, Thái Nguyên' WHERE id=2");
$db->query("UPDATE projects SET project_name='Starlake Tây Hồ - Hệ thống MEP Tòa nhà VP', location='Starlake, Tây Hồ, Hà Nội' WHERE id=3");

echo "Fixed missing chars successfully.\n";
