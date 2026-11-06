<?php
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
        <section class="auth-hero" aria-label="ProstheCare Branding">
            <header class="auth-brand">
                <div class="brand-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg></div>
                <div class="brand-info"><h1 class="brand-title">ProstheCare</h1><span class="brand-subtitle">MANAGEMENT SYSTEM</span></div>
            </header>
            <div class="auth-hero-main">
                <p class="auth-eyebrow">ACCOUNT RECOVERY</p>
                <h2 class="auth-hero-headline">Seamless<br>access,<br>uninterrupted.</h2>
                <p class="auth-hero-description">Quickly restore access to keep clinical fittings on schedule.</p>
            </div>
            <footer class="auth-hero-footer"><span>Password recovery tokens expire in 60 minutes</span></footer>
        </section>

        <main class="auth-form-panel">
            <div class="auth-card-inner">
                <span class="form-eyebrow">PASSWORD ASSISTANCE</span>
                <h2 class="form-main-heading">Reset your password</h2>
                <p class="form-sub-heading">Enter your email and we'll send a secure password reset link.</p>

                <?php if (!empty($errorMessage)): ?>
                    <div class="auth-alert-box alert-error"><span><?= htmlspecialchars($errorMessage) ?></span></div>
                <?php endif; ?>

                <form class="auth-form" method="POST" action="forgot-password.php">
                    <div class="form-field-group">
                        <label for="email" class="form-label-text">Registered email address</label>
                        <input type="email" id="email" name="email" class="form-input-control" placeholder="you@example.com" required>
                    </div>
                    <button type="submit" class="btn-auth-action">Send reset instructions</button>
                    <div class="auth-switch-prompt">
                        <span>Remembered your password?</span>
                        <a href="index.php" class="link-auth-switch">Back to sign in</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>