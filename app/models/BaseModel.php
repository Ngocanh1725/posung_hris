<?php
/**
 * ============================================================
 *  POSUNG HRIS – Base Model
 * ============================================================
 *  Cung cấp các hàm CRUD cơ bản dùng chung cho các Model con.
 * ============================================================
 */

abstract class BaseModel
{
    /** @var Database Đối tượng kết nối CSDL */
    protected Database $db;

    /** @var string Tên bảng (lớp con phải định nghĩa) */
    protected string $table;

    /** @var string Khoá chính (mặc định là 'id') */
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    // ── READ ────────────────────────────────────────────────

    /**
     * Tìm 1 bản ghi theo khoá chính.
     */
    public function find(int $id): ?object
    {
        $this->db->query(
            "SELECT * FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id LIMIT 1",
            ['id' => $id]
        );
        $result = $this->db->fetch();
        return $result ? (object)$result : null;
    }

    /**
     * Lấy tất cả bản ghi.
     */
    public function all(string $orderBy = 'id ASC'): array
    {
        $this->db->query("SELECT * FROM `{$this->table}` ORDER BY {$orderBy}");
        return $this->db->fetchAll();
    }

    /**
     * Lấy danh sách bản ghi theo điều kiện.
     */
    public function where(array $conditions, string $orderBy = 'id ASC'): array
    {
        $clauses = [];
        $params  = [];
        foreach ($conditions as $col => $val) {
            $clauses[] = "`{$col}` = :{$col}";
            $params[$col] = $val;
        }
        $sql = "SELECT * FROM `{$this->table}` WHERE "
             . implode(' AND ', $clauses)
             . " ORDER BY {$orderBy}";

        $this->db->query($sql, $params);
        return $this->db->fetchAll();
    }

    /**
     * Đếm số bản ghi.
     */
    public function count(array $conditions = []): int
    {
        if (empty($conditions)) {
            $this->db->query("SELECT COUNT(*) AS cnt FROM `{$this->table}`");
            $row = $this->db->fetch();
        } else {
            $clauses = [];
            $params  = [];
            foreach ($conditions as $col => $val) {
                $clauses[] = "`{$col}` = :{$col}";
                $params[$col] = $val;
            }
            $this->db->query(
                "SELECT COUNT(*) AS cnt FROM `{$this->table}` WHERE " . implode(' AND ', $clauses),
                $params
            );
            $row = $this->db->fetch();
        }
        return (int) ($row['cnt'] ?? 0);
    }

    // ── CREATE ──────────────────────────────────────────────

    /**
     * Thêm bản ghi mới.
     *
     * @param  array $data  Mảng ['tên_cột' => 'giá_trị']
     * @return int          ID vừa thêm
     */
    public function create(array $data): int
    {
        $columns = array_keys($data);
        $holders = array_map(fn($c) => ":{$c}", $columns);

        $sql = sprintf(
            "INSERT INTO `%s` (`%s`) VALUES (%s)",
            $this->table,
            implode('`, `', $columns),
            implode(', ', $holders)
        );

        $this->db->query($sql, $data);
        return (int) $this->db->lastInsertId();
    }

    // ── UPDATE ──────────────────────────────────────────────

    /**
     * Cập nhật bản ghi theo ID.
     */
    public function update(int $id, array $data): bool
    {
        $sets = [];
        foreach (array_keys($data) as $col) {
            $sets[] = "`{$col}` = :{$col}";
        }
        $data[$this->primaryKey] = $id;

        $sql = sprintf(
            "UPDATE `%s` SET %s WHERE `%s` = :%s",
            $this->table,
            implode(', ', $sets),
            $this->primaryKey,
            $this->primaryKey
        );

        $this->db->query($sql, $data);
        return true;
    }

    // ── DELETE ──────────────────────────────────────────────

    /**
     * Xoá bản ghi theo ID.
     */
    public function delete(int $id): bool
    {
        $this->db->query(
            "DELETE FROM `{$this->table}` WHERE `{$this->primaryKey}` = :id",
            ['id' => $id]
        );
        return true;
    }

    // ── Pagination ──────────────────────────────────────────

    /**
     * Phân trang dữ liệu.
     */
    public function paginate(int $page = 1, int $perPage = 20, string $orderBy = 'id DESC', array $conditions = []): object
    {
        $page    = max(1, $page);
        $offset  = ($page - 1) * $perPage;

        $where   = '';
        $params  = [];
        if (!empty($conditions)) {
            $clauses = [];
            foreach ($conditions as $col => $val) {
                $clauses[] = "`{$col}` = :{$col}";
                $params[$col] = $val;
            }
            $where = 'WHERE ' . implode(' AND ', $clauses);
        }

        $this->db->query("SELECT COUNT(*) AS cnt FROM `{$this->table}` {$where}", $params);
        $totalRow = $this->db->fetch();
        $total = (int) ($totalRow['cnt'] ?? 0);

        $this->db->query("SELECT * FROM `{$this->table}` {$where} ORDER BY {$orderBy} LIMIT {$perPage} OFFSET {$offset}", $params);
        $data = $this->db->fetchAll();

        return (object) [
            'data'       => $data,
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => (int) ceil($total / $perPage),
        ];
    }
}
