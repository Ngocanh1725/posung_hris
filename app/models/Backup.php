<?php
/**
 * ============================================================
 *  POSUNG HRIS – Model Backup (Sao lưu & Phục hồi Cơ sở dữ liệu)
 * ============================================================
 */

class Backup extends BaseModel
{
    protected string $table = 'system_backups';
    protected string $backupDir;

    public function __construct()
    {
        parent::__construct();
        $this->backupDir = ROOT_PATH . '/backups';
        if (!is_dir($this->backupDir)) {
            @mkdir($this->backupDir, 0777, true);
        }
    }

    /**
     * Lấy thư mục chứa file backup
     */
    public function getBackupDir(): string
    {
        return $this->backupDir;
    }

    /**
     * Lấy danh sách toàn bộ các bản sao lưu đã tạo
     */
    public function getBackups(): array
    {
        // Đồng bộ dữ liệu file trên đĩa với DB
        $files = glob($this->backupDir . '/*.sql');
        $fileMap = [];
        foreach ($files as $f) {
            $bn = basename($f);
            $fileMap[$bn] = [
                'filename'   => $bn,
                'file_path'  => $f,
                'file_size'  => filesize($f),
                'created_at' => date('Y-m-d H:i:s', filemtime($f)),
            ];
        }

        // Lấy metadata từ DB
        $this->db->query("SELECT * FROM system_backups ORDER BY created_at DESC");
        $dbRecords = $this->db->fetchAll();
        $dbMap = [];
        foreach ($dbRecords as $r) {
            $dbMap[$r['filename']] = $r;
        }

        $result = [];
        foreach ($fileMap as $fn => $fInfo) {
            if (isset($dbMap[$fn])) {
                $item = array_merge($fInfo, $dbMap[$fn]);
            } else {
                $item = array_merge($fInfo, [
                    'id'           => null,
                    'backup_type'  => 'Manual',
                    'creator_name' => 'System / File',
                    'tables_count' => 0,
                    'records_count'=> 0,
                    'status'       => 'Completed',
                    'notes'        => 'File sao lưu phát hiện từ đĩa',
                ]);
            }
            $result[] = $item;
        }

        // Sắp xếp bản mới nhất lên đầu
        usort($result, function($a, $b) {
            return strtotime($b['created_at']) <=> strtotime($a['created_at']);
        });

        return $result;
    }

    /**
     * Thống kê dung lượng và số bản sao lưu
     */
    public function getStats(): array
    {
        $backups = $this->getBackups();
        $totalCount = count($backups);
        $totalSize = 0;
        $latest = null;

        foreach ($backups as $idx => $b) {
            $totalSize += (int)$b['file_size'];
            if ($idx === 0) {
                $latest = $b['created_at'];
            }
        }

        return [
            'total_count' => $totalCount,
            'total_size'  => $totalSize,
            'total_size_formatted' => $this->formatBytes($totalSize),
            'latest'      => $latest,
        ];
    }

    /**
     * Tạo bản sao lưu CSDL mới (Hỗ trợ cả exec mysqldump và Native PHP Dump)
     */
    public function createBackup(string $type = 'Manual', string $notes = ''): array
    {
        $filename = 'backup_' . date('Ymd_His') . '.sql';
        $filepath = $this->backupDir . '/' . $filename;

        $tablesCount = 0;
        $recordsCount = 0;
        $success = false;

        // 1. Thử dùng mysqldump nếu khả dụng
        $mysqldumpPath = 'c:\\xampp\\mysql\\bin\\mysqldump.exe';
        if (file_exists($mysqldumpPath)) {
            $cmd = "\"{$mysqldumpPath}\" --user=" . DB_USER . " " . (!empty(DB_PASS) ? "--password=" . DB_PASS . " " : "") . "--host=" . DB_HOST . " " . DB_NAME . " > \"{$filepath}\"";
            @exec($cmd, $out, $ret);
            if ($ret === 0 && file_exists($filepath) && filesize($filepath) > 1024) {
                $success = true;
            }
        }

        // 2. Dự phòng: Native PHP SQL Dumper (Đảm bảo 100% thành công trên mọi môi trường)
        if (!$success) {
            $dumpResult = $this->nativePhpDump($filepath);
            if ($dumpResult['success']) {
                $success = true;
                $tablesCount = $dumpResult['tables_count'];
                $recordsCount = $dumpResult['records_count'];
            }
        }

        if (!$success || !file_exists($filepath)) {
            return ['success' => false, 'message' => 'Không thể tạo file sao lưu CSDL.'];
        }

        $fileSize = filesize($filepath);

        // Lưu thông tin vào system_backups
        $creatorId = Session::userId();
        $creatorName = Session::userFullName() ?? (Session::get('username') ?? 'System');

        $this->db->query(
            "INSERT INTO system_backups 
             (filename, file_path, file_size, tables_count, records_count, backup_type, created_by, creator_name, notes, status, created_at)
             VALUES (:fn, :fp, :fs, :tc, :rc, :type, :cid, :cname, :notes, 'Completed', NOW())",
            [
                'fn'    => $filename,
                'fp'    => $filepath,
                'fs'    => $fileSize,
                'tc'    => $tablesCount,
                'rc'    => $recordsCount,
                'type'  => $type,
                'cid'   => $creatorId,
                'cname' => $creatorName,
                'notes' => $notes ?: 'Sao lưu CSDL thủ công',
            ]
        );

        // Ghi Audit Log
        AuditLogger::log('backup', 'system', $filename, null, [
            'filename'  => $filename,
            'file_size' => $this->formatBytes($fileSize),
            'type'      => $type
        ], "Tạo bản sao lưu CSDL: {$filename} ({$this->formatBytes($fileSize)})");

        return [
            'success'   => true,
            'message'   => "Đã tạo bản sao lưu thành công ({$this->formatBytes($fileSize)}).",
            'filename'  => $filename,
            'file_size' => $fileSize,
        ];
    }

