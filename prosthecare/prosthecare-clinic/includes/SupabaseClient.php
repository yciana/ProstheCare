<?php
/**
 * ProstheCare Clinic Management System
 * Supabase REST & Auth Client
 */

require_once __DIR__ . '/../config/supabase.php';

class SupabaseClient {
    private $url;
    private $anonKey;
    private $currentTable = null;
    private $queryParams = [];   // list of [key, value] pairs, so the same column can be filtered twice
    private $lastError = null;

    public function __construct() {
        $this->url = SUPABASE_URL;
        $this->anonKey = SUPABASE_ANON_KEY;
    }

    public function isLive() {
        return isSupabaseConfigured();
    }

    /**
     * Message from the last failed call (null if it succeeded). Useful for debugging.
     */
    public function getLastError() {
        return $this->lastError;
    }

    /* ------------------------------------------------------------------
     * Query builder
     * ------------------------------------------------------------------ */

    public function from($table) {
        $this->currentTable = $table;
        $this->queryParams = [];
        $this->lastError = null;
        return $this;
    }

    public function select($columns = '*') {
        return $this->addParam('select', $columns);
    }

    public function eq($column, $value)  { return $this->filter($column, 'eq', $value); }
    public function neq($column, $value) { return $this->filter($column, 'neq', $value); }
    public function gt($column, $value)  { return $this->filter($column, 'gt', $value); }
    public function gte($column, $value) { return $this->filter($column, 'gte', $value); }
    public function lt($column, $value)  { return $this->filter($column, 'lt', $value); }
    public function lte($column, $value) { return $this->filter($column, 'lte', $value); }

    public function like($column, $pattern) {
        return $this->addParam($column, 'like.' . str_replace('%', '*', $pattern));
    }

    public function ilike($column, $pattern) {
        return $this->addParam($column, 'ilike.' . str_replace('%', '*', $pattern));
    }

    public function in($column, array $values) {
        $parts = [];
        foreach ($values as $v) {
            $parts[] = '"' . str_replace(['\\', '"'], ['\\\\', '\\"'], (string)$v) . '"';
        }
        return $this->addParam($column, 'in.(' . implode(',', $parts) . ')');
    }

    public function order($column, $ascending = true) {
        $term = $column . '.' . ($ascending ? 'asc' : 'desc');
        foreach ($this->queryParams as $i => $param) {
            if ($param[0] === 'order') {
                $this->queryParams[$i][1] .= ',' . $term;   // multiple order() calls stack
                return $this;
            }
        }
        return $this->addParam('order', $term);
    }

    public function limit($count) {
        return $this->addParam('limit', max(0, (int)$count));
    }

    public function offset($count) {
        return $this->addParam('offset', max(0, (int)$count));
    }

    /* ------------------------------------------------------------------
     * Data operations
     * ------------------------------------------------------------------ */

    public function get() {
        if (!$this->guard()) {
            return [];
        }

        $response = $this->restRequest('GET', $this->tablePath() . $this->buildQueryString());
        if (!$this->isSuccess($response)) {
            return [];
        }

        return is_array($response['data']) ? $response['data'] : [];
    }

    /**
     * @return array|true|false Inserted rows (or true), false on failure.
     */
    public function insert($data) {
        if (!$this->guard()) {
            return false;
        }

        $response = $this->restRequest('POST', $this->tablePath(), $data, ['Prefer: return=representation']);
        if (!$this->isSuccess($response)) {
            return false;
        }

        return !empty($response['data']) ? $response['data'] : true;
    }

    /**
     * Returns false on failure OR when no row matched (no match, or blocked by RLS).
     */
    public function update($data, $matchColumn, $matchValue) {
        if (!$this->guard() || $matchColumn === null || $matchColumn === '') {
            return false;
        }

        $path = $this->tablePath() . '?' . rawurlencode($matchColumn) . '=eq.' . rawurlencode((string)$matchValue);
        $response = $this->restRequest('PATCH', $path, $data, ['Prefer: return=representation']);

        if (!$this->isSuccess($response)) {
            return false;
        }

        if (is_array($response['data']) && count($response['data']) === 0) {
            $this->lastError = 'No rows were updated (nothing matched, or row-level security blocked it).';
            return false;
        }

        return true;
    }

    /**
     * Returns false on failure OR when no row was deleted (no match, or blocked by RLS).
     */
    public function delete($matchColumn, $matchValue) {
        if (!$this->guard() || $matchColumn === null || $matchColumn === '') {
            return false;
        }

        $path = $this->tablePath() . '?' . rawurlencode($matchColumn) . '=eq.' . rawurlencode((string)$matchValue);
        $response = $this->restRequest('DELETE', $path, null, ['Prefer: return=representation']);

        if (!$this->isSuccess($response)) {
            return false;
        }

        if (is_array($response['data']) && count($response['data']) === 0) {
            $this->lastError = 'No rows were deleted (nothing matched, or row-level security blocked it).';
            return false;
        }

        return true;
    }

