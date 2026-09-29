<?php
/**
 * ============================================================
 *  POSUNG HRIS – Asset Model
 * ============================================================
 *  Quản lý Danh mục và Hồ sơ Tài sản Doanh nghiệp (Asset Management)
 *  Cấp phát, Thu hồi, Lịch sử bàn giao và Kiểm kê tài sản
 * ============================================================
 */

class Asset extends BaseModel
{
    protected string $table = 'assets';

    /**
     * Lấy danh sách tài sản kèm thông tin loại tài sản và nhân viên đang giữ
     */
    public function getAllWithRelations(array $filters = []): array
    {
        $sql = "SELECT a.*,
                       c.name AS category_name,
                       c.category_code,
                       c.icon AS category_icon,
                       cur_asg.id AS current_assignment_id,
                       cur_asg.assigned_date,
                       cur_asg.employee_id AS current_holder_id,
                       e.full_name AS holder_name,
                       e.emp_code AS holder_code,
                       d.dept_name AS holder_dept
                FROM `{$this->table}` a
                LEFT JOIN `asset_categories` c ON a.category_id = c.id
                LEFT JOIN (
                    SELECT aa.*
                    FROM `asset_assignments` aa
                    INNER JOIN (
                        SELECT asset_id, MAX(id) AS max_id
                        FROM `asset_assignments`
                        WHERE return_date IS NULL
                        GROUP BY asset_id
                    ) latest ON aa.id = latest.max_id
                ) cur_asg ON a.id = cur_asg.asset_id
                LEFT JOIN `employees` e ON cur_asg.employee_id = e.id
                LEFT JOIN `departments` d ON e.department_id = d.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (a.asset_code LIKE :search OR a.name LIKE :search2 OR a.serial_number LIKE :search3 OR a.location LIKE :search4)";
            $params['search']  = "%{$filters['search']}%";
            $params['search2'] = "%{$filters['search']}%";
            $params['search3'] = "%{$filters['search']}%";
            $params['search4'] = "%{$filters['search']}%";
        }

        if (!empty($filters['category_id'])) {
            $sql .= " AND a.category_id = :category_id";
            $params['category_id'] = (int)$filters['category_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND a.status = :status";
            $params['status'] = $filters['status'];
        }

        if (!empty($filters['condition'])) {
            $sql .= " AND a.condition = :condition";
            $params['condition'] = $filters['condition'];
        }

        if (!empty($filters['location'])) {
            $sql .= " AND a.location LIKE :location";
            $params['location'] = "%{$filters['location']}%";
        }

        $sql .= " ORDER BY a.id DESC";

        $this->db->query($sql, $params);
        $results = $this->db->fetchAll();

        return json_decode(json_encode($results));
    }

    /**
     * Chi tiết tài sản kèm thông tin danh mục và người đang nắm giữ
     */
    public function getByIdWithRelations(int $id): ?object
    {
        $sql = "SELECT a.*,
                       c.name AS category_name,
                       c.category_code,
                       c.icon AS category_icon,
                       cur_asg.id AS current_assignment_id,
                       cur_asg.assigned_date,
                       cur_asg.condition_on_assign,
                       cur_asg.notes AS assignment_notes,
                       cur_asg.employee_id AS current_holder_id,
                       e.full_name AS holder_name,
                       e.emp_code AS holder_code,
                       e.phone AS holder_phone,
                       e.email AS holder_email,
                       d.dept_name AS holder_dept,
                       p.pos_title AS holder_position
                FROM `{$this->table}` a
                LEFT JOIN `asset_categories` c ON a.category_id = c.id
                LEFT JOIN (
                    SELECT aa.*
                    FROM `asset_assignments` aa
                    WHERE aa.asset_id = :asset_id AND aa.return_date IS NULL
                    ORDER BY aa.id DESC
                    LIMIT 1
                ) cur_asg ON a.id = cur_asg.asset_id
                LEFT JOIN `employees` e ON cur_asg.employee_id = e.id
                LEFT JOIN `departments` d ON e.department_id = d.id
                LEFT JOIN `positions` p ON e.position_id = p.id
                WHERE a.id = :id
                LIMIT 1";

        $this->db->query($sql, ['id' => $id, 'asset_id' => $id]);
        $row = $this->db->fetch();

        return $row ? (object)$row : null;
    }

