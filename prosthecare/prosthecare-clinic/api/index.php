<?php
/**
 * ProstheCare Clinic Management System
 * Production Vercel Serverless Function Router & Front Controller
 */

// Parse incoming request path
$requestUri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = ltrim($requestUri, '/');

// Base directory (project root)
$baseDir = dirname(__DIR__);

// Forbidden paths: prevent direct browser access to sensitive internal files
$forbiddenPrefixes = ['config/', 'includes/', 'supabase/', '.env', '.git', '.vscode', 'vendor/'];
foreach ($forbiddenPrefixes as $prefix) {
    if (strpos($path, $prefix) === 0) {
        http_response_code(403);
        echo "<!DOCTYPE html><html><head><title>403 Forbidden</title></head><body><h1>403 Forbidden</h1><p>Direct access to this resource is denied.</p></body></html>";
        exit;
    }
}

// Route mapping
$aliases = [
    'dashboard' => 'dashboard.php',
    'patients' => 'patients.php',
    'client-records' => 'patients.php',
    'client-record' => 'patients.php',
    'maintenance' => 'maintenance.php',
    'announcements' => 'announcements.php',
    'settings' => 'settings.php',
    'appointments' => 'appointments.php',
    'book-appointment' => 'appointments.php',
    'book-an-appointment' => 'appointments.php',
    'leg-designer' => 'designer.php',
    'designer' => 'designer.php',
    'my-requests' => 'requests.php',
    'requests' => 'requests.php',
    'notifications' => 'notifications.php',
    'logout' => 'logout.php',
    'register' => 'register.php',
    'forgot-password' => 'forgot-password.php',
    'create-admin' => 'create-admin.php',
    'setup-admin' => 'setup-admin.php',
    'test-connection' => 'test-connection.php'
];

if (empty($path) || $path === 'index' || $path === 'index.php') {
    $script = 'index.php';
} elseif (isset($aliases[$path])) {
    $script = $aliases[$path];
} else {
    // If path already ends with .php
    if (substr($path, -4) === '.php') {
        $candidate = $path;
    } else {
        $candidate = $path . '.php';
    }

    if (file_exists($baseDir . '/' . basename($candidate))) {
        $script = basename($candidate);
    } else {
        $script = null;
    }
}

if ($script) {
    $scriptFile = basename($script);
    $targetFile = $baseDir . '/' . $scriptFile;

    // Security check: ensure target file exists and is a PHP file inside baseDir
    if (file_exists($targetFile) && substr($targetFile, -4) === '.php') {
        // Set server variables to match direct script execution for headers & active tab navigation
        $_SERVER['SCRIPT_NAME'] = '/' . $scriptFile;
        $_SERVER['PHP_SELF'] = '/' . $scriptFile;

        // Switch working directory to base directory so relative includes (config/, includes/) resolve properly
        chdir($baseDir);

        require $targetFile;
        exit;
    }
}

// 404 Fallback handler
http_response_code(404);
echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1.0'><title>404 Not Found - ProstheCare</title><style>body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;text-align:center;padding:60px 20px;background:#f8fafc;color:#1e293b;}h1{color:#0d797f;margin-bottom:12px;font-size:28px;}p{font-size:16px;color:#64748b;margin-bottom:24px;}a{display:inline-block;padding:10px 20px;background:#0d797f;color:#fff;text-decoration:none;border-radius:6px;font-weight:600;}</style></head><body>";
echo "<h1>404 - Page Not Found</h1>";
echo "<p>The requested page <code>/" . htmlspecialchars($path) . "</code> does not exist on ProstheCare.</p>";
echo "<a href='/'>Return to ProstheCare Sign In</a>";
echo "</body></html>";
exit;
