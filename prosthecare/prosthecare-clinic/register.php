<?php
/**
 * ProstheCare Clinic Management System - Client / Patient Registration
 * Allows prosthetic clients to register their personal patient care account
 */

require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/includes/SupabaseClient.php';

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$supabase = new SupabaseClient();
$errorMessage = '';
$warningNotice = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $amputationLevel = trim($_POST['amputation_level'] ?? 'Transtibial (Below Knee)');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if (empty($fullName) || empty($email) || empty($password)) {
        $errorMessage = 'Please complete all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please enter a valid email address.';
    } elseif ($password !== $confirmPassword) {
        $errorMessage = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $errorMessage = 'Password must be at least 6 characters.';
    } elseif (!$supabase->isLive()) {
        $errorMessage = 'Supabase credentials are not fully configured. Please set your SUPABASE_ANON_KEY in the .env file.';
    } else {
        $nameParts = explode(' ', $fullName, 2);
        $firstName = $nameParts[0];
        $lastName = !empty($nameParts[1]) ? $nameParts[1] : 'Client';

        $metadata = [
            'full_name' => $fullName,
            'role' => 'Client',
            'phone' => $phone,
            'amputation_level' => $amputationLevel
        ];

        $result = $supabase->signUp($email, $password, $metadata);
        if ($result['success']) {
            $userId = $result['user']['id'] ?? null;
            $patientCode = 'PT-' . date('Y') . '-' . rand(1000, 9999);

            // Automatically create record in Supabase patients table
            $patientData = [
                'patient_code' => $patientCode,
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'amputation_level' => $amputationLevel,
                'fitting_stage' => 'Assessment',
                'status' => 'Active',
                'clinical_notes' => 'Self-registered client via ProstheCare web portal.'
            ];
            $patientResult = $supabase->from('patients')->insert($patientData);

            // Record profile linked to user_id
            $profileData = [
                'email' => $email,
                'full_name' => $fullName,
                'role' => 'Client',
                'phone' => $phone
            ];
            if ($userId) {
                $profileData['user_id'] = $userId;
            }
            $profileResult = $supabase->from('profiles')->insert($profileData);

            if (!$patientResult || !$profileResult) {
                error_log("ProstheCare Table Insert Notice: " . ($supabase->getLastError() ?: 'Unknown issue'));
            }

            // Check if email confirmation is required by Supabase Auth
            if (!empty($result['requires_confirmation'])) {
                header('Location: index.php?registered=confirm_email');
                exit;
            }

            // If session was granted immediately (email confirmation disabled in Supabase)
            if (isset($_SESSION['user']['token'])) {
                setcookie('prosthecare_user', json_encode([
                    'id' => $_SESSION['user']['id'],
                    'name' => $_SESSION['user']['name'],
                    'email' => $_SESSION['user']['email'],
                    'role' => $_SESSION['user']['role']
                ]), [
                    'expires' => time() + 86400 * 7,
                    'path' => '/',
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);
                header('Location: dashboard.php');
                exit;
            }

            header('Location: index.php?registered=1');
            exit;
        } else {
            $errorMessage = $result['error'] ?? 'Registration failed. Please check your Supabase connection.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Registration - ProstheCare Management System</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="auth-container">
        <!-- LEFT PANEL: Hero Branding -->
        <section class="auth-hero" aria-label="ProstheCare Branding">
            <header class="auth-brand">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </div>
                <div class="brand-info">
                    <h1 class="brand-title">ProstheCare</h1>
                    <span class="brand-subtitle">MANAGEMENT SYSTEM</span>
                </div>
            </header>

            <div class="auth-hero-main">
                <p class="auth-eyebrow">CLIENT CARE PORTAL</p>
                <h2 class="auth-hero-headline">Your care<br>journey, personalized<br>& connected.</h2>
                <p class="auth-hero-description">
                    Create your client account to track your prosthetic fitting milestones, appointment schedules, and component specifications with your dedicated clinical team.
                </p>
            </div>

            <!-- Concentric Radar Graphic with Glowing Amber Dot -->
            <div class="auth-hero-graphic" aria-hidden="true">
                <div class="ring-outer"></div>
                <div class="ring-middle">
                    <div class="orbital-dot"></div>
                </div>
                <div class="ring-inner"></div>
                <div class="ring-center-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </div>
            </div>

            <footer class="auth-hero-footer">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    <polyline points="9 12 11 14 15 10"></polyline>
                </svg>
                <span>HIPAA-aligned data encryption and private clinical records</span>
            </footer>
        </section>

        <!-- RIGHT PANEL: Client Registration Form -->
        <main class="auth-form-panel">
            <div class="auth-card-inner">
                <span class="form-eyebrow">PATIENT ONBOARDING</span>
                <h2 class="form-main-heading">Create your patient account</h2>
                <p class="form-sub-heading">Register to manage your prosthetic care, fittings, and appointments.</p>

                <?php if (!isSupabaseConfigured()): ?>
                    <div class="auth-alert-box alert-error" role="alert">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span>
                            <strong>Supabase API key needed:</strong> Add your <code>SUPABASE_ANON_KEY</code> to <code>.env</code> to connect with project <code>gdpzdypxospokczofxlu</code>.
                        </span>
                    </div>
                <?php endif; ?>

                <?php if (!empty($errorMessage)): ?>
                    <div class="auth-alert-box alert-error" role="alert">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                        <span><?= htmlspecialchars($errorMessage) ?></span>
                    </div>
                <?php endif; ?>

                <form class="auth-form" method="POST" action="register.php" novalidate>
                    <div class="form-field-group">
                        <label for="full_name" class="form-label-text">Full name *</label>
                        <input 
                            type="text" 
                            id="full_name" 
                            name="full_name" 
                            class="form-input-control" 
                            placeholder="e.g. Eleanor Vance" 
                            value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="form-field-group">
                        <label for="phone" class="form-label-text">Contact phone number</label>
                        <input 
                            type="tel" 
                            id="phone" 
                            name="phone" 
                            class="form-input-control" 
                            placeholder="+1 (555) 234-5678" 
                            value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>"
                        >
                    </div>

                    <div class="form-field-group">
                        <label for="amputation_level" class="form-label-text">Prosthetic care / limb level</label>
                        <select id="amputation_level" name="amputation_level" class="form-input-control">
                            <option value="Transtibial (Below Knee)" <?= (($_POST['amputation_level'] ?? '') === 'Transtibial (Below Knee)') ? 'selected' : '' ?>>Transtibial (Below Knee)</option>
                            <option value="Transfemoral (Above Knee)" <?= (($_POST['amputation_level'] ?? '') === 'Transfemoral (Above Knee)') ? 'selected' : '' ?>>Transfemoral (Above Knee)</option>
                            <option value="Transradial (Below Elbow)" <?= (($_POST['amputation_level'] ?? '') === 'Transradial (Below Elbow)') ? 'selected' : '' ?>>Transradial (Below Elbow)</option>
                            <option value="Transhumeral (Above Elbow)" <?= (($_POST['amputation_level'] ?? '') === 'Transhumeral (Above Elbow)') ? 'selected' : '' ?>>Transhumeral (Above Elbow)</option>
                            <option value="Knee Disarticulation" <?= (($_POST['amputation_level'] ?? '') === 'Knee Disarticulation') ? 'selected' : '' ?>>Knee Disarticulation</option>
                            <option value="Partial Foot / Symes" <?= (($_POST['amputation_level'] ?? '') === 'Partial Foot / Symes') ? 'selected' : '' ?>>Partial Foot / Syme's</option>
                            <option value="General Prosthetic Consultation" <?= (($_POST['amputation_level'] ?? '') === 'General Prosthetic Consultation') ? 'selected' : '' ?>>General Prosthetic Consultation</option>
                        </select>
                    </div>

                    <div class="form-field-group">
                        <label for="email" class="form-label-text">Email address *</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input-control" 
                            placeholder="you@example.com" 
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="form-field-group">
                        <label for="password" class="form-label-text">Password *</label>
                        <div class="password-input-container">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input-control" 
                                placeholder="Create a secure password (min 6 characters)" 
                                required
                            >
                            <button 
                                type="button" 
                                class="password-toggle-button" 
                                aria-label="Toggle password visibility"
                                tabindex="-1"
                            >
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                    <circle cx="12" cy="12" r="3"></circle>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label for="confirm_password" class="form-label-text">Confirm password *</label>
                        <div class="password-input-container">
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                class="form-input-control" 
                                placeholder="Re-enter your password" 
                                required
                            >
                        </div>
                    </div>

                    <button type="submit" class="btn-auth-action">
                        Create patient account
                    </button>

                    <div class="auth-switch-prompt">
                        <span>Already registered?</span>
                        <a href="index.php" class="link-auth-switch">Sign in to your account</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="js/auth.js"></script>
</body>
</html>
