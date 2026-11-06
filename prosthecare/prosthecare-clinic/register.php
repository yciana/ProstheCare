<?php
/**
 * ProstheCare Clinic Management System - Create Account
 * Live Supabase Auth Registration
 */

require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/includes/SupabaseClient.php';

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$supabase = new SupabaseClient();
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = trim($_POST['full_name'] ?? '');
    $role = trim($_POST['role'] ?? 'Prosthetist');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if (empty($fullName) || empty($email) || empty($password)) {
        $errorMessage = 'Please complete all required fields.';
    } elseif ($password !== $confirmPassword) {
        $errorMessage = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $errorMessage = 'Password must be at least 6 characters.';
    } else {
        $metadata = [
            'full_name' => $fullName,
            'role' => $role
        ];

        $result = $supabase->signUp($email, $password, $metadata);
        if ($result['success']) {
            // Also store profile into public.profiles table
            $supabase->from('profiles')->insert([
                'email' => $email,
                'full_name' => $fullName,
                'role' => $role
            ]);
            
            header('Location: index.php?registered=1');
            exit;
        } else {
            $errorMessage = $result['error'] ?? 'Registration failed.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an account - ProstheCare Management System</title>
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
                <p class="auth-eyebrow">PRACTITIONER ONBOARDING</p>
                <h2 class="auth-hero-headline">Empowering<br>prosthetic<br>excellence.</h2>
                <p class="auth-hero-description">
                    Join clinical teams using ProstheCare to coordinate diagnostic fittings, component tracking, and client outcomes with total precision.
                </p>
            </div>

            <!-- Concentric Radar Graphic -->
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
                <span>HIPAA-aligned data encryption and strict access control</span>
            </footer>
        </section>

        <!-- RIGHT PANEL: Registration Form -->
        <main class="auth-form-panel">
            <div class="auth-card-inner">
                <span class="form-eyebrow">GET STARTED</span>
                <h2 class="form-main-heading">Create your account</h2>
                <p class="form-sub-heading">Set up your practitioner profile to access ProstheCare.</p>

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
                        <label for="full_name" class="form-label-text">Full name</label>
                        <input 
                            type="text" 
                            id="full_name" 
                            name="full_name" 
                            class="form-input-control" 
                            placeholder="Dr. Jordan Hayes, CPO" 
                            value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="form-field-group">
                        <label for="role" class="form-label-text">Clinical role</label>
                        <input 
                            type="text" 
                            id="role" 
                            name="role" 
                            class="form-input-control" 
                            placeholder="e.g. Certified Prosthetist, Orthotist, Clinic Admin" 
                            value="<?= htmlspecialchars($_POST['role'] ?? 'Prosthetist') ?>"
                            required
                        >
                    </div>

                    <div class="form-field-group">
                        <label for="email" class="form-label-text">Work email address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input-control" 
                            placeholder="jordan.hayes@clinic.com" 
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            required
                        >
                    </div>

                    <div class="form-field-group">
                        <label for="password" class="form-label-text">Password</label>
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
                        <label for="confirm_password" class="form-label-text">Confirm password</label>
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
                        Create account
                    </button>

                    <div class="auth-switch-prompt">
                        <span>Already have an account?</span>
                        <a href="index.php" class="link-auth-switch">Sign in</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="js/auth.js"></script>
</body>
</html>
