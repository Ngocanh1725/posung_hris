<!-- app/views/timesheet/mobile_checkin.php -->
<div class="container-fluid pt-3 pb-5" style="max-width: 500px; margin: 0 auto; background-color: #f8f9fc; min-height: 100vh;">
    <div class="card shadow-sm border-0 rounded-4 mb-4">
        <div class="card-body text-center p-4">
            <div class="mb-3">
                <i class="fas fa-map-marker-alt text-primary" style="font-size: 3rem;"></i>
            </div>
            <h4 class="font-weight-bold text-gray-800 mb-1">Chấm Công GPS</h4>
            <p class="text-muted small">Cập nhật vị trí để điểm danh tại công trường</p>

            <form id="checkinForm" class="mt-4 text-start">
                <input type="hidden" id="csrf_token" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?? '' ?>">
                <input type="hidden" id="employee_id" name="employee_id" value="<?= h($employeeId) ?>">
                <input type="hidden" id="latitude" name="latitude" value="">
                <input type="hidden" id="longitude" name="longitude" value="">

                <div class="mb-3">
                    <label class="form-label font-weight-bold text-gray-700">Dự án / Công trường <span class="text-danger">*</span></label>
                    <select class="form-select form-select-lg rounded-3 shadow-sm" id="project_id" name="project_id" required>
                        <option value="">-- Chọn nơi làm việc --</option>
                        <?php if (!empty($projects)): ?>
                            <?php foreach ($projects as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= h($p['project_code'] . ' - ' . $p['project_name']) ?></option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label font-weight-bold text-gray-700">Loại Check-in <span class="text-danger">*</span></label>
                    <div class="d-flex gap-2">
                        <input type="radio" class="btn-check" name="in_out" id="inOutIN" value="IN" checked>
                        <label class="btn btn-outline-success flex-fill rounded-3 py-2 fw-bold" for="inOutIN">
                            <i class="fas fa-sign-in-alt mb-1"></i><br>VÀO CA
                        </label>

                        <input type="radio" class="btn-check" name="in_out" id="inOutOUT" value="OUT">
                        <label class="btn btn-outline-danger flex-fill rounded-3 py-2 fw-bold" for="inOutOUT">
                            <i class="fas fa-sign-out-alt mb-1"></i><br>RA CA
                        </label>
                    </div>
                </div>

                <div class="alert alert-info rounded-3 p-3 shadow-sm text-center" id="locationStatus">
                    <div class="spinner-border spinner-border-sm text-info me-2" role="status"></div>
                    <span class="small fw-bold">Đang lấy tọa độ GPS...</span>
                </div>

                <button type="submit" id="btnSubmitCheckin" class="btn btn-primary btn-lg w-100 rounded-pill shadow fw-bold py-3 mt-2" disabled>
                    <i class="fas fa-fingerprint me-2"></i> CHẤM CÔNG NGAY
                </button>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const statusDiv = document.getElementById('locationStatus');
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');
    const btnSubmit = document.getElementById('btnSubmitCheckin');

    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition(function(position) {
            latInput.value = position.coords.latitude;
            lngInput.value = position.coords.longitude;
            
            statusDiv.classList.remove('alert-info');
            statusDiv.classList.add('alert-success');
            statusDiv.innerHTML = `<i class="fas fa-check-circle me-1"></i> Đã lấy được tọa độ GPS<br><small class="text-muted">Độ chính xác: ~${Math.round(position.coords.accuracy)}m</small>`;
            
            btnSubmit.disabled = false;
        }, function(error) {
            statusDiv.classList.remove('alert-info');
            statusDiv.classList.add('alert-danger');
            let msg = 'Lỗi không xác định';
            switch(error.code) {
                case error.PERMISSION_DENIED: msg = 'Bạn đã từ chối cấp quyền Vị trí.'; break;
                case error.POSITION_UNAVAILABLE: msg = 'Không thể lấy thông tin GPS.'; break;
                case error.TIMEOUT: msg = 'Hết thời gian chờ lấy GPS.'; break;
            }
            statusDiv.innerHTML = `<i class="fas fa-exclamation-triangle me-1"></i> ${msg}<br><small>Vui lòng bật GPS và làm mới trang.</small>`;
        }, {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        });
    } else {
        statusDiv.innerHTML = `<i class="fas fa-times-circle me-1"></i> Trình duyệt/Thiết bị của bạn không hỗ trợ GPS.`;
    }

    document.getElementById('checkinForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const projectId = document.getElementById('project_id').value;
        if (!projectId) {
            alert('Vui lòng chọn dự án!');
            return;
        }

        btnSubmit.disabled = true;
        btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Đang xử lý...';

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        fetch('<?= BASE_URL ?>/timesheet/checkinGps', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire({
                    icon: 'success',
                    title: 'Thành công!',
                    text: data.message,
                    confirmButtonColor: '#4e73df'
                }).then(() => {
                    window.location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Thất bại',
                    text: data.message,
                    confirmButtonColor: '#e74a3b'
                });
                btnSubmit.disabled = false;
                btnSubmit.innerHTML = '<i class="fas fa-fingerprint me-2"></i> CHẤM CÔNG NGAY';
            }
        })
        .catch(error => {
            alert('Lỗi kết nối mạng, vui lòng thử lại.');
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<i class="fas fa-fingerprint me-2"></i> CHẤM CÔNG NGAY';
        });
    });
});
</script>
