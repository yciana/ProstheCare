<?php
/**
 * ProstheCare Clinic Management System - Supabase Diagnostic Test
 */

require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/includes/SupabaseClient.php';

$supabase = new SupabaseClient();

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supabase Connection Diagnostics - ProstheCare</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/auth.css">
    <style>
        body { padding: 40px; background: #07474b; color: #fff; font-family: sans-serif; }
        .diag-box { max-width: 800px; margin: 0 auto; background: #fff; color: #222; padding: 30px; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,0.2); }
        .diag-title { font-size: 24px; font-weight: bold; margin-bottom: 20px; color: #0d797f; }
        .diag-item { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee; }
        .diag-badge { padding: 4px 10px; border-radius: 4px; font-weight: bold; font-size: 13px; }
        .badge-ok { background: #e6f4ea; color: #137333; }
        .badge-err { background: #fce8e6; color: #c5221f; }
        .badge-warn { background: #fef7e0; color: #b06000; }
        .diag-actions { margin-top: 24px; display: flex; gap: 12px; }
        .diag-btn { padding: 10px 18px; border-radius: 4px; text-decoration: none; font-weight: bold; font-size: 14px; }
        .btn-green { background: #0d797f; color: #fff; }
        .btn-gray { background: #f1f3f4; color: #333; }
        code { background: #f1f3f4; padding: 2px 6px; border-radius: 4px; font-size: 13px; }
    </style>
</head>
<body>
    <div class="diag-box">
        <h1 class="diag-title">🩺 Supabase Connection Diagnostics</h1>

        <div class="diag-item">
            <span>Project URL (SUPABASE_URL):</span>
            <span><code><?= htmlspecialchars(SUPABASE_URL) ?></code></span>
        </div>

        <div class="diag-item">
            <span>Anon Public Key (SUPABASE_ANON_KEY):</span>
            <?php if (!empty(SUPABASE_ANON_KEY)): ?>
                <span class="diag-badge badge-ok">Configured (<?= strlen(SUPABASE_ANON_KEY) ?> chars)</span>
            <?php else: ?>
                <span class="diag-badge badge-err">Missing in .env</span>
            <?php endif; ?>
        </div>

        <div class="diag-item">
            <span>Service Role Key (SUPABASE_SERVICE_KEY):</span>
            <?php if (!empty(SUPABASE_SERVICE_KEY)): ?>
                <span class="diag-badge badge-ok">Configured</span>
            <?php else: ?>
                <span class="diag-badge badge-warn">Optional (Not set)</span>
            <?php endif; ?>
        </div>

        <?php
        $tableStatus = [];
        if ($supabase->isLive()) {
            foreach (['patients', 'profiles', 'appointments', 'care_plans'] as $table) {
                $rows = $supabase->from($table)->limit(1)->get();
                $err = $supabase->getLastError();
                $tableStatus[$table] = [
                    'ok' => empty($err),
                    'count' => count($rows),
                    'err' => $err
                ];
            }
        }
        ?>

        <?php if ($supabase->isLive()): ?>
            <h3 style="margin-top: 24px; margin-bottom: 12px;">Database Tables Verification:</h3>
            <?php foreach ($tableStatus as $table => $status): ?>
                <div class="diag-item">
                    <span>Table <code>public.<?= htmlspecialchars($table) ?></code>:</span>
                    <?php if ($status['ok']): ?>
                        <span class="diag-badge badge-ok">Available (Accessible)</span>
                    <?php else: ?>
                        <span class="diag-badge badge-err">Error: <?= htmlspecialchars($status['err']) ?></span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="margin-top: 20px; padding: 16px; background: #fff8e1; border-left: 4px solid #ffb300; border-radius: 4px;">
                <strong>Next Step:</strong> Please open <code>.env</code> and paste your <code>SUPABASE_ANON_KEY</code>.<br>
                You can copy it from your Supabase dashboard at:<br>
                <a href="https://supabase.com/dashboard/project/gdpzdypxospokczofxlu/settings/api" target="_blank" style="color: #0d797f; font-weight: bold;">
                    https://supabase.com/dashboard/project/gdpzdypxospokczofxlu/settings/api
                </a>
            </div>
        <?php endif; ?>

        <div class="diag-actions">
            <a href="register.php" class="diag-btn btn-green">Go to Registration</a>
            <a href="index.php" class="diag-btn btn-gray">Go to Sign In</a>
            <a href="test-connection.php" class="diag-btn btn-gray">Re-run Test</a>
        </div>
    </div>
</body>
</html>
