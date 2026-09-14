<?php
/**
 * ============================================================
 *  POSUNG HRIS – Điểm vào duy nhất (Entry Point)
 * ============================================================
 *  Mọi request HTTP đều được .htaccess chuyển hướng về file này.
 *  Thứ tự nạp:
 *    1. Config     → Hằng số cấu hình (DB, URL, ...)
 *    2. Core       → Database, Session, Controller, App
 *    3. Session    → Khởi tạo phiên làm việc
 *    4. App        → Phân tích URL và gọi Controller tương ứng
 * ============================================================
 */

// ── 1. Nạp file cấu hình ───────────────────────────────────
require_once dirname(__DIR__) . '/config/config.php';

// ── 2. Nạp các lớp Core theo đúng thứ tự phụ thuộc ─────────
require_once APP_ROOT . '/core/Database.php';    // Kết nối CSDL (phải nạp đầu tiên)
require_once APP_ROOT . '/core/Session.php';     // Quản lý session & phân quyền
require_once APP_ROOT . '/core/Controller.php';  // Lớp Controller cơ sở
require_once APP_ROOT . '/models/BaseModel.php'; // Lớp Model cơ sở (CRUD chung)
require_once APP_ROOT . '/core/App.php';         // Front Controller / Router

// ── 3. Khởi tạo Session an toàn ────────────────────────────
Session::start();

// ── 4. Khởi chạy ứng dụng (Router sẽ tự điều hướng) ────────
$app = new App();
