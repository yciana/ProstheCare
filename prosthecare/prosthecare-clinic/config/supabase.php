<?php
/**
 * ProstheCare Clinic Management System
 * Production Supabase Configuration
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Load environment variables from .env file for local development
 */
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

// Load .env if present
loadEnv();

// Fetch credentials from environment (supports local .env and Vercel Environment Variables)
$supabaseUrl = getenv('SUPABASE_URL') ?: (isset($_SERVER['SUPABASE_URL']) ? $_SERVER['SUPABASE_URL'] : '');
$supabaseAnonKey = getenv('SUPABASE_ANON_KEY') ?: (isset($_SERVER['SUPABASE_ANON_KEY']) ? $_SERVER['SUPABASE_ANON_KEY'] : '');
$supabaseServiceKey = getenv('SUPABASE_SERVICE_KEY') ?: (isset($_SERVER['SUPABASE_SERVICE_KEY']) ? $_SERVER['SUPABASE_SERVICE_KEY'] : '');
$clinicName = getenv('CLINIC_NAME') ?: 'ProstheCare Management System';

define('SUPABASE_URL', rtrim($supabaseUrl, '/'));
define('SUPABASE_ANON_KEY', $supabaseAnonKey);
define('SUPABASE_SERVICE_KEY', $supabaseServiceKey);
define('CLINIC_NAME', $clinicName);

/**
 * Validates whether Supabase credentials are configured
 */
function isSupabaseConfigured() {
    return !empty(SUPABASE_URL) && !empty(SUPABASE_ANON_KEY);
}