    /**
     * Lấy toàn bộ lịch sử cấp phát và thu hồi của một tài sản
     */
    public function getHistory(int $assetId): array
    {
        $sql = "SELECT aa.*,
                       e.full_name AS employee_name,
                       e.emp_code,
                       d.dept_name,
                       u_assign.username AS assigned_by_name,
                       u_return.username AS returned_to_name
                FROM `asset_assignments` aa
                JOIN `employees` e ON aa.employee_id = e.id
                LEFT JOIN `departments` d ON e.department_id = d.id
                LEFT JOIN `users` u_assign ON aa.assigned_by = u_assign.id
                LEFT JOIN `users` u_return ON aa.returned_to = u_return.id
                WHERE aa.asset_id = :asset_id
                ORDER BY aa.id DESC";

        $this->db->query($sql, ['asset_id' => $assetId]);
        $results = $this->db->fetchAll();

        return json_decode(json_encode($results));
    }

    /**
     * Lấy thông tin 1 đợt bàn giao (cho biên bản in)
     */
    public function getAssignment(int $assignmentId): ?object
    {
        $sql = "SELECT aa.*,
                       a.asset_code,
                       a.name AS asset_name,
                       a.serial_number,
                       a.purchase_cost,
                       a.warranty_expiry,
                       c.name AS category_name,
                       e.full_name AS employee_name,
                       e.emp_code,
                       e.id_card_no,
                       e.phone AS employee_phone,
                       e.email AS employee_email,
                       d.dept_name,
                       p.pos_title,
                       u_assign.username AS assigned_by_name,
                       COALESCE(emp_assign.full_name, u_assign.username) AS assigned_by_fullname,
                       u_return.username AS returned_to_name,
                       COALESCE(emp_return.full_name, u_return.username) AS returned_to_fullname
                FROM `asset_assignments` aa
                JOIN `assets` a ON aa.asset_id = a.id
                LEFT JOIN `asset_categories` c ON a.category_id = c.id
                JOIN `employees` e ON aa.employee_id = e.id
                LEFT JOIN `departments` d ON e.department_id = d.id
                LEFT JOIN `positions` p ON e.position_id = p.id
                LEFT JOIN `users` u_assign ON aa.assigned_by = u_assign.id
                LEFT JOIN `employees` emp_assign ON u_assign.employee_id = emp_assign.id
                LEFT JOIN `users` u_return ON aa.returned_to = u_return.id
                LEFT JOIN `employees` emp_return ON u_return.employee_id = emp_return.id
                WHERE aa.id = :id
                LIMIT 1";

        $this->db->query($sql, ['id' => $assignmentId]);
        $row = $this->db->fetch();

        return $row ? (object)$row : null;
    }

