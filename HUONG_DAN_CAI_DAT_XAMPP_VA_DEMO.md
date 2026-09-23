# Hướng Dẫn Cài Đặt & Kịch Bản Demo XAMPP (Po Sung HRIS)

Chào mừng bạn đến với Hệ thống Quản trị Nguồn Nhân lực (HRIS) của **Po Sung MEC Co., Ltd**.
Dưới đây là hướng dẫn chi tiết cách cài đặt hệ thống trên môi trường XAMPP cục bộ và kịch bản demo (End-to-End) minh họa chu trình quản lý nhân sự.

---

## 1. Cài đặt Môi trường XAMPP

1. **Chuẩn bị:** Tải và cài đặt [XAMPP](https://www.apachefriends.org/index.html) phiên bản hỗ trợ PHP 8.1 trở lên.
2. **Cấu hình thư mục:**
   - Đặt toàn bộ mã nguồn dự án vào thư mục: `C:\xampp\htdocs\posung_hris` (đối với Windows) hoặc `/opt/lampp/htdocs/posung_hris` (đối với Linux).
3. **Cơ sở dữ liệu:**
   - Mở trình duyệt, truy cập `http://localhost/phpmyadmin`.
   - Tạo một Database mới với tên: `posung_hris` (Encoding: `utf8mb4_unicode_ci`).
   - Import file SQL Master: Trỏ tới tab *Import* và chọn tệp `sql/posung_hris_master_v3.sql` (nằm trong thư mục dự án) để tiến hành nhập cấu trúc & dữ liệu mẫu.
4. **Cấu hình kết nối:**
   - Mở tệp `app/config/config.php`.
   - Đảm bảo các thông số DB khớp với máy chủ local:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'posung_hris');
     define('BASE_URL', 'http://localhost/posung_hris');
     ```

## 2. Danh Sách Tài Khoản Phân Quyền (RBAC)

Hệ thống cung cấp 6 tài khoản mẫu tương ứng với 6 vai trò đặc thù để bạn thử nghiệm toàn bộ hệ sinh thái:

| STT | Vai trò | Tên đăng nhập | Mật khẩu | Chức năng nổi bật |
|:---:|---|---|---|---|
| 1 | **Super Admin** | `admin` | `admin123` | Toàn quyền toàn hệ thống (Thiết lập RBAC đa tầng). |
| 2 | **Admin Khung Web** | `ad_khung` | `khung123` | Quản trị giao diện, cấu hình Menu động & Routing. |
| 3 | **Quản lý trưởng (Site PM)** | `pm_amkor` | `pm123` | Xem, điều phối và duyệt công cho nhân sự dự án Amkor Bắc Ninh. |
| 4 | **Quản trị VP (QTVP)** | `qtvp_staff` | `qtvp123` | Quản lý vòng đời Expat, xử lý Visa, quản trị hồ sơ 2C. |
| 5 | **Chuyên viên C&B** | `cb_staff` | `cb123` | Xử lý dữ liệu chấm công (FaceID Edge) & bảng lương động. |
| 6 | **Nhân viên (ESS)** | `emp_kim` | `emp123` | Xem bảng lương cá nhân, khai báo thông tin tự phục vụ. |

---

## 3. Kịch Bản Demo Toàn Diện (8 Bước Chuyển Đổi Số)

Kịch bản này mô phỏng sát thực tế nghiệp vụ quản lý vòng đời của một nhân viên thi công tại công trường:

### Bước 1: Gán quyền linh hoạt (Super Admin)
- Đăng nhập bằng `admin`.
- Vào **Quản trị Hệ thống > Phân Quyền (RBAC)**.
- Gán thêm hoặc thu hồi một tính năng bất kỳ của QTVP (Ví dụ: Cho phép QTVP được duyệt yêu cầu tuyển dụng nhưng không được xem Lương).

### Bước 2: Tùy biến Menu (Admin Khung)
- Đăng nhập bằng `ad_khung`.
- Vào **Cấu hình Giao diện > System Menu**.
- Thay đổi thứ tự hiển thị, hoặc đổi icon của tab "Tuyển Dụng" để cập nhật ngay lập tức giao diện người dùng mà không cần sửa code.

### Bước 3: Phễu Tuyển dụng (Kanban ATS)
- Đăng nhập bằng `qtvp_staff`.
- Mở module **Tuyển Dụng**.
- Tạo mới 1 ứng viên "Thợ Hàn 6G". Kéo thả thẻ (card) ứng viên này từ "Mới ứng tuyển" sang "Đang phỏng vấn" qua giao diện Kanban.

### Bước 4: Kiểm tra An toàn & Cảnh báo HSE (HSE Blacklist)
- Thử kéo ứng viên vào cột "Trúng tuyển" (Hired).
- Nếu số CCCD của ứng viên trùng khớp với danh sách vi phạm An toàn Lao động (HSE Blacklists), hệ thống sẽ **block** và cảnh báo ngay lập tức.

### Bước 5: In Thư Mời Nhận Việc & Hồ sơ 2C
- Sau khi ứng viên qua vòng kiểm tra, tự động chuyển đổi Ứng viên -> Nhân sự chính thức.
- Truy cập **Hồ sơ Nhân sự** -> Bấm **In lý lịch 2C** chuẩn biểu mẫu nhà nước.

### Bước 6: Điều động Nhân sự & Cost Center (Mobilization)
- Trưởng phòng Ban Hành chính tạo lệnh Điều chuyển nhân viên này xuống Công trường **Amkor Bắc Ninh**.
- Ngay khi duyệt lệnh, hệ thống lưu lịch sử vào `job_movements` và tự động điều chỉnh Phụ cấp Xa nhà áp dụng riêng cho mã Cost Center của Amkor.

### Bước 7: Chấm công Edge & Bảng Lương Động (C&B)
- Đăng nhập bằng `cb_staff`.
- Vào **Chấm công** -> Import file dữ liệu mẫu lấy từ máy FaceID công trường (nếu có mô phỏng) hoặc chọn Nút *Tính toán Chấm công*.
- Vào **Tiền Lương** -> Tính lương tháng. Hệ thống bóc tách tự động các giờ OT (150%, 200%) và trừ thuế TNCN tự động. Bảng lương đã hoàn tất!

### Bước 8: Hệ Chuyên Gia AI (Expert Analytics)
- Bất kỳ lúc nào, **Super Admin** hoặc **Giám đốc** có thể truy cập **BI Dashboard / AI Command Center**.
- Hệ thống AI sẽ đọc toàn bộ dữ liệu OT / Lương / Lệnh điều động để:
  + (1) Phân tích số giờ OT quá cao của kỹ sư và ra cảnh báo **Nguy cơ nghỉ việc**.
  + (2) Dự báo thiếu hụt nhân sự "Thợ hàn" có chứng chỉ 6G cho dự án sắp chạy để đề xuất tạo lệnh tuyển dụng khẩn.

---

**Kết luận:** Hệ thống Posung HRIS không chỉ dừng lại ở Quản lý Dữ liệu số (Data Entry) mà đã vươn tầm thành Hệ Chuyên Gia Tự Động Hóa (Expert System), mang đến cái nhìn toàn cảnh và chủ động cho Ban Giám đốc trong việc quản trị nguồn nhân lực!
