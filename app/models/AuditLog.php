<?php
/**
 * ============================================================
 *  POSUNG HRIS – Model AuditLog (Nhật ký Hoạt động Hệ thống)
 * ============================================================
 */

class AuditLog extends BaseModel
{
    protected string $table = 'audit_logs';

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Lấy danh sách nhật ký kèm bộ lọc và phân trang
     */
    public function getLogs(array $filters = [], int $page = 1, int $limit = 25): array
    {
        $offset = ($page - 1) * $limit;
        $sqlWhere = " WHERE 1=1";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sqlWhere .= " AND a.user_id = :uid";
            $params['uid'] = (int)$filters['user_id'];
        }

        if (!empty($filters['module'])) {
            $sqlWhere .= " AND a.module = :module";
            $params['module'] = $filters['module'];
        }

        if (!empty($filters['action'])) {
            $sqlWhere .= " AND a.action = :action";
            $params['action'] = $filters['action'];
        }

        if (!empty($filters['date_from'])) {
            $sqlWhere .= " AND DATE(a.created_at) >= :d_from";
            $params['d_from'] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sqlWhere .= " AND DATE(a.created_at) <= :d_to";
            $params['d_to'] = $filters['date_to'];
        }

        if (!empty($filters['search'])) {
            $sqlWhere .= " AND (a.description LIKE :kw OR a.user_name LIKE :kw OR a.record_id LIKE :kw OR a.ip_address LIKE :kw)";
            $params['kw'] = '%' . $filters['search'] . '%';
        }

        // Đếm tổng số dòng
        $countSql = "SELECT COUNT(*) as total FROM audit_logs a" . $sqlWhere;
        $this->db->query($countSql, $params);
        $totalRow = $this->db->fetch();
        $total = (int)($totalRow['total'] ?? 0);

        // Lấy dữ liệu phân trang
        $sql = "SELECT a.*, u.username, u.full_name as current_user_fullname
                FROM audit_logs a
                LEFT JOIN users u ON a.user_id = u.id"
                . $sqlWhere .
                " ORDER BY a.created_at DESC, a.id DESC 
                LIMIT {$limit} OFFSET {$offset}";

        $this->db->query($sql, $params);
        $logs = $this->db->fetchAll();

        return [
            'data'       => $logs,
            'total'      => $total,
            'page'       => $page,
            'limit'      => $limit,
            'total_pages'=> ceil($total / $limit) ?: 1,
        ];
    }

    /**
     * Lấy chi tiết 1 bản ghi audit log
     */
    public function getLogById(int $id): ?object
    {
        $this->db->query(
            "SELECT a.*, u.username, u.full_name as current_user_fullname
             FROM audit_logs a
             LEFT JOIN users u ON a.user_id = u.id
             WHERE a.id = :id LIMIT 1",
            ['id' => $id]
        );
        $row = $this->db->fetch();
        if (!$row) return null;

        $row['old_decoded'] = !empty($row['old_values']) ? json_decode($row['old_values'], true) : null;
        $row['new_decoded'] = !empty($row['new_values']) ? json_decode($row['new_values'], true) : null;

        return (object)$row;
    }

    /**
     * Thống kê KPI hoạt động
     */
    public function getStats(): array
    {
        $this->db->query("SELECT 
            COUNT(*) as total_logs,
            SUM(CASE WHEN DATE(created_at) = CURRENT_DATE THEN 1 ELSE 0 END) as today_logs,
            SUM(CASE WHEN `action` IN ('create', 'update', 'delete') THEN 1 ELSE 0 END) as cud_actions,
            SUM(CASE WHEN `action` = 'login' THEN 1 ELSE 0 END) as login_actions,
            SUM(CASE WHEN `action` IN ('backup', 'restore') THEN 1 ELSE 0 END) as backup_actions
            FROM audit_logs");
        $stats = $this->db->fetch() ?: [];

        // Top 5 module hoạt động nhiều nhất
        $this->db->query("SELECT `module`, COUNT(*) as count 
                          FROM audit_logs 
                          GROUP BY `module` 
                          ORDER BY count DESC LIMIT 5");
        $topModules = $this->db->fetchAll();

        return [
            'total_logs'     => (int)($stats['total_logs'] ?? 0),
            'today_logs'     => (int)($stats['today_logs'] ?? 0),
            'cud_actions'    => (int)($stats['cud_actions'] ?? 0),
            'login_actions'  => (int)($stats['login_actions'] ?? 0),
            'backup_actions' => (int)($stats['backup_actions'] ?? 0),
            'top_modules'    => $topModules,
        ];
    }

    /**
     * Lấy các tùy chọn cho bộ lọc
     */
    public function getFilterOptions(): array
    {
        $this->db->query("SELECT DISTINCT user_id, user_name FROM audit_logs WHERE user_id IS NOT NULL ORDER BY user_name ASC");
        $users = $this->db->fetchAll();

        $this->db->query("SELECT DISTINCT `module` FROM audit_logs ORDER BY `module` ASC");
        $modules = array_column($this->db->fetchAll(), 'module');

        $actions = ['create', 'update', 'delete', 'login', 'logout', 'view', 'export', 'backup', 'restore'];

        return [
            'users'   => $users,
            'modules' => $modules,
            'actions' => $actions,
        ];
    }

    /**
     * Xuất danh sách nhật ký phục vụ CSV export
     */
    public function exportLogs(array $filters = []): array
    {
        $res = $this->getLogs($filters, 1, 5000);
        return $res['data'];
    }
}