    /* ------------------------------------------------------------------
     * Auth
     * ------------------------------------------------------------------ */

    public function signIn($email, $password) {
        if (!$this->isLive()) {
            return [
                'success' => false,
                'error' => 'Supabase is not configured. Please add SUPABASE_URL and SUPABASE_ANON_KEY to your environment.'
            ];
        }

        $response = $this->authRequest('token?grant_type=password', [
            'email' => trim($email),
            'password' => $password
        ]);

        $data = $response['data'];
        if ($response['status'] === 200 && is_array($data) && isset($data['access_token'])) {
            $user = $data['user'] ?? [];
            if (empty($user['id'])) {
                return ['success' => false, 'error' => 'Unexpected response from the authentication server.'];
            }

            // Prevent session fixation
            if (session_status() === PHP_SESSION_ACTIVE) {
                session_regenerate_id(true);
            }

            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'] ?? $email,
                'name' => $user['user_metadata']['full_name'] ?? explode('@', $email)[0],
                // Real role is loaded from the profiles table right after login (see below).
                // Never trust user_metadata for roles: users can edit it themselves.
                'role' => 'Client',
                'token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'] ?? null,
                'expires_at' => isset($data['expires_at'])
                    ? (int)$data['expires_at']
                    : time() + (int)($data['expires_in'] ?? 3600),
            ];

            // Load the real role/name from the profiles table (RLS lets users read their own row).
            $profile = $this->fetchProfile($user['id']);
            if ($profile) {
                $_SESSION['user']['role'] = $profile['role'] ?? 'Client';
                if (!empty($profile['full_name'])) {
                    $_SESSION['user']['name'] = $profile['full_name'];
                }
            }

