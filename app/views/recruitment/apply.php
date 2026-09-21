<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kiosk Ứng Tuyển Nhanh - PO SUNG MEC</title>
    <style>
        :root {
            --primary: #2563eb;
            --primary-hover: #1d4ed8;
            --bg-color: #f1f5f9;
            --surface: #ffffff;
            --text-heading: #1e293b;
            --text-body: #475569;
            --border: #e2e8f0;
            --danger: #ef4444;
            --success: #10b981;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: var(--bg-color);
            color: var(--text-body);
            line-height: 1.5;
            padding: 20px 15px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background: var(--surface);
            border-radius: 12px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: var(--primary);
            color: white;
            text-align: center;
            padding: 20px;
        }
        .header h1 {
            font-size: 20px;
            margin-bottom: 5px;
        }
        .header p {
            font-size: 14px;
            opacity: 0.9;
        }
        .form-content {
            padding: 24px;
        }
        .form-group {
            margin-bottom: 16px;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-size: 14px;
            font-weight: 500;
            color: var(--text-heading);
        }
        label .required { color: var(--danger); }
        input[type="text"],
        input[type="date"],
        input[type="tel"],
        select {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 15px;
            outline: none;
            transition: border-color 0.2s;
        }
        input:focus, select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        }
        .file-upload {
            border: 2px dashed var(--border);
            border-radius: 6px;
            padding: 20px;
            text-align: center;
            background: #f8fafc;
            position: relative;
        }
        .file-upload p {
            font-size: 13px;
            color: #64748b;
            margin-bottom: 10px;
        }
        .file-upload input[type="file"] {
            display: block;
            margin: 0 auto;
            font-size: 13px;
        }
        .btn-submit {
            display: block;
            width: 100%;
            padding: 12px;
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            transition: background 0.2s;
        }
        .btn-submit:hover {
            background: var(--primary-hover);
        }
        .alert {
            padding: 12px;
            border-radius: 6px;
            margin-bottom: 16px;
            font-size: 14px;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #34d399;
        }
        .alert-error {
            background: #fee2e2;
            color: #b91c1c;
            border: 1px solid #f87171;
        }
        .logo-placeholder {
            width: 60px;
            height: 60px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            margin: 0 auto 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            font-size: 24px;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        <div class="logo-placeholder">PS</div>
        <h1>PO SUNG MEC - Kiosk Ứng Tuyển</h1>
        <p>Vui lòng điền thông tin để đăng ký nhận việc</p>
    </div>

    <div class="form-content">
        <?php if ($flash = Session::getFlash('success')): ?>
            <div class="alert alert-success"><?= h($flash) ?></div>
        <?php endif; ?>
        <?php if ($flash = Session::getFlash('error')): ?>
            <div class="alert alert-error"><?= h($flash) ?></div>
        <?php endif; ?>

        <form action="<?= BASE_URL ?>/recruitment/submitApply" method="POST" enctype="multipart/form-data">
            
            <div class="form-group">
                <label>Họ và Tên <span class="required">*</span></label>
                <input type="text" name="full_name" required placeholder="Nhập đầy đủ họ tên">
            </div>

            <div class="form-group">
                <label>Số CCCD <span class="required">*</span></label>
                <input type="text" name="id_card" required placeholder="Nhập 12 số CCCD">
            </div>

            <div class="form-group">
                <label>Ngày sinh <span class="required">*</span></label>
                <input type="date" name="dob" required>
            </div>

            <div class="form-group">
                <label>Số điện thoại liên hệ <span class="required">*</span></label>
                <input type="tel" name="phone" required placeholder="Nhập số điện thoại">
            </div>

            <div class="form-group">
                <label>Quê quán (Tỉnh/Thành phố)</label>
                <input type="text" name="hometown" placeholder="VD: Bắc Ninh, Thanh Hóa...">
            </div>

            <div class="form-group">
                <label>Vị trí ứng tuyển <span class="required">*</span></label>
                <select name="position" required>
                    <option value="">-- Chọn vị trí --</option>
                    <option value="Thợ hàn 6G">Thợ hàn 6G / 3G</option>
                    <option value="Thợ lắp ống">Thợ lắp ống</option>
                    <option value="Thợ phụ">Thợ phụ (Lao động phổ thông)</option>
                    <option value="Kỹ thuật viên điện">Kỹ thuật viên điện</option>
                    <option value="Kỹ thuật viên cơ khí">Kỹ thuật viên cơ khí</option>
                    <option value="Cán bộ An toàn (HSE)">Cán bộ An toàn (HSE)</option>
                </select>
            </div>

            <h3 style="font-size: 16px; margin: 24px 0 12px; color: var(--text-heading); border-bottom: 1px solid var(--border); padding-bottom: 8px;">Tải lên Ảnh / Giấy tờ</h3>
            <p style="font-size: 12px; color: var(--text-body); margin-bottom: 12px;">Chụp ảnh rõ nét từ điện thoại của bạn.</p>

            <div class="form-group">
                <label>Ảnh CCCD (Mặt trước) <span class="required">*</span></label>
                <div class="file-upload">
                    <p>Nhấn để chụp hoặc chọn ảnh</p>
                    <input type="file" name="front_id_card" accept="image/*" capture="environment" required>
                </div>
            </div>

            <div class="form-group">
                <label>Ảnh CCCD (Mặt sau)</label>
                <div class="file-upload">
                    <input type="file" name="back_id_card" accept="image/*" capture="environment">
                </div>
            </div>

            <div class="form-group">
                <label>Ảnh Chứng chỉ (An toàn / Thợ hàn)</label>
                <div class="file-upload">
                    <p>Nếu có chứng chỉ 6G, thẻ an toàn nhóm 3...</p>
                    <input type="file" name="cert_file" accept="image/*,.pdf" capture="environment">
                </div>
            </div>

            <button type="submit" class="btn-submit">Gửi Hồ Sơ Ứng Tuyển</button>
        </form>
    </div>
</div>

<script>
// Logic đơn giản để validate nếu cần
</script>
</body>
</html>
