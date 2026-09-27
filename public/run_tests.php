<?php
// Mock server environment
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SCRIPT_NAME'] = '/index.php';

require_once 'config/config.php';
require_once 'app/core/Database.php';
require_once 'app/core/Session.php';
require_once 'app/core/Controller.php';
require_once 'app/models/BaseModel.php';

// Mock Session
Session::start();
$_SESSION['user_id'] = 1;
$_SESSION['username'] = 'admin';
$_SESSION['role_id'] = 1;
$_SESSION['role_code'] = 'super_admin';

// Define test routes
$routes = [
    'dashboard',
    'employee',
    'timesheet',
    'payroll',
    'report',
    'ai'
];

ob_start();
$success = 0;
$failed = 0;

foreach ($routes as $route) {
    try {
        $_GET['_route'] = $route;
        // Output buffering to catch view rendering
        ob_start();
        
        require_once 'app/core/App.php';
        $app = new App();
        
        $output = ob_get_clean();
        
        if (strpos($output, 'Fatal error') !== false || strpos($output, 'Parse error') !== false) {
            echo "[FAILED] $route\n";
            $failed++;
        } else {
            echo "[OK] $route\n";
            $success++;
        }
    } catch (Exception $e) {
        ob_end_clean();
        echo "[FAILED] $route : " . $e->getMessage() . "\n";
        $failed++;
    } catch (Error $e) {
        ob_end_clean();
        echo "[FAILED] $route : " . $e->getMessage() . "\n";
        $failed++;
    }
}
ob_end_clean();

echo "\nTest Results: $success Passed, $failed Failed.\n";
