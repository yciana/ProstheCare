<?php
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

    if (empty($fullName) || empty($email) || empty($password)) {
        $errorMessage = 'Please complete all required fields.';
    } elseif (strlen($password) < 6) {
        $errorMessage = 'Password must be at least 6 characters.';
    } else {
        $result = $supabase->signUp($email, $password, ['full_name' => $fullName, 'role' => $role]);
        if ($result['success']) {
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
        <section class="auth-hero" aria-label="ProstheCare Branding">
            <header class="auth-brand">
                <div class="brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                </div>
                <div class="brand-info">
                    <h1 class="brand-title">ProstheCare</h1>
                    <span class="brand-subtitle">MANAGEMENT SYSTEM</span>
                </div>
            </header>
            <div class="auth-hero-main">
                <p class="auth-eyebrow">PRACTITIONER ONBOARDING</p>
                <h2 class="auth-hero-headline">Empowering<br>prosthetic<br>excellence.</h2>
                <p class="auth-hero-description">Join clinical teams using ProstheCare to coordinate diagnostic fittings and client outcomes.</p>
            </div>
            <footer class="auth-hero-footer">
                <span>HIPAA-aligned data encryption and strict access control</span>
            </footer>
        </section>

        <main class="auth-form-panel">
            <div class="auth-card-inner">
                <span class="form-eyebrow">GET STARTED</span>
                <h2 class="form-main-heading">Create your account</h2>
                <p class="form-sub-heading">Set up your practitioner profile to access ProstheCare.</p>

                <?php if (!empty($errorMessage)): ?>
                    <div class="auth-alert-box alert-error"><span><?= htmlspecialchars($errorMessage) ?></span></div>
                <?php endif; ?>

                <form class="auth-form" method="POST" action="register.php">
                    <div class="form-field-group">
                        <label for="full_name" class="form-label-text">Full name</label>
                        <input type="text" id="full_name" name="full_name" class="form-input-control" placeholder="Dr. Jordan Hayes, CPO" required>
                    </div>
                    <div class="form-field-group">
                        <label for="role" class="form-label-text">Clinical role</label>
                        <input type="text" id="role" name="role" class="form-input-control" placeholder="e.g. Certified Prosthetist" value="Prosthetist" required>
                    </div>
                    <div class="form-field-group">
                        <label for="email" class="form-label-text">Work email address</label>
                        <input type="email" id="email" name="email" class="form-input-control" placeholder="jordan.hayes@clinic.com" required>
                    </div>
                    <div class="form-field-group">
                        <label for="password" class="form-label-text">Password</label>
                        <input type="password" id="password" name="password" class="form-input-control" placeholder="Create a secure password" required>
                    </div>
                    <button type="submit" class="btn-auth-action">Create account</button>
                    <div class="auth-switch-prompt">
                        <span>Already have an account?</span>
                        <a href="index.php" class="link-auth-switch">Sign in</a>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>