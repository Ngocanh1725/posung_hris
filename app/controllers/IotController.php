<?php
class IotController extends Controller
{
    /**
     * API Endpoint nhận data từ Máy chấm công (FaceID/AI Camera)
     * Method: POST
     * URL: /iot/attendance
     */
    public function attendance(): void
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
            return;
        }

        $input = file_get_contents("php://input");
        $data = json_decode($input, true);

        if (!$data || !isset($data['emp_code']) || !isset($data['check_time'])) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid data format']);
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'];
        $code = $data['emp_code'];
        $time = $data['check_time'];
        $confidence = $data['confidence'] ?? 99.00;

        $db = Database::getInstance();
        $db->query("INSERT INTO attendance_logs (emp_code, device_ip, check_time, confidence) VALUES (:c, :ip, :t, :conf)", [
            'c' => $code,
            'ip' => $ip,
            't' => $time,
            'conf' => $confidence
        ]);

        echo json_encode(['status' => 'success', 'message' => 'Log saved']);
    }

    /**
     * API Endpoint lấy dữ liệu Real-time (Cho Dashboard)
     */
    public function realtime(): void
    {
        header('Content-Type: application/json');
        
        $db = Database::getInstance();
        $today = date('Y-m-d');
        
        // Count unique employees who checked in today
        $db->query("SELECT COUNT(DISTINCT emp_code) as present FROM attendance_logs WHERE DATE(check_time) = :td", ['td' => $today]);
        $present = $db->fetch()['present'];

        // Get total workforce (employees + active sub workers)
        $db->query("SELECT COUNT(id) as total FROM employees WHERE status = 'Active'");
        $totalEmps = $db->fetch()['total'];

        $db->query("SELECT COUNT(id) as total FROM sub_workers WHERE is_active = 1");
        $totalSubs = $db->fetch()['total'];

        $totalForce = $totalEmps + $totalSubs;

        echo json_encode([
            'status' => 'success',
            'data' => [
                'total_workforce' => $totalForce,
                'present_now' => $present,
                'absent' => $totalForce - $present,
                'ratio' => $totalForce > 0 ? round(($present / $totalForce) * 100, 1) : 0
            ]
        ]);
    }
}
