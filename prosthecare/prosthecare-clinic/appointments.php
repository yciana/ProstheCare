<?php
require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/includes/SupabaseClient.php';

$supabase = new SupabaseClient();
$message = '';
$isError = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_appointment') {
    $patientId = trim($_POST['patient_id'] ?? '');
    $date = trim($_POST['appointment_date'] ?? '');
    $time = trim($_POST['appointment_time'] ?? '');
    $type = trim($_POST['appointment_type'] ?? '');

    if (empty($patientId) || empty($date) || empty($time) || empty($type)) {
        $message = 'Please provide all appointment details.';
        $isError = true;
    } else {
        $appointmentData = [
            'patient_id' => $patientId,
            'appointment_date' => $date,
            'appointment_time' => $time,
            'appointment_type' => $type,
            'status' => 'Scheduled',
            'room' => trim($_POST['room'] ?? 'Gait Lab A'),
            'practitioner_notes' => trim($_POST['practitioner_notes'] ?? '')
        ];

        if ($supabase->from('appointments')->insert($appointmentData)) {
            $message = 'Clinical appointment scheduled successfully!';
        } else {
            $message = 'Failed to schedule appointment.';
            $isError = true;
        }
    }
}

$pageTitle = 'Fitting & Alignment Schedule';
require_once __DIR__ . '/includes/header.php';

$appointments = $supabase->from('appointments')->select('*')->order('appointment_date', true)->get();
$patients = $supabase->from('patients')->select('*')->order('first_name', true)->get();
?>

<div class="content-card-panel">
    <div class="panel-header-row">
        <div class="top-nav-left">
            <input 
                type="text" 
                class="form-input-control" 
                placeholder="Search appointments..." 
                data-table-search="appointmentsTable"
            >
        </div>
        <button type="button" class="btn-primary-action" data-modal-target="newAppointmentModal">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Book Clinical Fitting</span>
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
        <table class="clinic-table" id="appointmentsTable">
            <thead>
                <tr>
                    <th>Date & Time</th>
                    <th>Patient Identifier</th>
                    <th>Procedure</th>
                    <th>Suite / Lab</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($appointments)): ?>
                    <tr><td colspan="5">No fitting sessions scheduled.</td></tr>
                <?php else: ?>
                    <?php foreach ($appointments as $apt): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($apt['appointment_date'] ?? '') ?></strong> at <?= htmlspecialchars($apt['appointment_time'] ?? '') ?></td>
                            <td><?= htmlspecialchars($apt['patient_name'] ?? 'Client') ?></td>
                            <td><?= htmlspecialchars($apt['appointment_type'] ?? 'Fitting') ?></td>
                            <td><?= htmlspecialchars($apt['room'] ?? 'Suite 1') ?></td>
                            <td><span class="status-pill status-fitting"><?= htmlspecialchars($apt['status'] ?? 'Scheduled') ?></span></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal-overlay" id="newAppointmentModal">
    <div class="modal-card">
        <div class="modal-header-bar">
            <h3 class="modal-title">Schedule Fitting Session</h3>
            <button type="button" class="modal-close-btn" data-modal-close>
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
        </div>

        <form method="POST" action="appointments.php">
            <input type="hidden" name="action" value="create_appointment">
            <div class="modal-body">
                <div class="form-field-group">
                    <label for="patient_id" class="form-label-text">Select Patient *</label>
                    <select id="patient_id" name="patient_id" class="form-input-control" required>
                        <option value="">-- Choose Patient --</option>
                        <?php foreach ($patients as $p): ?>
                            <option value="<?= htmlspecialchars($p['id'] ?? $p['patient_code']) ?>">
                                <?= htmlspecialchars(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-grid-2col">
                    <div class="form-field-group">
                        <label for="appointment_date" class="form-label-text">Date *</label>
                        <input type="date" id="appointment_date" name="appointment_date" class="form-input-control" value="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-field-group">
                        <label for="appointment_time" class="form-label-text">Time *</label>
                        <input type="time" id="appointment_time" name="appointment_time" class="form-input-control" value="10:00" required>
                    </div>
                </div>
                <div class="form-field-group">
                    <label for="appointment_type" class="form-label-text">Procedure Type *</label>
                    <select id="appointment_type" name="appointment_type" class="form-input-control" required>
                        <option value="Initial Clinical Assessment">Initial Clinical Assessment</option>
                        <option value="Diagnostic Check Socket Fitting" selected>Diagnostic Check Socket Fitting</option>
                        <option value="Dynamic Gait Alignment">Dynamic Gait Alignment</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer-bar">
                <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                <button type="submit" class="btn-primary-action">Confirm Appointment</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>