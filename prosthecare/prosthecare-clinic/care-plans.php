<?php
/**
 * ProstheCare Clinic Management System - Prosthetic Devices & Care Plans
 * Production Supabase Integration
 */

require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/includes/SupabaseClient.php';

$supabase = new SupabaseClient();
$message = '';
$isError = false;

// Handle new care plan submission to Supabase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_care_plan') {
    $patientId = trim($_POST['patient_id'] ?? '');
    $planTitle = trim($_POST['plan_title'] ?? '');
    $prescriptionCode = 'RX-PRO-' . rand(1000, 9999);
    $socketType = trim($_POST['socket_type'] ?? '');
    $suspensionType = trim($_POST['suspension_type'] ?? '');
    $componentry = trim($_POST['componentry_details'] ?? '');
    $stage = trim($_POST['fabrication_stage'] ?? 'Cast Modification');
    $deliveryDate = trim($_POST['target_delivery_date'] ?? '');
    $warrantyMonths = (int)($_POST['warranty_months'] ?? 24);
    $notes = trim($_POST['notes'] ?? '');

    if (empty($patientId) || empty($planTitle)) {
        $message = 'Please provide the care plan title and select a patient.';
        $isError = true;
    } else {
        $planData = [
            'patient_id' => $patientId,
            'plan_title' => $planTitle,
            'prescription_code' => $prescriptionCode,
            'socket_type' => $socketType,
            'suspension_type' => $suspensionType,
            'componentry_details' => $componentry,
            'fabrication_stage' => $stage,
            'target_delivery_date' => $deliveryDate ?: null,
            'warranty_months' => $warrantyMonths,
            'notes' => $notes
        ];

        $insertResult = $supabase->from('care_plans')->insert($planData);
        if ($insertResult) {
            $message = "Prosthetic device care plan '{$planTitle}' successfully created in Supabase!";
        } else {
            $message = 'Failed to create prosthetic care plan in Supabase.';
            $isError = true;
        }
    }
}

$pageTitle = 'Prosthetic Devices & Fabrication';
require_once __DIR__ . '/includes/header.php';

// Fetch care plans and patients from Supabase
$carePlans = $supabase->from('care_plans')->select('*')->order('created_at', false)->get();
$patients = $supabase->from('patients')->select('*')->order('first_name', true)->get();
?>

<div class="content-card-panel">
    <div class="panel-header-row">
        <div class="top-nav-left">
            <input 
                type="text" 
                class="form-input-control" 
                placeholder="Search prosthetic plans & sockets..." 
                data-table-search="carePlansTable"
            >
        </div>
        <button type="button" class="btn-primary-action" data-modal-target="newCarePlanModal">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>New Prosthetic Prescription</span>
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
        <table class="clinic-table" id="carePlansTable">
            <thead>
                <tr>
                    <th>Rx Code</th>
                    <th>Device Title & Patient</th>
                    <th>Socket Design</th>
                    <th>Suspension Mechanism</th>
                    <th>Target Delivery</th>
                    <th>Fabrication Stage</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($carePlans)): ?>
                    <tr>
                        <td colspan="6">No prosthetic device plans recorded yet in Supabase.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($carePlans as $cp): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($cp['prescription_code'] ?? 'RX-000') ?></strong></td>
                            <td>
                                <strong><?= htmlspecialchars($cp['plan_title'] ?? '') ?></strong>
                                <br><small><?= htmlspecialchars($cp['patient_name'] ?? $cp['patient_id'] ?? 'Client') ?></small>
                            </td>
                            <td><?= htmlspecialchars($cp['socket_type'] ?? 'Standard') ?></td>
                            <td><?= htmlspecialchars($cp['suspension_type'] ?? 'Suction') ?></td>
                            <td><?= htmlspecialchars($cp['target_delivery_date'] ?? 'TBD') ?></td>
                            <td>
                                <span class="status-pill status-fabrication">
                                    <?= htmlspecialchars($cp['fabrication_stage'] ?? 'In Progress') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal: New Care Plan -->
