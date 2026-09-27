<?php
class SettingController extends Controller
{
    public function index(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo'])) {
            $uploadDir = APP_ROOT . '/../public/uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $targetFile = $uploadDir . 'logo.png';
            $imageFileType = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            
            if (in_array($imageFileType, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $targetFile)) {
                    Session::setFlash('success', 'Đã cập nhật logo thành công!');
                } else {
                    Session::setFlash('error', 'Có lỗi khi tải lên tệp.');
                }
            } else {
                Session::setFlash('error', 'Định dạng ảnh không hợp lệ.');
            }
            $this->redirect('setting');
            return;
        }

        $this->view('layouts/header', ['pageTitle' => 'Cài đặt hệ thống']);
        $this->view('setting/index');
        $this->view('layouts/footer');
    }
}
