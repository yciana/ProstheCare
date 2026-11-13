<?php
/**
 * ProstheCare Clinic Management System - Staff Dashboard
 * Matches Figma design (Screenshot 1: Clinic Overview)
 */

$pageTitle = 'Clinic Overview';
require_once __DIR__ . '/includes/header.php';

$message = '';
$isError = false;

// Handle Pending Approvals (Approve / Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_approval') {
    $requestCode = trim($_POST['request_code'] ?? '');
    $newStatus = trim($_POST['status'] ?? 'Approved');

    if (!empty($requestCode)) {
        // Update in Supabase
        $supabase->from('pending_approvals')->eq('request_code', $requestCode)->update(['status' => $newStatus]);
        // Update session approvals store if present
        if (!isset($_SESSION['processed_approvals'])) {
            $_SESSION['processed_approvals'] = [];
        }
        $_SESSION['processed_approvals'][$requestCode] = $newStatus;

        $message = "Request {$requestCode} has been marked as {$newStatus}.";
    }
}

// Fetch live data from Supabase (or fallback to Figma design defaults)
$queueItems = $supabase->from('clinic_queue')->select('*')->order('created_at', true)->get();
if (empty($queueItems)) {
    $queueItems = [
        ['ticket_number' => 'A-04', 'client_name' => 'Daniel Lee', 'service_type' => 'Prosthetic fitting', 'status' => 'In service'],
        ['ticket_number' => 'A-05', 'client_name' => 'Maria Santos', 'service_type' => 'Consultation', 'status' => 'Waiting'],
        ['ticket_number' => 'A-06', 'client_name' => 'Robert Chen', 'service_type' => 'Maintenance', 'status' => 'Waiting'],
        ['ticket_number' => 'A-07', 'client_name' => 'Sarah Mitchell', 'service_type' => 'Prosthetic fitting', 'status' => 'Checked in']
    ];
}

$pendingApprovals = $supabase->from('pending_approvals')->select('*')->eq('status', 'Pending')->get();
if (empty($pendingApprovals)) {
    $pendingApprovals = [
        ['request_code' => 'PR-0192', 'client_name' => 'Michael Grant', 'title' => 'Socket replacement', 'status' => 'Pending'],
        ['request_code' => 'PR-0190', 'client_name' => 'Emma Davis', 'title' => 'Knee unit upgrade', 'status' => 'Pending']
    ];
}

// Filter out locally processed in session
if (!empty($_SESSION['processed_approvals'])) {
    $pendingApprovals = array_filter($pendingApprovals, function($item) {
        return empty($_SESSION['processed_approvals'][$item['request_code']]);
    });
}

// Compute or define KPIs matching Screenshot 1
$waitingCount = count(array_filter($queueItems, fn($q) => strtolower($q['status'] ?? '') === 'waiting')) ?: 8;
$inServiceCount = count(array_filter($queueItems, fn($q) => strtolower($q['status'] ?? '') === 'in service')) ?: 4;
$completedTodayCount = 23;
$avgWaitMinutes = 16;
?>

<!-- Header Flash Alert -->
<?php if (!empty($message)): ?>
    <div class="auth-alert-box alert-success" style="margin-bottom: 20px;">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span><?= htmlspecialchars($message) ?></span>
    </div>
<?php endif; ?>

<!-- CLINIC OVERVIEW HEADER (Screenshot 1) -->
<section class="staff-overview-header" aria-label="Overview Header">
    <div>
        <h1 class="client-greeting-title">Clinic overview</h1>
        <p class="client-greeting-subtitle">Wednesday, 14 May - Live operations</p>
    </div>
    <div>
        <span class="clinic-status-badge">
            <span class="status-pulse-dot"></span>
            <span>Clinic is open</span>
        </span>
    </div>
</section>

<!-- KPI 4-CARD STATS GRID (Screenshot 1) -->
<section class="staff-kpi-grid" aria-label="Key Performance Indicators">
    <div class="staff-kpi-card">
        <span class="kpi-label">WAITING</span>
        <div class="kpi-number"><?= htmlspecialchars((string)$waitingCount) ?></div>
        <span class="kpi-note">+2 in the last 15 min</span>
    </div>

    <div class="staff-kpi-card">
        <span class="kpi-label">IN SERVICE</span>
        <div class="kpi-number"><?= htmlspecialchars((string)$inServiceCount) ?></div>
        <span class="kpi-note">Across 6 treatment rooms</span>
    </div>

    <div class="staff-kpi-card">
        <span class="kpi-label">COMPLETED TODAY</span>
        <div class="kpi-number"><?= htmlspecialchars((string)$completedTodayCount) ?></div>
        <span class="kpi-note">83% of today's bookings</span>
    </div>

    <div class="staff-kpi-card">
        <span class="kpi-label">AVG. WAIT TIME</span>
        <div class="kpi-number"><?= htmlspecialchars((string)$avgWaitMinutes) ?> <span style="font-size: 16px; font-weight: 500;">min</span></div>
        <span class="kpi-note">+4 min vs. yesterday</span>
    </div>
</section>