<div class="modal-overlay" id="newCarePlanModal">
    <div class="modal-card">
        <div class="modal-header-bar">
            <h3 class="modal-title">Create Prosthetic Fabrication Plan</h3>
            <button type="button" class="modal-close-btn" data-modal-close aria-label="Close dialog">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <form method="POST" action="care-plans.php">
            <input type="hidden" name="action" value="create_care_plan">

            <div class="modal-body">
                <div class="form-field-group">
                    <label for="plan_title" class="form-label-text">Device Prescription Title *</label>
                    <input type="text" id="plan_title" name="plan_title" class="form-input-control" placeholder="e.g. Dynamic Transtibial Carbon Prosthesis" required>
                </div>

                <div class="form-field-group">
                    <label for="care_patient_id" class="form-label-text">Patient *</label>
                    <select id="care_patient_id" name="patient_id" class="form-input-control" required>
                        <option value="">-- Select Patient --</option>
                        <?php foreach ($patients as $p): ?>
                            <option value="<?= htmlspecialchars($p['id'] ?? $p['patient_code']) ?>">
                                <?= htmlspecialchars(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '') . ' (' . ($p['patient_code'] ?? '') . ')') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-grid-2col">
                    <div class="form-field-group">
                        <label for="socket_type" class="form-label-text">Socket Design Type</label>
                        <select id="socket_type" name="socket_type" class="form-input-control">
                            <option value="TSB (Total Surface Bearing)">TSB (Total Surface Bearing)</option>
                            <option value="IC (Ischial Containment)">IC (Ischial Containment)</option>
                            <option value="Sub-Ischial Vacuum">Sub-Ischial Vacuum</option>
                            <option value="PTB (Patellar Tendon Bearing)">PTB (Patellar Tendon Bearing)</option>
                            <option value="Myo-Electrode Frame Socket">Myo-Electrode Frame Socket</option>
                        </select>
                    </div>

                    <div class="form-field-group">
                        <label for="suspension_type" class="form-label-text">Suspension Mechanism</label>
                        <select id="suspension_type" name="suspension_type" class="form-input-control">
                            <option value="Elevated Active Vacuum">Elevated Active Vacuum</option>
                            <option value="Pin & Shuttle Lock">Pin & Shuttle Lock</option>
                            <option value="Passive Suction Valve">Passive Suction Valve</option>
                            <option value="Anatomical Supracondylar">Anatomical Supracondylar</option>
                        </select>
                    </div>
                </div>

                <div class="form-grid-2col">
                    <div class="form-field-group">
                        <label for="fabrication_stage" class="form-label-text">Initial Stage</label>
                        <select id="fabrication_stage" name="fabrication_stage" class="form-input-control">
                            <option value="Cast Modification">Cast Modification</option>
                            <option value="Diagnostic Check Socket" selected>Diagnostic Check Socket</option>
                            <option value="Dynamic Alignment Trial">Dynamic Alignment Trial</option>
                            <option value="Definitive Carbon Lamination">Definitive Carbon Lamination</option>
                            <option value="Final Component Assembly">Final Component Assembly</option>
                        </select>
                    </div>

                    <div class="form-field-group">
                        <label for="target_delivery_date" class="form-label-text">Target Delivery Date</label>
                        <input type="date" id="target_delivery_date" name="target_delivery_date" class="form-input-control" value="<?= date('Y-m-d', strtotime('+14 days')) ?>">
                    </div>
                </div>

                <div class="form-field-group">
                    <label for="componentry_details" class="form-label-text">Componentry Specifications</label>
                    <textarea id="componentry_details" name="componentry_details" class="form-input-control" rows="2" placeholder="Foot module, pylon rating, microprocessor knee model..."></textarea>
                </div>
            </div>

            <div class="modal-footer-bar">
                <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn-primary-action">Save Care Plan</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
