<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<style>
.ql-container {
    height: 400px;
    font-size: 1rem;
    font-family: inherit;
    background: #fff;
    border-bottom-left-radius: 8px;
    border-bottom-right-radius: 8px;
}
.ql-toolbar {
    background: #f8fafc;
    border-top-left-radius: 8px;
    border-top-right-radius: 8px;
}
.form-group label {
    font-weight: 600;
    color: #334155;
    margin-bottom: 8px;
}
.error-msg {
    color: #ef4444;
    font-size: 0.85rem;
    margin-top: 5px;
    display: none;
}
input.error, select.error {
    border-color: #ef4444;
    box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.1);
}
</style>

<div class="breadcrumb-bar">
    <a href="<?= BASE_URL ?>/dashboard"><i class="fas fa-home"></i> Trang chủ</a>
    <i class="fas fa-chevron-right"></i>
    <a href="<?= BASE_URL ?>/articles">Quản lý Bảng tin</a>
    <i class="fas fa-chevron-right"></i>
    <span>Viết bài mới</span>
</div>

<div class="panel glass-panel">
    <h3 style="margin-bottom: 24px; color: #0f172a;"><i class="fas fa-pen-nib text-gradient"></i> Soạn thảo Bài viết mới</h3>

    <form method="POST" action="<?= BASE_URL ?>/articles/create" id="articleForm" onsubmit="return validateForm()">
        <?= Session::csrfField() ?>
        
        <div class="row">
            <div class="col-md-8">
                <div class="form-group mb-4">
                    <label>Tiêu đề bài viết <span style="color:red;">*</span></label>
                    <input type="text" name="title" id="title" class="form-control form-control-lg" placeholder="Nhập tiêu đề hấp dẫn...">
                    <div class="error-msg" id="err-title">Vui lòng nhập tiêu đề bài viết.</div>
                </div>

                <div class="form-group mb-4">
                    <label>Nội dung bài viết <span style="color:red;">*</span></label>
                    <!-- Trình soạn thảo QuillJS -->
                    <div id="editor-container"></div>
                    <input type="hidden" name="content" id="hiddenContent">
                    <div class="error-msg" id="err-content">Nội dung bài viết không được để trống.</div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel" style="background: #f8fafc; border: 1px solid #e2e8f0; box-shadow: none;">
                    <h4 style="font-size: 1rem; margin-bottom: 15px;"><i class="fas fa-cogs"></i> Phân loại & Xuất bản</h4>
                    
                    <div class="form-group mb-3">
                        <label>Chuyên mục</label>
                        <select name="category" class="form-control">
                            <option value="news">Tin tức nội bộ</option>
                            <option value="hse_rule">Quy định An toàn (HSE)</option>
                            <option value="notice">Thông báo</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label>Trạng thái</label>
                        <select name="status" class="form-control">
                            <option value="draft">Lưu Nháp (Draft)</option>
                            <option value="published" selected>Xuất bản ngay (Published)</option>
                        </select>
                    </div>

                    <div class="form-group mb-4">
                        <label>File đính kèm (Tùy chọn)</label>
                        <input type="file" name="attachment" class="form-control">
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-primary w-100 btn-lg"><i class="fas fa-paper-plane"></i> LƯU BÀI VIẾT</button>
                    <a href="<?= BASE_URL ?>/articles" class="btn btn-outline-secondary w-100 mt-2">Hủy bỏ</a>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Nạp thư viện QuillJS -->
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
<script>
    var quill = new Quill('#editor-container', {
        theme: 'snow',
        placeholder: 'Bắt đầu viết nội dung tại đây...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, false] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                [{ 'color': [] }, { 'background': [] }],
                ['link', 'image'],
                ['clean']
            ]
        }
    });

    function validateForm() {
        let isValid = true;
        const title = document.getElementById('title');
        const content = document.querySelector('#editor-container .ql-editor').innerHTML;
        const hiddenContent = document.getElementById('hiddenContent');
        
        // Reset errors
        title.classList.remove('error');
        document.getElementById('err-title').style.display = 'none';
        document.querySelector('.ql-container').style.borderColor = '#ccc';
        document.getElementById('err-content').style.display = 'none';

        if (title.value.trim() === '') {
            title.classList.add('error');
            document.getElementById('err-title').style.display = 'block';
            isValid = false;
        }

        if (quill.getText().trim() === '') {
            document.querySelector('.ql-container').style.borderColor = '#ef4444';
            document.getElementById('err-content').style.display = 'block';
            isValid = false;
        } else {
            // Lấy HTML lưu vào input hidden
            hiddenContent.value = content;
        }

        return isValid;
    }

    // Xóa lỗi khi gõ
    document.getElementById('title').addEventListener('input', function() {
        this.classList.remove('error');
        document.getElementById('err-title').style.display = 'none';
    });
    quill.on('text-change', function() {
        document.querySelector('.ql-container').style.borderColor = '#ccc';
        document.getElementById('err-content').style.display = 'none';
    });
</script>
