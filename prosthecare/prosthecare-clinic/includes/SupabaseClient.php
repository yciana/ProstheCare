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
    private $queryHeaders = [];
    private $isConfigured;

    public function __construct() {
        $this->url = SUPABASE_URL;
        $this->anonKey = SUPABASE_ANON_KEY;
        $this->serviceKey = SUPABASE_SERVICE_KEY;
        $this->isConfigured = isSupabaseConfigured();

        // Initialize mock session store if not configured yet
        if (!$this->isConfigured) {
            $this->initMockData();
        }
    }

    /**
     * Check if client is using live Supabase or demo mode
     */
    public function isLive() {
        return $this->isConfigured;
    }

    /**
     * Set the table for subsequent database operations
     */
    public function from($table) {
        $this->currentTable = $table;
        $this->queryParams = [];
        $this->queryHeaders = [];
        return $this;
    }

    /**
     * Select columns
     */
    public function select($columns = '*') {
        $this->queryParams['select'] = $columns;
        return $this;
    }

    /**
     * Filter equality
     */
    public function eq($column, $value) {
        $this->queryParams[$column] = 'eq.' . $value;
        return $this;
    }

    /**
     * Order results
     */
    public function order($column, $ascending = true) {
        $this->queryParams['order'] = $column . '.' . ($ascending ? 'asc' : 'desc');
        return $this;
    }

    /**
     * Limit count
     */
    public function limit($count) {
        $this->queryParams['limit'] = (int)$count;
        return $this;
    }

    /**
     * Execute GET query against Supabase PostgREST
     */
    public function get() {
        if (!$this->isConfigured) {
            return $this->mockGet();
        }

        $endpoint = $this->url . '/rest/v1/' . $this->currentTable;
        if (!empty($this->queryParams)) {
            $queryString = http_build_query($this->queryParams);
            $endpoint .= '?' . $queryString;
        }

        $headers = [
            'apikey: ' . $this->anonKey,
            'Authorization: Bearer ' . ($this->getAccessToken() ?: $this->anonKey),
            'Content-Type: application/json'
        ];

        $response = $this->executeCurl($endpoint, 'GET', null, $headers);
        return $response['data'] ?? [];
    }

    /**
     * Execute INSERT query
     */
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

        $payload = json_encode($data);
        $response = $this->executeCurl($endpoint, 'POST', $payload, $headers);
        return $response['data'] ?? false;
    }

    /**
     * Execute UPDATE query
     */
    public function update($data, $matchColumn, $matchValue) {
        if (!$this->isConfigured) {
            return $this->mockUpdate($data, $matchColumn, $matchValue);
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
        return $response['data'] ?? false;
    }

    /**
     * Execute DELETE query
     */
    public function delete($matchColumn, $matchValue) {
        if (!$this->isConfigured) {
            return $this->mockDelete($matchColumn, $matchValue);
        }

        $endpoint = $this->url . '/rest/v1/' . $this->currentTable . '?' . $matchColumn . '=eq.' . urlencode($matchValue);
        $headers = [
            'apikey: ' . $this->anonKey,
            'Authorization: Bearer ' . ($this->getAccessToken() ?: $this->anonKey),
            'Content-Type: application/json',
            'Prefer: return=representation'
        ];

        $response = $this->executeCurl($endpoint, 'DELETE', null, $headers);
        return $response['status'] >= 200 && $response['status'] < 300;
    }

    /**
     * Supabase Auth: Sign In with Email & Password
     */
    public function signIn($email, $password) {
        if (!$this->isConfigured) {
            return $this->mockSignIn($email, $password);
        }

        $endpoint = $this->url . '/auth/v1/token?grant_type=password';
        $headers = [
            'apikey: ' . $this->anonKey,
            'Content-Type: application/json'
        ];

        $payload = json_encode([
            'email' => $email,
            'password' => $password
        ]);

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

        $errorMsg = $response['data']['error_description'] ?? $response['data']['msg'] ?? 'Invalid credentials or user not found.';
        return ['success' => false, 'error' => $errorMsg];
    }

    /**
     * Supabase Auth: Register new account
     */
    public function signUp($email, $password, $metadata = []) {
        if (!$this->isConfigured) {
            return $this->mockSignUp($email, $password, $metadata);
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
            $user = $response['data']['user'] ?? $response['data'];
            return [
                'success' => true,
                'message' => 'Account created successfully! Check your email if email confirmation is enabled.',
                'user' => $user
            ];
        }

        $errorMsg = $response['data']['msg'] ?? $response['data']['error_description'] ?? 'Registration failed.';
        return ['success' => false, 'error' => $errorMsg];
    }

    /**
     * Supabase Auth: Send Password Reset Email
     */
    public function resetPassword($email) {
        if (!$this->isConfigured) {
            return [
                'success' => true,
                'message' => 'Password reset instruction link has been sent to ' . htmlspecialchars($email) . ' (Demo Mode).'
            ];
        }

        $endpoint = $this->url . '/auth/v1/recover';
        $headers = [
            'apikey: ' . $this->anonKey,
            'Content-Type: application/json'
        ];

        $payload = json_encode(['email' => $email]);
        $response = $this->executeCurl($endpoint, 'POST', $payload, $headers);

        if ($response['status'] === 200) {
            return ['success' => true, 'message' => 'Password reset instructions have been sent to your email.'];
        }

        $errorMsg = $response['data']['msg'] ?? 'Unable to send reset email.';
        return ['success' => false, 'error' => $errorMsg];
    }

    /**
     * Get current user access token from session
     */
    private function getAccessToken() {
        return $_SESSION['user']['token'] ?? null;
    }

    /**
     * cURL Request Handler
     */
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
            return [
                'status' => 500,
                'error' => $curlError,
                'data' => null
            ];
        }

        $decoded = json_decode($rawResponse, true);
        return [
            'status' => $statusCode,
            'data' => $decoded !== null ? $decoded : $rawResponse
        ];
    }

    // =========================================================================
    // MOCK / DEMO DATA STORE (Runs automatically when .env is unconfigured)
    // =========================================================================

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
                        'clinical_notes' => 'Athlete looking for high dynamic energy return. Final carbon fiber layup scheduled for Friday.',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))
                    ],
                    [
                        'id' => 'pt-003',
                        'patient_code' => 'PT-2026-083',
                        'first_name' => 'Sophia',
                        'last_name' => 'Rodriguez',
                        'dob' => '2001-03-08',
                        'gender' => 'Female',
                        'phone' => '+1 (555) 345-6789',
                        'email' => 'sophia.r@example.com',
                        'amputation_level' => 'Transradial (Below Elbow)',
                        'affected_side' => 'Right',
                        'activity_level' => 'K3',
                        'device_type' => 'Multi-Articulating Myoelectric Hand',
                        'fitting_stage' => 'Assessment',
                        'status' => 'Active',
                        'clinical_notes' => 'Undergoing initial EMG sensor calibration and electrode socket placement test.',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))
                    ],
                    [
                        'id' => 'pt-004',
                        'patient_code' => 'PT-2026-084',
                        'first_name' => 'David',
                        'last_name' => 'Kowalski',
                        'dob' => '1975-09-12',
                        'gender' => 'Male',
                        'phone' => '+1 (555) 901-2345',
                        'email' => 'david.k@example.com',
                        'amputation_level' => 'Transtibial (Below Knee)',
                        'affected_side' => 'Left',
                        'activity_level' => 'K2',
                        'device_type' => 'Energy Storage and Return (ESR) Prosthesis',
                        'fitting_stage' => 'Final Delivery',
                        'status' => 'Completed',
                        'clinical_notes' => 'Definitive device delivered. Gait is symmetrical. Scheduled routine 30-day review.',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-12 days'))
                    ],
                    [
                        'id' => 'pt-005',
                        'patient_code' => 'PT-2026-085',
                        'first_name' => 'Amara',
                        'last_name' => 'Okonkwo',
                        'dob' => '1996-07-28',
                        'gender' => 'Female',
                        'phone' => '+1 (555) 432-1098',
                        'email' => 'amara.o@example.com',
                        'amputation_level' => 'Bilateral Transtibial',
                        'affected_side' => 'Bilateral',
                        'activity_level' => 'K3',
                        'device_type' => 'Dual All-Terrain Shock-Absorbing Pylons',
                        'fitting_stage' => 'Diagnostic Socket',
                        'status' => 'Active',
                        'clinical_notes' => 'First test walk with transparent check sockets. Slight pressure medial distal right tibia noted and relieved.',
                        'created_at' => date('Y-m-d H:i:s', strtotime('-18 days'))
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
                    ],
                    [
                        'id' => 'apt-002',
                        'patient_id' => 'pt-005',
                        'patient_name' => 'Amara Okonkwo',
                        'appointment_date' => date('Y-m-d'),
                        'appointment_time' => '11:00',
                        'appointment_type' => 'Diagnostic Socket Evaluation',
                        'status' => 'Scheduled',
                        'room' => 'Fitting Suite 2',
                        'practitioner_notes' => 'Adjust distal clearance and suction sleeve fit.'
                    ],
                    [
                        'id' => 'apt-003',
                        'patient_id' => 'pt-003',
                        'patient_name' => 'Sophia Rodriguez',
                        'appointment_date' => date('Y-m-d', strtotime('+1 day')),
                        'appointment_time' => '14:00',
                        'appointment_type' => 'Myoelectric Electrode Tuning',
                        'status' => 'Scheduled',
                        'room' => 'Upper Extremity Lab',
                        'practitioner_notes' => 'Fine-tune dual-site EMG thresholds for grip pattern switching.'
                    ],
                    [
                        'id' => 'apt-004',
                        'patient_id' => 'pt-002',
                        'patient_name' => 'Marcus Chen',
                        'appointment_date' => date('Y-m-d', strtotime('+2 days')),
                        'appointment_time' => '10:15',
                        'appointment_type' => 'Final Delivery & Gait Training',
                        'status' => 'Scheduled',
                        'room' => 'Gait Lab B',
                        'practitioner_notes' => 'Handover definitive prosthesis, provide maintenance guide and wear schedule.'
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
                        'componentry_details' => 'Triton Carbon Foot + Harmony P3 vacuum pump',
                        'fabrication_stage' => 'Check Socket',
                        'target_delivery_date' => date('Y-m-d', strtotime('+14 days')),
                        'warranty_months' => 24,
                        'notes' => 'Patient aims for trail hiking. Socket trim lines lowered for maximum knee flexion.'
                    ],
                    [
                        'id' => 'cp-002',
                        'patient_id' => 'pt-002',
                        'patient_name' => 'Marcus Chen',
                        'plan_title' => 'Microprocessor Transfemoral Dynamic System',
                        'prescription_code' => 'RX-PRO-9921',
                        'socket_type' => 'IC (Ischial Containment)',
                        'suspension_type' => 'Sub-atmospheric Active Suction',
                        'componentry_details' => 'Ottobock Genium + Taleo Carbon Foot',
                        'fabrication_stage' => 'Carbon Lamination',
                        'target_delivery_date' => date('Y-m-d', strtotime('+5 days')),
                        'warranty_months' => 36,
                        'notes' => 'Multi-layer prepreg carbon structure with titanium pyramid adapters.'
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
        $table = $this->currentTable;
        return $_SESSION['mock_db'][$table] ?? [];
    }

    private function mockInsert($data) {
        $table = $this->currentTable;
        if (!isset($data['id'])) {
            $data['id'] = 'mock-' . uniqid();
        }
        if (!isset($data['created_at'])) {
            $data['created_at'] = date('Y-m-d H:i:s');
        }
        $_SESSION['mock_db'][$table][] = $data;
        return [$data];
    }

    private function mockUpdate($data, $column, $value) {
        $table = $this->currentTable;
        if (!isset($_SESSION['mock_db'][$table])) {
            return false;
        }

        foreach ($_SESSION['mock_db'][$table] as &$row) {
            if (isset($row[$column]) && $row[$column] == $value) {
                $row = array_merge($row, $data);
                return [$row];
            }
        }
        return false;
    }

    private function mockDelete($column, $value) {
        $table = $this->currentTable;
        if (!isset($_SESSION['mock_db'][$table])) {
            return false;
        }

        $_SESSION['mock_db'][$table] = array_values(array_filter(
            $_SESSION['mock_db'][$table],
            function($row) use ($column, $value) {
                return !isset($row[$column]) || $row[$column] != $value;
            }
        ));
        return true;
    }

    private function mockSignIn($email, $password) {
        // Allow default demo credentials or any email with password length >= 6
        $validUsers = $_SESSION['mock_db']['users'] ?? [];
        foreach ($validUsers as $u) {
            if (strtolower($u['email']) === strtolower($email) && $u['password'] === $password) {
                $_SESSION['user'] = [
                    'id' => 'usr-' . substr(md5($email), 0, 8),
                    'email' => $u['email'],
                    'name' => $u['name'],
                    'role' => $u['role']
                ];
                return ['success' => true, 'user' => $_SESSION['user']];
            }
        }

        // Demo convenience: if password is valid, auto-log in as demo prosthetist
        if (strlen($password) >= 6) {
            $namePart = ucwords(str_replace(['.', '_', '-'], ' ', explode('@', $email)[0]));
            $_SESSION['user'] = [
                'id' => 'usr-' . substr(md5($email), 0, 8),
                'email' => $email,
                'name' => $namePart ?: 'Clinical Practitioner',
                'role' => 'Certified Prosthetist'
            ];
            return ['success' => true, 'user' => $_SESSION['user']];
        }

        return ['success' => false, 'error' => 'Invalid email or password (minimum 6 characters required).'];
    }

    private function mockSignUp($email, $password, $metadata = []) {
        if (strlen($password) < 6) {
            return ['success' => false, 'error' => 'Password must be at least 6 characters long.'];
        }

        $newUser = [
            'email' => $email,
            'password' => $password,
            'name' => $metadata['full_name'] ?? 'Clinical Practitioner',
            'role' => $metadata['role'] ?? 'Prosthetist'
        ];

        $_SESSION['mock_db']['users'][] = $newUser;
        return [
            'success' => true,
            'message' => 'Account created successfully! You can now sign in with your credentials.',
            'user' => $newUser
        ];
    }
}
