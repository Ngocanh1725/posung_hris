<?php
/**
 * ============================================================
 *  POSUNG HRIS – Search Controller (Tìm kiếm toàn cục)
 * ============================================================
 *  - Tìm kiếm đa nguồn tức thời (Real-time Global Search):
 *    + Nhân viên (Họ tên, Mã NV, SĐT, Email)
 *    + Phòng ban (Tên phòng, Mã phòng)
 *    + Dự án công trường (Tên dự án, Mã dự án)
 *    + Khóa đào tạo (Tên khóa học, Đơn vị đào tạo)
 *  - Hỗ trợ API JSON (AJAX debounce 300ms, phím tắt Ctrl+K hoặc /)
 *  - Trang kết quả tìm kiếm đầy đủ (Full Search Results Page)
 * ============================================================
 */

class SearchController extends Controller
{
    /** @var Database Đối tượng kết nối CSDL */
    protected Database $db;

    /**
     * Kiểm tra yêu cầu có phải là AJAX hay không
     */
    protected function isAjax(): bool
    {
        return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }

    public function __construct()
    {
        if (!Session::isLoggedIn()) {
            if ($this->isAjax()) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
                exit;
            }
            Session::setFlash('error', 'Vui lòng đăng nhập để tiếp tục.');
            $this->redirect('auth/login');
            exit;
        }

