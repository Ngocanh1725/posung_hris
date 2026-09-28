# Kế hoạch Prompt: Rà soát & Tối ưu Cơ sở dữ liệu Hệ thống (Database Audit & Optimization)

Dưới đây là bộ prompt được chia thành các **Phiên (Sessions)** để bạn có thể copy/paste yêu cầu Antigravity (hoặc bất kỳ AI nào) thực hiện việc rà soát, gỡ lỗi và tối ưu hóa hệ thống CSDL một cách an toàn và triệt để nhất.

---

## 🚀 Phiên 1: Rà soát Cấu trúc & Ràng buộc Khóa Ngoại (Foreign Keys & Schema Audit)

**Hãy copy và gửi đoạn prompt sau:**

```text
Vai trò: Bạn là Chuyên gia Quản trị Cơ sở dữ liệu (Senior DBA) và Kỹ sư Backend hệ thống.

Bối cảnh: Hệ thống POSUNG HRIS đã trải qua nhiều đợt nâng cấp (patch) thêm bảng và cột, dẫn đến nguy cơ thiếu hụt ràng buộc khóa ngoại (Foreign Key), thiếu cột hoặc sai lệch cấu trúc so với thiết kế ban đầu.

Nhiệm vụ Phiên 1: Rà soát cấu trúc CSDL
1. Sử dụng các công cụ của bạn (như chạy lệnh PHP PDO) để quét toàn bộ các bảng trong CSDL `posung_hris`.
2. Kiểm tra các bảng cốt lõi (employees, projects, timesheets, payrolls, hse_safety_cards, job_movements...) xem có:
   - Thiếu khóa ngoại liên kết (Foreign Keys) giữa các bảng không? (Ví dụ: project_id có liên kết đúng với bảng projects không).
   - Thiếu các trường dữ liệu thiết yếu hoặc sai kiểu dữ liệu (Data Type) gây tràn bộ nhớ hoặc lỗi truy vấn không?
   - Bảng nào đang thiếu Index ở các cột thường xuyên được dùng để tìm kiếm (WHERE) hoặc JOIN (như emp_code, project_id, month, year)?
3. Tạo một file `patch_posung_construction_v7_audit.sql` để bổ sung các Khóa ngoại (FOREIGN KEY), Chỉ mục (INDEX), và các trường bị thiếu nhằm đảm bảo tính toàn vẹn dữ liệu.
4. Chạy file patch này và báo cáo kết quả cho tôi.
```

---

## 🚀 Phiên 2: Xử lý Dữ liệu Rác & Lỗi Truy vấn (Data Integrity & Query Errors)

**Hãy copy và gửi đoạn prompt sau:**

```text
Vai trò: Bạn là Chuyên gia Quản trị Cơ sở dữ liệu (Senior DBA) và Kỹ sư Backend hệ thống.

Bối cảnh: Do trước đây CSDL thiếu khóa ngoại nên có thể đang tồn tại "dữ liệu mồ côi" (Orphaned data - ví dụ: timesheets hoặc payrolls của một nhân viên đã bị xóa khỏi bảng employees). Đồng thời, một số truy vấn có thể bị lỗi do sai logic JOIN.

Nhiệm vụ Phiên 2: Dọn dẹp và sửa lỗi truy vấn
1. Quét tìm Dữ liệu mồ côi (Orphaned Records): Viết script kiểm tra xem có bản ghi nào trong các bảng (payrolls, timesheets, job_movements, certificates...) trỏ tới `employee_id`, `project_id`, `department_id` không tồn tại hay không. Nếu có, hãy đưa ra phương án xử lý (Xóa hoặc Gán về NULL/Default).
2. Rà soát file `error_log` (nếu có) hoặc tự động kiểm tra cú pháp các câu lệnh SQL trong thư mục `app/models/` xem có lỗi:
   - "Column not found" (Lỗi thiếu cột).
   - "Ambiguous column name" (Lỗi trùng tên cột khi JOIN).
   - "Cannot add or update a child row: a foreign key constraint fails" (Lỗi xung đột khóa ngoại).
3. Sửa lỗi trực tiếp vào các file Model PHP tương ứng nếu phát hiện câu lệnh SQL viết sai cú pháp. Trình bày chi tiết các file bạn đã sửa.
```

---

## 🚀 Phiên 3: Tối ưu Hiệu suất Truy vấn CSDL (Query Optimization & N+1 Problem)

**Hãy copy và gửi đoạn prompt sau:**

```text
Vai trò: Bạn là Kỹ sư Tối ưu hóa Hiệu năng (Performance Optimization Engineer).

Bối cảnh: Hệ thống có thể gặp tình trạng load chậm khi dữ liệu lớn lên do lỗi N+1 Query hoặc sử dụng quá nhiều vòng lặp truy vấn trong PHP thay vì JOIN trực tiếp trong SQL.

Nhiệm vụ Phiên 3: Tối ưu hiệu suất mã nguồn
1. Rà soát toàn bộ thư mục `app/models/` và `app/controllers/`: 
   - Tìm kiếm các vòng lặp `foreach` mà bên trong đó có chứa lệnh gọi `$this->db->query(...)`. Đây là nguyên nhân gây ra lỗi N+1 Query làm chậm hệ thống.
   - Refactor (viết lại) các hàm này bằng cách dùng `JOIN`, `IN(...)` hoặc gom nhóm truy vấn (Batch Query) để lấy dữ liệu chỉ bằng 1-2 lần gọi Database.
2. Kiểm tra các hàm tính toán nặng (như `calculateProjectAllocatedPayroll` trong Payroll.php hoặc tính toán Timesheet) xem có thể sử dụng Transaction (`beginTransaction`, `commit`) tối ưu hơn để tránh block database không.
3. Cập nhật lại mã nguồn và giải thích cho tôi biết tốc độ dự kiến sẽ cải thiện như thế nào.
```

---

## 🚀 Phiên 4: Giả lập Chạy thử (Stress Test) và Bàn giao Hệ thống

**Hãy copy và gửi đoạn prompt sau:**

```text
Vai trò: Bạn là Kỹ sư Đảm bảo Chất lượng Hệ thống (QA/QC Engineer).

Bối cảnh: Chúng ta cần đảm bảo 100% hệ thống không bị crash hoặc văng lỗi CSDL (SQL Exception) khi vận hành thực tế.

Nhiệm vụ Phiên 4: Test luồng và Bàn giao
1. Hãy tạo một file script `test_db_flows.php` để tự động giả lập (Mock):
   - Tạo thử 1 dự án mới, 1 nhân viên mới.
   - Gán nhân viên vào dự án, chấm công ngẫu nhiên cho nhân viên đó.
   - Chạy Engine tính lương cho nhân viên đó.
   - Cấp phát 1 thẻ an toàn HSE.
   - Xóa thử nhân viên đó (Kiểm tra xem CSDL có chặn lại do vướng ràng buộc Khóa ngoại (Restrict) hay tự động xóa các bảng con (Cascade) đúng như thiết kế không).
2. Chạy script này, bắt các ngoại lệ (Try/Catch) PDOException. Nếu script chạy mượt mà từ đầu đến cuối không xuất hiện lỗi SQL nào, hãy xóa file test đó đi.
3. Nếu có lỗi phát sinh, hãy tự động fix ngay trong Controller/Model và chạy lại đến khi hoàn hảo. Báo cáo kết quả nghiệm thu cuối cùng cho tôi.
```
