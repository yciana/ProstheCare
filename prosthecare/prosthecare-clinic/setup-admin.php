<?php
/**
 * ProstheCare Clinic Management System - Admin Setup Portal
 */

require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/includes/SupabaseClient.php';

$supabase = new SupabaseClient();
$message = '';
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $fullName = trim($_POST['full_name'] ?? '');
    $role = trim($_POST['role'] ?? 'Admin');

    if (empty($email) || empty($password) || empty($fullName)) {
        $message = 'Please provide full name, email, and password.';
        $isError = true;
    } elseif (strlen($password) < 6) {
        $message = 'Password must be at least 6 characters.';
        $isError = true;
    } else {
        $serviceKey = defined('SUPABASE_SERVICE_KEY') ? SUPABASE_SERVICE_KEY : '';
        $url = defined('SUPABASE_URL') ? SUPABASE_URL : '';

        if (empty($serviceKey)) {
            $message = 'SUPABASE_SERVICE_KEY is required in .env to create admin staff accounts.';
            $isError = true;
        } else {
            // Call Supabase Admin API
            $endpoint = $url . '/auth/v1/admin/users';
            $headers = [
                'apikey: ' . $serviceKey,
                'Authorization: Bearer ' . $serviceKey,
                'Content-Type: application/json'
            ];

            $payload = json_encode([
                'email' => $email,
                'password' => $password,
                'email_confirm' => true,
                'user_metadata' => [
                    'full_name' => $fullName,
                    'role' => $role,
                    'clinic_name' => CLINIC_NAME
                ]
            ]);

            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
            $raw = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $data = json_decode($raw, true);
            $userId = null;

            if ($code === 200 || $code === 201) {
                $userId = $data['id'] ?? ($data['user']['id'] ?? null);
            } elseif (isset($data['msg']) && stripos($data['msg'], 'already') !== false) {
                // User already exists, update their role and password
                $listCh = curl_init($url . '/auth/v1/admin/users');
                curl_setopt($listCh, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($listCh, CURLOPT_HTTPHEADER, $headers);
                $listData = json_decode(curl_exec($listCh), true);
                curl_close($listCh);

                $users = $listData['users'] ?? (is_array($listData) ? $listData : []);
                foreach ($users as $u) {
                    if (($u['email'] ?? '') === $email) {
                        $userId = $u['id'];
                        break;
                    }
                }

                if ($userId) {
                    $upCh = curl_init($url . '/auth/v1/admin/users/' . $userId);
                    curl_setopt($upCh, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($upCh, CURLOPT_CUSTOMREQUEST, 'PUT');
                    curl_setopt($upCh, CURLOPT_HTTPHEADER, $headers);
                    curl_setopt($upCh, CURLOPT_POSTFIELDS, json_encode([
                        'password' => $password,
                        'email_confirm' => true,
                        'user_metadata' => [
                            'full_name' => $fullName,
                            'role' => $role,
                            'clinic_name' => CLINIC_NAME
                        ]
                    ]));
                    curl_exec($upCh);
                    curl_close($upCh);
                }
            } else {
                $message = $data['msg'] ?? $data['message'] ?? 'Failed to create user in Supabase Auth.';
                $isError = true;
            }

            if (!$isError) {
                // Save or update profile in profiles table
                $existing = $supabase->from('profiles')->select('*')->eq('email', $email)->get();
                if (empty($existing)) {
                    $profileData = [
                        'email' => $email,
                        'full_name' => $fullName,
                        'role' => $role,
                        'clinic_name' => CLINIC_NAME
                    ];
                    if ($userId) {
                        $profileData['user_id'] = $userId;
                    }
                    $supabase->from('profiles')->insert($profileData);
                } else {
                    $supabase->from('profiles')->update([
                        'role' => $role,
                        'full_name' => $fullName
                    ], 'email', $email);
                }

                $message = "Staff account for {$fullName} ({$email}) successfully created with role: {$role}!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Staff / Admin Account - ProstheCare</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/auth.css">
    <style>
        body { background: #07474b; padding: 40px 20px; font-family: sans-serif; }
        .setup-card { max-width: 520px; margin: 0 auto; background: #fff; padding: 36px; border-radius: 8px; box-shadow: 0 4px 24px rgba(0,0,0,0.25); }
        .setup-header { margin-bottom: 24px; }
        .setup-title { font-size: 22px; font-weight: bold; color: #0d797f; margin-bottom: 6px; }
        .setup-desc { font-size: 14px; color: #666; }
    </style>
</head>
<body>
    <div class="setup-card">
        <div class="setup-header">
            <h1 class="setup-title">🛡️ Admin & Staff Account Portal</h1>
            <p class="setup-desc">Create or update administrative and clinical practitioner accounts for ProstheCare.</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="auth-alert-box <?= $isError ? 'alert-error' : 'alert-success' ?>">
                <span><?= htmlspecialchars($message) ?></span>
            </div>
        <?php endif; ?>

        <form method="POST" action="setup-admin.php">
            <div class="form-field-group">
                <label for="full_name" class="form-label-text">Staff Full Name</label>
                <input type="text" id="full_name" name="full_name" class="form-input-control" placeholder="e.g. Dr. Jordan Hayes" value="<?= htmlspecialchars($_POST['full_name'] ?? 'Clinic Administrator') ?>" required>
            </div>

            <div class="form-field-group">
                <label for="role" class="form-label-text">System Role</label>
                <select id="role" name="role" class="form-input-control">
                    <option value="Admin">Admin (Full System Access)</option>
                    <option value="Prosthetist">Prosthetist (Clinical Practitioner)</option>
                    <option value="Orthotist">Orthotist</option>
                    <option value="Lab Technician">Lab Technician (Fabrication)</option>
                </select>
            </div>

            <div class="form-field-group">
                <label for="email" class="form-label-text">Email Address</label>
                <input type="email" id="email" name="email" class="form-input-control" placeholder="admin@prosthecare.com" value="<?= htmlspecialchars($_POST['email'] ?? 'admin@prosthecare.com') ?>" required>
            </div>

            <div class="form-field-group">
                <label for="password" class="form-label-text">Password (min 6 characters)</label>
                <input type="password" id="password" name="password" class="form-input-control" placeholder="Set a secure password" value="<?= htmlspecialchars($_POST['password'] ?? 'admin123') ?>" required>
            </div>

            <button type="submit" class="btn-auth-action">
                Create / Update Admin Account
            </button>
        </form>

        <div class="auth-switch-prompt" style="margin-top: 20px;">
            <a href="index.php" class="link-auth-switch">← Return to Sign In</a>
        </div>
    </div>
</body>
</html>