        $this->db = Database::getInstance();
    }

    /**
     * Trang kết quả tìm kiếm toàn cục hoặc trả JSON nếu là AJAX
     */
    public function index(): void
    {
        $query = trim($_GET['q'] ?? $_GET['query'] ?? '');

        // Nếu là yêu cầu AJAX hoặc có query param format=json, trả JSON
        if ($this->isAjax() || ($_GET['format'] ?? '') === 'json') {
            $this->search();
            return;
        }

        // Nếu là request trang web thông thường, thực hiện tìm kiếm và hiển thị View
        $results = [];
        $totalCount = 0;

        if (mb_strlen($query, 'UTF-8') >= 1) {
            $results = $this->executeSearch($query, 20);
            $totalCount = ($results['counts']['employees'] ?? 0)
                        + ($results['counts']['departments'] ?? 0)
                        + ($results['counts']['projects'] ?? 0)
                        + ($results['counts']['trainings'] ?? 0);
        }

        $this->view('layouts/header', [
            'pageTitle' => 'Kết quả tìm kiếm: ' . ($query ?: 'Tất cả'),
            'breadcrumbs' => [
                ['title' => 'Trang chủ', 'url' => BASE_URL],
                ['title' => 'Tìm kiếm toàn cục', 'url' => null]
            ]
        ]);
        $this->view('search/index', [
            'query'      => $query,
            'results'    => $results,
            'totalCount' => $totalCount
        ]);
        $this->view('layouts/footer');
    }

    /**
     * API Endpoint trả JSON kết quả tìm kiếm cho Global Search Dropdown
     */
    public function search(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $query = trim($_GET['q'] ?? $_GET['query'] ?? $_POST['q'] ?? '');

        if (mb_strlen($query, 'UTF-8') < 1) {
            echo json_encode([
                'status'  => 'success',
                'query'   => $query,
                'total'   => 0,
                'results' => [
                    'employees'   => [],
                    'departments' => [],
                    'projects'    => [],
                    'trainings'   => []
                ]
            ], JSON_UNESCAPED_UNICODE);
            exit;
        }

        $data = $this->executeSearch($query, 10);

        echo json_encode([
            'status'  => 'success',
            'query'   => $query,
            'total'   => $data['total'],
            'counts'  => $data['counts'],
            'results' => $data['results']
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    /**
     * Thực thi truy vấn tìm kiếm trên các bảng nguồn
     */
    protected function executeSearch(string $query, int $limitPerGroup = 10): array
    {
        $likeTerm = '%' . $query . '%';
        $exactTerm = $query . '%';

        // 1. Tìm kiếm Nhân viên (Employees)
        $this->db->query("
            SELECT e.id, e.emp_code, e.full_name, e.phone, e.email, e.status, e.avatar_path,
                   d.dept_name, pos.pos_title
            FROM employees e
            LEFT JOIN departments d ON e.department_id = d.id
            LEFT JOIN positions pos ON e.position_id = pos.id
            WHERE (e.full_name COLLATE utf8mb4_unicode_ci LIKE :q1
               OR e.emp_code COLLATE utf8mb4_unicode_ci LIKE :q2
               OR e.phone LIKE :q3
               OR e.email LIKE :q4)
            ORDER BY 
                CASE 
                    WHEN e.emp_code = :exact1 THEN 1
                    WHEN e.full_name COLLATE utf8mb4_unicode_ci LIKE :exact2 THEN 2
                    ELSE 3
                END,
                e.id DESC
            LIMIT {$limitPerGroup}
        ", [
            'q1'     => $likeTerm,
            'q2'     => $likeTerm,
            'q3'     => $likeTerm,
            'q4'     => $likeTerm,
            'exact1' => $query,
            'exact2' => $exactTerm
        ]);
        $employees = $this->db->fetchAll();

        $formattedEmployees = [];
        foreach ($employees as $emp) {
            $formattedEmployees[] = [
                'id'          => (int)$emp['id'],
                'type'        => 'employee',
                'title'       => $emp['full_name'],
                'subtitle'    => ($emp['emp_code'] ?: 'POSUNG') . ' • ' . ($emp['pos_title'] ?? $emp['dept_name'] ?? 'Nhân viên'),
                'meta'        => $emp['dept_name'] ?? '',
                'badge'       => $emp['status'] ?? 'Active',
                'badge_class' => match(strtolower($emp['status'] ?? '')) {
                    'active', 'chính thức' => 'bg-success-subtle text-success',
                    'probation', 'thử việc' => 'bg-warning-subtle text-warning-emphasis',
                    'resigned', 'nghỉ việc' => 'bg-danger-subtle text-danger',
                    default => 'bg-primary-subtle text-primary'
                },
                'icon'        => 'fa-solid fa-user',
                'avatar'      => !empty($emp['avatar_path']) ? BASE_URL . '/' . ltrim($emp['avatar_path'], '/') : null,
                'url'         => BASE_URL . '/employee/detail/' . $emp['id']
            ];
        }

        // 2. Tìm kiếm Phòng ban (Departments)
        $this->db->query("
            SELECT d.id, d.dept_code, d.dept_name, d.office_location, d.status,
                   (SELECT COUNT(*) FROM employees WHERE department_id = d.id) as employee_count
            FROM departments d
            WHERE (d.dept_name COLLATE utf8mb4_unicode_ci LIKE :q1
               OR d.dept_code COLLATE utf8mb4_unicode_ci LIKE :q2)
            ORDER BY d.id ASC
            LIMIT {$limitPerGroup}
        ", [
            'q1' => $likeTerm,
            'q2' => $likeTerm
        ]);
        $departments = $this->db->fetchAll();

        $formattedDepartments = [];
        foreach ($departments as $dept) {
            $formattedDepartments[] = [
                'id'          => (int)$dept['id'],
                'type'        => 'department',
                'title'       => $dept['dept_name'],
                'subtitle'    => 'Mã: ' . ($dept['dept_code'] ?: 'DEP') . ($dept['office_location'] ? ' • ' . $dept['office_location'] : ''),
                'meta'        => ($dept['employee_count'] ?? 0) . ' nhân sự',
                'badge'       => 'Phòng ban',
                'badge_class' => 'bg-info-subtle text-info-emphasis',
                'icon'        => 'fa-solid fa-sitemap',
                'url'         => BASE_URL . '/organization/detail/' . $dept['id']
            ];
        }

        // 3. Tìm kiếm Dự án công trường (Projects)
        $this->db->query("
            SELECT pr.id, pr.project_code, pr.project_name, pr.client_name, pr.location, pr.status, pr.progress_percent
            FROM projects pr
            WHERE (pr.project_name COLLATE utf8mb4_unicode_ci LIKE :q1
               OR pr.project_code COLLATE utf8mb4_unicode_ci LIKE :q2
               OR pr.client_name COLLATE utf8mb4_unicode_ci LIKE :q3)
            ORDER BY pr.id DESC
            LIMIT {$limitPerGroup}
        ", [
            'q1' => $likeTerm,
            'q2' => $likeTerm,
            'q3' => $likeTerm
        ]);
        $projects = $this->db->fetchAll();

        $formattedProjects = [];
        foreach ($projects as $prj) {
            $formattedProjects[] = [
                'id'          => (int)$prj['id'],
                'type'        => 'project',
                'title'       => $prj['project_name'],
                'subtitle'    => 'Mã: ' . ($prj['project_code'] ?: 'PRJ') . ($prj['location'] ? ' • ' . $prj['location'] : ''),
                'meta'        => 'Tiến độ: ' . ($prj['progress_percent'] ?? 0) . '%',
                'badge'       => $prj['status'] ?? 'Dự án',
                'badge_class' => 'bg-primary-subtle text-primary',
                'icon'        => 'fa-solid fa-building-shield',
                'url'         => BASE_URL . '/project/detail/' . $prj['id']
            ];
        }

        // 4. Tìm kiếm Khóa đào tạo (Trainings)
        $this->db->query("
            SELECT t.id, t.course_name, t.provider, t.start_date, t.location, t.status,
                   (SELECT COUNT(*) FROM training_participants WHERE training_id = t.id) as participant_count
            FROM trainings t
            WHERE t.deleted_at IS NULL
              AND (t.course_name COLLATE utf8mb4_unicode_ci LIKE :q1
               OR t.provider COLLATE utf8mb4_unicode_ci LIKE :q2)
            ORDER BY t.id DESC
            LIMIT {$limitPerGroup}
        ", [
            'q1' => $likeTerm,
            'q2' => $likeTerm
        ]);
        $trainings = $this->db->fetchAll();

        $formattedTrainings = [];
        foreach ($trainings as $tr) {
            $startDate = !empty($tr['start_date']) ? date('d/m/Y', strtotime($tr['start_date'])) : '';
            $formattedTrainings[] = [
                'id'          => (int)$tr['id'],
                'type'        => 'training',
                'title'       => $tr['course_name'],
                'subtitle'    => ($tr['provider'] ? $tr['provider'] . ' • ' : '') . ($startDate ? 'Bắt đầu: ' . $startDate : 'Đang tổ chức'),
                'meta'        => ($tr['participant_count'] ?? 0) . ' học viên',
                'badge'       => 'Đào tạo',
                'badge_class' => 'bg-warning-subtle text-warning-emphasis',
                'icon'        => 'fa-solid fa-graduation-cap',
                'url'         => BASE_URL . '/training/show/' . $tr['id']
            ];
        }

        $totalCount = count($formattedEmployees) + count($formattedDepartments) + count($formattedProjects) + count($formattedTrainings);

        return [
            'total'   => $totalCount,
            'counts'  => [
                'employees'   => count($formattedEmployees),
                'departments' => count($formattedDepartments),
                'projects'    => count($formattedProjects),
                'trainings'   => count($formattedTrainings)
            ],
            'results' => [
                'employees'   => $formattedEmployees,
                'departments' => $formattedDepartments,
                'projects'    => $formattedProjects,
                'trainings'   => $formattedTrainings
            ]
        ];
    }
}
