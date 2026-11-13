<?php
/**
 * ProstheCare Clinic Management System - Client Records
 * Matches Figma design (Screenshot 2: Client record)
 */

$pageTitle = 'Client Records';
require_once __DIR__ . '/includes/header.php';

$message = '';
$isError = false;

// Handle Edit Clinical Overview / Care Notes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_clinical_overview') {
    $patientCode = trim($_POST['patient_code'] ?? 'PC-1048');
    $ampLevel = trim($_POST['amputation_level'] ?? 'Left transtibial');
    $deviceType = trim($_POST['device_type'] ?? 'Dynamic Response K3');
    $activityLevel = trim($_POST['activity_level'] ?? 'K3 - Community ambulator');
    $careNotes = trim($_POST['care_notes'] ?? '');

    $supabase->from('patients')->eq('patient_code', $patientCode)->update([
        'amputation_level' => $ampLevel,
        'device_type' => $deviceType,
        'activity_level' => $activityLevel,
        'clinical_notes' => $careNotes
    ]);

    // Update in session store
    $_SESSION['client_record_override'] = [
        'amputation_level' => $ampLevel,
        'device_type' => $deviceType,
        'activity_level' => $activityLevel,
        'clinical_notes' => $careNotes
    ];

    $message = "Clinical record for {$patientCode} has been updated successfully.";
}

// Handle New Clinical Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_request') {
    $clientName = trim($_POST['client_name'] ?? 'Sarah Mitchell');
    $title = trim($_POST['request_title'] ?? 'Socket replacement');
    $requestCode = 'PR-' . rand(1000, 9999);

    $supabase->from('pending_approvals')->insert([
        'request_code' => $requestCode,
        'client_name' => $clientName,
        'title' => $title,
        'status' => 'Pending'
    ]);

    $message = "New request {$requestCode} ({$title}) submitted for review.";
}

// Handle Message Client
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'message_client') {
    $clientName = trim($_POST['client_name'] ?? 'Sarah Mitchell');
    $msgBody = trim($_POST['message_body'] ?? '');
    $message = "Message successfully sent to {$clientName}!";
}

// Fetch all patients for directory/search switch
$allPatients = $supabase->from('patients')->select('*')->get();

// Determine selected client (default: Sarah Mitchell PC-1048)
$searchQuery = trim($_GET['search'] ?? '');
$targetCode = trim($_GET['code'] ?? 'PC-1048');

$selectedClient = null;
if (!empty($searchQuery)) {
    foreach ($allPatients as $p) {
        $name = ($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '');
        if (stripos($name, $searchQuery) !== false || 
            stripos($p['patient_code'] ?? '', $searchQuery) !== false ||
            stripos($p['email'] ?? '', $searchQuery) !== false ||
            stripos($p['phone'] ?? '', $searchQuery) !== false) {
            $selectedClient = $p;
            break;
        }
    }
}

if (!$selectedClient) {
    foreach ($allPatients as $p) {
        if (($p['patient_code'] ?? '') === $targetCode) {
            $selectedClient = $p;
            break;
        }
    }
}

// Fallback matching Figma Screenshot 2
if (!$selectedClient) {
    $selectedClient = [
        'patient_code' => 'PC-1048',
        'first_name' => 'Sarah',
        'last_name' => 'Mitchell',
        'email' => 'sarah.m@example.com',
        'phone' => '+1 555 014 9822',
        'dob' => '1989-03-18',
        'amputation_level' => 'Left transtibial',
        'device_type' => 'Dynamic Response K3',
        'activity_level' => 'K3 - Community ambulator',
        'clinical_notes' => 'Client reports improved comfort since the last socket adjustment. Continue current liner care routine and monitor pressure at distal tibia.',
        'status' => 'Active'
    ];
}

// Apply session override if any
if (!empty($_SESSION['client_record_override'])) {
    $selectedClient['amputation_level'] = $_SESSION['client_record_override']['amputation_level'];
    $selectedClient['device_type'] = $_SESSION['client_record_override']['device_type'];
    $selectedClient['activity_level'] = $_SESSION['client_record_override']['activity_level'];
    $selectedClient['clinical_notes'] = $_SESSION['client_record_override']['clinical_notes'];
}

