<?php
/**
 * ProstheCare Clinic Management System - Dashboard
 * Adapts seamlessly for both Clients/Patients and Staff/Practitioners
 */

$pageTitle = 'Care Dashboard';
require_once __DIR__ . '/includes/header.php';

$userRole = $currentUser['role'] ?? 'Client';
$userEmail = $currentUser['email'] ?? '';
$userName = $currentUser['name'] ?? 'Client';

$isClient = in_array(strtolower($userRole), ['client', 'patient']);

if ($isClient) {
    $clientRecords = $supabase->from('patients')->select('*')->eq('email', $userEmail)->get();
    $myPatient = !empty($clientRecords) ? $clientRecords[0] : null;

    $myAppointments = [];
    $myCarePlans = [];

    if ($myPatient && !empty($myPatient['id'])) {
        $myAppointments = $supabase->from('appointments')->select('*')->eq('patient_id', $myPatient['id'])->get();
        $myCarePlans = $supabase->from('care_plans')->select('*')->eq('patient_id', $myPatient['id'])->get();
    }
} else {
    $patients = $supabase->from('patients')->select('*')->order('created_at', false)->get();
    $appointments = $supabase->from('appointments')->select('*')->order('appointment_date', true)->get();
    $carePlans = $supabase->from('care_plans')->select('*')->get();

    $totalPatients = count($patients);
    $activeFittings = count(array_filter($patients, function($p) {
        return in_array($p['fitting_stage'] ?? '', ['Assessment', 'Diagnostic Socket', 'Trial Fitting']);
    }));
    $inFabrication = count(array_filter($patients, function($p) {
        return ($p['status'] ?? '') === 'In Fabrication' || ($p['fitting_stage'] ?? '') === 'Definitive Lamination';
    }));
    $completedCases = count(array_filter($patients, function($p) {
        return ($p['status'] ?? '') === 'Completed';
    }));
}
?>

<?php if ($isClient): ?>
    <section class="stats-grid-row" aria-label="My Care Summary">
        <div class="stat-card-widget">
            <div class="stat-metric-info">
                <span class="stat-label-title">Current Fitting Stage</span>
                <span class="stat-value-display"><?= htmlspecialchars($myPatient['fitting_stage'] ?? 'Assessment') ?></span>
            </div>
            <div class="stat-icon-wrapper stat-icon-teal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
        </div>

        <div class="stat-card-widget">
            <div class="stat-metric-info">
                <span class="stat-label-title">Prescribed Device</span>
                <span class="stat-value-display"><?= htmlspecialchars(!empty($myPatient['device_type']) ? $myPatient['device_type'] : 'Custom Prosthesis') ?></span>
            </div>
            <div class="stat-icon-wrapper stat-icon-blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>
            </div>
        </div>

        <div class="stat-card-widget">
            <div class="stat-metric-info">
                <span class="stat-label-title">My Patient Code</span>
                <span class="stat-value-display"><?= htmlspecialchars($myPatient['patient_code'] ?? 'PT-PENDING') ?></span>
            </div>
            <div class="stat-icon-wrapper stat-icon-green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
            </div>
        </div>
    </section>

    <section class="content-card-panel">
        <div class="panel-header-row">
            <h2 class="panel-title-text">My Upcoming Clinic Appointments</h2>
            <a href="appointments.php" class="btn-secondary">Request Fitting Session</a>
        </div>
        <div class="data-table-container">
            <table class="clinic-table">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Fitting Procedure</th>
                        <th>Clinic Suite</th>
                        <th>Notes / Instructions</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($myAppointments)): ?>
                        <tr>
                            <td colspan="5">No appointments currently scheduled. Click 'Request Fitting Session' to book a consultation.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($myAppointments as $apt): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($apt['appointment_date'] ?? '') ?></strong>
                                    <br><small><?= htmlspecialchars($apt['appointment_time'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($apt['appointment_type'] ?? 'Fitting') ?></td>
                                <td><?= htmlspecialchars($apt['room'] ?? 'Fitting Suite') ?></td>
                                <td><?= htmlspecialchars($apt['practitioner_notes'] ?? 'Standard consultation') ?></td>
                                <td>
                                    <span class="status-pill status-fitting">
                                        <?= htmlspecialchars($apt['status'] ?? 'Scheduled') ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

<?php else: ?>
    <section class="stats-grid-row" aria-label="Clinic Statistics">
        <div class="stat-card-widget">
            <div class="stat-metric-info">
                <span class="stat-label-title">Total Active Clients</span>
                <span class="stat-value-display"><?= $totalPatients ?></span>
            </div>
            <div class="stat-icon-wrapper stat-icon-teal">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                </svg>
            </div>
        </div>

        <div class="stat-card-widget">
            <div class="stat-metric-info">
                <span class="stat-label-title">Active Fitting Trials</span>
                <span class="stat-value-display"><?= $activeFittings ?></span>
            </div>
            <div class="stat-icon-wrapper stat-icon-blue">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <polyline points="12 6 12 12 16 14"></polyline>
                </svg>
            </div>
        </div>

        <div class="stat-card-widget">
            <div class="stat-metric-info">
                <span class="stat-label-title">In Socket Fabrication</span>
                <span class="stat-value-display"><?= $inFabrication ?></span>
            </div>
            <div class="stat-icon-wrapper stat-icon-amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>
            </div>
        </div>

        <div class="stat-card-widget">
            <div class="stat-metric-info">
                <span class="stat-label-title">Completed Deliveries</span>
                <span class="stat-value-display"><?= $completedCases ?></span>
            </div>
            <div class="stat-icon-wrapper stat-icon-green">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
        </div>
    </section>

    <section class="content-card-panel">
        <div class="panel-header-row">
            <h2 class="panel-title-text">Upcoming Fitting & Alignment Sessions</h2>
            <a href="appointments.php" class="btn-secondary">View Full Schedule</a>
        </div>
        <div class="data-table-container">
            <table class="clinic-table">
                <thead>
                    <tr>
                        <th>Time & Date</th>
                        <th>Patient Identifier</th>
                        <th>Clinical Procedure</th>
                        <th>Room / Lab</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)): ?>
                        <tr><td colspan="5">No scheduled appointments recorded yet in Supabase.</td></tr>
                    <?php else: ?>
                        <?php foreach (array_slice($appointments, 0, 4) as $apt): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($apt['appointment_time'] ?? '') ?></strong>
                                    <br><small><?= htmlspecialchars($apt['appointment_date'] ?? '') ?></small>
                                </td>
                                <td><?= htmlspecialchars($apt['patient_name'] ?? $apt['patient_id'] ?? 'Client') ?></td>
                                <td><?= htmlspecialchars($apt['appointment_type'] ?? '') ?></td>
                                <td><?= htmlspecialchars($apt['room'] ?? 'Fitting Suite') ?></td>
                                <td><span class="status-pill status-fitting"><?= htmlspecialchars($apt['status'] ?? 'Scheduled') ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
