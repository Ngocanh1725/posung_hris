<?php
/**
 * ============================================================
 *  POSUNG HRIS – JobMovement Model
 * ============================================================
 *  Quản lý Lệnh điều động (Transfer Orders) và Lịch sử
 *  luân chuyển công tác (Job Movements).
 * ============================================================
 */

class JobMovement extends BaseModel
{
    protected string $table = 'job_movements';

    /**
     * Tạo mới lệnh điều động (Mass Transfer Order)
     *
     * @param array $orderData Thông tin chung của lệnh
     * @param array $employeeIds Danh sách ID nhân viên được điều động
     * @param int $costCenterId Mã trung tâm chi phí đích
     * @return int|false Trả về Order ID nếu thành công
     */
    public function createTransferOrder(array $orderData, array $employeeIds, int $costCenterId, string $status = 'Draft')
    {
        $this->db->beginTransaction();

        try {
            // 1. Thêm bản ghi vào transfer_orders
            $this->db->query(
                "INSERT INTO transfer_orders (decision_number, from_project_id, to_project_id, effective_date, reason, status, created_by)
                 VALUES (:decision_number, :from_project_id, :to_project_id, :effective_date, :reason, :status, :created_by)",
                array_merge($orderData, ['status' => $status])
            );
            $orderId = (int) $this->db->lastInsertId();

            // 2. Lấy thông tin dự án/phòng ban hiện tại của nhân sự để lưu vào lịch sử
            foreach ($employeeIds as $empId) {
                $this->db->query("SELECT department_id, current_project_id, position_id FROM employees WHERE id = :id", ['id' => $empId]);
                $empInfo = $this->db->fetch();

                if ($empInfo) {
                    $this->db->query(
                        "INSERT INTO job_movements (employee_id, transfer_order_id, movement_type, 
                                from_project_id, to_project_id, from_dept_id, to_dept_id, 
                                from_position_id, to_position_id, decision_number, effective_date, cost_center_id, reason)
                         VALUES (:emp, :order, 'Transfer', :f_proj, :t_proj, :f_dept, :t_dept, :f_pos, :t_pos, :decision, :date, :cost_center, :reason)",
                        [
                            'emp'         => $empId,
                            'order'       => $orderId,
                            'f_proj'      => $empInfo['current_project_id'],
                            't_proj'      => $orderData['to_project_id'],
                            'f_dept'      => $empInfo['department_id'],
                            't_dept'      => $empInfo['department_id'], // Giữ nguyên phòng ban
                            'f_pos'       => $empInfo['position_id'],
                            't_pos'       => $empInfo['position_id'], // Giữ nguyên chức vụ
                            'decision'    => $orderData['decision_number'],
                            'date'        => $orderData['effective_date'],
                            'cost_center' => $costCenterId,
                            'reason'      => $orderData['reason']
                        ]
                    );
                }
            }

            $this->db->commit();
            return $orderId;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Lỗi tạo Transfer Order: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Phê duyệt lệnh điều động (Cập nhật bảng employees)
     *
     * @param int $orderId ID của lệnh điều động
     * @param int $approverId ID của người duyệt (Giám đốc / Trưởng phòng HR)
     * @return bool
     */
    public function approveTransferOrder(int $orderId, int $approverId): bool
    {
        $this->db->beginTransaction();

        try {
            // 1. Kiểm tra lệnh điều động có tồn tại và đang Pending không
            $this->db->query("SELECT * FROM transfer_orders WHERE id = :id FOR UPDATE", ['id' => $orderId]);
            $order = $this->db->fetch();

            if (!$order || $order['status'] !== 'Pending') {
                $this->db->rollBack();
                return false;
            }

            // 2. Cập nhật trạng thái lệnh
            $this->db->query(
                "UPDATE transfer_orders SET status = 'Approved', approved_by = :approver WHERE id = :id",
                ['approver' => $approverId, 'id' => $orderId]
            );

            // 3. Lấy danh sách nhân viên trong lệnh này kèm thông tin chức vụ, dự án, cost center
            $this->db->query("
                SELECT jm.id, jm.employee_id, jm.to_project_id, jm.to_dept_id, jm.to_position_id, jm.cost_center_id, 
                       p.project_name, cc.code AS cost_center_code, pos.pos_title AS position_name
                FROM job_movements jm
                LEFT JOIN projects p ON jm.to_project_id = p.id
                LEFT JOIN cost_centers cc ON jm.cost_center_id = cc.id
                LEFT JOIN positions pos ON jm.to_position_id = pos.id
                WHERE jm.transfer_order_id = :orderId
            ", ['orderId' => $orderId]);
            $movements = $this->db->fetchAll();

            // 4. Cập nhật bảng employees và emp_work_histories
            foreach ($movements as $mov) {
                // Cập nhật nhân sự
                $this->db->query(
                    "UPDATE employees SET current_project_id = :proj, department_id = :dept, position_id = :pos WHERE id = :emp",
                    [
                        'proj' => $mov['to_project_id'],
                        'dept' => $mov['to_dept_id'],
                        'pos'  => $mov['to_position_id'],
                        'emp'  => $mov['employee_id']
                    ]
                );

                // Cập nhật approver trong bảng job_movements
                $this->db->query(
                    "UPDATE job_movements SET approved_by = :approver WHERE id = :id",
                    ['approver' => $approverId, 'id' => $mov['id']]
                );

                // Đóng bản ghi work history cũ
                $this->db->query(
                    "UPDATE emp_work_histories 
                     SET to_date = DATE_SUB(:effective_date, INTERVAL 1 DAY) 
                     WHERE employee_id = :emp AND to_date IS NULL",
                    ['effective_date' => $order['effective_date'], 'emp' => $mov['employee_id']]
                );

                // Mở bản ghi work history mới với Cost Center Code và Project ID
                $this->db->query(
                    "INSERT INTO emp_work_histories (employee_id, from_date, to_date, organization, position, project_name, project_id, cost_center_code, description)
                     VALUES (:emp, :effective_date, NULL, 'CÔNG TY TNHH PO SUNG MEC VIỆT NAM', :pos_name, :proj_name, :proj_id, :cc_code, :desc)",
                    [
                        'emp' => $mov['employee_id'],
                        'effective_date' => $order['effective_date'],
                        'pos_name' => $mov['position_name'],
                        'proj_name' => $mov['project_name'],
                        'proj_id' => $mov['to_project_id'],
                        'cc_code' => $mov['cost_center_code'],
                        'desc' => "Theo Quyết định số: " . $order['decision_number']
                    ]
                );

                // Tự động điều chỉnh phụ cấp xa nhà / phụ cấp dự án
                $this->db->query("SELECT id FROM allowances WHERE code IN ('PC_XANHA', 'PC_DUAN', 'SITE_ALLOWANCE') LIMIT 1");
                $allowance = $this->db->fetch();
                if ($allowance) {
                    // Lấy mức phụ cấp dựa theo Cost Center hoặc Project (Ví dụ: VP = 0, Công trường = 3,000,000)
                    // Ở đây mô phỏng logic: Trụ sở/Văn phòng thường là cost center id = 1 hoặc project = 1
                    $siteAmount = ($mov['to_project_id'] <= 1) ? 0 : 3000000; 
                    
                    $this->db->query("SELECT id FROM employee_allowances WHERE employee_id = :emp AND allowance_id = :allw", [
                        'emp' => $mov['employee_id'],
                        'allw' => $allowance['id']
                    ]);
                    $empAllw = $this->db->fetch();

                    if ($empAllw) {
                        $this->db->query(
                            "UPDATE employee_allowances SET amount = :amount, effective_date = :date WHERE id = :id",
                            ['amount' => $siteAmount, 'date' => $order['effective_date'], 'id' => $empAllw['id']]
                        );
                    } elseif ($siteAmount > 0) {
                        $this->db->query(
                            "INSERT INTO employee_allowances (employee_id, allowance_id, amount, currency, effective_date) 
                             VALUES (:emp, :allw, :amount, 'VND', :date)",
                            ['emp' => $mov['employee_id'], 'allw' => $allowance['id'], 'amount' => $siteAmount, 'date' => $order['effective_date']]
                        );
                    }
                }
            }

            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log('Lỗi duyệt Transfer Order: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Lấy danh sách lệnh điều động theo trạng thái (dùng cho Index)
     */
    public function getOrders(?string $status = null): array
    {
        $sql = "SELECT tor.*, 
                       fp.project_name AS from_project, 
                       tp.project_name AS to_project,
                       u.username AS creator_name,
                       (SELECT COUNT(*) FROM job_movements WHERE transfer_order_id = tor.id) AS employee_count
                FROM transfer_orders tor
                LEFT JOIN projects fp ON tor.from_project_id = fp.id
                LEFT JOIN projects tp ON tor.to_project_id = tp.id
                LEFT JOIN users u ON tor.created_by = u.id";
        
        $params = [];
        if ($status) {
            $sql .= " WHERE tor.status = :status";
            $params['status'] = $status;
        }
        $sql .= " ORDER BY tor.created_at DESC";

        $this->db->query($sql, $params);
        return json_decode(json_encode($this->db->fetchAll()));
    }

    /**
     * Lấy chi tiết lệnh điều động và danh sách nhân sự đi kèm
     */
    public function getOrderDetails(int $orderId): ?object
    {
        // Thông tin chung của lệnh
        $this->db->query(
            "SELECT tor.*, fp.project_name AS from_project, tp.project_name AS to_project,
                    c.full_name AS creator_name, a.full_name AS approver_name
             FROM transfer_orders tor
             LEFT JOIN projects fp ON tor.from_project_id = fp.id
             LEFT JOIN projects tp ON tor.to_project_id = tp.id
             LEFT JOIN users c ON tor.created_by = c.id
             LEFT JOIN users a ON tor.approved_by = a.id
             WHERE tor.id = :id",
            ['id' => $orderId]
        );
        $order = $this->db->fetch();

        if (!$order) return null;

        $orderObj = (object) $order;

        // Danh sách nhân sự trong lệnh
        $this->db->query(
            "SELECT jm.*, e.emp_code, e.full_name, p.pos_title, d.dept_name, cc.code AS cc_code, cc.name AS cc_name
             FROM job_movements jm
             JOIN employees e ON jm.employee_id = e.id
             LEFT JOIN positions p ON e.position_id = p.id
             LEFT JOIN departments d ON e.department_id = d.id
             LEFT JOIN cost_centers cc ON jm.cost_center_id = cc.id
             WHERE jm.transfer_order_id = :id",
            ['id' => $orderId]
        );
        $orderObj->employees = json_decode(json_encode($this->db->fetchAll()));

        return $orderObj;
    }
}
