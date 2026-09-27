<?php
/**
 * ============================================================
 *  POSUNG HRIS – Phân tích Thiết kế Hệ thống
 * ============================================================
 *  Hiển thị Biểu đồ DFD (Các mức) và Sơ đồ ERD dùng Mermaid
 * ============================================================
 */
?>
<style>
.analysis-header {
    background: linear-gradient(135deg, #0f172a 0%, #334155 100%);
    border-radius: 12px;
    padding: 30px;
    color: #fff;
    margin-bottom: 24px;
}
.analysis-header h1 {
    font-size: 1.6rem;
    font-weight: 700;
    margin-bottom: 8px;
}
.analysis-header p {
    color: #cbd5e1;
    margin: 0;
}

.nav-pills .nav-link {
    color: #475569;
    font-weight: 500;
    border-radius: 8px;
    padding: 10px 20px;
}
.nav-pills .nav-link.active {
    background-color: #0ea5e9;
    color: #fff;
}

.mermaid-wrapper {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 24px;
    margin-top: 24px;
    overflow: auto; /* Cho phép cuộn/zoom nếu to */
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
    text-align: center;
}
.mermaid-wrapper h3 {
    font-size: 1.2rem;
    color: #0f172a;
    margin-bottom: 20px;
    font-weight: 600;
    border-bottom: 2px solid #f1f5f9;
    padding-bottom: 10px;
}
</style>

<div class="analysis-header">
    <h1><i class="fas fa-project-diagram"></i> Phân tích Thiết kế Hệ thống POSUNG HRIS</h1>
    <p>Biểu đồ Luồng Dữ liệu (DFD) và Sơ đồ Quan hệ Thực thể (ERD) của phần mềm Quản trị Nhân lực.</p>
</div>

<!-- Tabs -->
<ul class="nav nav-pills mb-4" id="analysisTabs" role="tablist">
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="dfd-context-tab" data-bs-toggle="tab" data-bs-target="#dfd-context" type="button" role="tab">DFD Mức Ngữ cảnh</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="dfd-level1-tab" data-bs-toggle="tab" data-bs-target="#dfd-level1" type="button" role="tab">DFD Mức 1</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="dfd-level2-tab" data-bs-toggle="tab" data-bs-target="#dfd-level2" type="button" role="tab">DFD Mức 2 (Chi tiết)</button>
  </li>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="erd-tab" data-bs-toggle="tab" data-bs-target="#erd" type="button" role="tab">Sơ đồ ERD</button>
  </li>
</ul>

<!-- Tab Contents -->
<div class="tab-content" id="analysisTabsContent">
    
    <!-- DFD Ngữ Cảnh -->
    <div class="tab-pane fade show active" id="dfd-context" role="tabpanel">
        <div class="mermaid-wrapper">
            <h3>Biểu đồ Luồng Dữ liệu Mức Ngữ cảnh (Context Level)</h3>
            <div class="mermaid">
graph TD
  NhanVien[Nhân viên]
  UngVien[Ứng viên]
  TruongPhong[Trưởng phòng]
  BanGiamDoc[Ban Giám đốc]
  KeToan[Kế toán]
  AIEngine[AI Engine]
  HRIS((Hệ thống POSUNG HRIS))
  
  NhanVien -- "Chấm công, thông tin cá nhân" --> HRIS
  HRIS -- "Phiếu lương, QĐ KT/KL" --> NhanVien
  
  UngVien -- "Nộp hồ sơ" --> HRIS
  HRIS -- "Thông báo kết quả" --> UngVien
  
  TruongPhong -- "Yêu cầu tuyển dụng, Đề xuất KT/KL" --> HRIS
  HRIS -- "Báo cáo quân số" --> TruongPhong
  
  BanGiamDoc -- "Phê duyệt YCTD, QĐ" --> HRIS
  HRIS -- "Báo cáo tổng hợp, AI Dashboard" --> BanGiamDoc
  
  HRIS -- "Bảng lương, Cost allocation" --> KeToan
  
  HRIS -- "Dữ liệu nhân sự, công" --> AIEngine
  AIEngine -- "Khuyến nghị, Cảnh báo" --> HRIS
            </div>
        </div>
    </div>

    <!-- DFD Mức 1 -->
    <div class="tab-pane fade" id="dfd-level1" role="tabpanel">
        <div class="mermaid-wrapper">
            <h3>Biểu đồ Luồng Dữ liệu Mức 1 (Phân rã chức năng)</h3>
            <div class="mermaid">
graph TD
  P1((1.0 Quản lý<br>Hồ sơ))
  P2((2.0 Tuyển dụng))
  P3((3.0 Chấm công))
  P4((4.0 Tính lương))
  P5((5.0 Thuyên chuyển))
  P6((6.0 KT - KL))
  P7((7.0 Nghỉ hưu<br>Nghỉ việc))
  P8((8.0 Thống kê<br>Báo cáo))
  P9((9.0 Phân tích AI))
  
  DB_Emp[(DB: Nhân viên)]
  DB_Rec[(DB: Tuyển dụng)]
  DB_Time[(DB: Chấm công)]
  DB_Pay[(DB: Lương)]
  
  P2 -- "Hồ sơ trúng tuyển" --> P1
  P1 <--> DB_Emp
  P2 <--> DB_Rec
  P3 <--> DB_Time
  P4 <--> DB_Pay
  
  P3 -- "Ngày công" --> P4
  DB_Emp -- "Cấu hình lương" --> P4
  
  P5 --> DB_Emp
  P6 --> DB_Emp
  P7 --> DB_Emp
  
  DB_Emp --> P8
  DB_Time --> P8
  DB_Pay --> P8
  
  DB_Emp --> P9
  P9 -- "Khuyến nghị" --> P2
            </div>
        </div>
    </div>

    <!-- DFD Mức 2 -->
    <div class="tab-pane fade" id="dfd-level2" role="tabpanel">
        
        <div class="mermaid-wrapper">
            <h3>DFD Mức 2 – Tiến trình 2.0 (Tuyển dụng)</h3>
            <div class="mermaid">
graph LR
  P21((2.1 Lập Đề xuất))
  P22((2.2 Phê duyệt YCTD))
  P23((2.3 Nhận & Lọc HS))
  P24((2.4 Phỏng vấn))
  P25((2.5 Chốt Hired))
  
  TruongPhong[Trưởng phòng] -->|"Yêu cầu"| P21
  P21 -->|"Phiếu YCTD"| P22
  BanGiamDoc[Ban Giám đốc] -->|"Duyệt"| P22
  P22 -->|"Đăng tuyển"| P23
  UngVien[Ứng viên] -->|"Hồ sơ"| P23
  P23 -->|"DS Ứng viên"| P24
  P24 -->|"Đánh giá"| P25
  P25 -->|"Offer Letter"| UngVien
            </div>
        </div>

        <div class="mermaid-wrapper">
            <h3>DFD Mức 2 – Tiến trình 3.0 (Chấm công)</h3>
            <div class="mermaid">
graph LR
  P31((3.1 Đồng bộ API))
  P32((3.2 Xử lý Quẹt thẻ))
  P33((3.3 Tính giờ OT))
  P34((3.4 Ghi nhận Phụ cấp))
  
  EdgeServer[Edge Server] -->|"Log quẹt thẻ"| P31
  P31 --> P32
  P32 --> P33
  P33 --> P34
  P34 --> DB_Time[(Timesheets)]
            </div>
        </div>

        <div class="mermaid-wrapper">
            <h3>DFD Mức 2 – Tiến trình 4.0 (Tính lương)</h3>
            <div class="mermaid">
graph LR
  P41((4.1 Lấy Dữ liệu Công))
  P42((4.2 Tính Lương CB & Phụ cấp))
  P43((4.3 Phân bổ chi phí<br>Cost Allocation))
  P44((4.4 Chốt Bảng lương))
  P45((4.5 Xuất Phiếu lương))
  
  DB_Time[(Timesheets)] --> P41
  DB_Emp[(Employees)] -->|"Lương CB, Phụ cấp"| P42
  P41 --> P42
  P42 --> P43
  P43 --> P44
  P44 --> P45
  P45 --> NhanVien[Nhân viên]
  P44 --> KeToan[Kế toán]
            </div>
        </div>

    </div>

    <!-- ERD -->
    <div class="tab-pane fade" id="erd" role="tabpanel">
        <div class="mermaid-wrapper">
            <h3>Sơ đồ Quan hệ Thực thể (ERD)</h3>
            <div class="mermaid">
erDiagram
    employees ||--o{ timesheets : "has"
    employees ||--o{ payrolls : "has"
    employees ||--o{ job_movements : "has"
    employees ||--o{ rewards_disciplines : "has"
    employees ||--o{ certificates : "has"
    employees ||--o{ family_members : "has"
    employees ||--o{ salary_progressions : "has"
    departments ||--o{ employees : "contains"
    positions ||--o{ employees : "defines"
    projects ||--o{ employees : "assigned_to"
    recruitment_requests ||--o{ candidates : "generates"
    departments ||--o{ recruitment_requests : "requests"
    projects ||--o{ payrolls : "allocated_to"
            </div>
        </div>
    </div>

</div>

<!-- Thư viện Bootstrap JS (để chạy Tabs) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Thư viện Mermaid -->
<script type="module">
  import mermaid from 'https://cdn.jsdelivr.net/npm/mermaid@10/dist/mermaid.esm.min.mjs';
  mermaid.initialize({ 
      startOnLoad: true, 
      theme: 'default',
      securityLevel: 'loose',
      flowchart: {
          curve: 'basis'
      }
  });

  // Re-render mermaid khi chuyển tab để tránh lỗi hiển thị sai kích thước
  document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('button[data-bs-toggle="tab"]');
    tabs.forEach(tab => {
        tab.addEventListener('shown.bs.tab', function (event) {
            // Chỉ cần gọi init lại (hoặc mermaid sẽ tự lo)
            // Mermaid 10+ có thể không cần thiết, nhưng đề phòng trường hợp SVG bị bóp méo khi render trong hidden div
        });
    });
  });
</script>
