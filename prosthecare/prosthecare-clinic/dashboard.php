<?php
/**
 * ProstheCare Clinic Management System - Dashboard
 * Pixel-perfect adaptation for Client UI matching Figma design (Screenshot 1)
 * Full Clinical operations view preserved for Staff/Admin
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

// Check if user is in Client preview or has client role
if ($isClient) {
    // Dynamic or mock clinic values for Sarah Mitchell
    $userEmail = $currentUser['email'] ?? '';
    $clientRecords = $supabase->from('patients')->select('*')->eq('email', $userEmail)->get();
    $myPatient = !empty($clientRecords) ? $clientRecords[0] : null;

    $myAppointments = [];
    if ($myPatient && !empty($myPatient['id'])) {
        $myAppointments = $supabase->from('appointments')->select('*')->eq('patient_id', $myPatient['id'])->get();
    }

    $upcomingAptTitle = !empty($myAppointments[0]['appointment_type']) ? $myAppointments[0]['appointment_type'] : 'Prosthetic fitting';
    $upcomingAptDay = '15';
    $upcomingAptMonth = 'MAY';
    $upcomingAptTime = 'Thursday, 10:30 AM';
    if (!empty($myAppointments[0]['appointment_date'])) {
        $timeObj = strtotime($myAppointments[0]['appointment_date']);
        if ($timeObj) {
            $upcomingAptDay = date('d', $timeObj);
            $upcomingAptMonth = strtoupper(date('M', $timeObj));
            $upcomingAptTime = date('l, g:i A', $timeObj);
        }
    }
?>
    <!-- CLIENT DASHBOARD GREETING -->
    <section class="client-greeting-row" aria-label="Greeting Header">
        <div>
            <h1 class="client-greeting-title">Good morning, <?= htmlspecialchars($firstName) ?></h1>
            <p class="client-greeting-subtitle">Here's what's happening with your care today.</p>
        </div>
        <div>
            <a href="appointments.php" class="btn-primary-action">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <span>Book appointment</span>
            </a>
        </div>
    </section>

    <!-- ROW 1: TODAY'S QUEUE CARD & UPCOMING APPOINTMENT -->
    <section class="client-hero-grid" aria-label="Clinic Status and Appointment">
        <!-- Today's Queue Number Card -->
        <div class="client-queue-card">
            <div class="queue-card-watermark"></div>
            <div class="queue-card-left">
                <div class="queue-card-eyebrow">TODAY'S QUEUE NUMBER</div>
                <div class="queue-card-number">A-07</div>
                <div class="queue-status-pill">
                    <span class="queue-dot-pulse"></span>
                    <span>Now serving A-04</span>
                </div>
            </div>
            <div class="queue-card-divider"></div>
            <div class="queue-card-right">
                <div class="queue-wait-label">Estimated wait</div>
                <div class="queue-wait-value">~18 min</div>
                <div class="queue-wait-note">Check in at reception</div>
            </div>
        </div>

        <!-- Upcoming Appointment Card -->
        <div class="client-appointment-card">
            <div class="card-header-label-row">
                <span class="card-tiny-eyebrow">UPCOMING APPOINTMENT</span>
                <button type="button" class="btn-icon-more" title="More options" aria-label="More options">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                        <circle cx="5" cy="12" r="2"></circle>
                        <circle cx="12" cy="12" r="2"></circle>
                        <circle cx="19" cy="12" r="2"></circle>
                    </svg>
                </button>
            </div>
            <h2 class="appointment-main-title"><?= htmlspecialchars($upcomingAptTitle) ?></h2>
            <div class="appointment-details-block">
                <div class="appointment-date-badge">
                    <span class="date-badge-day"><?= htmlspecialchars($upcomingAptDay) ?></span>
                    <span class="date-badge-month"><?= htmlspecialchars($upcomingAptMonth) ?></span>
                </div>
                <div class="appointment-info-text">
                    <span class="appointment-datetime"><?= htmlspecialchars($upcomingAptTime) ?></span>
                    <span class="appointment-subtext">with Dr. James Wilson</span>
                    <span class="appointment-subtext">Fitting Room 2 &middot; 45 min</span>
                </div>
            </div>
            <a href="appointments.php" class="link-view-action">
                <span>View appointment</span>
                <span>&rarr;</span>
            </a>
        </div>
    </section>

    <!-- ROW 2: ACTIVE REQUEST PROGRESS & ANNOUNCEMENTS -->
    <section class="client-secondary-grid" aria-label="Active Prosthetic Request and Announcements">
        <!-- Active Request Card with 5-Step Stepper -->
        <div class="client-active-request-card">
            <div class="request-card-header">
                <span class="card-tiny-eyebrow">ACTIVE REQUEST</span>
                <span class="status-pill-amber">In fabrication</span>
            </div>
            <h2 class="request-card-title">Transtibial prosthesis</h2>
            <p class="request-card-subtitle">Request #PR-2025-0184 &middot; Submitted Apr 22</p>

            <!-- 5-Step Horizontal Timeline Stepper -->
            <div class="stepper-timeline-container">
                <div class="stepper-track-line"></div>
                <div class="stepper-track-progress"></div>

                <div class="stepper-node-item">
                    <div class="stepper-node-circle node-completed">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="stepper-node-label">Request received</span>
                    <span class="stepper-node-date">Apr 22</span>
                </div>

                <div class="stepper-node-item">
                    <div class="stepper-node-circle node-completed">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="stepper-node-label">Assessment</span>
                    <span class="stepper-node-date">Apr 25</span>
                </div>

                <div class="stepper-node-item">
                    <div class="stepper-node-circle node-completed">
                        <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="stepper-node-label">Approved</span>
                    <span class="stepper-node-date">Apr 28</span>
                </div>

                <div class="stepper-node-item">
                    <div class="stepper-node-circle node-active">
                        <span>4</span>
                    </div>
                    <span class="stepper-node-label">Fabrication</span>
                    <span class="stepper-node-date">In progress</span>
                </div>

                <div class="stepper-node-item">
                    <div class="stepper-node-circle node-upcoming">
                        <span>5</span>
                    </div>
                    <span class="stepper-node-label">Ready</span>
                    <span class="stepper-node-date">Est. May 24</span>
                </div>
            </div>

            <a href="requests.php" class="link-view-action">
                <span>View request details</span>
                <span>&rarr;</span>
            </a>
        </div>

        <!-- Clinic Announcements Card -->
        <div class="client-announcements-card">
            <div class="announcements-header-row">
                <h2 class="announcements-title">Announcements</h2>
                <a href="notifications.php?tab=care" class="link-view-action">View all</a>
            </div>

            <div class="announcement-list-item">
                <div class="announcement-icon-box icon-box-orange">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                </div>
                <div class="announcement-content-block">
                    <span class="announcement-tag-pill tag-orange">Clinic update</span>
                    <h3 class="announcement-item-title">Holiday hours this month</h3>
                    <p class="announcement-item-text">We'll be closed on May 26 for the public holiday.</p>
                    <span class="announcement-timestamp">2 days ago</span>
                </div>
            </div>

            <div class="announcement-list-item">
                <div class="announcement-icon-box icon-box-teal">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path>
                        <path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path>
                    </svg>
                </div>
                <div class="announcement-content-block">
                    <span class="announcement-tag-pill tag-teal">Care tip</span>
                    <h3 class="announcement-item-title">Summer prosthetic care</h3>
                    <p class="announcement-item-text">Simple ways to protect your liner in warmer weather.</p>
                    <span class="announcement-timestamp">May 8</span>
                </div>
            </div>
        </div>
    </section>

    <!-- FOOTER (Image 1) -->
    <footer class="client-footer-row">
        <div>&copy; 2025 ProstheCare Clinic</div>
        <div class="client-footer-links">
            <a href="#" class="footer-link">Privacy</a>
            <span>&middot;</span>
            <a href="#" class="footer-link">Help center</a>
            <span>&middot;</span>
            <span>v2.4.1</span>
        </div>
    </footer>

<?php } else {
    // Staff & Admin Clinical Dashboard
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
                        <?php foreach (array_slice($appointments, 0, 5) as $apt): ?>
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
<?php } ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
