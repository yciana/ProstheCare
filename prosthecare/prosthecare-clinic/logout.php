<?php
/**
 * ProstheCare Clinic Management System - Logout
 */

require_once __DIR__ . '/config/supabase.php';

// Unset session
$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Clear persistence cookie
setcookie('prosthecare_user', '', time() - 3600, '/');

session_destroy();

header('Location: index.php');
exit;