            return ['success' => true, 'user' => $_SESSION['user']];
        }

        return ['success' => false, 'error' => $this->extractError($response, 'Invalid email or password.')];
    }

    public function signUp($email, $password, $metadata = []) {
        if (!$this->isLive()) {
            return ['success' => false, 'error' => 'Supabase is not configured.'];
        }

        // Never let the client choose its own role at signup.
        unset($metadata['role']);

        $response = $this->authRequest('signup', [
            'email' => trim($email),
            'password' => $password,
            'data' => (object)$metadata   // keeps "data" as {} instead of [] when empty
        ]);

        if ($response['status'] === 200 || $response['status'] === 201) {
            $userData = $response['data']['user'] ?? $response['data'];

            // With email confirmation on, Supabase answers 200 for an already-registered
            // email but returns a user with no identities.
            if (is_array($userData) && array_key_exists('identities', $userData) && empty($userData['identities'])) {
                return ['success' => false, 'error' => 'An account with this email already exists.'];
            }

            return [
                'success' => true,
                'message' => 'Account successfully registered in Supabase!',
                'user' => $userData
            ];
        }

        return ['success' => false, 'error' => $this->extractError($response, 'Registration failed.')];
    }

    public function resetPassword($email, $redirectTo = null) {
        if (!$this->isLive()) {
            return ['success' => false, 'error' => 'Supabase is not configured.'];
        }

        $path = 'recover';
        if ($redirectTo) {
            $path .= '?redirect_to=' . rawurlencode($redirectTo);
        }

        $response = $this->authRequest($path, ['email' => trim($email)]);

        if ($response['status'] === 200) {
            return ['success' => true, 'message' => 'Password reset instructions sent.'];
        }

        return ['success' => false, 'error' => $this->extractError($response, 'Unable to send reset email.')];
    }

    public function signOut() {
        $token = $_SESSION['user']['token'] ?? null;

        if ($this->isLive() && $token) {
            $this->authRequest('logout', new stdClass(), 'POST', $token);
        }

        unset($_SESSION['user']);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        return true;
    }

    /* ------------------------------------------------------------------
     * Internals
     * ------------------------------------------------------------------ */

    private function fetchProfile($userId) {
        $response = $this->restRequest(
            'GET',
            'profiles?select=role,full_name&limit=1&user_id=eq.' . rawurlencode((string)$userId)
        );

        if ($this->isSuccess($response) && is_array($response['data']) && isset($response['data'][0])) {
            return $response['data'][0];
        }
        return null;
    }

    private function addParam($key, $value) {
        $this->queryParams[] = [$key, (string)$value];
        return $this;
    }

    private function filter($column, $op, $value) {
        if ($value === null) {
            $expr = ($op === 'neq') ? 'not.is.null' : 'is.null';
        } else {
            if (is_bool($value)) {
                $value = $value ? 'true' : 'false';
            }
            $expr = $op . '.' . $value;
        }
        return $this->addParam($column, $expr);
    }

    private function buildQueryString() {
        if (empty($this->queryParams)) {
            return '';
        }
        $pairs = [];
        foreach ($this->queryParams as $param) {
            $pairs[] = rawurlencode($param[0]) . '=' . rawurlencode($param[1]);
        }
        return '?' . implode('&', $pairs);
    }

    private function tablePath() {
        return rawurlencode((string)$this->currentTable);
    }

    private function guard() {
        $this->lastError = null;

        if (!$this->isLive()) {
            $this->lastError = 'Supabase is not configured.';
            return false;
        }
        if ($this->currentTable === null || $this->currentTable === '') {
            $this->lastError = 'No table selected. Call from() first.';
            return false;
        }
        return true;
    }

    private function isSuccess($response) {
        return $response['status'] >= 200 && $response['status'] < 300;
    }

    private function extractError($response, $fallback) {
        if (($response['status'] ?? 0) === 0 && !empty($response['error'])) {
            return 'Could not reach Supabase: ' . $response['error'];
        }

        $data = $response['data'] ?? null;
        if (is_array($data)) {
            foreach (['error_description', 'msg', 'message', 'error'] as $key) {
                if (!empty($data[$key]) && is_string($data[$key])) {
                    return $data[$key];
                }
            }
        }
        return $fallback;
    }

    private function getAccessToken() {
        $user = $_SESSION['user'] ?? null;
        if (!$user || empty($user['token'])) {
            return null;
        }

        // Access tokens expire (1 hour by default); refresh shortly before that.
        if (!empty($user['expires_at']) && ($user['expires_at'] - 30) <= time()) {
            $this->refreshSession();
        }

        return $_SESSION['user']['token'] ?? null;
    }

    /**
     * Swap the refresh token for a new access token.
     * Only logs the user out when Supabase rejects the refresh token; a network error keeps the session.
     */
    private function refreshSession() {
        $refreshToken = $_SESSION['user']['refresh_token'] ?? null;
        if (!$refreshToken || !$this->isLive()) {
            return false;
        }

        $response = $this->authRequest('token?grant_type=refresh_token', ['refresh_token' => $refreshToken]);
        $data = $response['data'];

        if ($response['status'] === 200 && is_array($data) && isset($data['access_token'])) {
            $_SESSION['user']['token'] = $data['access_token'];
            $_SESSION['user']['refresh_token'] = $data['refresh_token'] ?? $refreshToken;
            $_SESSION['user']['expires_at'] = isset($data['expires_at'])
                ? (int)$data['expires_at']
                : time() + (int)($data['expires_in'] ?? 3600);
            return true;
        }

        if (in_array($response['status'], [400, 401, 403], true)) {
            unset($_SESSION['user']);
        }
        return false;
    }

    private function authRequest($path, $payload, $method = 'POST', $token = null) {
        $headers = [
            'apikey: ' . $this->anonKey,
            'Content-Type: application/json',
            'Accept: application/json'
        ];
        if ($token) {
            $headers[] = 'Authorization: Bearer ' . $token;
        }

        $body = json_encode($payload);
        if ($body === false) {
            return ['status' => 0, 'error' => 'Could not encode request data as JSON.', 'data' => null];
        }

        return $this->executeCurl($this->url . '/auth/v1/' . $path, $method, $body, $headers);
    }

    private function restRequest($method, $path, $payload = null, $extraHeaders = []) {
        $body = null;
        if ($payload !== null) {
            $body = json_encode($payload);
            if ($body === false) {
                $this->lastError = 'Could not encode request data as JSON.';
                return ['status' => 0, 'error' => $this->lastError, 'data' => null];
            }
        }

        $attempt = 0;
        do {
            $token = $this->getAccessToken();
            $headers = array_merge([
                'apikey: ' . $this->anonKey,
                'Authorization: Bearer ' . ($token ?: $this->anonKey),
                'Content-Type: application/json',
                'Accept: application/json'
            ], $extraHeaders);

            $response = $this->executeCurl($this->url . '/rest/v1/' . $path, $method, $body, $headers);

            // Token rejected (e.g. expired mid-request): refresh once and retry.
            $retry = ($response['status'] === 401 && $token && $attempt === 0 && $this->refreshSession());
            $attempt++;
        } while ($retry);

        $this->lastError = $this->isSuccess($response)
            ? null
            : $this->extractError($response, 'Request failed with HTTP status ' . $response['status'] . '.');

        return $response;
    }

    private function executeCurl($url, $method = 'GET', $body = null, $headers = []) {
        $ch = curl_init($url);
        if ($ch === false) {
            return ['status' => 0, 'error' => 'Could not initialise cURL.', 'data' => null];
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_TIMEOUT        => 20,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_SSL_VERIFYHOST => 2,
            CURLOPT_FOLLOWLOCATION => false,
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $rawResponse = curl_exec($ch);
        $statusCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        if (PHP_VERSION_ID < 80000) {
            curl_close($ch);
        }

        if ($rawResponse === false || $curlError !== '') {
            return ['status' => 0, 'error' => $curlError !== '' ? $curlError : 'Unknown cURL error', 'data' => null];
        }

        if ($rawResponse === '') {
            return ['status' => $statusCode, 'data' => null];
        }

        $decoded = json_decode($rawResponse, true);
        return [
            'status' => $statusCode,
            'data' => (json_last_error() === JSON_ERROR_NONE) ? $decoded : $rawResponse
        ];
    }
}