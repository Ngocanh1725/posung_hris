<?php
/**
 * ============================================================
 *  POSUNG HRIS – AssetController
 * ============================================================
 *  Quản lý Tài sản Doanh nghiệp (Company Asset Management)
 *  Laptop, điện thoại, máy móc công trình, xe cộ, thẻ ra vào...
 * ============================================================
 */

class AssetController extends Controller
{
    /**
     * Danh sách tài sản (Table view / Card view + Bộ lọc)
     */
    public function index(): void
    {
        $this->checkPermission('asset.view');

        $assetModel = $this->model('Asset');

        $filters = [
            'search'      => trim($this->getData('search', '')),
            'category_id' => $this->getData('category_id', ''),
            'status'      => $this->getData('status', ''),
            'condition'   => $this->getData('condition', ''),
            'location'    => trim($this->getData('location', '')),
            'view'        => $this->getData('view', 'table'), // 'table' or 'card'
        ];

        $assets = $assetModel->getAllWithRelations($filters);
        $categories = $assetModel->getCategories();

        // Lấy danh sách nhân viên phục vụ modal Giao tài sản nhanh
        $employeeModel = $this->model('Employee');
        $employees = $employeeModel->getAll();

        // Thống kê nhanh thanh số liệu trên cùng
        $stats = [
            'total'       => count($assets),
            'available'   => 0,
            'assigned'    => 0,
            'maintenance' => 0,
            'total_value' => 0,
        ];
        foreach ($assets as $a) {
            if ($a->status === 'Available') $stats['available']++;
            if ($a->status === 'Assigned') $stats['assigned']++;
            if ($a->status === 'Maintenance') $stats['maintenance']++;
            $stats['total_value'] += (float)($a->purchase_cost ?? 0);
        }

        $this->view('layouts/header', ['pageTitle' => 'Quản lý Tài sản Công ty']);
        $this->view('asset/index', [
            'assets'     => $assets,
            'categories' => $categories,
            'employees'  => $employees,
            'filters'    => $filters,
            'stats'      => $stats,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Form thêm mới tài sản
     */
    public function create(): void
    {
        $this->checkPermission('asset.create');

        $assetModel = $this->model('Asset');
        $categories = $assetModel->getCategories();

        // Lấy danh sách dự án
        $db = Database::getInstance();
        $db->query("SELECT id, project_code, project_name FROM projects ORDER BY project_name ASC");
        $projects = $db->fetchAll();

        $defaultCatId = !empty($categories) ? $categories[0]->id : 1;
        $suggestedCode = $assetModel->generateAssetCode($defaultCatId);

        $this->view('layouts/header', ['pageTitle' => 'Thêm mới Tài sản']);
        $this->view('asset/create', [
            'categories'    => $categories,
            'projects'      => $projects,
            'suggestedCode' => $suggestedCode,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Xử lý lưu tài sản mới
     */
    public function store(): void
    {
        $this->checkPermission('asset.create');

        if (!$this->isPost()) {
            $this->redirect('asset');
            return;
        }

        $assetModel = $this->model('Asset');

        $assetCode = trim($this->postData('asset_code', ''));
        $name = trim($this->postData('name', ''));
        $categoryId = (int)$this->postData('category_id');

        if (empty($assetCode) || empty($name) || empty($categoryId)) {
            Session::setFlash('error', 'Vui lòng nhập đầy đủ Mã tài sản, Tên tài sản và Phân loại.');
            $this->redirect('asset/create');
            return;
        }

        // Kiểm tra trùng mã tài sản
        $db = Database::getInstance();
        $db->query("SELECT id FROM assets WHERE asset_code = :code LIMIT 1", ['code' => $assetCode]);
        if ($db->fetch()) {
            Session::setFlash('error', "Mã tài sản '{$assetCode}' đã tồn tại trong hệ thống. Vui lòng chọn mã khác.");
            $this->redirect('asset/create');
            return;
        }

        $purchaseCost = (float)str_replace([',', '.'], '', $this->postData('purchase_cost', '0'));
        if (strpos($this->postData('purchase_cost', ''), '.') !== false && strpos($this->postData('purchase_cost', ''), ',') !== false) {
            // Trường hợp nhập dạng 10.000.000
            $cleanStr = str_replace('.', '', $this->postData('purchase_cost', '0'));
            $cleanStr = str_replace(',', '.', $cleanStr);
            $purchaseCost = (float)$cleanStr;
        }

        $data = [
            'asset_code'      => $assetCode,
            'name'            => $name,
            'category_id'     => $categoryId,
            'serial_number'   => trim($this->postData('serial_number', '')) ?: null,
            'purchase_date'   => $this->postData('purchase_date') ?: null,
            'purchase_cost'   => $purchaseCost,
            'warranty_expiry' => $this->postData('warranty_expiry') ?: null,
            'condition'       => $this->postData('condition', 'Good'),
            'status'          => $this->postData('status', 'Available'),
            'location'        => trim($this->postData('location', '')) ?: null,
            'project_id'      => $this->postData('project_id') ? (int)$this->postData('project_id') : null,
            'notes'           => trim($this->postData('notes', '')) ?: null,
            'created_by'      => Session::userId(),
        ];

        $insertSql = "INSERT INTO assets 
                      (asset_code, name, category_id, serial_number, purchase_date, purchase_cost, 
                       warranty_expiry, `condition`, `status`, location, project_id, notes, created_by)
                      VALUES 
                      (:asset_code, :name, :category_id, :serial_number, :purchase_date, :purchase_cost,
                       :warranty_expiry, :condition, :status, :location, :project_id, :notes, :created_by)";

        if ($db->query($insertSql, $data)) {
            $newId = $db->lastInsertId();
            Session::setFlash('success', "Thêm tài sản '{$name}' ({$assetCode}) thành công.");
            $this->redirect('asset/show/' . $newId);
        } else {
            Session::setFlash('error', 'Đã xảy ra lỗi khi thêm tài sản. Vui lòng thử lại.');
            $this->redirect('asset/create');
        }
    }

    /**
     * Xem chi tiết tài sản + Lịch sử bàn giao / thu hồi
     */
    public function show(int $id): void
    {
        $this->checkPermission('asset.view');

        $assetModel = $this->model('Asset');
        $asset = $assetModel->getByIdWithRelations($id);

        if (!$asset) {
            Session::setFlash('error', 'Không tìm thấy tài sản yêu cầu.');
            $this->redirect('asset');
            return;
        }

        $history = $assetModel->getHistory($id);

        // Lấy danh sách nhân viên nếu cần bàn giao
        $employeeModel = $this->model('Employee');
        $employees = $employeeModel->getAll();

        $this->view('layouts/header', ['pageTitle' => 'Chi tiết Tài sản - ' . $asset->asset_code]);
        $this->view('asset/show', [
            'asset'     => $asset,
            'history'   => $history,
            'employees' => $employees,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Form cập nhật tài sản
     */
    public function edit(int $id): void
    {
        $this->checkPermission('asset.edit');

        $assetModel = $this->model('Asset');
        $asset = $assetModel->getByIdWithRelations($id);

        if (!$asset) {
            Session::setFlash('error', 'Không tìm thấy tài sản.');
            $this->redirect('asset');
            return;
        }

        $categories = $assetModel->getCategories();

        $db = Database::getInstance();
        $db->query("SELECT id, project_code, project_name FROM projects ORDER BY project_name ASC");
        $projects = $db->fetchAll();

        $this->view('layouts/header', ['pageTitle' => 'Sửa Tài sản - ' . $asset->asset_code]);
        $this->view('asset/edit', [
            'asset'      => $asset,
            'categories' => $categories,
            'projects'   => $projects,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Xử lý cập nhật thông tin tài sản
     */
    public function update(int $id): void
    {
        $this->checkPermission('asset.edit');

        if (!$this->isPost()) {
            $this->redirect('asset/show/' . $id);
            return;
        }

        $name = trim($this->postData('name', ''));
        $categoryId = (int)$this->postData('category_id');

        if (empty($name) || empty($categoryId)) {
            Session::setFlash('error', 'Tên tài sản và Loại tài sản không được để trống.');
            $this->redirect('asset/edit/' . $id);
            return;
        }

        $purchaseCost = (float)str_replace([',', '.'], '', $this->postData('purchase_cost', '0'));
        if (strpos($this->postData('purchase_cost', ''), '.') !== false && strpos($this->postData('purchase_cost', ''), ',') !== false) {
            $cleanStr = str_replace('.', '', $this->postData('purchase_cost', '0'));
            $cleanStr = str_replace(',', '.', $cleanStr);
            $purchaseCost = (float)$cleanStr;
        }

        $data = [
            'id'              => $id,
            'name'            => $name,
            'category_id'     => $categoryId,
            'serial_number'   => trim($this->postData('serial_number', '')) ?: null,
            'purchase_date'   => $this->postData('purchase_date') ?: null,
            'purchase_cost'   => $purchaseCost,
            'warranty_expiry' => $this->postData('warranty_expiry') ?: null,
            'condition'       => $this->postData('condition', 'Good'),
            'status'          => $this->postData('status', 'Available'),
            'location'        => trim($this->postData('location', '')) ?: null,
            'project_id'      => $this->postData('project_id') ? (int)$this->postData('project_id') : null,
            'notes'           => trim($this->postData('notes', '')) ?: null,
        ];

        $sql = "UPDATE assets SET 
                    name = :name,
                    category_id = :category_id,
                    serial_number = :serial_number,
                    purchase_date = :purchase_date,
                    purchase_cost = :purchase_cost,
                    warranty_expiry = :warranty_expiry,
                    `condition` = :condition,
                    `status` = :status,
                    location = :location,
                    project_id = :project_id,
                    notes = :notes
                WHERE id = :id";

        $db = Database::getInstance();
        if ($db->query($sql, $data)) {
            Session::setFlash('success', 'Cập nhật tài sản thành công.');
            $this->redirect('asset/show/' . $id);
        } else {
            Session::setFlash('error', 'Lỗi khi cập nhật tài sản.');
            $this->redirect('asset/edit/' . $id);
        }
    }

    /**
     * Xóa tài sản
     */
    public function delete(int $id): void
    {
        $this->checkPermission('asset.delete');

        $db = Database::getInstance();
        // Kiểm tra xem có đang được cấp phát không
        $db->query("SELECT id FROM asset_assignments WHERE asset_id = :id AND return_date IS NULL LIMIT 1", ['id' => $id]);
        if ($db->fetch()) {
            Session::setFlash('error', 'Không thể xóa tài sản đang được cấp phát cho nhân viên. Hãy thu hồi tài sản trước.');
            $this->redirect('asset/show/' . $id);
            return;
        }

        $db->query("DELETE FROM assets WHERE id = :id", ['id' => $id]);
        Session::setFlash('success', 'Đã xóa tài sản khỏi hệ thống.');
        $this->redirect('asset');
    }

    /**
     * Bàn giao tài sản cho nhân viên
     */
    public function assign(int $id): void
    {
        $this->checkPermission('asset.assign');

        if (!$this->isPost()) {
            $this->redirect('asset/show/' . $id);
            return;
        }

        $employeeId = (int)$this->postData('employee_id');
        $assignedDate = $this->postData('assigned_date', date('Y-m-d'));
        $conditionOnAssign = $this->postData('condition_on_assign', 'Good');
        $notes = trim($this->postData('notes', ''));

        if (empty($employeeId)) {
            Session::setFlash('error', 'Vui lòng chọn nhân viên tiếp nhận bàn giao.');
            $this->redirect('asset/show/' . $id);
            return;
        }

        $assetModel = $this->model('Asset');
        $result = $assetModel->assignAsset([
            'asset_id'            => $id,
            'employee_id'         => $employeeId,
            'assigned_date'       => $assignedDate,
            'assigned_by'         => Session::userId(),
            'condition_on_assign' => $conditionOnAssign,
            'notes'               => $notes,
        ]);

        if ($result) {
            Session::setFlash('success', 'Bàn giao tài sản cho nhân viên thành công.');
        } else {
            Session::setFlash('error', 'Bàn giao thất bại. Tài sản này có thể đang ở trạng thái không khả dụng.');
        }

        $this->redirect('asset/show/' . $id);
    }

    /**
     * Thu hồi tài sản từ nhân viên về kho
     */
    public function return(int $id): void
    {
        $this->checkPermission('asset.assign');

        if (!$this->isPost()) {
            $this->redirect('asset/show/' . $id);
            return;
        }

        $assignmentId = (int)$this->postData('assignment_id');
        $returnDate = $this->postData('return_date', date('Y-m-d'));
        $conditionOnReturn = $this->postData('condition_on_return', 'Good');
        $notes = trim($this->postData('notes', ''));

        if (empty($assignmentId)) {
            Session::setFlash('error', 'Thông tin đợt bàn giao không hợp lệ.');
            $this->redirect('asset/show/' . $id);
            return;
        }

        $assetModel = $this->model('Asset');
        $result = $assetModel->returnAsset($assignmentId, [
            'return_date'         => $returnDate,
            'returned_to'         => Session::userId(),
            'condition_on_return' => $conditionOnReturn,
            'notes'               => $notes,
        ]);

        if ($result) {
            Session::setFlash('success', 'Đã hoàn tất thủ tục thu hồi tài sản về kho công ty.');
        } else {
            Session::setFlash('error', 'Thu hồi tài sản thất bại. Vui lòng thử lại.');
        }

        $this->redirect('asset/show/' . $id);
    }

    /**
     * Quản lý Danh mục tài sản (Categories CRUD)
     */
    public function categories(): void
    {
        $this->checkPermission('asset.create');

        $assetModel = $this->model('Asset');
        $categories = $assetModel->getCategories();

        $this->view('layouts/header', ['pageTitle' => 'Danh mục Loại Tài sản']);
        $this->view('asset/categories', [
            'categories' => $categories,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * Thêm / Cập nhật Danh mục tài sản
     */
    public function saveCategory(): void
    {
        $this->checkPermission('asset.create');

        if (!$this->isPost()) {
            $this->redirect('asset/categories');
            return;
        }

        $id = $this->postData('id') ? (int)$this->postData('id') : null;
        $name = trim($this->postData('name', ''));
        $code = trim($this->postData('category_code', ''));
        $icon = trim($this->postData('icon', 'fas fa-box'));
        $status = $this->postData('status', 'Active');
        $description = trim($this->postData('description', ''));

        if (empty($name) || empty($code)) {
            Session::setFlash('error', 'Tên loại và Mã loại không được để trống.');
            $this->redirect('asset/categories');
            return;
        }

        $assetModel = $this->model('Asset');
        $res = $assetModel->saveCategory([
            'category_code' => strtoupper($code),
            'name'          => $name,
            'description'   => $description,
            'icon'          => $icon,
            'status'        => $status,
        ], $id);

        if ($res) {
            Session::setFlash('success', 'Lưu danh mục loại tài sản thành công.');
        } else {
            Session::setFlash('error', 'Lỗi khi lưu danh mục loại tài sản.');
        }

        $this->redirect('asset/categories');
    }

    /**
     * Xóa danh mục tài sản
     */
    public function deleteCategory(int $id): void
    {
        $this->checkPermission('asset.delete');

        $assetModel = $this->model('Asset');
        if ($assetModel->deleteCategory($id)) {
            Session::setFlash('success', 'Xóa danh mục loại tài sản thành công.');
        } else {
            Session::setFlash('error', 'Không thể xóa danh mục đang có tài sản trực thuộc. Vui lòng chuyển hoặc xóa tài sản trước.');
        }

        $this->redirect('asset/categories');
    }

    /**
     * Báo cáo kiểm kê tài sản (BI & Inventory Report)
     */
    public function report(): void
    {
        $this->checkPermission('asset.report');

        $assetModel = $this->model('Asset');
        $reportData = $assetModel->getReportStats();

        $this->view('layouts/header', ['pageTitle' => 'Báo cáo Kiểm kê Tài sản Công ty']);
        $this->view('asset/report', $reportData);
        $this->view('layouts/footer');
    }

    /**
     * Danh sách tài sản mà một nhân viên cụ thể đang giữ
     */
    public function employeeAssets(int $employeeId): void
    {
        $this->checkPermission('asset.view');

        $assetModel = $this->model('Asset');
        $assignments = $assetModel->getAssignmentsByEmployee($employeeId);

        $employeeModel = $this->model('Employee');
        $employee = $employeeModel->find($employeeId);

        if (!$employee) {
            Session::setFlash('error', 'Nhân viên không tồn tại.');
            $this->redirect('asset');
            return;
        }

        $this->view('layouts/header', ['pageTitle' => 'Tài sản giữ bởi ' . $employee->full_name]);
        $this->view('asset/employee_assets', [
            'employee'    => $employee,
            'assignments' => $assignments,
        ]);
        $this->view('layouts/footer');
    }

    /**
     * In Biên bản bàn giao tài sản chuẩn A4
     */
    public function handoverReceipt(int $assignmentId): void
    {
        $this->checkPermission('asset.view');

        $assetModel = $this->model('Asset');
        $assignment = $assetModel->getAssignment($assignmentId);

        if (!$assignment) {
            Session::setFlash('error', 'Không tìm thấy thông tin bàn giao tài sản.');
            $this->redirect('asset');
            return;
        }

        // Biên bản in độc lập không kèm layout header/footer web app
        $this->view('asset/receipt', [
            'assignment' => $assignment,
        ]);
    }

    /**
     * AJAX Endpoint: Sinh mã tài sản gợi ý theo loại
     */
    public function ajaxSuggestCode(): void
    {
        $categoryId = (int)$this->getData('category_id');
        $assetModel = $this->model('Asset');
        $code = $assetModel->generateAssetCode($categoryId);

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'code' => $code]);
        exit;
    }
}