    /**
     * Native PHP Database Dumper (Không phụ thuộc binary mysqldump)
     */
    private function nativePhpDump(string $targetFile): array
    {
        $handle = fopen($targetFile, 'w');
        if (!$handle) {
            return ['success' => false, 'tables_count' => 0, 'records_count' => 0];
        }

        // Header SQL
        fwrite($handle, "-- ============================================================\n");
        fwrite($handle, "-- POSUNG HRIS - DATABASE BACKUP (NATIVE PHP DUMP)\n");
        fwrite($handle, "-- Thời gian tạo: " . date('Y-m-d H:i:s') . "\n");
        fwrite($handle, "-- Host: " . DB_HOST . " | Database: " . DB_NAME . "\n");
        fwrite($handle, "-- ============================================================\n\n");
        fwrite($handle, "SET NAMES utf8mb4;\n");
        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 0;\n");
        fwrite($handle, "SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';\n\n");

        $this->db->query("SHOW TABLES");
        $tables = $this->db->fetchAll();
        $tablesCount = count($tables);
        $totalRecords = 0;

        foreach ($tables as $tRow) {
            $tableName = current($tRow);

            fwrite($handle, "-- ------------------------------------------------------------\n");
            fwrite($handle, "-- Cấu trúc bảng: `{$tableName}`\n");
            fwrite($handle, "-- ------------------------------------------------------------\n");
            fwrite($handle, "DROP TABLE IF EXISTS `{$tableName}`;\n");

            // Lấy CREATE TABLE
            $this->db->query("SHOW CREATE TABLE `{$tableName}`");
            $createRow = $this->db->fetch();
            if ($createRow && isset($createRow['Create Table'])) {
                fwrite($handle, $createRow['Create Table'] . ";\n\n");
            }

            // Dump data
            $this->db->query("SELECT * FROM `{$tableName}`");
            $rows = $this->db->fetchAll();
            $rowCount = count($rows);
            $totalRecords += $rowCount;

            if ($rowCount > 0) {
                fwrite($handle, "-- Dữ liệu bảng: `{$tableName}` ({$rowCount} dòng)\n");
                
                $columns = array_keys($rows[0]);
                $colNames = implode('`, `', $columns);

                // Ghi theo từng chunk 100 rows
                $chunks = array_chunk($rows, 100);
                foreach ($chunks as $chunk) {
                    $insertValues = [];
                    foreach ($chunk as $row) {
                        $escapedValues = [];
                        foreach ($columns as $col) {
                            $val = $row[$col];
                            if ($val === null) {
                                $escapedValues[] = 'NULL';
                            } else {
                                $escapedValues[] = "'" . addslashes((string)$val) . "'";
                            }
                        }
                        $insertValues[] = "(" . implode(', ', $escapedValues) . ")";
                    }
                    fwrite($handle, "INSERT INTO `{$tableName}` (`{$colNames}`) VALUES\n" . implode(",\n", $insertValues) . ";\n");
                }
                fwrite($handle, "\n");
            }
        }

        fwrite($handle, "SET FOREIGN_KEY_CHECKS = 1;\n");
        fwrite($handle, "-- ============================================================\n");
        fwrite($handle, "-- HOÀN TẤT SAO LƯU: " . $tablesCount . " bảng, " . $totalRecords . " dòng dữ liệu.\n");
        fwrite($handle, "-- ============================================================\n");

        fclose($handle);

        return [
            'success'       => true,
            'tables_count'  => $tablesCount,
            'records_count' => $totalRecords,
        ];
    }

