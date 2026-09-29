<?php
/**
 * ============================================================
 *  POSUNG HRIS – Onboarding Model
 * ============================================================
 *  Quản lý Quy trình & Checklist Hội nhập nhân sự mới (Onboarding)
 * ============================================================
 */

class Onboarding extends BaseModel
{
    protected string $table = 'employee_onboardings';

    /**
     * Lấy toàn bộ mẫu Onboarding
     */
    public function getAllTemplates(bool $activeOnly = false): array
    {
        $where = $activeOnly ? "WHERE t.is_active = 1" : "";
        $this->db->query(
            "SELECT t.*, d.dept_name, d.dept_code,
                    COUNT(tasks.id) AS total_tasks,
                    SUM(CASE WHEN tasks.is_required = 1 THEN 1 ELSE 0 END) AS required_tasks
             FROM onboarding_templates t
             LEFT JOIN departments d ON t.department_id = d.id
             LEFT JOIN onboarding_tasks tasks ON t.id = tasks.template_id
             {$where}
             GROUP BY t.id
             ORDER BY t.is_active DESC, t.id ASC"
        );
        return $this->db->fetchAll();
    }

    /**
     * Lấy chi tiết mẫu kèm danh sách tasks
     */
    public function getTemplateById(int $id): ?array
    {
        $this->db->query(
            "SELECT t.*, d.dept_name, d.dept_code
             FROM onboarding_templates t
             LEFT JOIN departments d ON t.department_id = d.id
             WHERE t.id = :id
             LIMIT 1",
            ['id' => $id]
        );
        $template = $this->db->fetch();
        if (!$template) return null;

        $this->db->query(
            "SELECT * FROM onboarding_tasks 
             WHERE template_id = :tid 
             ORDER BY sort_order ASC, id ASC",
            ['tid' => $id]
        );
        $template['tasks'] = $this->db->fetchAll();

        return $template;
    }

