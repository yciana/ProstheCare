<?php
/**
 * ProstheCare Clinic Management System
 * Supabase PHP Client & REST API Integration
 */

require_once __DIR__ . '/../config/supabase.php';

class SupabaseClient {
    private $url;
    private $anonKey;
    private $serviceKey;
    private $currentTable;
    private $queryParams = [];
    private $isConfigured;

    public function __construct() {
        $this->url = SUPABASE_URL;
        $this->anonKey = SUPABASE_ANON_KEY;
        $this->serviceKey = SUPABASE_SERVICE_KEY;
        $this->isConfigured = isSupabaseConfigured();

        if (!$this->isConfigured) {
            $this->initMockData();
        }
    }

    public function isLive() {
        return $this->isConfigured;
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
        if (!$this->isConfigured) {
            return $this->mockGet();
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
        return $response['data'] ?? [];
    }

    public function insert($data) {
        if (!$this->isConfigured) {
            return $this->mockInsert($data);
        }

        $endpoint = $this->url . '/rest/v1/' . $this->currentTable;
        $headers = [
            'apikey: ' . $this->anonKey,
            'Authorization: Bearer ' . ($this->getAccessToken() ?: $this->anonKey),
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];

        $response = $this->executeCurl($endpoint, 'POST', json_encode($data), $headers);
        return $response['data'] ?? false;
    }

    public function signIn($email, $password) {
        if (!$this->isConfigured) {
            return $this->mockSignIn($email, $password);
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
                'role' => $user['user_metadata']['role'] ?? 'Prosthetist',
                'token' => $response['data']['access_token']
            ];
            return ['success' => true, 'user' => $_SESSION['user']];
        }

        $errorMsg = $response['data']['error_description'] ?? 'Invalid credentials.';
        return ['success' => false, 'error' => $errorMsg];
    }

    public function signUp($email, $password, $metadata = []) {
        if (!$this->isConfigured) {
            return $this->mockSignUp($email, $password, $metadata);
        }

        $endpoint = $this->url . '/auth/v1/signup';
        $headers = [
            'apikey: ' . $this->anonKey,
            'Content-Type: application/json'
        ];

        $payload = json_encode(['email' => $email, 'password' => $password, 'data' => $metadata]);
        $response = $this->executeCurl($endpoint, 'POST', $payload, $headers);

        if ($response['status'] === 200 || $response['status'] === 201) {
            return ['success' => true, 'user' => $response['data']];
        }

        return ['success' => false, 'error' => $response['data']['msg'] ?? 'Registration failed.'];
    }

    public function resetPassword($email) {
        if (!$this->isConfigured) {
            return ['success' => true, 'message' => 'Reset sent (Demo Mode).'];
        }

        $endpoint = $this->url . '/auth/v1/recover';
        $headers = ['apikey: ' . $this->anonKey, 'Content-Type: application/json'];
        $response = $this->executeCurl($endpoint, 'POST', json_encode(['email' => $email]), $headers);
        return ['success' => $response['status'] === 200];
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
        curl_close($ch);

        $decoded = json_decode($rawResponse, true);
        return ['status' => $statusCode, 'data' => $decoded !== null ? $decoded : $rawResponse];
    }

