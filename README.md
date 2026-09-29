# POSUNG HRIS - Human Resource Information System

Hệ thống Quản trị Nguồn Nhân lực Toàn diện dành cho ngành Cơ điện & Xây dựng Công trình (**Po Sung MEC Co., Ltd**).

Hệ thống được xây dựng theo kiến trúc **PHP thuần MVC**, cơ sở dữ liệu **MySQL**, hỗ trợ quản trị nhân sự 360°, chu trình tuyển dụng - đào tạo - đánh giá KPI, bảo hộ lao động PPE/HSE, quản lý tài sản, bảo hiểm xã hội, chấm công FaceID/IoT, và tính lương đa cấu hình.

---

## 🚀 Tính Năng Chính

1. **Hồ sơ nhân sự 360° (Employee Management)**: Quản lý đầy đủ thông tin nhân thân, hợp đồng, bằng cấp, điều chuyển, khen thưởng/kỷ luật, tài sản cấp phát, PPE.
2. **Tuyển dụng & Đào tạo (Recruitment & ATS)**: Quản lý vị trí tuyển dụng, pipeline ứng viên dạng Kanban, quản lý khóa đào tạo, đánh giá sau đào tạo.
3. **Chấm công & Ca kíp (Timesheet & IoT)**: Đồng bộ máy chấm công vân tay/khuôn mặt FaceID Edge, chấm công theo ca kíp, dự án/công trường.
4. **Tính lương Đa cấu hình (Payroll & Formula Engine)**: Tùy biến công thức tính lương động theo từng vị trí, phụ cấp công trường, trích đóng BHXH/BHYT/BHTN.
5. **Bảo hiểm Xã hội (Social Insurance)**: Theo dõi tỷ lệ đóng, quản lý số sổ BHXH, lịch sử tăng/giảm lao động báo BHXH, giải quyết chế độ ốm đau/thai sản.
6. **Quản lý Tài sản & PPE (Asset & PPE Management)**: Cấp phát, thu hồi tài sản công ty (IT, xe, thiết bị) và bảo hộ lao động (mũ, áo, giày) cho nhân viên công trường.
7. **Đánh giá Hiệu suất (Performance & KPI)**: Thiết lập tiêu chí, đợt đánh giá, tự đánh giá và quản lý đánh giá trực tiếp.
8. **Cổng Tự Phục Vụ Nhân Viên (ESS Portal)**: Nhân viên xem phiếu lương, lịch sử công, làm đơn xin nghỉ phép, cập nhật thông tin cá nhân.
9. **Quản trị Hệ thống & Bảo mật (RBAC & Audit & Backup)**: Phân quyền đa cấp, ghi nhận Audit Log toàn diện, Sao lưu & Phục hồi CSDL tự động/thủ công.
10. **Trợ lý Phân tích AI (AI Analytics)**: Dự báo biến động nhân sự, phân tích năng suất lao động bằng AI.

---

## 🛠️ Cài Đặt Môi Trường (XAMPP)

### Yêu cầu:
- **XAMPP** hỗ trợ **PHP 8.1+** và **MySQL/MariaDB**.
- Apache module: `mod_rewrite` bật.

### Các bước thiết lập:
1. Đặt mã nguồn tại thư mục: `C:\xampp\htdocs\posung_hris`.
2. Tạo CSDL trong phpMyAdmin:
   - Tên CSDL: `posung_hris`
   - Bảng mã (Collation): `utf8mb4_unicode_ci`
3. Import CSDL:
   - File master: `sql/posung_hris_master_latest.sql`
   - Các file patch trong thư mục `sql/` (nếu cần bổ sung tính năng mới nhất).
4. Cấu hình kết nối trong `config/config.php` hoặc `app/config/config.php`:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'posung_hris');
   define('BASE_URL', 'http://localhost/posung_hris');
   ```
5. Khởi động Apache & MySQL trên XAMPP Control Panel.
6. Truy cập: `http://localhost/posung_hris`

---

## 👥 Danh Sách Tài Khoản Mẫu (RBAC Demo)

| Tài khoản | Mật khẩu | Vai trò | Quyền hạn tiêu biểu |
|---|---|---|---|
| `admin` | `admin123` / `123456` | **Super Admin** | Toàn quyền quản trị hệ thống, RBAC, CSDL |
| `ad_khung` | `khung123` | **Admin Khung Web** | Quản trị menu động, giao diện hệ thống |
| `pm_amkor` | `pm123` | **Site PM** | Điều phối & duyệt công nhân sự công trường |
| `qtvp_staff` | `qtvp123` | **Quản trị Văn phòng** | Quản lý vòng đời nhân sự, hồ sơ, tuyển dụng |
| `cb_staff` | `cb123` | **Chuyên viên C&B** | Xử lý bảng lương, chấm công, BHXH |
| `emp_kim` | `emp123` | **Nhân viên (ESS)** | Xem phiếu lương, chấm công, gửi đơn xin phép |

> Mã PIN ký số nội bộ mặc định: `posung@123`

---

## 📂 Cấu Trúc Thư Mục Dự Án

```
posung_hris/
├── app/
│   ├── config/          # Cấu hình CSDL và hệ thống
│   ├── controllers/     # Controller điều hướng nghiệp vụ MVC
│   ├── models/          # Model truy vấn dữ liệu MySQL
│   ├── views/           # Giao diện người dùng (Blade/PHP Views)
│   └── helpers/         # Hàm tiện ích (Session, Auth, AuditLogger, ...)
├── backups/             # Thư mục lưu các bản sao lưu database (.sql)
├── config/              # File cấu hình bổ sung
├── doc/                 # Tài liệu quy trình, hướng dẫn phân quyền, PTTK
├── public/              # Tài nguyên tĩnh (CSS, JS, Fonts, Images)
├── scripts/             # Các script bảo trì, kiểm tra dữ liệu
├── sql/                 # CSDL master và các bản patch cập nhật
│   └── archive/         # Lưu trữ các bản SQL cũ
├── cron_notifications.php # Script chạy ngầm gửi cảnh báo tự động
└── README.md            # Tài liệu dự án
```

---

## 📑 Tài Liệu Bổ Sung

Xem thêm các hướng dẫn chi tiết trong thư mục [doc/](file:///c:/xampp/htdocs/posung_hris/doc/):
- [doc/GIOI_THIEU_QUY_TRINH.md](file:///c:/xampp/htdocs/posung_hris/doc/GIOI_THIEU_QUY_TRINH.md): Giới thiệu quy trình nghiệp vụ tổng thể.
- [doc/HUONG_DAN_CAI_DAT_XAMPP_VA_DEMO.md](file:///c:/xampp/htdocs/posung_hris/doc/HUONG_DAN_CAI_DAT_XAMPP_VA_DEMO.md): Kịch bản demo 8 bước chuyển đổi số.
- [doc/HUONG_DAN_PHAN_QUYEN.md](file:///c:/xampp/htdocs/posung_hris/doc/HUONG_DAN_PHAN_QUYEN.md): Ma trận phân quyền RBAC.
