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
function loadEnv($explicitPath = null) {
    $searchPaths = $explicitPath ? [$explicitPath] : [
        __DIR__ . '/../.env',
        __DIR__ . '/../../.env',
        (getcwd() ? getcwd() . '/.env' : null),
        (getcwd() ? getcwd() . '/prosthecare-clinic/.env' : null)
    ];

    foreach ($searchPaths as $path) {
        if (!$path || !file_exists($path)) {
            continue;
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
                
                putenv("$key=$value");
                $_ENV[$key] = $value;
                $_SERVER[$key] = $value;
            }
        }
        break;
    }
}

// Load .env if present
loadEnv();

// Fetch credentials from environment
$defaultUrl = 'https://gdpzdypxospokczofxlu.supabase.co';
$defaultAnonKey = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6ImdkcHpkeXB4b3Nwb2tjem9meGx1Iiwicm9sZSI6ImFub24iLCJpYXQiOjE3OTM4MzM5MDUsImV4cCI6MjEwOTQwOTkwNX0.MehZbKa8zVtBPECMgf5PLsIqdPNSW2Do6JHsCMXgIAQ';

$rawUrl = getenv('SUPABASE_URL') 
    ?: getenv('NEXT_PUBLIC_SUPABASE_URL') 
    ?: (isset($_SERVER['SUPABASE_URL']) && !empty($_SERVER['SUPABASE_URL']) ? $_SERVER['SUPABASE_URL'] : '')
    ?: (isset($_SERVER['NEXT_PUBLIC_SUPABASE_URL']) && !empty($_SERVER['NEXT_PUBLIC_SUPABASE_URL']) ? $_SERVER['NEXT_PUBLIC_SUPABASE_URL'] : $defaultUrl);

// Strip any trailing /rest/v1 or /auth/v1 or trailing slashes so SUPABASE_URL is always the clean base domain
$cleanUrl = preg_replace('#/(rest|auth)/v1/?$#i', '', trim($rawUrl));
$supabaseUrl = rtrim($cleanUrl, '/');

$supabaseAnonKey = getenv('SUPABASE_ANON_KEY') 
    ?: getenv('NEXT_PUBLIC_SUPABASE_PUBLISHABLE_KEY') 
    ?: getenv('SUPABASE_PUBLISHABLE_KEY') 
    ?: (isset($_SERVER['SUPABASE_ANON_KEY']) && !empty($_SERVER['SUPABASE_ANON_KEY']) ? $_SERVER['SUPABASE_ANON_KEY'] : '')
    ?: (isset($_SERVER['NEXT_PUBLIC_SUPABASE_PUBLISHABLE_KEY']) && !empty($_SERVER['NEXT_PUBLIC_SUPABASE_PUBLISHABLE_KEY']) ? $_SERVER['NEXT_PUBLIC_SUPABASE_PUBLISHABLE_KEY'] : '')
    ?: (isset($_SERVER['SUPABASE_PUBLISHABLE_KEY']) && !empty($_SERVER['SUPABASE_PUBLISHABLE_KEY']) ? $_SERVER['SUPABASE_PUBLISHABLE_KEY'] : '')
    ?: $defaultAnonKey;

$supabaseServiceKey = getenv('SUPABASE_SERVICE_KEY') 
    ?: getenv('SUPABASE_SECRET_KEY')
    ?: (isset($_SERVER['SUPABASE_SERVICE_KEY']) && !empty($_SERVER['SUPABASE_SERVICE_KEY']) ? $_SERVER['SUPABASE_SERVICE_KEY'] : '')
    ?: (isset($_SERVER['SUPABASE_SECRET_KEY']) && !empty($_SERVER['SUPABASE_SECRET_KEY']) ? $_SERVER['SUPABASE_SECRET_KEY'] : '');

$clinicName = getenv('CLINIC_NAME') ?: (isset($_SERVER['CLINIC_NAME']) ? $_SERVER['CLINIC_NAME'] : 'ProstheCare Orthotics & Prosthetics Center');

if (!defined('SUPABASE_URL')) {
    define('SUPABASE_URL', $supabaseUrl);
}
if (!defined('SUPABASE_ANON_KEY')) {
    define('SUPABASE_ANON_KEY', $supabaseAnonKey);
}
if (!defined('SUPABASE_SERVICE_KEY')) {
    define('SUPABASE_SERVICE_KEY', $supabaseServiceKey);
}
if (!defined('CLINIC_NAME')) {
    define('CLINIC_NAME', $clinicName);
}

/**
 * Validates whether Supabase credentials are fully configured
 */
function isSupabaseConfigured() {
    return !empty(SUPABASE_URL) && !empty(SUPABASE_ANON_KEY);
}
