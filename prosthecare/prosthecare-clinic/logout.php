<?php
require_once __DIR__ . '/config/supabase.php';

$_SESSION = [];
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
setcookie('prosthecare_user', '', time() - 3600, '/');
session_destroy();

header('Location: index.php');
exit;