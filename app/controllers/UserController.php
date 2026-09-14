<?php
/**
 * ============================================================
 *  POSUNG HRIS – UserController
 * ============================================================
 *  Quản lý Tài khoản người dùng (CRUD + Reset password).
 * ============================================================
 */

class UserController extends Controller
{
    /**
     * Danh sách tài khoản
     */
    public function index(): void
    {
        Session::checkPermission(['Admin']);

        $userModel = $this->model('User');
        $users = $userModel->getAllUsers();

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Tài khoản']);
        $this->view('user/index', ['users' => $users]);
        $this->view('layouts/footer');
    }

    /**
     * Thêm tài khoản mới
     */
    public function store(): void
    {
        Session::checkPermission(['Admin']);

        if ($this->isPost()) {
            $userModel = $this->model('User');

            // Kiểm tra trùng username
            $existing = $userModel->getUserByUsername($this->postData('username'));
            if ($existing) {
                Session::setFlash('error', 'Tên đăng nhập đã tồn tại!');
                $this->redirect('user');
                return;
            }

            $data = [
                'username'  => $this->postData('username'),
                'password'  => $this->postData('password', '123456'),
                'full_name' => $this->postData('full_name'),
                'email'     => $this->postData('email'),
                'role'      => $this->postData('role', 'Employee'),
            ];

            try {
                $userModel->createUser($data);
                Session::setFlash('success', 'Tạo tài khoản thành công! Mật khẩu mặc định: ' . $data['password']);
            } catch (Exception $e) {
                Session::setFlash('error', 'Lỗi: ' . $e->getMessage());
            }

            $this->redirect('user');
        }
    }

    /**
     * Cập nhật tài khoản
     */
    public function update(int $id = 0): void
    {
        Session::checkPermission(['Admin']);

        if ($this->isPost() && $id > 0) {
            $userModel = $this->model('User');
            
            $data = [
                'full_name' => $this->postData('full_name'),
                'email'     => $this->postData('email'),
                'role'      => $this->postData('role'),
                'status'    => $this->postData('status', 'Active'),
            ];

            if ($userModel->updateUser($id, $data)) {
                Session::setFlash('success', 'Cập nhật tài khoản thành công!');
            } else {
                Session::setFlash('error', 'Không thể cập nhật tài khoản.');
            }

            $this->redirect('user');
        }
    }

    /**
     * Reset mật khẩu
     */
    public function resetPassword(int $id = 0): void
    {
        Session::checkPermission(['Admin']);

        if ($id > 0) {
            $userModel = $this->model('User');
            $newPassword = '123456';
            
            if ($userModel->changePassword($id, $newPassword)) {
                Session::setFlash('success', 'Đã reset mật khẩu về: ' . $newPassword);
            } else {
                Session::setFlash('error', 'Không thể reset mật khẩu.');
            }
        }

        $this->redirect('user');
    }
}
