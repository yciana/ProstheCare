<?php
/**
 * ProstheCare Clinic Management System - Forgot Password
 * Live Supabase Auth Recovery
 */

require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/includes/SupabaseClient.php';

$supabase = new SupabaseClient();
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $errorMessage = 'Please enter your registered email address.';
    } else {
        $result = $supabase->resetPassword($email);
        if ($result['success']) {
            header('Location: index.php?reset_sent=1');
            exit;
        } else {
            $errorMessage = $result['error'] ?? 'Could not send reset email.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - ProstheCare Management System</title>
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
                <p class="auth-eyebrow">ACCOUNT RECOVERY</p>
                <h2 class="auth-hero-headline">Seamless<br>access,<br>uninterrupted.</h2>
                <p class="auth-hero-description">
                    Quickly restore your access to ensure clinical fittings, client histories, and lab orders remain on schedule.
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
                <span>Automated password recovery tokens expire in 60 minutes</span>
            </footer>
        </section>

        <!-- RIGHT PANEL: Reset Form -->
        <main class="auth-form-panel">
            <div class="auth-card-inner">
                <span class="form-eyebrow">PASSWORD ASSISTANCE</span>
                <h2 class="form-main-heading">Reset your password</h2>
                <p class="form-sub-heading">Enter your email and we'll send a secure password reset link.</p>

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

                <form class="auth-form" method="POST" action="forgot-password.php" novalidate>
                    <div class="form-field-group">
                        <label for="email" class="form-label-text">Registered email address</label>
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

                    <button type="submit" class="btn-auth-action">
                        Send reset instructions
                    </button>

                    <div class="auth-switch-prompt">
                        <span>Remembered your password?</span>
                        <a href="index.php" class="link-auth-switch">Back to sign in</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="js/auth.js"></script>
</body>
</html>
