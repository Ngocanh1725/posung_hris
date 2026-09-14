<?php
/**
 * ============================================================
 *  POSUNG HRIS – Lớp Kết nối Cơ sở dữ liệu (Singleton)
 * ============================================================
 *  Sử dụng mô hình thiết kế Singleton Pattern để đảm bảo
 *  toàn bộ ứng dụng chỉ tồn tại DUY NHẤT một kết nối PDO.
 *
 *  Cách dùng:
 *    $db = Database::getInstance();
 *    $db->query("SELECT * FROM employees WHERE status = :s", ['s' => 'Active']);
 *    $rows = $db->fetchAll();         // Lấy tất cả kết quả
 *    $row  = $db->fetch();            // Lấy 1 dòng duy nhất
 *    $cnt  = $db->rowCount();         // Số dòng bị ảnh hưởng
 * ============================================================
 */

class Database
{
    // ── Thuộc tính ──────────────────────────────────────────

    /** @var Database|null Instance duy nhất (Singleton) */
    private static ?Database $instance = null;

    /** @var PDO Đối tượng kết nối PDO */
    private PDO $pdo;

    /** @var PDOStatement|null Câu lệnh đã chuẩn bị gần nhất */
    private ?PDOStatement $stmt = null;

    // ── Constructor (private – không cho phép tạo từ bên ngoài) ─

    /**
     * Hàm khởi tạo kết nối PDO.
     * Private để buộc sử dụng qua getInstance().
     */
    private function __construct()
    {
        // Xây dựng chuỗi DSN (Data Source Name)
        $dsn = 'mysql:host=' . DB_HOST
             . ';dbname='    . DB_NAME
             . ';charset='   . DB_CHARSET;

        // Các tuỳ chọn PDO an toàn
        $options = [
            // Ném ngoại lệ khi có lỗi SQL (thay vì im lặng)
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,

            // Trả về mảng liên kết (associative array) thay vì đối tượng
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,

            // Tắt chế độ giả lập prepared statement → dùng native MySQL prepare
            // Giúp chống SQL Injection triệt để hơn
            PDO::ATTR_EMULATE_PREPARES   => false,

            // Thiết lập charset ngay khi kết nối
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES '" . DB_CHARSET . "'",
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Trong production nên ghi log thay vì hiển thị lỗi
            die('<h3>Lỗi kết nối CSDL</h3><p>' . $e->getMessage() . '</p>');
        }
    }

    // ── Singleton Pattern ───────────────────────────────────

    /**
     * Lấy instance duy nhất của Database.
     *
     * @return self
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Cấm clone đối tượng singleton.
     */
    private function __clone() {}

    /**
     * Cấm unserialize đối tượng singleton.
     */
    public function __wakeup()
    {
        throw new Exception('Không thể unserialize đối tượng Singleton Database.');
    }

    // ── Truy vấn cơ bản ────────────────────────────────────

    /**
     * Chuẩn bị và thực thi một câu truy vấn SQL có tham số.
     *
     * Ví dụ:
     *   $db->query("SELECT * FROM employees WHERE dept_id = :d", ['d' => 6]);
     *   $db->query("INSERT INTO users (username, password) VALUES (:u, :p)",
     *              ['u' => 'admin', 'p' => $hash]);
     *
     * @param  string $sql    Câu truy vấn SQL với placeholder (:tên hoặc ?)
     * @param  array  $params Mảng tham số tương ứng với placeholder
     * @return self           Trả về chính đối tượng Database (method chaining)
     */
    public function query(string $sql, array $params = []): self
    {
        try {
            // Bước 1: Chuẩn bị câu lệnh (prepare)
            $this->stmt = $this->pdo->prepare($sql);

            // Bước 2: Gắn tham số và thực thi (execute)
            $this->stmt->execute($params);
        } catch (PDOException $e) {
            // Ghi lỗi để dễ debug (development mode)
            die('<h3>Lỗi truy vấn SQL</h3>'
              . '<p><strong>SQL:</strong> ' . htmlspecialchars($sql) . '</p>'
              . '<p><strong>Chi tiết:</strong> ' . $e->getMessage() . '</p>');
        }

        return $this;
    }

    // ── Lấy kết quả ────────────────────────────────────────

    /**
     * Lấy MỘT dòng kết quả duy nhất (dạng mảng liên kết).
     *
     * Ví dụ:
     *   $user = $db->query("SELECT * FROM users WHERE id = :id", ['id' => 1])->fetch();
     *   // $user = ['id' => 1, 'username' => 'admin', ...]
     *
     * @return array|false  Mảng liên kết hoặc false nếu không có kết quả
     */
    public function fetch(): array|false
    {
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Lấy TẤT CẢ các dòng kết quả (dạng mảng chứa mảng liên kết).
     *
     * Ví dụ:
     *   $employees = $db->query("SELECT * FROM employees")->fetchAll();
     *   // $employees = [['id'=>1, 'full_name'=>'Park...'], ['id'=>2, ...], ...]
     *
     * @return array Mảng 2 chiều, mỗi phần tử là một dòng kết quả
     */
    public function fetchAll(): array
    {
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Đếm số dòng bị ảnh hưởng bởi câu truy vấn gần nhất.
     * Dùng cho INSERT, UPDATE, DELETE để biết có bao nhiêu dòng thay đổi.
     *
     * Ví dụ:
     *   $db->query("UPDATE employees SET status = 'Resigned' WHERE id = :id", ['id' => 5]);
     *   $affected = $db->rowCount(); // 1 nếu cập nhật thành công
     *
     * @return int Số dòng bị ảnh hưởng
     */
    public function rowCount(): int
    {
        return $this->stmt->rowCount();
    }

    /**
     * Lấy ID tự tăng (auto increment) của dòng vừa INSERT.
     *
     * Ví dụ:
     *   $db->query("INSERT INTO employees (...) VALUES (...)", [...]);
     *   $newId = $db->lastInsertId(); // ID của nhân viên vừa thêm
     *
     * @return string ID dạng chuỗi số
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    // ── Quản lý giao dịch (Transaction) ─────────────────────

    /**
     * Bắt đầu một giao dịch (transaction).
     * Dùng khi cần thực hiện nhiều thao tác phải thành công đồng thời.
     *
     * Ví dụ:
     *   $db->beginTransaction();
     *   try {
     *       $db->query("INSERT INTO ...", [...]);
     *       $db->query("UPDATE ...", [...]);
     *       $db->commit();        // Xác nhận tất cả
     *   } catch (Exception $e) {
     *       $db->rollBack();      // Huỷ bỏ tất cả nếu có lỗi
     *   }
     *
     * @return bool
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * Xác nhận (commit) giao dịch hiện tại.
     *
     * @return bool
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }

    /**
     * Huỷ bỏ (rollback) giao dịch hiện tại.
     * Tất cả thao tác từ lúc beginTransaction() sẽ bị huỷ.
     *
     * @return bool
     */
    public function rollBack(): bool
    {
        return $this->pdo->rollBack();
    }

    // ── Truy cập PDO gốc (dùng cho trường hợp đặc biệt) ───

    /**
     * Lấy đối tượng PDO gốc, dùng khi cần thao tác nâng cao
     * mà các hàm helper không hỗ trợ.
     *
     * @return PDO
     */
    public function getConnection(): PDO
    {
        return $this->pdo;
    }
}
