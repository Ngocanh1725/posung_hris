# Ghi chú Cập nhật tính năng Mật khẩu Chữ ký số (E-Sign PIN)

## 1. Thay đổi về cấu trúc Cơ sở dữ liệu
- Bảng ảnh hưởng: `users`
- Cột mới được thêm: `esign_pin` (Kiểu dữ liệu: `VARCHAR(255) NULL`)
- Script SQL tham khảo nếu cần chạy lại:
  ```sql
  ALTER TABLE users ADD COLUMN esign_pin VARCHAR(255) NULL;
  ```

## 2. Dữ liệu mặc định
- Cập nhật mật khẩu E-Sign mặc định cho toàn bộ các tài khoản người dùng hiện tại là: `![ô](image.png)`
- Script SQL:
  ```sql
  UPDATE users SET esign_pin = 'posung@123';
  ```

## 3. Thay đổi về Logic Code
- File ảnh hưởng: `app/models/Workflow.php`
- Hàm: `processRequest()`
- Chi tiết:
  Thay vì kiểm tra mã cứng (hardcode) bằng lệnh `if ($password !== 'posung@123')`, hệ thống hiện tại đã truy vấn trực tiếp vào CSDL để lấy mật khẩu cấp 2 của người dùng đang đăng nhập:
  ```php
  // 1. Verify E-Sign Password from Database
  $this->db->query("SELECT esign_pin FROM users WHERE id = :uid", ['uid' => $userId]);
  $user = $this->db->fetch();
        
  $validPin = ($user && !empty($user['esign_pin'])) ? $user['esign_pin'] : 'posung@123';
        
  if ($password !== $validPin) {
      return false;
  }
  ```
  *(Lưu ý: Có sử dụng `'posung@123'` như một giá trị fallback dự phòng trong trường hợp tài khoản mới chưa được set E-Sign PIN)*

## 4. Mục đích
Nâng cấp tính bảo mật và đảm bảo luồng (Workflow) phê duyệt chữ ký số cấp 2 (Kanban E-Sign) hoạt động đúng theo logic định danh người dùng trong thực tế.
