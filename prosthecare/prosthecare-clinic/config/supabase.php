<?php
/**
 * ProstheCare Clinic Management System
 * Supabase Configuration
 */

/**
 * Start the session with safer cookie settings.
 */
if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https'); // behind Vercel / proxies

    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

/**
 * Load environment variables from a .env file (local development only).
 * Variables already set in the real environment always win.
 */
if (!function_exists('loadEnv')) {
    function loadEnv($path = __DIR__ . '/../.env') {
        if (!is_readable($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || $line[0] === '#') {
                continue;
            }

            if (strpos($line, 'export ') === 0) {
                $line = ltrim(substr($line, 7));
            }

            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }

            $key   = trim(substr($line, 0, $pos));
            $value = trim(trim(substr($line, $pos + 1)), "\"'");

            if ($key === '') {
                continue;
            }

            if (getenv($key) !== false || isset($_ENV[$key]) || isset($_SERVER[$key])) {
                continue;
            }

            putenv("$key=$value");
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
        }
    }
}

/**
 * Read a setting from the environment (getenv, $_ENV, $_SERVER), first non-empty wins.
 */
if (!function_exists('supabaseEnv')) {
    function supabaseEnv($key, $default = '') {
        $value = getenv($key);
        if ($value !== false && $value !== '') {
            return trim($value);
        }
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') {
            return trim($_ENV[$key]);
        }
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') {
            return trim($_SERVER[$key]);
        }
        return $default;
    }
}

// Load .env if present
loadEnv();

// Credentials (works with a local .env and with Vercel Environment Variables)
$supabaseUrl = supabaseEnv('SUPABASE_URL');
// People often paste the REST/Auth URL by mistake; keep only the project root.
$supabaseUrl = preg_replace('#/(rest|auth)/v1/?$#', '', rtrim($supabaseUrl, '/'));

$supabaseAnonKey    = supabaseEnv('SUPABASE_ANON_KEY');
// Supabase's own docs call this SUPABASE_SERVICE_ROLE_KEY, so accept both names.
$supabaseServiceKey = supabaseEnv('SUPABASE_SERVICE_KEY', supabaseEnv('SUPABASE_SERVICE_ROLE_KEY'));
$clinicName         = supabaseEnv('CLINIC_NAME', 'ProstheCare Management System');

if (!defined('SUPABASE_URL'))         define('SUPABASE_URL', $supabaseUrl);
if (!defined('SUPABASE_ANON_KEY'))    define('SUPABASE_ANON_KEY', $supabaseAnonKey);
if (!defined('SUPABASE_SERVICE_KEY')) define('SUPABASE_SERVICE_KEY', $supabaseServiceKey);
if (!defined('CLINIC_NAME'))          define('CLINIC_NAME', $clinicName);

/**
 * Validates whether Supabase credentials are configured
 */
if (!function_exists('isSupabaseConfigured')) {
    function isSupabaseConfigured() {
        return SUPABASE_URL !== ''
            && filter_var(SUPABASE_URL, FILTER_VALIDATE_URL) !== false
            && SUPABASE_ANON_KEY !== '';
    }
}