    /**
     * Thực hiện giao tài sản cho nhân viên
     */
    public function assignAsset(array $data): bool
    {
        try {
            $this->db->beginTransaction();

            // 1. Kiểm tra tài sản có đang Available hay không
            $asset = $this->find((int)$data['asset_id']);
            if (!$asset || $asset->status === 'Assigned') {
                $this->db->rollBack();
                return false;
            }

            // 2. Tạo bản ghi bàn giao
            $sql = "INSERT INTO `asset_assignments` 
                    (`asset_id`, `employee_id`, `assigned_date`, `assigned_by`, `condition_on_assign`, `notes`)
                    VALUES (:asset_id, :employee_id, :assigned_date, :assigned_by, :condition_on_assign, :notes)";

            $this->db->query($sql, [
                'asset_id'            => (int)$data['asset_id'],
                'employee_id'         => (int)$data['employee_id'],
                'assigned_date'       => $data['assigned_date'] ?? date('Y-m-d'),
                'assigned_by'         => $data['assigned_by'] ?? null,
                'condition_on_assign' => $data['condition_on_assign'] ?? 'Good',
                'notes'               => $data['notes'] ?? null,
            ]);

            $assignmentId = $this->db->lastInsertId();

            // 3. Cập nhật trạng thái tài sản thành Assigned
            $updateSql = "UPDATE `{$this->table}` SET `status` = 'Assigned' WHERE `id` = :id";
            $this->db->query($updateSql, ['id' => (int)$data['asset_id']]);

            $this->db->commit();

            // Gửi thông báo đến nhân viên nếu có NotificationService
            if (class_exists('NotificationService')) {
                NotificationService::sendToEmployee(
                    (int)$data['employee_id'],
                    'reminder',
                    "Bàn giao tài sản: {$asset->name}",
                    "Bạn đã được bàn giao tài sản {$asset->name} (Mã: {$asset->asset_code}). Vui lòng bảo quản và sử dụng đúng quy định công ty.",
                    "asset/show/{$asset->id}"
                );
            }

            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Lỗi assignAsset: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Thu hồi tài sản từ nhân viên về kho
     */
    public function returnAsset(int $assignmentId, array $data): bool
    {
        try {
            $this->db->beginTransaction();

            // 1. Lấy thông tin assignment
            $assignment = $this->getAssignment($assignmentId);
            if (!$assignment || !empty($assignment->return_date)) {
                $this->db->rollBack();
                return false;
            }

            // 2. Cập nhật thông tin trả trong asset_assignments
            $sql = "UPDATE `asset_assignments` 
                    SET `return_date` = :return_date,
                        `returned_to` = :returned_to,
                        `condition_on_return` = :condition_on_return,
                        `notes` = CONCAT_WS(' | Ghi chú trả: ', COALESCE(notes, ''), :return_notes)
                    WHERE `id` = :id";

            $this->db->query($sql, [
                'return_date'         => $data['return_date'] ?? date('Y-m-d'),
                'returned_to'         => $data['returned_to'] ?? null,
                'condition_on_return' => $data['condition_on_return'] ?? 'Good',
                'return_notes'        => $data['notes'] ?? '',
                'id'                  => $assignmentId,
            ]);

            // 3. Cập nhật trạng thái tài sản (Available hoặc Maintenance nếu hư hỏng)
            $newStatus = ($data['condition_on_return'] === 'Damaged') ? 'Maintenance' : 'Available';
            $updateSql = "UPDATE `{$this->table}` 
                          SET `status` = :status, `condition` = :condition 
                          WHERE `id` = :asset_id";

            $this->db->query($updateSql, [
                'status'    => $newStatus,
                'condition' => $data['condition_on_return'] ?? 'Good',
                'asset_id'  => $assignment->asset_id,
            ]);

            $this->db->commit();

            // Gửi thông báo đến nhân viên nếu có NotificationService
            if (class_exists('NotificationService')) {
                NotificationService::sendToEmployee(
                    (int)$assignment->employee_id,
                    'system',
                    "Hoàn tất thu hồi tài sản: {$assignment->asset_name}",
                    "Công ty đã tiếp nhận thu hồi tài sản {$assignment->asset_name} (Mã: {$assignment->asset_code}) thành công.",
                    "asset/show/{$assignment->asset_id}"
                );
            }

            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Lỗi returnAsset: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy danh sách tài sản của 1 nhân viên (Đã & Đang giữ)
     */
    public function getAssignmentsByEmployee(int $employeeId): array
    {
        $sql = "SELECT aa.*,
                       a.asset_code,
                       a.name AS asset_name,
                       a.serial_number,
                       a.purchase_cost,
                       a.status AS asset_status,
                       c.name AS category_name,
                       c.icon AS category_icon,
                       u_assign.username AS assigned_by_name
                FROM `asset_assignments` aa
                JOIN `assets` a ON aa.asset_id = a.id
                LEFT JOIN `asset_categories` c ON a.category_id = c.id
                LEFT JOIN `users` u_assign ON aa.assigned_by = u_assign.id
                WHERE aa.employee_id = :employee_id
                ORDER BY aa.return_date IS NULL DESC, aa.assigned_date DESC";

        $this->db->query($sql, ['employee_id' => $employeeId]);
        $results = $this->db->fetchAll();

        return json_decode(json_encode($results));
    }

    /**
     * Lấy các tài sản NHÂN VIÊN ĐANG GIỮ CHƯA TRẢ (Phục vụ cảnh báo Offboarding)
     */
    public function getActiveAssignmentsByEmployee(int $employeeId): array
    {
        $sql = "SELECT aa.*,
                       a.asset_code,
                       a.name AS asset_name,
                       a.serial_number,
                       a.purchase_cost,
                       c.name AS category_name,
                       c.icon AS category_icon
                FROM `asset_assignments` aa
                JOIN `assets` a ON aa.asset_id = a.id
                LEFT JOIN `asset_categories` c ON a.category_id = c.id
                WHERE aa.employee_id = :employee_id AND aa.return_date IS NULL
                ORDER BY aa.assigned_date DESC";

        $this->db->query($sql, ['employee_id' => $employeeId]);
        $results = $this->db->fetchAll();

        return json_decode(json_encode($results));
    }

    /**
     * Quản lý Danh mục tài sản: Lấy tất cả kèm số lượng tài sản
     */
    public function getCategories(): array
    {
        $sql = "SELECT c.*,
                       COUNT(a.id) AS total_assets,
                       SUM(CASE WHEN a.status = 'Available' THEN 1 ELSE 0 END) AS available_count,
                       SUM(CASE WHEN a.status = 'Assigned' THEN 1 ELSE 0 END) AS assigned_count,
                       SUM(CASE WHEN a.status = 'Maintenance' THEN 1 ELSE 0 END) AS maintenance_count,
                       COALESCE(SUM(a.purchase_cost), 0) AS total_cost
                FROM `asset_categories` c
                LEFT JOIN `assets` a ON c.id = a.category_id
                GROUP BY c.id
                ORDER BY c.id ASC";

        $this->db->query($sql);
        $results = $this->db->fetchAll();

        return json_decode(json_encode($results));
    }

    /**
     * Lấy chi tiết 1 danh mục
     */
    public function getCategory(int $id): ?object
    {
        $this->db->query("SELECT * FROM `asset_categories` WHERE `id` = :id LIMIT 1", ['id' => $id]);
        $row = $this->db->fetch();

        return $row ? (object)$row : null;
    }

    /**
     * Thêm hoặc cập nhật danh mục
     */
    public function saveCategory(array $data, ?int $id = null): bool
    {
        if ($id) {
            $sql = "UPDATE `asset_categories` 
                    SET `name` = :name, `category_code` = :category_code, `description` = :description, 
                        `icon` = :icon, `status` = :status 
                    WHERE `id` = :id";
            $data['id'] = $id;
            return $this->db->query($sql, $data);
        } else {
            $sql = "INSERT INTO `asset_categories` (`category_code`, `name`, `description`, `icon`, `status`)
                    VALUES (:category_code, :name, :description, :icon, :status)";
            return $this->db->query($sql, $data);
        }
    }

    /**
     * Xóa danh mục (chỉ khi chưa có tài sản nào thuộc danh mục này)
     */
    public function deleteCategory(int $id): bool
    {
        $this->db->query("SELECT COUNT(*) AS cnt FROM `assets` WHERE `category_id` = :id", ['id' => $id]);
        $cnt = (int)($this->db->fetch()['cnt'] ?? 0);
        if ($cnt > 0) {
            return false;
        }

        return $this->db->query("DELETE FROM `asset_categories` WHERE `id` = :id", ['id' => $id]);
    }

    /**
     * Thống kê kiểm kê tài sản (BI & Report)
     */
    public function getReportStats(): array
    {
        // 1. Tổng quan
        $this->db->query("SELECT 
                            COUNT(*) AS total_assets,
                            SUM(CASE WHEN status = 'Available' THEN 1 ELSE 0 END) AS total_available,
                            SUM(CASE WHEN status = 'Assigned' THEN 1 ELSE 0 END) AS total_assigned,
                            SUM(CASE WHEN status = 'Maintenance' THEN 1 ELSE 0 END) AS total_maintenance,
                            SUM(CASE WHEN status = 'Disposed' THEN 1 ELSE 0 END) AS total_disposed,
                            COALESCE(SUM(purchase_cost), 0) AS total_value,
                            COALESCE(SUM(CASE WHEN status = 'Assigned' THEN purchase_cost ELSE 0 END), 0) AS assigned_value
                          FROM `assets`");
        $overview = (object)$this->db->fetch();

        // 2. Thống kê theo loại tài sản
        $this->db->query("SELECT 
                            c.id, c.name, c.icon,
                            COUNT(a.id) AS count,
                            COALESCE(SUM(a.purchase_cost), 0) AS total_cost,
                            SUM(CASE WHEN a.status = 'Assigned' THEN 1 ELSE 0 END) AS assigned_count
                          FROM `asset_categories` c
                          LEFT JOIN `assets` a ON c.id = a.category_id
                          GROUP BY c.id, c.name, c.icon
                          ORDER BY count DESC");
        $byCategory = json_decode(json_encode($this->db->fetchAll()));

        // 3. Top nhân viên đang giữ tài sản nhiều nhất
        $this->db->query("SELECT 
                            e.id, e.emp_code, e.full_name, d.dept_name,
                            COUNT(aa.id) AS holding_count,
                            COALESCE(SUM(a.purchase_cost), 0) AS total_holding_value
                          FROM `asset_assignments` aa
                          JOIN `assets` a ON aa.asset_id = a.id
                          JOIN `employees` e ON aa.employee_id = e.id
                          LEFT JOIN `departments` d ON e.department_id = d.id
                          WHERE aa.return_date IS NULL
                          GROUP BY e.id, e.emp_code, e.full_name, d.dept_name
                          ORDER BY holding_count DESC, total_holding_value DESC
                          LIMIT 10");
        $topHolders = json_decode(json_encode($this->db->fetchAll()));

        // 4. Danh sách tài sản sắp hoặc đã hết hạn bảo hành
        $this->db->query("SELECT a.*, c.name AS category_name 
                          FROM `assets` a
                          LEFT JOIN `asset_categories` c ON a.category_id = c.id
                          WHERE a.warranty_expiry IS NOT NULL 
                            AND a.warranty_expiry <= DATE_ADD(CURDATE(), INTERVAL 60 DAY)
                          ORDER BY a.warranty_expiry ASC
                          LIMIT 10");
        $warrantyAlerts = json_decode(json_encode($this->db->fetchAll()));

        return [
            'overview'       => $overview,
            'byCategory'     => $byCategory,
            'topHolders'     => $topHolders,
            'warrantyAlerts' => $warrantyAlerts,
        ];
    }

    /**
     * Tự động sinh mã tài sản theo danh mục (vd: AST-IT-0005)
     */
    public function generateAssetCode(int $categoryId): string
    {
        $cat = $this->getCategory($categoryId);
        $prefixCode = 'AST';
        if ($cat && !empty($cat->category_code)) {
            $parts = explode('_', $cat->category_code);
            $sub = strtoupper(substr($parts[0], 0, 3));
            $prefixCode = "AST-{$sub}";
        }

        $this->db->query(
            "SELECT asset_code FROM `{$this->table}` 
             WHERE asset_code LIKE :prefix 
             ORDER BY id DESC LIMIT 1",
            ['prefix' => "{$prefixCode}-%"]
        );
        $lastRow = $this->db->fetch();

        if ($lastRow && !empty($lastRow['asset_code'])) {
            $parts = explode('-', $lastRow['asset_code']);
            $num = (int)end($parts);
            $newNum = $num + 1;
        } else {
            $newNum = 1;
        }

        return $prefixCode . '-' . str_pad((string)$newNum, 4, '0', STR_PAD_LEFT);
    }
}