    /**
     * Tạo mẫu quy trình mới kèm tasks
     */
    public function createTemplate(array $data, array $tasks): int
    {
        $this->db->beginTransaction();
        try {
            $this->db->query(
                "INSERT INTO onboarding_templates (name, description, department_id, is_active)
                 VALUES (:name, :description, :department_id, :is_active)",
                [
                    'name'          => $data['name'],
                    'description'   => $data['description'] ?? null,
                    'department_id' => !empty($data['department_id']) ? (int)$data['department_id'] : null,
                    'is_active'     => isset($data['is_active']) ? (int)$data['is_active'] : 1,
                ]
            );
            $templateId = (int)$this->db->lastInsertId();

            $sort = 1;
            foreach ($tasks as $task) {
                if (empty(trim($task['title'] ?? ''))) continue;
                $this->db->query(
                    "INSERT INTO onboarding_tasks (template_id, title, description, responsible_department, due_days_after_join, sort_order, is_required)
                     VALUES (:template_id, :title, :description, :responsible_department, :due_days_after_join, :sort_order, :is_required)",
                    [
                        'template_id'            => $templateId,
                        'title'                  => trim($task['title']),
                        'description'            => trim($task['description'] ?? ''),
                        'responsible_department' => $task['responsible_department'] ?? 'HR',
                        'due_days_after_join'    => max(0, (int)($task['due_days_after_join'] ?? 1)),
                        'sort_order'             => $sort++,
                        'is_required'            => isset($task['is_required']) ? (int)$task['is_required'] : 1,
                    ]
                );
            }

            $this->db->commit();
            return $templateId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("createTemplate Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Cập nhật mẫu quy trình & đồng bộ lại tasks
     */
    public function updateTemplate(int $id, array $data, array $tasks): bool
    {
        $this->db->beginTransaction();
        try {
            $this->db->query(
                "UPDATE onboarding_templates 
                 SET name = :name, description = :description, department_id = :department_id, is_active = :is_active
                 WHERE id = :id",
                [
                    'id'            => $id,
                    'name'          => $data['name'],
                    'description'   => $data['description'] ?? null,
                    'department_id' => !empty($data['department_id']) ? (int)$data['department_id'] : null,
                    'is_active'     => isset($data['is_active']) ? (int)$data['is_active'] : 1,
                ]
            );

            // Xóa tasks cũ & chèn lại danh sách tasks mới
            $this->db->query("DELETE FROM onboarding_tasks WHERE template_id = :tid", ['tid' => $id]);

            $sort = 1;
            foreach ($tasks as $task) {
                if (empty(trim($task['title'] ?? ''))) continue;
                $this->db->query(
                    "INSERT INTO onboarding_tasks (template_id, title, description, responsible_department, due_days_after_join, sort_order, is_required)
                     VALUES (:template_id, :title, :description, :responsible_department, :due_days_after_join, :sort_order, :is_required)",
                    [
                        'template_id'            => $id,
                        'title'                  => trim($task['title']),
                        'description'            => trim($task['description'] ?? ''),
                        'responsible_department' => $task['responsible_department'] ?? 'HR',
                        'due_days_after_join'    => max(0, (int)($task['due_days_after_join'] ?? 1)),
                        'sort_order'             => $sort++,
                        'is_required'            => isset($task['is_required']) ? (int)$task['is_required'] : 1,
                    ]
                );
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("updateTemplate Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Xóa mẫu onboarding (nếu chưa có nhân viên áp dụng)
     */
    public function deleteTemplate(int $id): bool
    {
        $this->db->query("SELECT COUNT(*) AS cnt FROM employee_onboardings WHERE template_id = :id", ['id' => $id]);
        if ((int)$this->db->fetch()['cnt'] > 0) {
            // Đã có nhân sự áp dụng -> chỉ vô hiệu hóa
            $this->db->query("UPDATE onboarding_templates SET is_active = 0 WHERE id = :id", ['id' => $id]);
            return true;
        }
        $this->db->query("DELETE FROM onboarding_templates WHERE id = :id", ['id' => $id]);
        return true;
    }

    /**
     * Bắt đầu quy trình Onboarding cho nhân viên mới
     */
    public function startOnboarding(int $employeeId, int $templateId, ?string $startDate = null, ?string $notes = null, ?int $createdBy = null): int
    {
        $startDate = $startDate ?: date('Y-m-d');

        // Kiểm tra xem đã có onboarding cho nhân viên này chưa
        $this->db->query(
            "SELECT id FROM employee_onboardings WHERE employee_id = :emp_id AND status IN ('InProgress', 'Overdue') LIMIT 1",
            ['emp_id' => $employeeId]
        );
        $existing = $this->db->fetch();
        if ($existing) {
            return (int)$existing['id'];
        }

        $this->db->beginTransaction();
        try {
            $this->db->query(
                "INSERT INTO employee_onboardings (employee_id, template_id, start_date, status, notes, created_by)
                 VALUES (:emp_id, :tpl_id, :start_date, 'InProgress', :notes, :created_by)",
                [
                    'emp_id'     => $employeeId,
                    'tpl_id'     => $templateId,
                    'start_date' => $startDate,
                    'notes'      => $notes,
                    'created_by' => $createdBy,
                ]
            );
            $onboardingId = (int)$this->db->lastInsertId();

            // Lấy danh sách tasks từ template
            $this->db->query(
                "SELECT * FROM onboarding_tasks WHERE template_id = :tid ORDER BY sort_order ASC, id ASC",
                ['tid' => $templateId]
            );
            $tasks = $this->db->fetchAll();

            foreach ($tasks as $t) {
                $days = (int)$t['due_days_after_join'];
                $dueDate = date('Y-m-d', strtotime($startDate . " +{$days} days"));

                $this->db->query(
                    "INSERT INTO employee_onboarding_items (onboarding_id, task_id, status, due_date)
                     VALUES (:onb_id, :task_id, 'Pending', :due_date)",
                    [
                        'onb_id'   => $onboardingId,
                        'task_id'  => $t['id'],
                        'due_date' => $dueDate,
                    ]
                );
            }

            $this->db->commit();
            return $onboardingId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("startOnboarding Error: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lấy chi tiết phiên Onboarding theo ID
     */
    public function getOnboardingById(int $id): ?array
    {
        $this->db->query(
            "SELECT o.*, 
                    e.emp_code, e.full_name, e.gender, e.phone, e.email, e.avatar_path, e.join_date,
                    d.dept_name, d.dept_code,
                    p.pos_title,
                    tpl.name AS template_name
             FROM employee_onboardings o
             JOIN employees e ON o.employee_id = e.id
             LEFT JOIN departments d ON e.department_id = d.id
             LEFT JOIN positions p ON e.position_id = p.id
             JOIN onboarding_templates tpl ON o.template_id = tpl.id
             WHERE o.id = :id
             LIMIT 1",
            ['id' => $id]
        );
        $onboarding = $this->db->fetch();
        if (!$onboarding) return null;

        // Lấy checklist items
        $this->db->query(
            "SELECT i.*, 
                    t.title, t.description, t.responsible_department, t.due_days_after_join, t.is_required, t.sort_order,
                    u.username AS assigned_username
             FROM employee_onboarding_items i
             JOIN onboarding_tasks t ON i.task_id = t.id
             LEFT JOIN users u ON i.assigned_to = u.id
             WHERE i.onboarding_id = :onb_id
             ORDER BY t.responsible_department ASC, t.sort_order ASC",
            ['onb_id' => $id]
        );
        $items = $this->db->fetchAll();

        // Tính % tiến độ
        $totalItems = count($items);
        $doneItems = 0;
        $overdueItems = 0;
        $byDept = [];

        $today = date('Y-m-d');
        foreach ($items as &$item) {
            $dept = $item['responsible_department'] ?: 'HR';
            if (!isset($byDept[$dept])) {
                $byDept[$dept] = [];
            }
            if ($item['status'] === 'Done' || $item['status'] === 'Skipped') {
                $doneItems++;
            } elseif ($item['due_date'] && $item['due_date'] < $today) {
                $overdueItems++;
                $item['is_overdue'] = true;
            }
            $byDept[$dept][] = $item;
        }

        $progressPct = $totalItems > 0 ? round(($doneItems / $totalItems) * 100) : 0;

        $onboarding['items']         = $items;
        $onboarding['items_by_dept'] = $byDept;
        $onboarding['total_items']   = $totalItems;
        $onboarding['done_items']    = $doneItems;
        $onboarding['overdue_items'] = $overdueItems;
        $onboarding['progress_pct']  = $progressPct;

        return $onboarding;
    }

    /**
     * Lấy phiên Onboarding mới nhất của một nhân viên
     */
    public function getOnboardingByEmployee(int $employeeId): ?array
    {
        $this->db->query(
            "SELECT id FROM employee_onboardings 
             WHERE employee_id = :emp_id 
             ORDER BY id DESC LIMIT 1",
            ['emp_id' => $employeeId]
        );
        $row = $this->db->fetch();
        if (!$row) return null;

        return $this->getOnboardingById((int)$row['id']);
    }

    /**
     * Cập nhật trạng thái một checklist item
     */
    public function updateTaskItem(int $itemId, string $status, ?string $notes = null, ?int $assignedTo = null): bool
    {
        $validStatuses = ['Pending', 'InProgress', 'Done', 'Skipped'];
        if (!in_array($status, $validStatuses)) return false;

        $completedAt = in_array($status, ['Done', 'Skipped']) ? date('Y-m-d H:i:s') : null;

        $this->db->query(
            "UPDATE employee_onboarding_items 
             SET status = :status, completed_at = :completed_at, notes = COALESCE(:notes, notes), assigned_to = COALESCE(:assigned_to, assigned_to)
             WHERE id = :id",
            [
                'id'           => $itemId,
                'status'       => $status,
                'completed_at' => $completedAt,
                'notes'        => $notes,
                'assigned_to'  => $assignedTo,
            ]
        );

        // Lấy onboarding_id để cập nhật trạng thái tổng thể
        $this->db->query("SELECT onboarding_id FROM employee_onboarding_items WHERE id = :id", ['id' => $itemId]);
        $onbId = (int)($this->db->fetch()['onboarding_id'] ?? 0);
        if ($onbId > 0) {
            $this->recalculateOnboardingStatus($onbId);
        }

        return true;
    }

    /**
     * Tự động tính toán lại trạng thái tổng thể của Onboarding
     */
    public function recalculateOnboardingStatus(int $onboardingId): void
    {
        $this->db->query(
            "SELECT i.status, i.due_date, t.is_required
             FROM employee_onboarding_items i
             JOIN onboarding_tasks t ON i.task_id = t.id
             WHERE i.onboarding_id = :id",
            ['id' => $onboardingId]
        );
        $items = $this->db->fetchAll();

        $allRequiredDone = true;
        $hasOverdue = false;
        $today = date('Y-m-d');

        foreach ($items as $item) {
            $isDone = in_array($item['status'], ['Done', 'Skipped']);
            if ($item['is_required'] && !$isDone) {
                $allRequiredDone = false;
            }
            if (!$isDone && $item['due_date'] && $item['due_date'] < $today) {
                $hasOverdue = true;
            }
        }

        if ($allRequiredDone) {
            $newStatus = 'Completed';
            $completedAt = date('Y-m-d H:i:s');
        } elseif ($hasOverdue) {
            $newStatus = 'Overdue';
            $completedAt = null;
        } else {
            $newStatus = 'InProgress';
            $completedAt = null;
        }

        $this->db->query(
            "UPDATE employee_onboardings 
             SET status = :status, completed_at = :completed_at 
             WHERE id = :id",
            [
                'id'           => $onboardingId,
                'status'       => $newStatus,
                'completed_at' => $completedAt,
            ]
        );
    }

    /**
     * Danh sách tất cả các đợt Onboarding (kèm filters)
     */
    public function getAllOnboardings(array $filters = []): array
    {
        $where = ["1=1"];
        $params = [];

        if (!empty($filters['status'])) {
            $where[] = "o.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['department_id'])) {
            $where[] = "e.department_id = :dept_id";
            $params['dept_id'] = (int)$filters['department_id'];
        }

        if (!empty($filters['search'])) {
            $where[] = "(e.full_name LIKE :s OR e.emp_code LIKE :s OR tpl.name LIKE :s)";
            $params['s'] = '%' . $filters['search'] . '%';
        }

        $whereClause = implode(" AND ", $where);

        $this->db->query(
            "SELECT o.*, 
                    e.emp_code, e.full_name, e.avatar_path, e.gender,
                    d.dept_name, d.dept_code,
                    p.pos_title,
                    tpl.name AS template_name,
                    COUNT(i.id) AS total_items,
                    SUM(CASE WHEN i.status IN ('Done', 'Skipped') THEN 1 ELSE 0 END) AS done_items,
                    SUM(CASE WHEN i.status NOT IN ('Done', 'Skipped') AND i.due_date < CURRENT_DATE THEN 1 ELSE 0 END) AS overdue_items
             FROM employee_onboardings o
             JOIN employees e ON o.employee_id = e.id
             LEFT JOIN departments d ON e.department_id = d.id
             LEFT JOIN positions p ON e.position_id = p.id
             JOIN onboarding_templates tpl ON o.template_id = tpl.id
             LEFT JOIN employee_onboarding_items i ON o.id = i.onboarding_id
             WHERE {$whereClause}
             GROUP BY o.id
             ORDER BY o.status = 'InProgress' DESC, o.status = 'Overdue' DESC, o.id DESC",
            $params
        );
        $results = $this->db->fetchAll();

        foreach ($results as &$r) {
            $total = (int)($r['total_items'] ?? 0);
            $done = (int)($r['done_items'] ?? 0);
            $r['progress_pct'] = $total > 0 ? round(($done / $total) * 100) : 0;
        }

        return $results;
    }

    /**
     * Dữ liệu thống kê cho Dashboard Onboarding
     */
    public function getDashboardStats(): array
    {
        // 1. Thống kê theo trạng thái
        $this->db->query("SELECT status, COUNT(*) AS cnt FROM employee_onboardings GROUP BY status");
        $counts = ['InProgress' => 0, 'Completed' => 0, 'Overdue' => 0];
        foreach ($this->db->fetchAll() as $row) {
            $counts[$row['status']] = (int)$row['cnt'];
        }

        // 2. Danh sách task quá hạn cần xử lý gấp
        $this->db->query(
            "SELECT i.*, 
                    t.title AS task_title, t.responsible_department,
                    e.id AS emp_id, e.emp_code, e.full_name,
                    d.dept_name,
                    DATEDIFF(CURRENT_DATE, i.due_date) AS days_overdue
             FROM employee_onboarding_items i
             JOIN onboarding_tasks t ON i.task_id = t.id
             JOIN employee_onboardings o ON i.onboarding_id = o.id
             JOIN employees e ON o.employee_id = e.id
             LEFT JOIN departments d ON e.department_id = d.id
             WHERE i.status NOT IN ('Done', 'Skipped') AND i.due_date < CURRENT_DATE
             ORDER BY i.due_date ASC
             LIMIT 10"
        );
        $overdueTasks = $this->db->fetchAll();

        // 3. Nhân viên mới đang trong tiến trình Onboarding
        $recentOnboardings = $this->getAllOnboardings(['status' => 'InProgress']);

        return [
            'counts'             => $counts,
            'total_active'       => $counts['InProgress'] + $counts['Overdue'],
            'overdue_tasks'      => $overdueTasks,
            'recent_onboardings' => array_slice($recentOnboardings, 0, 8),
        ];
    }
}
