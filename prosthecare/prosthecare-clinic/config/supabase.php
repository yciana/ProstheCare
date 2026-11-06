<?php
/**
 * ProstheCare Clinic Management System
 * Supabase Configuration & Environment Loader
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function loadEnv($path = __DIR__ . '/../.env') {
    if (!file_exists($path)) {
        return;
    }

    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if (strpos($line, '#') === 0 || empty($line)) {
            continue;
        }

        if (strpos($line, '=') !== false) {
            list($key, $value) = explode('=', $line, 2);
            $key = trim($key);
            $value = trim(trim($value), "\"'");
            
            if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
    }
}

loadEnv();

define('SUPABASE_URL', rtrim(getenv('SUPABASE_URL') ?: '', '/'));
define('SUPABASE_ANON_KEY', getenv('SUPABASE_ANON_KEY') ?: '');
define('SUPABASE_SERVICE_KEY', getenv('SUPABASE_SERVICE_KEY') ?: '');
define('CLINIC_NAME', getenv('CLINIC_NAME') ?: 'ProstheCare Management System');

function isSupabaseConfigured() {
    $url = SUPABASE_URL;
    $key = SUPABASE_ANON_KEY;

    if (empty($url) || empty($key)) {
        return false;
    }

    if (strpos($url, 'your-project-ref') !== false || strpos($key, 'your-anon-key') !== false) {
        return false;
    }

    return true;
}