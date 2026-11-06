<?php
require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/includes/SupabaseClient.php';

$supabase = new SupabaseClient();
$message = '';
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_patient') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $code = 'PT-' . date('Y') . '-' . rand(100, 999);
    $amputation = trim($_POST['amputation_level'] ?? '');

    if (empty($firstName) || empty($lastName) || empty($amputation)) {
        $message = 'Please provide patient name and amputation details.';
        $isError = true;
    } else {
        $patientData = [
            'patient_code' => $code,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'dob' => trim($_POST['dob'] ?? '') ?: null,
            'phone' => trim($_POST['phone'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'amputation_level' => $amputation,
            'affected_side' => trim($_POST['affected_side'] ?? 'Right'),
            'activity_level' => $_POST['activity_level'] ?? 'K3',
            'device_type' => trim($_POST['device_type'] ?? ''),
            'fitting_stage' => trim($_POST['fitting_stage'] ?? 'Assessment'),
            'status' => 'Active',
            'clinical_notes' => trim($_POST['clinical_notes'] ?? '')
        ];

        if ($supabase->from('patients')->insert($patientData)) {
            $message = "Patient {$firstName} {$lastName} ({$code}) successfully registered!";
        } else {
            $message = 'Failed to save patient record.';
            $isError = true;
        }
    }
}

$pageTitle = 'Prosthetic Patients Directory';
require_once __DIR__ . '/includes/header.php';

$patients = $supabase->from('patients')->select('*')->order('created_at', false)->get();
?>

<div class="content-card-panel">
    <div class="panel-header-row">
        <div class="top-nav-left">
            <input 
                type="text" 
                class="form-input-control" 
                placeholder="Search patient by name, code or device..." 
                data-table-search="patientsTable"
            >
        </div>
        <button type="button" class="btn-primary-action" data-modal-target="newPatientModal">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Add Prosthetic Patient</span>
        </button>
    </div>

    <?php if (!empty($message)): ?>
        <div class="modal-body">
            <div class="auth-alert-box <?= $isError ? 'alert-error' : 'alert-success' ?>">
                <span><?= htmlspecialchars($message) ?></span>
            </div>
        </div>
    <?php endif; ?>

    <div class="data-table-container">
        <table class="clinic-table" id="patientsTable">
            <thead>
                <tr>
                    <th>Patient Identifier</th>
                    <th>Full Name</th>
                    <th>Amputation Level & Side</th>
                    <th>Activity Grade</th>
                    <th>Prescribed Device</th>
                    <th>Fitting Milestone</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($patients)): ?>
                    <tr><td colspan="7">No patients recorded yet in database.</td></tr>
                <?php else: ?>
                    <?php foreach ($patients as $p): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($p['patient_code'] ?? 'PT-000') ?></strong></td>
                            <td><strong><?= htmlspecialchars(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) ?></strong></td>
                            <td><?= htmlspecialchars($p['amputation_level'] ?? 'N/A') ?> (<?= htmlspecialchars($p['affected_side'] ?? 'Side') ?>)</td>
                            <td><span class="status-pill status-fitting">Level <?= htmlspecialchars($p['activity_level'] ?? 'K3') ?></span></td>
                            <td><?= htmlspecialchars($p['device_type'] ?? 'Custom Prosthesis') ?></td>
                            <td><span class="status-pill status-fitting"><?= htmlspecialchars($p['fitting_stage'] ?? 'Assessment') ?></span></td>
                            <td><span class="status-pill status-active"><?= htmlspecialchars($p['status'] ?? 'Active') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="newPatientModal">
    <div class="modal-card">
        <div class="modal-header-bar">
            <h3 class="modal-title">Register New Prosthetic Patient</h3>
            <button type="button" class="modal-close-btn" data-modal-close aria-label="Close dialog">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form method="POST" action="patients.php">
            <input type="hidden" name="action" value="create_patient">
            
            <div class="modal-body">
                <div class="form-grid-2col">
                    <div class="form-field-group">
                        <label for="first_name" class="form-label-text">First Name *</label>
                        <input type="text" id="first_name" name="first_name" class="form-input-control" required>
                    </div>
                    <div class="form-field-group">
                        <label for="last_name" class="form-label-text">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" class="form-input-control" required>
                    </div>
                </div>

                <div class="form-grid-2col">
                    <div class="form-field-group">
                        <label for="amputation_level" class="form-label-text">Amputation Level *</label>
                        <select id="amputation_level" name="amputation_level" class="form-input-control" required>
                            <option value="Transtibial (Below Knee)">Transtibial (Below Knee)</option>
                            <option value="Transfemoral (Above Knee)">Transfemoral (Above Knee)</option>
                            <option value="Transradial (Below Elbow)">Transradial (Below Elbow)</option>
                        </select>
                    </div>
                    <div class="form-field-group">
                        <label for="affected_side" class="form-label-text">Affected Side *</label>
                        <select id="affected_side" name="affected_side" class="form-input-control" required>
                            <option value="Right">Right</option>
                            <option value="Left">Left</option>
                            <option value="Bilateral">Bilateral</option>
                        </select>
                    </div>
                </div>

                <div class="form-field-group">
                    <label for="device_type" class="form-label-text">Prescribed Prosthetic Device</label>
                    <input type="text" id="device_type" name="device_type" class="form-input-control" placeholder="e.g. Endoskeletal Carbon Foot with Vacuum Socket">
                </div>
            </div>

            <div class="modal-footer-bar">
                <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn-primary-action">Save to Supabase</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>