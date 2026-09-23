# Hướng dẫn Vận hành và Phân quyền Hệ thống (RBAC)

Tài liệu này hướng dẫn Quản trị viên (Super Admin hoặc Sub-Admin có quyền ủy quyền) cách thức thiết lập, cấu hình và vận hành ma trận phân quyền của hệ thống POSUNG HRIS.

## 1. Nguyên tắc cốt lõi (Core Principles)
- **Quyền hạn 2 tầng**: Một người dùng (User) thừa hưởng quyền từ **Vai trò (Role)** của họ (ví dụ: `HR_Manager`). Ngoài ra, họ có thể được cấp **Quyền tùy chỉnh (Custom Permissions)** ghi đè thông qua Ma trận Phân quyền.
- **Ủy quyền (Delegation)**: Bất kỳ ai được cấp cờ `can_delegate = 1` sẽ có khả năng vào bảng phân quyền cho tài khoản cấp dưới.
- **Giới hạn an ninh (Security Boundary)**: Một Sub-Admin dù có cờ `can_delegate = 1` cũng **không thể** cấp cho người khác những quyền mà chính họ không sở hữu.

## 2. Thao tác Phân quyền
1. Truy cập vào **Quản lý Tài khoản** (từ Menu bên trái).
2. Tại danh sách nhân sự, tìm kiếm người cần cấp quyền và bấm vào biểu tượng chiếc khiên (<i class="fas fa-user-shield"></i>).
3. Hệ thống sẽ hiển thị **Ma trận Phân quyền (Permission Matrix)** của người đó.
4. Bạn có thể sử dụng nút "Chọn tất cả" tại từng Phân hệ hoặc tích lẻ từng chức năng.
5. (Dành riêng cho Super Admin): Bạn có thể tích chọn mục **Cho phép tài khoản này được ủy quyền (phân quyền cho người khác)** để thăng cấp người dùng đó thành Sub-Admin.
6. Bấm **Lưu Phân quyền** nằm ở thanh cố định phía dưới màn hình.
7. Khi người dùng đó F5 trang web, Menu điều hướng (Sidebar) của họ sẽ lập tức được cập nhật tương ứng.

---

## 3. Kịch bản Kiểm thử End-to-End (E2E Test Plan)

Để đảm bảo hệ thống chặn quyền (Authorization) hoạt động chính xác 100%, hãy thực thi kịch bản giả lập sau:

### Kịch bản 1 (Super Admin → Admin Kế toán)
**Mục tiêu**: Xác minh Super Admin tạo ra được một "Trưởng phòng Kế toán" có quyền phân quyền giới hạn trong nội bộ phòng kế toán.
1. **Tạo tài khoản `ketoan_truong`**: Đăng nhập bằng tài khoản Super Admin, tạo mới 1 User tên `ketoan_truong`.
2. **Cấp quyền**: 
   - Vào ma trận phân quyền của `ketoan_truong`.
   - Chọn **chỉ** phân hệ "Kế toán - Tài chính" (Tiền lương, Tính lương, v.v.).
   - **Tích chọn cờ** "Cho phép tài khoản này được ủy quyền".
   - Bấm Lưu.
3. **Kiểm tra**:
   - Đăng xuất Super Admin và đăng nhập bằng `ketoan_truong`.
   - Xác nhận: Ở Sidebar bên trái CHỈ xuất hiện phân hệ Kế toán/Tiền lương. Không nhìn thấy Hành chính, Nhân sự hay Tuyển dụng.
   - Thử tấn công trực tiếp: Nhập URL `/employee/create` vào thanh địa chỉ.
   - **Kết quả mong đợi**: Hệ thống chuyển hướng ra màn hình lỗi HTTP 403 (Access Denied).

### Kịch bản 2 (Admin Kế toán → Nhân viên Kế toán viên)
**Mục tiêu**: Xác minh "Trưởng phòng Kế toán" phân quyền cho lính của mình không vượt quá quyền hạn (Security Boundary).
1. **Tạo tài khoản `nhanvien_luong`**: Tài khoản `ketoan_truong` tạo mới một user `nhanvien_luong`.
2. **Cấp quyền**:
   - `ketoan_truong` bấm vào biểu tượng khiên của `nhanvien_luong`.
   - Xác nhận: Trong bảng Ma trận, các checkbox thuộc phân hệ Nhân sự, Hệ thống, Tuyển dụng... đều bị **mờ xám (disabled)** và hiển thị cảnh báo ổ khóa chữ đỏ. 
   - `ketoan_truong` tích cấp quyền "Xem bảng lương" (`payroll.view`) cho `nhanvien_luong` nhưng CỐ TÌNH KHÔNG CẤP quyền "Tính lương" (`payroll.calculate`). Bấm Lưu.
3. **Kiểm tra**:
   - Đăng xuất và đăng nhập vào `nhanvien_luong`.
   - Truy cập vào module Tính lương.
   - **Kết quả mong đợi**: Nhân viên này xem được dữ liệu, nhưng nút bấm "Tính Lương" bị ẩn, và nếu gõ URL POST để gọi lệnh tính lương, hệ thống báo lỗi 403.
