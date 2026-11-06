<?php
require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/includes/SupabaseClient.php';

$supabase = new SupabaseClient();
$message = '';
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_care_plan') {
    $patientId = trim($_POST['patient_id'] ?? '');
    $planTitle = trim($_POST['plan_title'] ?? '');

    if (empty($patientId) || empty($planTitle)) {
        $message = 'Please provide plan title and select patient.';
        $isError = true;
    } else {
        $planData = [
            'patient_id' => $patientId,
            'plan_title' => $planTitle,
            'prescription_code' => 'RX-PRO-' . rand(1000, 9999),
            'socket_type' => trim($_POST['socket_type'] ?? 'TSB'),
            'suspension_type' => trim($_POST['suspension_type'] ?? 'Suction'),
            'fabrication_stage' => trim($_POST['fabrication_stage'] ?? 'Check Socket'),
            'target_delivery_date' => trim($_POST['target_delivery_date'] ?? '') ?: null
        ];

        if ($supabase->from('care_plans')->insert($planData)) {
            $message = "Prosthetic device care plan '{$planTitle}' successfully created!";
        } else {
            $message = 'Failed to create plan.';
            $isError = true;
        }
    }
}

$pageTitle = 'Prosthetic Devices & Fabrication';
require_once __DIR__ . '/includes/header.php';

$carePlans = $supabase->from('care_plans')->select('*')->order('created_at', false)->get();
$patients = $supabase->from('patients')->select('*')->order('first_name', true)->get();
?>

<div class="content-card-panel">
    <div class="panel-header-row">
        <h2 class="panel-title-text">Prosthetic Socket & Fabrication Tracking</h2>
        <button type="button" class="btn-primary-action" data-modal-target="newCarePlanModal">
            <span>New Prescription</span>
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
        <table class="clinic-table">
            <thead>
                <tr>
                    <th>Rx Code</th>
                    <th>Device Title</th>
                    <th>Socket Design</th>
                    <th>Suspension</th>
                    <th>Target Delivery</th>
                    <th>Fabrication Stage</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($carePlans)): ?>
                    <tr><td colspan="6">No prosthetic device plans recorded.</td></tr>
                <?php else: ?>
                    <?php foreach ($carePlans as $cp): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($cp['prescription_code'] ?? 'RX-000') ?></strong></td>
                            <td><?= htmlspecialchars($cp['plan_title'] ?? '') ?></td>
                            <td><?= htmlspecialchars($cp['socket_type'] ?? 'Standard') ?></td>
                            <td><?= htmlspecialchars($cp['suspension_type'] ?? 'Suction') ?></td>
                            <td><?= htmlspecialchars($cp['target_delivery_date'] ?? 'TBD') ?></td>
                            <td><span class="status-pill status-fabrication"><?= htmlspecialchars($cp['fabrication_stage'] ?? 'In Progress') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="newCarePlanModal">
    <div class="modal-card">
        <div class="modal-header-bar">
            <h3 class="modal-title">Create Prosthetic Fabrication Plan</h3>
            <button type="button" class="modal-close-btn" data-modal-close>✕</button>
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
                                <?= htmlspecialchars(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grid-2col">
                    <div class="form-field-group">
                        <label for="socket_type" class="form-label-text">Socket Design</label>
                        <select id="socket_type" name="socket_type" class="form-input-control">
                            <option value="TSB (Total Surface Bearing)">TSB (Total Surface Bearing)</option>
                            <option value="IC (Ischial Containment)">IC (Ischial Containment)</option>
                            <option value="Sub-Ischial Vacuum">Sub-Ischial Vacuum</option>
                        </select>
                    </div>
                    <div class="form-field-group">
                        <label for="suspension_type" class="form-label-text">Suspension</label>
                        <select id="suspension_type" name="suspension_type" class="form-input-control">
                            <option value="Elevated Active Vacuum">Elevated Active Vacuum</option>
                            <option value="Pin & Shuttle Lock">Pin & Shuttle Lock</option>
                        </select>
                    </div>
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