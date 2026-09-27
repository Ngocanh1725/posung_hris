<?php
/**
 * ============================================================
 *  POSUNG HRIS – ArticlesController (Admin Nội dung)
 * ============================================================
 */

class ArticlesController extends Controller
{
    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            $this->redirect('auth/login');
        }
        // Yêu cầu quyền quản lý nội dung
        $this->requirePermission('content_admin', 'manage');
    }

    public function index(): void
    {
        $db = Database::getInstance();
        $db->query(
            "SELECT a.*, u.full_name as author_name 
             FROM internal_articles a 
             LEFT JOIN users u ON a.author_id = u.id 
             ORDER BY a.created_at DESC"
        );
        $articles = $db->fetchAll();

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Nội dung & Bảng tin']);
        $this->view('articles/index', ['articles' => $articles]);
        $this->view('layouts/footer');
    }

    public function create(): void
    {
        if ($this->isPost()) {
            $title = $this->postData('title');
            $category = $this->postData('category');
            $status = $this->postData('status');
            $content = $_POST['content'] ?? '';
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            $slug = $slug . '-' . time(); // Unique slug

            // Xử lý Validation cơ bản
            if (empty($title) || empty($content)) {
                Session::setFlash('error', 'Tiêu đề và Nội dung không được để trống.');
                $this->redirect('articles/create');
            }

            $db = Database::getInstance();
            $db->query(
                "INSERT INTO internal_articles (title, slug, category, status, content, author_id, published_at) 
                 VALUES (:title, :slug, :cat, :status, :content, :author, :published_at)",
                [
                    'title' => $title,
                    'slug' => $slug,
                    'cat' => $category,
                    'status' => $status,
                    'content' => $content,
                    'author' => Session::userId(),
                    'published_at' => ($status === 'published') ? date('Y-m-d H:i:s') : null
                ]
            );

            Session::setFlash('success', 'Đã lưu bài viết thành công.');
            $this->redirect('articles');
        }

        $this->view('layouts/header', ['pageTitle' => 'Thêm Bài viết mới']);
        $this->view('articles/create');
        $this->view('layouts/footer');
    }

    public function delete(int $id): void
    {
        $db = Database::getInstance();
        $db->query("DELETE FROM internal_articles WHERE id = :id", ['id' => $id]);
        Session::setFlash('success', 'Xóa bài viết thành công.');
        $this->redirect('articles');
    }
}
