<?php
/**
 * ProstheCare Clinic Management System - Sign In
 * Clean, production implementation matching Figma design
 */

require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/includes/SupabaseClient.php';

if (isset($_SESSION['user'])) {
    header('Location: dashboard.php');
    exit;
}

$supabase = new SupabaseClient();
$errorMessage = '';
$successMessage = '';

if (isset($_GET['registered'])) {
    if ($_GET['registered'] === 'confirm_email') {
        $successMessage = 'Registration successful! A confirmation email has been sent. Please confirm your email before signing in (or disable "Confirm email" in your Supabase Auth settings).';
    } else {
        $successMessage = 'Registration successful! You can now sign in with your credentials.';
    }
}

if (isset($_GET['reset_sent'])) {
    $successMessage = 'Password reset instructions have been dispatched to your email.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        $errorMessage = 'Please enter both your email address and password.';
    } else {
        $result = $supabase->signIn($email, $password);
        if ($result['success']) {
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
        } else {
            $errorMessage = $result['error'] ?? 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign in to your account - ProstheCare Management System</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>
    <div class="auth-container">
        <!-- LEFT PANEL: Hero Branding & Graphic Showcase -->
        <section class="auth-hero" aria-label="ProstheCare Branding">
            <!-- Brand Logo Header -->
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

            <!-- Hero Main Headline -->
            <div class="auth-hero-main">
                <p class="auth-eyebrow">PATIENT-CENTERED CARE</p>
                <h2 class="auth-hero-headline">Care<br>coordination,<br>made simpler.</h2>
                <p class="auth-hero-description">
                    A secure workspace built to help prosthetic care teams stay organized, connected, and focused on every client.
                </p>
            </div>

            <!-- Concentric Radar / Target Graphic with Glowing Amber Dot -->
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

            <!-- Footer Badge -->
            <footer class="auth-hero-footer">
                <div class="security-badge">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                    </svg>
                    <span>HIPAA compliant &middot; End-to-end encrypted medical records</span>
                </div>
            </footer>
        </section>

        <!-- RIGHT PANEL: Authentication Form -->
        <main class="auth-form-panel">
            <div class="auth-card-inner">
                <span class="form-eyebrow">WELCOME BACK</span>
                <h2 class="form-main-heading">Sign in to your account</h2>
                <p class="form-sub-heading">Enter your details to access your ProstheCare workspace.</p>

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

                <?php if (!empty($successMessage)): ?>
                    <div class="auth-alert-box alert-success" role="status">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                            <polyline points="22 4 12 14.01 9 11.01"></polyline>
                        </svg>
                        <span><?= htmlspecialchars($successMessage) ?></span>
                    </div>
                <?php endif; ?>

                <form class="auth-form" method="POST" action="index.php" novalidate>
                    <!-- Email Field -->
                    <div class="form-field-group">
                        <label for="email" class="form-label-text">Email address</label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="form-input-control" 
                            placeholder="you@example.com" 
                            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"
                            required 
                            autocomplete="email"
                        >
                    </div>

                    <!-- Password Field with Toggle -->
                    <div class="form-field-group">
                        <label for="password" class="form-label-text">Password</label>
                        <div class="password-input-container">
                            <input 
                                type="password" 
                                id="password" 
                                name="password" 
                                class="form-input-control" 
                                placeholder="Enter your password" 
                                required 
                                autocomplete="current-password"
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

                    <!-- Forgot Password Link -->
                    <div class="form-aux-row">
                        <a href="forgot-password.php" class="link-forgot-password">Forgot password?</a>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn-auth-action">
                        Sign in
                    </button>

                    <!-- Create Account Link -->
                    <div class="auth-switch-prompt">
                        <span>New to ProstheCare?</span>
                        <a href="register.php" class="link-auth-switch">Create an account</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="js/auth.js"></script>
</body>
</html>
