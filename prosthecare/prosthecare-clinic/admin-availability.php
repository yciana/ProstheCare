<?php
/**
 * ProstheCare Clinic Management System - Prosthetist Availability
 * Matches Figma design (media_1794282398883_93387bf4.png)
 * Strictly clean production view - No "Preview As" switchers.
 */

$pageTitle = 'Prosthetist availability';
require_once __DIR__ . '/includes/header.php';

$message = '';
$isError = false;

// Handle Availability Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_availability') {
    $practitionerName = trim($_POST['practitioner_name'] ?? '');
    $newStatus = trim($_POST['status'] ?? 'Available');
    $hours = trim($_POST['available_hours'] ?? '');

    if (!empty($practitionerName)) {
        $updateRes = $supabase->from('prosthetist_availability')
            ->eq('practitioner_name', $practitionerName)
            ->update([
                'status' => $newStatus,
                'available_hours' => $hours
            ]);
        $message = "Availability status for {$practitionerName} updated to {$newStatus}.";
    }
}

// Fetch availability from Supabase or use default canonical seed
$dbAvailability = $supabase->from('prosthetist_availability')->select('*')->order('display_order', true)->get();

if (empty($dbAvailability)) {
    $clinicalTeam = [
        [
            'name' => 'Dr. James Wilson',
            'title' => 'Prosthetist',
            'status' => 'Available',
            'hours' => '2:00 PM – 5:00 PM',
            'initials' => 'JW'
        ],
        [
            'name' => 'Dr. Rachel Kim',
            'title' => 'Senior Prosthetist',
            'status' => 'In session',
            'hours' => 'Available after 2:30 PM',
            'initials' => 'RK'
        ],
        [
            'name' => 'David Morgan',
            'title' => 'Prosthetic Technician',
            'status' => 'Offsite',
            'hours' => 'Returns tomorrow',
            'initials' => 'DM'
        ],
        [
            'name' => 'Amelia Patel',
            'title' => 'Prosthetist',
            'status' => 'Available',
            'hours' => 'Until 4:30 PM',
            'initials' => 'AP'
        ]
    ];
} else {
    $clinicalTeam = [];
    foreach ($dbAvailability as $item) {
        $clinicalTeam[] = [
            'name' => $item['practitioner_name'],
            'title' => $item['title'],
            'status' => $item['status'],
            'hours' => $item['available_hours'],
            'initials' => $item['avatar_initials'] ?? 'MD'
        ];
    }
}

// Calculate summary numbers
$availableCount = count(array_filter($clinicalTeam, fn($p) => strtolower($p['status']) === 'available'));
$inSessionCount = count(array_filter($clinicalTeam, fn($p) => strtolower($p['status']) === 'in session'));
$offsiteCount   = count(array_filter($clinicalTeam, fn($p) => strtolower($p['status']) === 'offsite'));
?>

<!-- HEADER ROW (Screenshot 3) -->
<section class="client-greeting-row" aria-label="Prosthetist Availability Header" style="margin-bottom: 24px;">
    <div>
        <h1 class="client-greeting-title">Prosthetist availability</h1>
        <p class="client-greeting-subtitle">View team availability and current clinic coverage.</p>
    </div>
    <div>
        <button type="button" class="btn-primary-action" onclick="document.getElementById('updateAvailModal').style.display='flex';">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Update availability</span>
        </button>
    </div>
</section>

<?php if (!empty($message)): ?>
    <div class="auth-alert-box alert-success" style="margin-bottom: 20px;">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span><?= htmlspecialchars($message) ?></span>
    </div>
<?php endif; ?>

<!-- 3 TOP STAT CARDS -->
<div class="admin-availability-stats-grid">
    <div class="staff-card avail-stat-card">
        <span class="avail-stat-number"><?= $availableCount ?></span>
        <span class="avail-stat-desc">Available now</span>
    </div>
    <div class="staff-card avail-stat-card">
        <span class="avail-stat-number"><?= $inSessionCount ?></span>
        <span class="avail-stat-desc">In session</span>
    </div>
    <div class="staff-card avail-stat-card">
        <span class="avail-stat-number"><?= $offsiteCount ?></span>
        <span class="avail-stat-desc">Offsite</span>
    </div>
</div>

<!-- CLINICAL TEAM LIVE LIST CARD -->
<div class="staff-card" style="margin-top: 24px;">
    <div class="staff-card-header" style="margin-bottom: 16px;">
        <div>
            <h2 class="staff-card-title">Clinical team</h2>
            <p class="staff-card-subtitle">Live availability for today</p>
        </div>
        <span class="live-indicator-pill">
            <span class="queue-dot-pulse"></span>
            <span>Live</span>
        </span>
    </div>

    <div class="avail-team-list">
        <?php foreach ($clinicalTeam as $doc): 
            $statusLower = strtolower($doc['status']);
            $pillClass = 'pill-avail-available';
            if ($statusLower === 'in session') {
                $pillClass = 'pill-avail-insession';
            } elseif ($statusLower === 'offsite') {
                $pillClass = 'pill-avail-offsite';
            }
        ?>
            <div class="avail-team-row">
                <div class="avail-team-left">
                    <div class="table-user-avatar"><?= htmlspecialchars($doc['initials']) ?></div>
                    <div>
                        <div class="table-user-name"><?= htmlspecialchars($doc['name']) ?></div>
                        <div class="table-user-email"><?= htmlspecialchars($doc['title']) ?></div>
                    </div>
                </div>

                <div class="avail-team-mid">
                    <span class="avail-status-badge <?= $pillClass ?>"><?= htmlspecialchars($doc['status']) ?></span>
                </div>

                <div class="avail-team-hours">
                    <span><?= htmlspecialchars($doc['hours']) ?></span>
                </div>

                <div class="avail-team-action">
                    <a href="admin-schedule.php" class="link-view-action" style="font-size: 13px;">View schedule &gt;</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- UPDATE AVAILABILITY MODAL -->
<div id="updateAvailModal" class="modal-overlay" style="display: none;">
    <div class="modal-dialog-card">
        <div class="modal-header-row">
            <h3 class="modal-title-text">Update team availability</h3>
            <button type="button" class="modal-close-btn" onclick="document.getElementById('updateAvailModal').style.display='none';">&times;</button>
        </div>
        <form method="POST" action="admin-availability.php">
            <input type="hidden" name="action" value="update_availability">
            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 6px;">Practitioner</label>
                <select name="practitioner_name" class="admin-select-filter" style="width: 100%;">
                    <?php foreach ($clinicalTeam as $doc): ?>
                        <option value="<?= htmlspecialchars($doc['name']) ?>"><?= htmlspecialchars($doc['name']) ?> (<?= htmlspecialchars($doc['title']) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 6px;">Current status</label>
                <select name="status" class="admin-select-filter" style="width: 100%;">
                    <option value="Available">Available</option>
                    <option value="In session">In session</option>
                    <option value="Offsite">Offsite</option>
                </select>
            </div>
            <div style="margin-bottom: 24px;">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 6px;">Available hours / Coverage note</label>
                <input type="text" name="available_hours" class="find-search-input" placeholder="e.g. 2:00 PM – 5:00 PM or Returns tomorrow" required>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-approval-reject" onclick="document.getElementById('updateAvailModal').style.display='none';">Cancel</button>
                <button type="submit" class="btn-primary-action">Save changes</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
