<?php
/**
 * ProstheCare Clinic Management System
 * Production Supabase REST & Auth Client
 */

require_once __DIR__ . '/../config/supabase.php';

class SupabaseClient {
    private $url;
    private $anonKey;
    private $serviceKey;
    private $currentTable;
    private $queryParams = [];
    private $lastError = null;

    public function __construct() {
        $this->url = defined('SUPABASE_URL') ? SUPABASE_URL : '';
        $this->anonKey = defined('SUPABASE_ANON_KEY') ? SUPABASE_ANON_KEY : '';
        $this->serviceKey = defined('SUPABASE_SERVICE_KEY') ? SUPABASE_SERVICE_KEY : '';
    }

    public function isLive() {
        return isSupabaseConfigured();
    }

    public function getLastError() {
        return $this->lastError;
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
            $this->lastError = 'Supabase is not configured.';
            return [];
        }

        $endpoint = $this->url . '/rest/v1/' . $this->currentTable;
        if (!empty($this->queryParams)) {
            $endpoint .= '?' . http_build_query($this->queryParams);
        }

        $headers = [
            'apikey: ' . $this->anonKey,
            'Authorization: Bearer ' . ($this->getAccessToken() ?: ($this->serviceKey ?: $this->anonKey)),
            'Content-Type: application/json'
        ];

        $response = $this->executeCurl($endpoint, 'GET', null, $headers);
        if ($response['status'] >= 200 && $response['status'] < 300) {
            return is_array($response['data']) ? $response['data'] : [];
        }

        $this->lastError = $this->extractErrorMessage($response, 'Failed to fetch data from ' . $this->currentTable);
        return [];
    }

    public function insert($data) {
        if (!$this->isLive()) {
            $this->lastError = 'Supabase is not configured.';
            return false;
        }

        $endpoint = $this->url . '/rest/v1/' . $this->currentTable;
        $headers = [
            'apikey: ' . $this->anonKey,
            'Authorization: Bearer ' . ($this->getAccessToken() ?: ($this->serviceKey ?: $this->anonKey)),
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];

        $payload = json_encode($data);
        $response = $this->executeCurl($endpoint, 'POST', $payload, $headers);

        if ($response['status'] >= 200 && $response['status'] < 300) {
            return $response['data'] ?? true;
        }

        $this->lastError = $this->extractErrorMessage($response, 'Failed to insert record into ' . $this->currentTable);
        return false;
    }

    public function update($data, $matchColumn, $matchValue) {
        if (!$this->isLive()) {
            $this->lastError = 'Supabase is not configured.';
            return false;
        }

        $endpoint = $this->url . '/rest/v1/' . $this->currentTable . '?' . $matchColumn . '=eq.' . urlencode($matchValue);
        $headers = [
            'apikey: ' . $this->anonKey,
            'Authorization: Bearer ' . ($this->getAccessToken() ?: ($this->serviceKey ?: $this->anonKey)),
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];

        $payload = json_encode($data);
        $response = $this->executeCurl($endpoint, 'PATCH', $payload, $headers);

        if ($response['status'] >= 200 && $response['status'] < 300) {
            return true;
        }

        $this->lastError = $this->extractErrorMessage($response, 'Failed to update record in ' . $this->currentTable);
        return false;
    }

    public function delete($matchColumn, $matchValue) {
        if (!$this->isLive()) {
            $this->lastError = 'Supabase is not configured.';
            return false;
        }

        $endpoint = $this->url . '/rest/v1/' . $this->currentTable . '?' . $matchColumn . '=eq.' . urlencode($matchValue);
        $headers = [
            'apikey: ' . $this->anonKey,
            'Authorization: Bearer ' . ($this->getAccessToken() ?: ($this->serviceKey ?: $this->anonKey)),
            'Content-Type: application/json'
        ];

        $response = $this->executeCurl($endpoint, 'DELETE', null, $headers);

        if ($response['status'] >= 200 && $response['status'] < 300) {
            return true;
        }

        $this->lastError = $this->extractErrorMessage($response, 'Failed to delete record from ' . $this->currentTable);
        return false;
    }

    public function signIn($email, $password) {
        if (!$this->isLive()) {
            return [
                'success' => false,
                'error' => 'Supabase is not configured. Please add SUPABASE_ANON_KEY to your .env file.'
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

        $errorMsg = $this->extractErrorMessage($response, 'Invalid email or password.');
        if (stripos($errorMsg, 'Email not confirmed') !== false) {
            $errorMsg = 'Email not confirmed yet. Please verify your email via the confirmation link sent to your inbox, or disable "Confirm email" in your Supabase Auth settings.';
        }

        $this->lastError = $errorMsg;
        return ['success' => false, 'error' => $errorMsg];
    }

    public function signUp($email, $password, $metadata = []) {
        if (!$this->isLive()) {
            return [
                'success' => false,
                'error' => 'Supabase is not configured. Please add your SUPABASE_ANON_KEY to .env'
            ];
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
            $data = is_array($response['data']) ? $response['data'] : [];
            $userData = $data['user'] ?? $data;
            $accessToken = $data['access_token'] ?? null;

            // If an active session token is immediately returned, save it
            if ($accessToken) {
                $_SESSION['user'] = [
                    'id' => $userData['id'] ?? uniqid(),
                    'email' => $userData['email'] ?? $email,
                    'name' => $userData['user_metadata']['full_name'] ?? ($metadata['full_name'] ?? explode('@', $email)[0]),
                    'role' => $userData['user_metadata']['role'] ?? ($metadata['role'] ?? 'Client'),
                    'token' => $accessToken
                ];
            }

            $requiresConfirmation = empty($accessToken) && empty($userData['email_confirmed_at']) && !empty($userData['confirmation_sent_at']);

            return [
                'success' => true,
                'message' => 'Account successfully registered in Supabase!',
                'user' => $userData,
                'access_token' => $accessToken,
                'requires_confirmation' => $requiresConfirmation
            ];
        }

        $errorMsg = $this->extractErrorMessage($response, 'Registration failed.');
        $this->lastError = $errorMsg;
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

        $errorMsg = $this->extractErrorMessage($response, 'Unable to send reset email.');
        $this->lastError = $errorMsg;
        return ['success' => false, 'error' => $errorMsg];
    }

    private function getAccessToken() {
        return $_SESSION['user']['token'] ?? null;
    }

    private function extractErrorMessage($response, $default = 'An unknown error occurred.') {
        if (is_array($response['data'])) {
            return $response['data']['msg']
                ?? $response['data']['message']
                ?? $response['data']['error_description']
                ?? (is_string($response['data']['error'] ?? null) ? $response['data']['error'] : null)
                ?? $default;
        }

        if (!empty($response['error'])) {
            return $response['error'];
        }

        if (is_string($response['data']) && !empty($response['data'])) {
            return $response['data'];
        }

        return $default;
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
            return ['status' => 500, 'error' => 'cURL error: ' . $curlError, 'data' => null];
        }

        $decoded = json_decode($rawResponse, true);
        return ['status' => $statusCode, 'data' => $decoded !== null ? $decoded : $rawResponse];
    }
}
