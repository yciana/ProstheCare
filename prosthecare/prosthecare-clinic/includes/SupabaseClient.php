<?php
/**
 * ProstheCare Clinic Management System
 * Production Supabase REST & Auth Client (Zero Demo/Mock code)
 */

require_once __DIR__ . '/../config/supabase.php';

class SupabaseClient {
    private $url;
    private $anonKey;
    private $serviceKey;
    private $currentTable;
    private $queryParams = [];

    public function __construct() {
        $this->url = SUPABASE_URL;
        $this->anonKey = SUPABASE_ANON_KEY;
        $this->serviceKey = SUPABASE_SERVICE_KEY;
    }

    public function isLive() {
        return isSupabaseConfigured();
    }

    public function from($table) {
        $this->currentTable = $table;
        $this->queryParams = [];
        return $this;
    }

    public function select($columns = '*') {
        $this->queryParams['select'] = $columns;
        return $this;
    }

    public function eq($column, $value) {
        $this->queryParams[$column] = 'eq.' . $value;
        return $this;
    }

    public function order($column, $ascending = true) {
        $this->queryParams['order'] = $column . '.' . ($ascending ? 'asc' : 'desc');
        return $this;
    }

    public function limit($count) {
        $this->queryParams['limit'] = (int)$count;
        return $this;
    }

    public function get() {
        if (!$this->isLive()) {
            return [];
        }

        $endpoint = $this->url . '/rest/v1/' . $this->currentTable;
        if (!empty($this->queryParams)) {
            $endpoint .= '?' . http_build_query($this->queryParams);
        }

        $headers = [
            'apikey: ' . $this->anonKey,
            'Authorization: Bearer ' . ($this->getAccessToken() ?: $this->anonKey),
            'Content-Type: application/json'
        ];

        $response = $this->executeCurl($endpoint, 'GET', null, $headers);
        return is_array($response['data']) ? $response['data'] : [];
    }

    public function insert($data) {
        if (!$this->isLive()) {
            return false;
        }

        $endpoint = $this->url . '/rest/v1/' . $this->currentTable;
        $headers = [
            'apikey: ' . $this->anonKey,
            'Authorization: Bearer ' . ($this->getAccessToken() ?: $this->anonKey),
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];

        $payload = json_encode($data);
        $response = $this->executeCurl($endpoint, 'POST', $payload, $headers);
        return ($response['status'] >= 200 && $response['status'] < 300) ? ($response['data'] ?? true) : false;
    }

    public function update($data, $matchColumn, $matchValue) {
        if (!$this->isLive()) {
            return false;
        }

        $endpoint = $this->url . '/rest/v1/' . $this->currentTable . '?' . $matchColumn . '=eq.' . urlencode($matchValue);
        $headers = [
            'apikey: ' . $this->anonKey,
            'Authorization: Bearer ' . ($this->getAccessToken() ?: $this->anonKey),
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];

        $payload = json_encode($data);
        $response = $this->executeCurl($endpoint, 'PATCH', $payload, $headers);
        return ($response['status'] >= 200 && $response['status'] < 300);
    }

    public function delete($matchColumn, $matchValue) {
        if (!$this->isLive()) {
            return false;
        }

        $endpoint = $this->url . '/rest/v1/' . $this->currentTable . '?' . $matchColumn . '=eq.' . urlencode($matchValue);
        $headers = [
            'apikey: ' . $this->anonKey,
            'Authorization: Bearer ' . ($this->getAccessToken() ?: $this->anonKey),
            'Content-Type: application/json'
        ];

        $response = $this->executeCurl($endpoint, 'DELETE', null, $headers);
        return ($response['status'] >= 200 && $response['status'] < 300);
    }

    public function signIn($email, $password) {
        if (!$this->isLive()) {
            return [
                'success' => false,
                'error' => 'Supabase is not configured. Please add SUPABASE_URL and SUPABASE_ANON_KEY to your environment.'
            ];
        }

        $endpoint = $this->url . '/auth/v1/token?grant_type=password';
        $headers = [
            'apikey: ' . $this->anonKey,
            'Content-Type: application/json'
        ];

        $payload = json_encode(['email' => $email, 'password' => $password]);
        $response = $this->executeCurl($endpoint, 'POST', $payload, $headers);

        if ($response['status'] === 200 && isset($response['data']['access_token'])) {
            $user = $response['data']['user'] ?? [];
            $_SESSION['user'] = [
                'id' => $user['id'] ?? uniqid(),
                'email' => $user['email'] ?? $email,
                'name' => $user['user_metadata']['full_name'] ?? explode('@', $email)[0],
                'role' => $user['user_metadata']['role'] ?? 'Client',
                'token' => $response['data']['access_token']
            ];
            return ['success' => true, 'user' => $_SESSION['user']];
        }

        $errorMsg = $response['data']['error_description'] ?? $response['data']['msg'] ?? 'Invalid email or password.';
        return ['success' => false, 'error' => $errorMsg];
    }

    public function signUp($email, $password, $metadata = []) {
        if (!$this->isLive()) {
            return ['success' => false, 'error' => 'Supabase is not configured.'];
        }

        $endpoint = $this->url . '/auth/v1/signup';
        $headers = [
            'apikey: ' . $this->anonKey,
            'Content-Type: application/json'
        ];

        $payload = json_encode([
            'email' => $email,
            'password' => $password,
            'data' => $metadata
        ]);

        $response = $this->executeCurl($endpoint, 'POST', $payload, $headers);

        if ($response['status'] === 200 || $response['status'] === 201) {
            $userData = $response['data']['user'] ?? $response['data'];
            return [
                'success' => true,
                'message' => 'Account successfully registered in Supabase!',
                'user' => $userData
            ];
        }

        $errorMsg = $response['data']['msg'] ?? $response['data']['error_description'] ?? 'Registration failed.';
        return ['success' => false, 'error' => $errorMsg];
    }

    public function resetPassword($email) {
        if (!$this->isLive()) {
            return ['success' => false, 'error' => 'Supabase is not configured.'];
        }

        $endpoint = $this->url . '/auth/v1/recover';
        $headers = [
            'apikey: ' . $this->anonKey,
            'Content-Type: application/json'
        ];

        $payload = json_encode(['email' => $email]);
        $response = $this->executeCurl($endpoint, 'POST', $payload, $headers);

        if ($response['status'] === 200) {
            return ['success' => true, 'message' => 'Password reset instructions sent.'];
        }

        return ['success' => false, 'error' => $response['data']['msg'] ?? 'Unable to send reset email.'];
    }

    private function getAccessToken() {
        return $_SESSION['user']['token'] ?? null;
    }

    private function executeCurl($url, $method = 'GET', $body = null, $headers = []) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        $rawResponse = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['status' => 500, 'error' => $curlError, 'data' => null];
        }

        $decoded = json_decode($rawResponse, true);
        return ['status' => $statusCode, 'data' => $decoded !== null ? $decoded : $rawResponse];
    }
}