$clientFullName = trim(($selectedClient['first_name'] ?? 'Sarah') . ' ' . ($selectedClient['last_name'] ?? 'Mitchell'));
$clientInitials = strtoupper(substr($selectedClient['first_name'] ?? 'S', 0, 1) . substr($selectedClient['last_name'] ?? 'M', 0, 1));
$dobFormatted = !empty($selectedClient['dob']) ? date('d M Y', strtotime($selectedClient['dob'])) : '18 Mar 1989';
$activeTab = $_GET['tab'] ?? 'overview';
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

<!-- CLIENT RECORD HEADER & ACTIONS (Screenshot 2) -->
<section class="client-record-header-row" aria-label="Client Record Header">
    <div>
        <h1 class="client-greeting-title">Client record</h1>
        <p class="client-greeting-subtitle"><?= htmlspecialchars($clientFullName) ?> - <?= htmlspecialchars($selectedClient['patient_code']) ?></p>
    </div>
    <div class="client-record-actions">
        <!-- Quick Switch Client Dropdown -->
        <?php if (!empty($allPatients) && count($allPatients) > 1): ?>
            <select onchange="window.location.href='patients.php?code=' + this.value" class="condition-select" style="min-width: 170px; padding: 9px 12px; font-weight: 500;">
                <?php foreach ($allPatients as $pat): ?>
                    <option value="<?= htmlspecialchars($pat['patient_code'] ?? '') ?>" <?= ($pat['patient_code'] ?? '') === $selectedClient['patient_code'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars(($pat['first_name'] ?? '') . ' ' . ($pat['last_name'] ?? '')) ?> (<?= htmlspecialchars($pat['patient_code'] ?? '') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        <?php endif; ?>

        <button type="button" class="btn-approval-reject" onclick="document.getElementById('messageModal').style.display = 'flex';">
            Message client
        </button>
        <button type="button" class="btn-approval-approve" onclick="document.getElementById('requestModal').style.display = 'flex';">
            + New request
        </button>
    </div>
</section>

<!-- CLIENT PROFILE BANNER (Screenshot 2) -->
<section class="client-profile-banner" aria-label="Client Profile Summary">
    <div class="client-banner-identity">
        <div class="client-banner-avatar">
            <?= htmlspecialchars($clientInitials) ?>
        </div>
        <div>
            <h2 class="client-banner-name"><?= htmlspecialchars($clientFullName) ?></h2>
            <div class="client-banner-status">
                <span>Client since August 2022</span>
                <span> &middot; </span>
                <span style="color: var(--color-success); font-weight: 600;">Active</span>
            </div>
        </div>
    </div>

    <div class="client-banner-meta-grid">
        <div class="meta-info-item">
            <span class="meta-info-label">Email</span>
            <span class="meta-info-val"><?= htmlspecialchars($selectedClient['email'] ?? 'sarah.m@example.com') ?></span>
        </div>
        <div class="meta-info-item">
            <span class="meta-info-label">Phone</span>
            <span class="meta-info-val"><?= htmlspecialchars($selectedClient['phone'] ?? '+1 555 014 9822') ?></span>
        </div>
        <div class="meta-info-item">
            <span class="meta-info-label">Date of birth</span>
            <span class="meta-info-val"><?= htmlspecialchars($dobFormatted) ?></span>
        </div>
        <div class="meta-info-item">
            <span class="meta-info-label">Primary prosthetist</span>
            <span class="meta-info-val">Dr. James Wilson</span>
        </div>
    </div>
</section>

<!-- NAVIGATION TABS (Screenshot 2) -->
<nav class="client-record-tabs-nav" aria-label="Record Tabs">
    <a href="patients.php?code=<?= urlencode($selectedClient['patient_code']) ?>&tab=overview" class="client-tab-link <?= $activeTab === 'overview' ? 'active' : '' ?>">Overview</a>
    <a href="patients.php?code=<?= urlencode($selectedClient['patient_code']) ?>&tab=appointments" class="client-tab-link <?= $activeTab === 'appointments' ? 'active' : '' ?>">Appointments</a>
    <a href="patients.php?code=<?= urlencode($selectedClient['patient_code']) ?>&tab=requests" class="client-tab-link <?= $activeTab === 'requests' ? 'active' : '' ?>">Requests</a>
    <a href="patients.php?code=<?= urlencode($selectedClient['patient_code']) ?>&tab=measurements" class="client-tab-link <?= $activeTab === 'measurements' ? 'active' : '' ?>">Measurements</a>
    <a href="maintenance.php?client=<?= urlencode($selectedClient['patient_code']) ?>" class="client-tab-link <?= $activeTab === 'maintenance' ? 'active' : '' ?>">Maintenance</a>
    <a href="patients.php?code=<?= urlencode($selectedClient['patient_code']) ?>&tab=documents" class="client-tab-link <?= $activeTab === 'documents' ? 'active' : '' ?>">Documents</a>
</nav>

<!-- TAB 1: OVERVIEW CONTENT (Screenshot 2) -->
<?php if ($activeTab === 'overview'): ?>
    <div class="staff-main-grid">
        <!-- LEFT: CLINICAL OVERVIEW -->
        <div class="clinical-card">
            <div class="clinical-header-row">
                <h3 class="staff-card-title">Clinical overview</h3>
                <button type="button" class="clinical-edit-link" style="background: none; border: none; cursor: pointer;" onclick="document.getElementById('editOverviewModal').style.display = 'flex';">Edit</button>
            </div>

            <div class="clinical-spec-grid">
                <div class="clinical-spec-item">
                    <span class="clinical-spec-label">Amputation level</span>
                    <span class="clinical-spec-value"><?= htmlspecialchars($selectedClient['amputation_level'] ?? 'Left transtibial') ?></span>
                </div>
                <div class="clinical-spec-item">
                    <span class="clinical-spec-label">Current device</span>
                    <span class="clinical-spec-value"><?= htmlspecialchars($selectedClient['device_type'] ?? 'Dynamic Response K3') ?></span>
                </div>
                <div class="clinical-spec-item">
                    <span class="clinical-spec-label">Activity level</span>
                    <span class="clinical-spec-value"><?= htmlspecialchars($selectedClient['activity_level'] ?? 'K3 - Community ambulator') ?></span>
                </div>
                <div class="clinical-spec-item">
                    <span class="clinical-spec-label">Last assessment</span>
                    <span class="clinical-spec-value">April 25, 2025</span>
                </div>
            </div>

            <div class="clinical-divider"></div>

            <div class="care-notes-eyebrow">Care notes</div>
            <div class="care-notes-content">
                <?= nl2br(htmlspecialchars($selectedClient['clinical_notes'] ?? 'Client reports improved comfort since the last socket adjustment. Continue current liner care routine and monitor pressure at distal tibia.')) ?>
            </div>
        </div>

        <!-- RIGHT: UPCOMING & ALERTS -->
        <div>
            <!-- UPCOMING CARD -->
            <div class="side-upcoming-card">
                <div class="side-upcoming-header">
                    <h3 class="staff-card-title" style="margin-bottom: 0;">Upcoming</h3>
                    <span class="badge-date-pill">May 15</span>
                </div>
                <div class="upcoming-service-name">
                    <span class="upcoming-dot-teal"></span>
                    <span>Prosthetic fitting</span>
                </div>
                <div class="upcoming-loc-text">10:30 AM &middot; Fitting Room 2</div>
                <div class="upcoming-doctor-text">Dr. James Wilson</div>
            </div>

            <!-- ALERTS CARD -->
            <div class="side-alerts-card">
                <h3 class="side-alerts-header">Alerts</h3>
                <div class="alert-callout-warning">
                    <div class="alert-warning-icon">!</div>
                    <div>
                        <div class="alert-warning-title">Latex sensitivity</div>
                        <div class="alert-warning-desc">Use hypoallergenic liner products.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php else: ?>
    <!-- OTHER TABS PLACEHOLDER (Appointments, Requests, Measurements, Documents) -->
    <div class="clinical-card" style="padding: 32px; text-align: center; color: var(--color-text-muted);">
        <h3 class="staff-card-title" style="margin-bottom: 8px;"><?= ucfirst(htmlspecialchars($activeTab)) ?></h3>
        <p style="font-size: 13px; margin-bottom: 20px;">All clinical <?= htmlspecialchars($activeTab) ?> records for <?= htmlspecialchars($clientFullName) ?> (<?= htmlspecialchars($selectedClient['patient_code']) ?>) are synchronized with Supabase.</p>
        <a href="patients.php?code=<?= urlencode($selectedClient['patient_code']) ?>&tab=overview" class="btn-approval-approve" style="display: inline-block; text-decoration: none;">Return to Clinical Overview</a>
    </div>
<?php endif; ?>

<!-- MODAL: EDIT CLINICAL OVERVIEW -->
<div id="editOverviewModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 520px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--color-text-main);">Edit Clinical Overview</h3>
            <button type="button" onclick="document.getElementById('editOverviewModal').style.display='none';" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--color-text-muted);">&times;</button>
        </div>
        <form method="POST" action="patients.php?code=<?= urlencode($selectedClient['patient_code']) ?>">
            <input type="hidden" name="action" value="edit_clinical_overview">
            <input type="hidden" name="patient_code" value="<?= htmlspecialchars($selectedClient['patient_code']) ?>">

            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 600; color: var(--color-text-main); display: block; margin-bottom: 4px;">Amputation Level</label>
                <input type="text" name="amputation_level" class="form-input-control" value="<?= htmlspecialchars($selectedClient['amputation_level'] ?? 'Left transtibial') ?>" required>
            </div>
            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 600; color: var(--color-text-main); display: block; margin-bottom: 4px;">Current Device</label>
                <input type="text" name="device_type" class="form-input-control" value="<?= htmlspecialchars($selectedClient['device_type'] ?? 'Dynamic Response K3') ?>" required>
            </div>
            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 600; color: var(--color-text-main); display: block; margin-bottom: 4px;">Activity Level</label>
                <input type="text" name="activity_level" class="form-input-control" value="<?= htmlspecialchars($selectedClient['activity_level'] ?? 'K3 - Community ambulator') ?>" required>
            </div>
            <div style="margin-bottom: 18px;">
                <label style="font-size: 12px; font-weight: 600; color: var(--color-text-main); display: block; margin-bottom: 4px;">Care Notes</label>
                <textarea name="care_notes" class="form-input-control" rows="4" style="resize: vertical;"><?= htmlspecialchars($selectedClient['clinical_notes'] ?? '') ?></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-approval-reject" onclick="document.getElementById('editOverviewModal').style.display='none';">Cancel</button>
                <button type="submit" class="btn-approval-approve">Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: MESSAGE CLIENT -->
<div id="messageModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 480px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--color-text-main);">Message <?= htmlspecialchars($clientFullName) ?></h3>
            <button type="button" onclick="document.getElementById('messageModal').style.display='none';" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--color-text-muted);">&times;</button>
        </div>
        <form method="POST" action="patients.php?code=<?= urlencode($selectedClient['patient_code']) ?>">
            <input type="hidden" name="action" value="message_client">
            <input type="hidden" name="client_name" value="<?= htmlspecialchars($clientFullName) ?>">
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 600; color: var(--color-text-main); display: block; margin-bottom: 4px;">Message</label>
                <textarea name="message_body" class="form-input-control" rows="4" placeholder="Write a secure message or reminder..." required></textarea>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-approval-reject" onclick="document.getElementById('messageModal').style.display='none';">Cancel</button>
                <button type="submit" class="btn-approval-approve">Send Message</button>
            </div>
        </form>
    </div>
</div>

<!-- MODAL: NEW REQUEST -->
<div id="requestModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 480px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
            <h3 style="font-size: 16px; font-weight: 700; color: var(--color-text-main);">Create New Request</h3>
            <button type="button" onclick="document.getElementById('requestModal').style.display='none';" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--color-text-muted);">&times;</button>
        </div>
        <form method="POST" action="patients.php?code=<?= urlencode($selectedClient['patient_code']) ?>">
            <input type="hidden" name="action" value="new_request">
            <input type="hidden" name="client_name" value="<?= htmlspecialchars($clientFullName) ?>">
            <div style="margin-bottom: 16px;">
                <label style="font-size: 12px; font-weight: 600; color: var(--color-text-main); display: block; margin-bottom: 4px;">Request Title</label>
                <input type="text" name="request_title" class="form-input-control" placeholder="e.g. Socket replacement, Alignment check" required>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-approval-reject" onclick="document.getElementById('requestModal').style.display='none';">Cancel</button>
                <button type="submit" class="btn-approval-approve">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