    /**
     * Phục hồi CSDL từ file backup đã chọn
     */
    public function restoreBackup(string $filename): array
    {
        $safeName = basename($filename);
        $filepath = $this->backupDir . '/' . $safeName;

        if (!file_exists($filepath)) {
            return ['success' => false, 'message' => 'Không tìm thấy file sao lưu trên hệ thống.'];
        }

        $success = false;

        // 1. Thử dùng mysql.exe CLI nếu có
        $mysqlCli = 'c:\\xampp\\mysql\\bin\\mysql.exe';
        if (file_exists($mysqlCli)) {
            $cmd = "\"{$mysqlCli}\" --user=" . DB_USER . " " . (!empty(DB_PASS) ? "--password=" . DB_PASS . " " : "") . "--host=" . DB_HOST . " " . DB_NAME . " < \"{$filepath}\"";
            $cmdLine = "cmd /c \"" . $cmd . "\"";
            @exec($cmdLine, $out, $ret);
            if ($ret === 0) {
                $success = true;
            }
        }

        // 2. Dự phòng: Native PHP SQL Runner
        if (!$success) {
            $success = $this->nativePhpRestore($filepath);
        }

        if (!$success) {
            return ['success' => false, 'message' => 'Lỗi trong quá trình phục hồi dữ liệu từ file SQL.'];
        }

        // Cập nhật trạng thái
        $this->db->query("UPDATE system_backups SET status = 'Restored' WHERE filename = :fn", ['fn' => $safeName]);

        // Ghi Audit Log
        AuditLogger::log('restore', 'system', $safeName, null, ['filename' => $safeName], "Phục hồi thành công CSDL từ bản sao lưu: {$safeName}");

        return [
            'success' => true,
            'message' => "Đã phục hồi Cơ sở dữ liệu thành công từ bản sao lưu {$safeName}!",
        ];
    }

    /**
     * Native PHP Restore: Đọc và thực thi file SQL
     */
    private function nativePhpRestore(string $filepath): bool
    {
        $sqlContent = file_get_contents($filepath);
        if ($sqlContent === false) return false;

        try {
            // Tắt foreign key checks
            $this->db->query("SET FOREIGN_KEY_CHECKS = 0");

            // Tách các câu lệnh theo dấu ; ở cuối dòng
            $queries = preg_split('/;\s*[\r\n]+/', $sqlContent);
            foreach ($queries as $query) {
                $query = trim($query);
                if (!empty($query) && !str_starts_with($query, '--')) {
                    $this->db->query($query);
                }
            }

            $this->db->query("SET FOREIGN_KEY_CHECKS = 1");
            return true;
        } catch (Throwable $e) {
            error_log("Native Restore Error: " . $e->getMessage());
            $this->db->query("SET FOREIGN_KEY_CHECKS = 1");
            return false;
        }
    }

    /**
     * Xóa bản sao lưu
     */
    public function deleteBackup(string $filename): bool
    {
        $safeName = basename($filename);
        $filepath = $this->backupDir . '/' . $safeName;

        if (file_exists($filepath)) {
            @unlink($filepath);
        }

        $this->db->query("DELETE FROM system_backups WHERE filename = :fn", ['fn' => $safeName]);

        AuditLogger::log('delete', 'system', $safeName, null, null, "Xóa bản sao lưu CSDL: {$safeName}");

        return true;
    }

    /**
     * Lấy cài đặt tự động sao lưu
     */
    public function getSettings(): array
    {
        $this->db->query("SELECT setting_key, setting_value FROM system_settings WHERE setting_key LIKE 'auto_backup_%'");
        $rows = $this->db->fetchAll();
        $settings = [
            'auto_backup_enabled'   => '1',
            'auto_backup_frequency' => 'daily',
            'auto_backup_time'      => '02:00',
            'auto_backup_keep_days' => '30',
        ];
        foreach ($rows as $r) {
            $settings[$r['setting_key']] = $r['setting_value'];
        }
        return $settings;
    }

    /**
     * Lưu cài đặt tự động sao lưu
     */
    public function saveSettings(array $data): bool
    {
        $keys = ['auto_backup_enabled', 'auto_backup_frequency', 'auto_backup_time', 'auto_backup_keep_days'];
        foreach ($keys as $k) {
            if (isset($data[$k])) {
                $this->db->query(
                    "INSERT INTO system_settings (setting_key, setting_value, updated_at) 
                     VALUES (:k, :v, NOW()) 
                     ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value), updated_at = NOW()",
                    ['k' => $k, 'v' => (string)$data[$k]]
                );
            }
        }

        AuditLogger::log('update', 'system', 'auto_backup', null, $data, "Cập nhật cấu hình tự động sao lưu CSDL");

        return true;
    }

    /**
     * Format dung lượng file
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