    private function initMockData() {
        if (!isset($_SESSION['mock_db'])) {
            $_SESSION['mock_db'] = [
                'patients' => [
                    [
                        'id' => 'pt-001',
                        'patient_code' => 'PT-2026-081',
                        'first_name' => 'Eleanor',
                        'last_name' => 'Vance',
                        'dob' => '1984-06-15',
                        'gender' => 'Female',
                        'phone' => '+1 (555) 234-5678',
                        'email' => 'eleanor.vance@example.com',
                        'amputation_level' => 'Transtibial (Below Knee)',
                        'affected_side' => 'Right',
                        'activity_level' => 'K3',
                        'device_type' => 'Endoskeletal Carbon Foot with Vacuum Socket',
                        'fitting_stage' => 'Trial Fitting',
                        'status' => 'Active',
                        'clinical_notes' => 'High mobility user. Evaluated elevated vacuum system for socket stability during trail walking.',
                        'created_at' => date('Y-m-d H:i:s')
                    ],
                    [
                        'id' => 'pt-002',
                        'patient_code' => 'PT-2026-082',
                        'first_name' => 'Marcus',
                        'last_name' => 'Chen',
                        'dob' => '1991-11-20',
                        'gender' => 'Male',
                        'phone' => '+1 (555) 876-5432',
                        'email' => 'marcus.chen@example.com',
                        'amputation_level' => 'Transfemoral (Above Knee)',
                        'affected_side' => 'Left',
                        'activity_level' => 'K4',
                        'device_type' => 'Genium Microprocessor Knee & Hydraulic Ankle',
                        'fitting_stage' => 'Definitive Lamination',
                        'status' => 'In Fabrication',
                        'clinical_notes' => 'Athlete looking for high dynamic energy return.',
                        'created_at' => date('Y-m-d H:i:s')
                    ]
                ],
                'appointments' => [
                    [
                        'id' => 'apt-001',
                        'patient_id' => 'pt-001',
                        'patient_name' => 'Eleanor Vance',
                        'appointment_date' => date('Y-m-d'),
                        'appointment_time' => '09:30',
                        'appointment_type' => 'Trial Fitting & Dynamic Alignment',
                        'status' => 'Scheduled',
                        'room' => 'Gait Lab A',
                        'practitioner_notes' => 'Evaluate stride symmetry on force plate treadmill.'
                    ]
                ],
                'care_plans' => [
                    [
                        'id' => 'cp-001',
                        'patient_id' => 'pt-001',
                        'patient_name' => 'Eleanor Vance',
                        'plan_title' => 'High-Performance Transtibial System',
                        'prescription_code' => 'RX-PRO-4402',
                        'socket_type' => 'TSB (Total Surface Bearing)',
                        'suspension_type' => 'Elevated Vacuum with Hypobaric Ring',
                        'fabrication_stage' => 'Check Socket',
                        'target_delivery_date' => date('Y-m-d', strtotime('+14 days'))
                    ]
                ],
                'users' => [
                    [
                        'email' => 'admin@prosthecare.com',
                        'password' => 'admin123',
                        'name' => 'Dr. Julian Ross, CPO',
                        'role' => 'Lead Prosthetist'
                    ]
                ]
            ];
        }
    }

    private function mockGet() {
        return $_SESSION['mock_db'][$this->currentTable] ?? [];
    }

    private function mockInsert($data) {
        $data['id'] = 'mock-' . uniqid();
        $data['created_at'] = date('Y-m-d H:i:s');
        $_SESSION['mock_db'][$this->currentTable][] = $data;
        return [$data];
    }

    private function mockSignIn($email, $password) {
        $validUsers = $_SESSION['mock_db']['users'] ?? [];
        foreach ($validUsers as $u) {
            if (strtolower($u['email']) === strtolower($email) && $u['password'] === $password) {
                $_SESSION['user'] = [
                    'id' => 'usr-001',
                    'email' => $u['email'],
                    'name' => $u['name'],
                    'role' => $u['role']
                ];
                return ['success' => true, 'user' => $_SESSION['user']];
            }
        }

        if (strlen($password) >= 6) {
            $_SESSION['user'] = [
                'id' => 'usr-' . substr(md5($email), 0, 8),
                'email' => $email,
                'name' => ucwords(explode('@', $email)[0]),
                'role' => 'Certified Prosthetist'
            ];
            return ['success' => true, 'user' => $_SESSION['user']];
        }

        return ['success' => false, 'error' => 'Invalid email or password (min 6 characters).'];
    }

    private function mockSignUp($email, $password, $metadata = []) {
        $newUser = [
            'email' => $email,
            'password' => $password,
            'name' => $metadata['full_name'] ?? 'Practitioner',
            'role' => $metadata['role'] ?? 'Prosthetist'
        ];
        $_SESSION['mock_db']['users'][] = $newUser;
        return ['success' => true, 'user' => $newUser];
    }
}