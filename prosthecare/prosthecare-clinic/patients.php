<?php
/**
 * ProstheCare Clinic Management System - Patient Directory
 */

require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/includes/SupabaseClient.php';

$supabase = new SupabaseClient();
$message = '';
$isError = false;

// Handle new patient insertion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_patient') {
    $firstName = trim($_POST['first_name'] ?? '');
    $lastName = trim($_POST['last_name'] ?? '');
    $code = 'PT-' . date('Y') . '-' . rand(100, 999);
    $dob = trim($_POST['dob'] ?? '');
    $gender = trim($_POST['gender'] ?? 'Other');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $amputation = trim($_POST['amputation_level'] ?? '');
    $side = trim($_POST['affected_side'] ?? 'Right');
    $deviceType = trim($_POST['device_type'] ?? '');
    $fittingStage = trim($_POST['fitting_stage'] ?? 'Assessment');
    $clinicalNotes = trim($_POST['clinical_notes'] ?? '');

    if (empty($firstName) || empty($lastName) || empty($amputation)) {
        $message = 'Please provide the patient name and amputation details.';
        $isError = true;
    } else {
        $patientData = [
            'patient_code' => $code,
            'first_name' => $firstName,
            'last_name' => $lastName,
            'dob' => $dob ?: null,
            'gender' => $gender,
            'phone' => $phone,
            'email' => $email,
            'amputation_level' => $amputation,
            'affected_side' => $side,
            'activity_level' => $_POST['activity_level'] ?? 'K3',
            'device_type' => $deviceType,
            'fitting_stage' => $fittingStage,
            'status' => 'Active',
            'clinical_notes' => $clinicalNotes
        ];

        $insertResult = $supabase->from('patients')->insert($patientData);
        if ($insertResult) {
            $message = "Patient {$firstName} {$lastName} ({$code}) successfully registered!";
        } else {
            $message = 'Failed to save patient record.';
            $isError = true;
        }
    }
}

$pageTitle = 'Prosthetic Patients Directory';
require_once __DIR__ . '/includes/header.php';

// Retrieve all patients
$patients = $supabase->from('patients')->select('*')->order('created_at', false)->get();
$shouldOpenModal = isset($_GET['action']) && $_GET['action'] === 'new';
?>

<!-- Action Toolbar -->
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

    <!-- Patients Data Table -->
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
                    <tr>
                        <td colspan="7">No patients recorded yet in database.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($patients as $p): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($p['patient_code'] ?? 'PT-000') ?></strong>
                                <?php if (!empty($p['phone'])): ?>
                                    <br><small><?= htmlspecialchars($p['phone']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?= htmlspecialchars(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) ?></strong>
                                <?php if (!empty($p['email'])): ?>
                                    <br><small><?= htmlspecialchars($p['email']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= htmlspecialchars($p['amputation_level'] ?? 'N/A') ?>
                                <br><small>Side: <?= htmlspecialchars($p['affected_side'] ?? 'Unspecified') ?></small>
                            </td>
                            <td>
                                <span class="status-pill status-fitting">
                                    Level <?= htmlspecialchars($p['activity_level'] ?? 'K3') ?>
                                </span>
                            </td>
                            <td><?= htmlspecialchars($p['device_type'] ?? 'Custom Prosthesis') ?></td>
                            <td>
                                <span class="status-pill status-fitting">
                                    <?= htmlspecialchars($p['fitting_stage'] ?? 'Assessment') ?>
                                </span>
                            </td>
                            <td>
                                <?php
                                    $st = $p['status'] ?? 'Active';
                                    $bClass = 'status-active';
                                    if ($st === 'In Fabrication') $bClass = 'status-fabrication';
                                    if ($st === 'Completed') $bClass = 'status-completed';
                                ?>
                                <span class="status-pill <?= $bClass ?>">
                                    <?= htmlspecialchars($st) ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Patient Modal Dialog -->
<div class="modal-overlay <?= $shouldOpenModal ? 'active' : '' ?>" id="newPatientModal">
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
                        <input type="text" id="first_name" name="first_name" class="form-input-control" placeholder="e.g. Liam" required>
                    </div>

                    <div class="form-field-group">
                        <label for="last_name" class="form-label-text">Last Name *</label>
                        <input type="text" id="last_name" name="last_name" class="form-input-control" placeholder="e.g. Vance" required>
                    </div>
                </div>

                <div class="form-grid-2col">
                    <div class="form-field-group">
                        <label for="phone" class="form-label-text">Phone Number</label>
                        <input type="tel" id="phone" name="phone" class="form-input-control" placeholder="+1 (555) 000-0000">
                    </div>

                    <div class="form-field-group">
                        <label for="email" class="form-label-text">Email Address</label>
                        <input type="email" id="email" name="email" class="form-input-control" placeholder="client@example.com">
                    </div>
                </div>

                <div class="form-grid-2col">
                    <div class="form-field-group">
                        <label for="amputation_level" class="form-label-text">Amputation Level *</label>
                        <select id="amputation_level" name="amputation_level" class="form-input-control" required>
                            <option value="Transtibial (Below Knee)">Transtibial (Below Knee)</option>
                            <option value="Transfemoral (Above Knee)">Transfemoral (Above Knee)</option>
                            <option value="Knee Disarticulation">Knee Disarticulation</option>
                            <option value="Syme's / Ankle Disarticulation">Syme's / Ankle Disarticulation</option>
                            <option value="Transradial (Below Elbow)">Transradial (Below Elbow)</option>
                            <option value="Transhumeral (Above Elbow)">Transhumeral (Above Elbow)</option>
                            <option value="Partial Foot">Partial Foot</option>
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

                <div class="form-grid-2col">
                    <div class="form-field-group">
                        <label for="activity_level" class="form-label-text">Mobility / K-Level</label>
                        <select id="activity_level" name="activity_level" class="form-input-control">
                            <option value="K1">K1 - Household Ambulator</option>
                            <option value="K2">K2 - Limited Community Ambulator</option>
                            <option value="K3" selected>K3 - Active Community Ambulator</option>
                            <option value="K4">K4 - High Impact / Athlete</option>
                        </select>
                    </div>

                    <div class="form-field-group">
                        <label for="fitting_stage" class="form-label-text">Initial Fitting Stage</label>
                        <select id="fitting_stage" name="fitting_stage" class="form-input-control">
                            <option value="Assessment" selected>Clinical Assessment</option>
                            <option value="Diagnostic Socket">Diagnostic Check Socket</option>
                            <option value="Trial Fitting">Dynamic Trial Fitting</option>
                            <option value="Definitive Lamination">Definitive Carbon Lamination</option>
                            <option value="Final Delivery">Final Handover & Delivery</option>
                        </select>
                    </div>
                </div>

                <div class="form-field-group">
                    <label for="device_type" class="form-label-text">Prescribed Prosthetic Device</label>
                    <input type="text" id="device_type" name="device_type" class="form-input-control" placeholder="e.g. Endoskeletal Carbon Foot with Vacuum Socket">
                </div>

                <div class="form-field-group">
                    <label for="clinical_notes" class="form-label-text">Clinical Notes & Observations</label>
                    <textarea id="clinical_notes" name="clinical_notes" class="form-input-control" rows="3" placeholder="Limb shape, skin tolerance, suspension considerations..."></textarea>
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