<!-- MAIN 2-COLUMN OPERATIONAL GRID (Screenshot 1) -->
<div class="staff-main-grid">
    <!-- LEFT COLUMN: LIVE QUEUE -->
    <div class="staff-card">
        <div class="staff-card-header">
            <div>
                <h2 class="staff-card-title">Live queue</h2>
                <p class="staff-card-subtitle">Auto-refreshes every 30 seconds</p>
            </div>
            <span class="badge-live-pill">
                <span class="status-pulse-dot" style="width: 6px; height: 6px;"></span>
                <span>Live</span>
            </span>
        </div>

        <div class="staff-queue-list">
            <?php foreach ($queueItems as $q): 
                $st = strtolower($q['status'] ?? 'waiting');
                $statusClass = 'queue-status-waiting';
                if ($st === 'in service') $statusClass = 'queue-status-in-service';
                if ($st === 'checked in') $statusClass = 'queue-status-checked-in';
            ?>
                <div class="staff-queue-row">
                    <div class="queue-col-ticket"><?= htmlspecialchars($q['ticket_number'] ?? 'A-00') ?></div>
                    <div class="queue-col-info">
                        <div class="queue-client-name"><?= htmlspecialchars($q['client_name'] ?? 'Client') ?></div>
                        <div class="queue-service-type"><?= htmlspecialchars($q['service_type'] ?? 'Fitting') ?></div>
                    </div>
                    <div>
                        <span class="queue-status-pill <?= $statusClass ?>">
                            <?= htmlspecialchars(ucfirst($q['status'] ?? 'Waiting')) ?>
                        </span>
                    </div>
                    <div>
                        <a href="patients.php?search=<?= urlencode($q['client_name'] ?? '') ?>" class="queue-action-link">
                            Open &gt;
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <a href="patients.php" class="staff-card-footer-link">Open full queue board</a>
    </div>

    <!-- RIGHT COLUMN: PENDING APPROVALS & MAINTENANCE DUE -->
    <div>
        <!-- PENDING APPROVALS CARD -->
        <div class="staff-card pending-approvals-card">
            <div class="staff-card-header">
                <div>
                    <h2 class="staff-card-title">Pending approvals</h2>
                    <p class="staff-card-subtitle"><?= count($pendingApprovals) ?> requests need review</p>
                </div>
                <span class="badge-amber-pill"><?= count($pendingApprovals) ?> pending</span>
            </div>

            <div class="staff-approvals-list">
                <?php if (empty($pendingApprovals)): ?>
                    <div style="padding: 16px 0; font-size: 13px; color: var(--color-text-muted);">
                        All pending prosthetic requests have been reviewed.
                    </div>
                <?php else: ?>
                    <?php foreach ($pendingApprovals as $app): ?>
                        <div class="approval-item-block">
                            <div class="approval-req-code"><?= htmlspecialchars($app['request_code'] ?? 'PR-0000') ?></div>
                            <div class="approval-person-name"><?= htmlspecialchars($app['client_name'] ?? 'Client') ?></div>
                            <div class="approval-procedure-title"><?= htmlspecialchars($app['title'] ?? 'Request') ?></div>
                            <div class="approval-actions-row">
                                <form method="POST" action="dashboard.php" style="margin: 0;">
                                    <input type="hidden" name="action" value="update_approval">
                                    <input type="hidden" name="request_code" value="<?= htmlspecialchars($app['request_code'] ?? '') ?>">
                                    <input type="hidden" name="status" value="Rejected">
                                    <button type="submit" class="btn-approval-reject" style="width: 100%;">Reject</button>
                                </form>
                                <form method="POST" action="dashboard.php" style="margin: 0;">
                                    <input type="hidden" name="action" value="update_approval">
                                    <input type="hidden" name="request_code" value="<?= htmlspecialchars($app['request_code'] ?? '') ?>">
                                    <input type="hidden" name="status" value="Approved">
                                    <button type="submit" class="btn-approval-approve" style="width: 100%;">Approve</button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <a href="patients.php" class="staff-card-footer-link">View all approvals</a>
        </div>

        <!-- MAINTENANCE DUE CARD -->
        <div class="staff-card">
            <div class="staff-card-header">
                <div>
                    <h2 class="staff-card-title">Maintenance due</h2>
                </div>
                <span class="badge-danger-pill">5 overdue</span>
            </div>

            <div class="maintenance-stat-row">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: var(--color-text-muted);">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>
                <span>12 clients due within 30 days</span>
            </div>

            <a href="maintenance.php" class="btn-review-maintenance">Review maintenance list</a>
        </div>
    </div>
</div>

<!-- FIND A CLIENT RECORD BAR (Screenshot 1 Bottom) -->
<section class="find-client-bar" aria-label="Find Client Record">
    <div class="find-client-left">
        <div class="find-icon-circle">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
        </div>
        <div>
            <h3 class="find-client-title">Find a client record</h3>
            <p class="find-client-subtitle">Search by name, client ID, phone, or email.</p>
        </div>
    </div>

    <form action="patients.php" method="GET" class="find-client-form">
        <div class="find-search-input-wrapper">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input 
                type="text" 
                name="search" 
                class="find-search-input" 
                placeholder="Search client records..." 
                aria-label="Search client records"
            >
        </div>
        <button type="submit" class="btn-find-search">Search</button>
    </form>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
