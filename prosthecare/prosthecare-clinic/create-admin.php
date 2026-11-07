<?php
/**
 * ProstheCare Clinic Management System - Admin Account Creator
 */

require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/includes/SupabaseClient.php';

$adminEmail = $argv[1] ?? 'admin@prosthecare.com';
$adminPassword = $argv[2] ?? 'admin123';
$adminName = $argv[3] ?? 'Clinic Administrator';
$adminRole = $argv[4] ?? 'Admin';

echo "Setting up admin account: {$adminEmail} ...\n";

$serviceKey = defined('SUPABASE_SERVICE_KEY') ? SUPABASE_SERVICE_KEY : '';
$url = defined('SUPABASE_URL') ? SUPABASE_URL : '';

if (empty($serviceKey) || empty($url)) {
    die("Error: SUPABASE_URL or SUPABASE_SERVICE_KEY is missing.\n");
}

// 1. Create or invite user via Supabase Admin API
$endpoint = $url . '/auth/v1/admin/users';
$headers = [
    'apikey: ' . $serviceKey,
    'Authorization: Bearer ' . $serviceKey,
    'Content-Type: application/json'
];

$payload = json_encode([
    'email' => $adminEmail,
    'password' => $adminPassword,
    'email_confirm' => true,
    'user_metadata' => [
        'full_name' => $adminName,
        'role' => $adminRole,
        'clinic_name' => CLINIC_NAME
    ]
]);

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = json_decode($response, true);
$userId = null;

if ($httpCode === 200 || $httpCode === 201) {
    echo "SUCCESS: Admin user created in Supabase Auth!\n";
    $userId = $data['id'] ?? ($data['user']['id'] ?? null);
} elseif (isset($data['msg']) && stripos($data['msg'], 'already') !== false) {
    echo "NOTICE: User already exists. Updating password & metadata...\n";
    // Find the user ID
    $listCh = curl_init($url . '/auth/v1/admin/users');
    curl_setopt($listCh, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($listCh, CURLOPT_HTTPHEADER, $headers);
    $listRes = json_decode(curl_exec($listCh), true);
    curl_close($listCh);

    $users = $listRes['users'] ?? (is_array($listRes) ? $listRes : []);
    foreach ($users as $u) {
        if (($u['email'] ?? '') === $adminEmail) {
            $userId = $u['id'];
            break;
        }
    }

    if ($userId) {
        // Update user
        $updateCh = curl_init($url . '/auth/v1/admin/users/' . $userId);
        curl_setopt($updateCh, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($updateCh, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($updateCh, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($updateCh, CURLOPT_POSTFIELDS, json_encode([
            'password' => $adminPassword,
            'email_confirm' => true,
            'user_metadata' => [
                'full_name' => $adminName,
                'role' => $adminRole,
                'clinic_name' => CLINIC_NAME
            ]
        ]));
        $upRes = curl_exec($updateCh);
        curl_close($updateCh);
        echo "SUCCESS: Updated existing admin user credentials.\n";
    }
} else {
    echo "HTTP {$httpCode}: " . ($data['msg'] ?? $data['message'] ?? $response) . "\n";
}

// 2. Ensure profile exists in profiles table
$supabase = new SupabaseClient();
$existingProfile = $supabase->from('profiles')->select('*')->eq('email', $adminEmail)->get();

if (empty($existingProfile)) {
    $profileData = [
        'email' => $adminEmail,
        'full_name' => $adminName,
        'role' => $adminRole,
        'clinic_name' => CLINIC_NAME
    ];
    if ($userId) {
        $profileData['user_id'] = $userId;
    }
    $supabase->from('profiles')->insert($profileData);
    echo "SUCCESS: Created admin profile in 'profiles' table.\n";
} else {
    $supabase->from('profiles')->update([
        'role' => $adminRole,
        'full_name' => $adminName
    ], 'email', $adminEmail);
    echo "SUCCESS: Updated admin profile in 'profiles' table.\n";
}

// 3. Test sign-in
$signInTest = $supabase->signIn($adminEmail, $adminPassword);
if ($signInTest['success']) {
    echo "\n=== ADMIN ACCOUNT READY ===";
    echo "\nEmail:    {$adminEmail}";
    echo "\nPassword: {$adminPassword}";
    echo "\nRole:     {$signInTest['user']['role']}";
    echo "\nName:     {$signInTest['user']['name']}\n";
} else {
    echo "\nSign-in verification failed: " . ($signInTest['error'] ?? 'Unknown error') . "\n";
}
