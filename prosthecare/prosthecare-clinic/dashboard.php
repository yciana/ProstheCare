<?php
$pageTitle = 'Clinical Dashboard';
require_once __DIR__ . '/includes/header.php';

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
?>

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
                    <th>Patient Code</th>
                    <th>Clinical Procedure</th>
                    <th>Room / Lab</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($appointments)): ?>
                    <tr><td colspan="5">No scheduled appointments recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach (array_slice($appointments, 0, 4) as $apt): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($apt['appointment_time'] ?? '') ?></strong>
                                <br><small><?= htmlspecialchars($apt['appointment_date'] ?? '') ?></small>
                            </td>
                            <td><?= htmlspecialchars($apt['patient_name'] ?? 'Client') ?></td>
                            <td><?= htmlspecialchars($apt['appointment_type'] ?? '') ?></td>
                            <td><?= htmlspecialchars($apt['room'] ?? 'Fitting Suite') ?></td>
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

<section class="content-card-panel">
    <div class="panel-header-row">
        <h2 class="panel-title-text">Recent Prosthetic Client Cases</h2>
        <a href="patients.php" class="btn-secondary">All Patients</a>
    </div>
    <div class="data-table-container">
        <table class="clinic-table">
            <thead>
                <tr>
                    <th>Case Code</th>
                    <th>Patient Name</th>
                    <th>Amputation Level</th>
                    <th>Device & Componentry</th>
                    <th>Fitting Stage</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($patients)): ?>
                    <tr><td colspan="6">No patient cases available. Click 'New Patient Case' to add one.</td></tr>
                <?php else: ?>
                    <?php foreach (array_slice($patients, 0, 5) as $pt): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($pt['patient_code'] ?? 'PT-NEW') ?></strong></td>
                            <td><?= htmlspecialchars(($pt['first_name'] ?? '') . ' ' . ($pt['last_name'] ?? '')) ?></td>
                            <td><?= htmlspecialchars($pt['amputation_level'] ?? 'N/A') ?> (<?= htmlspecialchars($pt['affected_side'] ?? 'Side') ?>)</td>
                            <td><?= htmlspecialchars($pt['device_type'] ?? 'Custom Prosthesis') ?></td>
                            <td>
                                <span class="status-pill status-fitting">
                                    <?= htmlspecialchars($pt['fitting_stage'] ?? 'Assessment') ?>
                                </span>
                            </td>
                            <td>
                                <span class="status-pill <?= ($pt['status'] ?? '') === 'In Fabrication' ? 'status-fabrication' : 'status-active' ?>">
                                    <?= htmlspecialchars($pt['status'] ?? 'Active') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>