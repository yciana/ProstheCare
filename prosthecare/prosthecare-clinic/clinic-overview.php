<?php
/**
 * ProstheCare Clinic Management System - Staff Clinic Overview
 * Full Clinical Operations workspace matching Figma Staff design (Screenshot 1)
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

// Fetch live queue data from Supabase (or fallback to Figma design defaults)
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

// Compute KPIs
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

<!-- CLINIC OVERVIEW HEADER (Staff View) -->
<section class="staff-overview-header" aria-label="Overview Header">
    <div>
        <h1 class="client-greeting-title">Clinic overview</h1>
        <p class="client-greeting-subtitle">Wednesday, 14 May &middot; Live operations</p>
    </div>
    <div>
        <span class="clinic-status-badge">
            <span class="status-pulse-dot"></span>
            <span>Clinic is open</span>
        </span>
    </div>
</section>

<!-- KPI 4-CARD STATS GRID -->
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
        <div class="kpi-number"><?= htmlspecialchars((string)$avgWaitMinutes) ?> min</div>
        <span class="kpi-note">+ 4 min vs. yesterday</span>
    </div>
</section>

<!-- MAIN TWO-COLUMN WORKSPACE GRID -->
<div class="staff-main-grid">
    <!-- LEFT COLUMN: LIVE QUEUE CARD -->
    <div class="staff-card">
        <div class="staff-card-header">
            <div>
                <h2 class="staff-card-title">Live queue</h2>
                <p class="staff-card-subtitle">Auto-refreshes every 30 seconds</p>
            </div>
            <span class="badge-live-pill">
                <span class="status-pulse-dot"></span>
                <span>Live</span>
            </span>
        </div>

        <div class="staff-queue-list">
            <?php foreach ($queueItems as $item): 
                $st = strtolower($item['status'] ?? '');
                $pillClass = 'queue-status-waiting';
                if ($st === 'in service') {
                    $pillClass = 'queue-status-in-service';
                } elseif ($st === 'checked in') {
                    $pillClass = 'queue-status-checked-in';
                }
            ?>
                <div class="staff-queue-row">
                    <div class="queue-col-ticket"><?= htmlspecialchars($item['ticket_number'] ?? 'A-00') ?></div>
                    <div class="queue-col-info">
                        <div class="queue-client-name"><?= htmlspecialchars($item['client_name'] ?? 'Client') ?></div>
                        <div class="queue-service-type"><?= htmlspecialchars($item['service_type'] ?? 'Fitting') ?></div>
                    </div>
                    <span class="queue-status-pill <?= $pillClass ?>"><?= htmlspecialchars($item['status'] ?? 'Waiting') ?></span>
                    <a href="patients.php?search=<?= urlencode($item['client_name'] ?? '') ?>" class="queue-action-link">Open &rsaquo;</a>
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

            <div class="approval-items-list">
                <?php if (empty($pendingApprovals)): ?>
                    <p class="kpi-note" style="padding: 16px 0;">All requests have been reviewed.</p>
                <?php else: ?>
                    <?php foreach ($pendingApprovals as $appr): ?>
                        <div class="approval-item-block">
                            <div class="approval-req-code"><?= htmlspecialchars($appr['request_code'] ?? 'PR-0000') ?></div>
                            <div class="approval-person-name"><?= htmlspecialchars($appr['client_name'] ?? '') ?></div>
                            <div class="approval-procedure-title"><?= htmlspecialchars($appr['title'] ?? '') ?></div>
                            
                            <form method="POST" class="approval-actions-row">
                                <input type="hidden" name="action" value="update_approval">
                                <input type="hidden" name="request_code" value="<?= htmlspecialchars($appr['request_code'] ?? '') ?>">
                                <button type="submit" name="status" value="Rejected" class="btn-approval-reject">Reject</button>
                                <button type="submit" name="status" value="Approved" class="btn-approval-approve">Approve</button>
                            </form>
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

<!-- FIND A CLIENT RECORD BAR -->
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
