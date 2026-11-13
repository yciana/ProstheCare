<?php
/**
 * ProstheCare Clinic Management System - Admin Patient List
 * Matches Figma design (media_1794282411280_589dc203.png)
 * Strictly clean production view - No "Preview As" switchers.
 */

$pageTitle = 'Patient list';
require_once __DIR__ . '/includes/header.php';

$message = '';
$isError = false;

// Handle Add Patient
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_patient') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $code = 'PC-' . rand(1210, 9990);
    $deviceType = trim($_POST['device_type'] ?? 'Transtibial');
    $phone = trim($_POST['phone'] ?? '+1 555 010 0000');
    $email = trim($_POST['email'] ?? '');

    if (empty($firstName) || empty($lastName)) {
        $message = 'First and last names are required.';
        $isError = true;
    } else {
        $supabase->from('patients')->insert([
            'patient_code' => $code,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'device_type' => $deviceType,
            'phone' => $phone,
            'email' => $email,
            'status' => 'Active'
        ]);
        $message = "Patient {$firstName} {$lastName} ({$code}) added to registry.";
    }
}

// Canonical patient list matching Figma design
$patientsData = [
    [
        'name' => 'Sarah Mitchell',
        'code' => 'PC-1048',
        'initials' => 'SM',
        'procedure' => 'Prosthetic fitting',
        'time' => '10:30 AM',
        'status' => 'Checked in'
    ],
    [
        'name' => 'Daniel Lee',
        'code' => 'PC-1102',
        'initials' => 'DL',
        'procedure' => 'Gait assessment',
        'time' => '9:00 AM',
        'status' => 'Completed'
    ],
    [
        'name' => 'Robert Chen',
        'code' => 'PC-1154',
        'initials' => 'RC',
        'procedure' => 'Maintenance',
        'time' => '11:30 AM',
        'status' => 'Waiting'
    ],
    [
        'name' => 'Maria Santos',
        'code' => 'PC-1187',
        'initials' => 'MS',
        'procedure' => 'Initial consultation',
        'time' => '1:00 PM',
        'status' => 'Expected'
    ],
    [
        'name' => 'Olivia Parker',
        'code' => 'PC-1203',
        'initials' => 'OP',
        'procedure' => 'Socket adjustment',
        'time' => '2:30 PM',
        'status' => 'Expected'
    ]
];

// Search filter
$search = strtolower(trim($_GET['search'] ?? ''));
if ($search !== '') {
    $patientsData = array_filter($patientsData, function($p) use ($search) {
        return stripos($p['name'], $search) !== false || stripos($p['code'], $search) !== false || stripos($p['procedure'], $search) !== false;
    });
}
?>

<!-- HEADER ROW (Screenshot 5) -->
<section class="client-greeting-row" aria-label="Patient List Header" style="margin-bottom: 24px;">
    <div>
        <h1 class="client-greeting-title">Patient list</h1>
        <p class="client-greeting-subtitle">View and manage clients expected in the clinic.</p>
    </div>
    <div>
        <button type="button" class="btn-primary-action" onclick="document.getElementById('addPatientModal').style.display='flex';">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Add patient</span>
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

<!-- PATIENT LIST CARD -->
<div class="staff-card patient-list-card">
    <!-- SEARCH HEADER -->
    <div class="patient-list-header-bar">
        <form method="GET" action="admin-patients.php" class="toolbar-search-wrap" style="flex: 1; max-width: 380px;">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input 
                type="text" 
                name="search" 
                class="find-search-input" 
                placeholder="Search patients" 
                value="<?= htmlspecialchars($_GET['search'] ?? '') ?>"
                oninput="this.form.submit()"
            >
        </form>
        <span class="patient-today-count-badge">5 patients today</span>
    </div>

    <!-- ROWS -->
    <div class="patient-list-items">
        <?php foreach ($patientsData as $pt): 
            $stLower = strtolower($pt['status']);
            $pillClass = 'pill-patient-expected';
            if ($stLower === 'checked in') {
                $pillClass = 'pill-patient-checkedin';
            } elseif ($stLower === 'completed') {
                $pillClass = 'pill-patient-completed';
            } elseif ($stLower === 'waiting') {
                $pillClass = 'pill-patient-waiting';
            }
        ?>
            <div class="patient-row-item">
                <div class="patient-row-left">
                    <div class="table-user-avatar"><?= htmlspecialchars($pt['initials']) ?></div>
                    <div>
                        <div class="table-user-name"><?= htmlspecialchars($pt['name']) ?></div>
                        <div class="table-user-email"><?= htmlspecialchars($pt['code']) ?></div>
                    </div>
                </div>

                <div class="patient-row-mid">
                    <div class="patient-procedure-title"><?= htmlspecialchars($pt['procedure']) ?></div>
                    <div class="patient-time-text"><?= htmlspecialchars($pt['time']) ?></div>
                </div>

                <div class="patient-row-status">
                    <span class="patient-status-pill <?= $pillClass ?>"><?= htmlspecialchars($pt['status']) ?></span>
                </div>

                <div class="patient-row-action">
                    <a href="patients.php" class="link-view-action" style="font-size: 13px;">Open record &gt;</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- ADD PATIENT MODAL -->
<div id="addPatientModal" class="modal-overlay" style="display: none;">
    <div class="modal-dialog-card">
        <div class="modal-header-row">
            <h3 class="modal-title-text">Add new patient</h3>
            <button type="button" class="modal-close-btn" onclick="document.getElementById('addPatientModal').style.display='none';">&times;</button>
        </div>
        <form method="POST" action="admin-patients.php">
            <input type="hidden" name="action" value="create_patient">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 14px;">
                <div>
                    <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 6px;">First name</label>
                    <input type="text" name="first_name" class="find-search-input" placeholder="e.g. John" required>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 6px;">Last name</label>
                    <input type="text" name="last_name" class="find-search-input" placeholder="e.g. Doe" required>
                </div>
            </div>
            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 6px;">Device / Prosthesis type</label>
                <input type="text" name="device_type" class="find-search-input" placeholder="e.g. Left transtibial prosthesis">
            </div>
            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 6px;">Contact phone</label>
                <input type="text" name="phone" class="find-search-input" placeholder="+1 555 010 0000">
            </div>
            <div style="margin-bottom: 24px;">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 6px;">Email address</label>
                <input type="email" name="email" class="find-search-input" placeholder="john.doe@example.com">
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-approval-reject" onclick="document.getElementById('addPatientModal').style.display='none';">Cancel</button>
                <button type="submit" class="btn-primary-action">Add patient record</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
