<?php
/**
 * ProstheCare Clinic Management System - Client Dashboard
 * Refactored to use shared includes/header.php + includes/footer.php pattern.
 * Matches the target Figma design (sidebar layout, queue card, appointment card,
 * active request stepper, announcements, footer).
 */

$pageTitle = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

// -------------------------------------------------------------------------
// ADDITIONAL DATA FETCHING
// (header.php already provides: $supabase, $currentUser, $displayName,
//  $firstName, $initials, $isClient, $isAdmin, $isStaff)
// -------------------------------------------------------------------------

// --- Patient record (needed to scope appointments / care plans) -----------
$userEmail  = $currentUser['email'] ?? 'sarah.m@example.com';
$myPatient  = null;

$clientRecords = $supabase->from('patients')->select('*')->eq('email', $userEmail)->get();
if (!empty($clientRecords)) {
    $myPatient = $clientRecords[0];
}

// --- Upcoming appointment -------------------------------------------------
$myAppointments = [];

// Prefer a freshly booked appointment from session
if (!empty($_SESSION['booked_appointment'])) {
    $myAppointments[] = $_SESSION['booked_appointment'];
}

if ($myPatient && !empty($myPatient['id'])) {
    $dbAppointments = $supabase
        ->from('appointments')
        ->select('*')
        ->eq('patient_id', $myPatient['id'])
        ->get();

    if (!empty($dbAppointments)) {
        $myAppointments = array_merge($myAppointments, $dbAppointments);
    }
}

// Defaults (shown when no DB record exists yet)
$upcomingAptTitle = 'Prosthetic fitting';
$upcomingAptDay   = '15';
$upcomingAptMonth = 'MAY';
$upcomingAptTime  = 'Thursday, 10:30 AM';
$upcomingDoctor   = 'with Dr. James Wilson';
$upcomingRoom     = 'Fitting Room 2 &middot; 45 min';

if (!empty($myAppointments[0])) {
    $apt = $myAppointments[0];

    if (!empty($apt['appointment_type'])) {
        $upcomingAptTitle = $apt['appointment_type'];
    }
    if (!empty($apt['appointment_date'])) {
        $ts = strtotime($apt['appointment_date']);
        if ($ts) {
            $upcomingAptDay   = date('d', $ts);
            $upcomingAptMonth = strtoupper(date('M', $ts));
            $upcomingAptTime  = date('l, g:i A', $ts);
        }
    }
    if (!empty($apt['doctor_name'])) {
        $upcomingDoctor = 'with ' . $apt['doctor_name'];
    }
    if (!empty($apt['room'])) {
        $upcomingRoom = htmlspecialchars($apt['room']);
    }
}

// --- Queue ticket ----------------------------------------------------------
// Looks up the client's ticket in the clinic_queue table by name or patient_id.
$queueTicket   = null;
$nowServing    = null;
$estimatedWait = null;

$queueResults = $supabase
    ->from('clinic_queue')
    ->select('*')
    ->eq('client_name', $displayName)
    ->get();

if (empty($queueResults) && $myPatient && !empty($myPatient['id'])) {
    // Fallback: try matching by patient_id if the column exists
    $queueResults = $supabase
        ->from('clinic_queue')
        ->select('*')
        ->eq('patient_id', $myPatient['id'])
        ->get();
}

if (!empty($queueResults)) {
    $queueTicket   = $queueResults[0]['ticket_number']       ?? null;
    $estimatedWait = $queueResults[0]['estimated_wait_minutes'] ?? null;
}

// Also fetch the ticket currently being served (status = 'serving')
$servingResults = $supabase
    ->from('clinic_queue')
    ->select('ticket_number')
    ->eq('status', 'serving')
    ->get();

if (!empty($servingResults)) {
    $nowServing = $servingResults[0]['ticket_number'];
}

// Fall back to design-spec defaults when no live queue data is available
$queueTicketDisplay = $queueTicket   ?? 'A-07';
$nowServingDisplay  = $nowServing    ?? 'A-04';
$waitDisplay        = ($estimatedWait !== null) ? '~' . $estimatedWait . ' min' : '~18 min';

// --- Active care plan / request -------------------------------------------
$activeRequest = null;

if ($myPatient && !empty($myPatient['id'])) {
    $carePlans = $supabase
        ->from('care_plans')
        ->select('*')
        ->eq('patient_id', $myPatient['id'])
        ->get();

    if (!empty($carePlans)) {
        // Prefer an in-progress plan
        foreach ($carePlans as $plan) {
            $s = strtolower($plan['status'] ?? '');
            if (strpos($s, 'fabricat') !== false || strpos($s, 'active') !== false || strpos($s, 'pending') !== false) {
                $activeRequest = $plan;
                break;
            }
        }
        // Fall back to the first plan if none match the preferred statuses
        if (!$activeRequest) {
            $activeRequest = $carePlans[0];
        }
    }
}

$activeRequestTitle  = $activeRequest['device_type']   ?? 'Transtibial prosthesis';
$activeRequestId     = $activeRequest['id']            ?? 'PR-2025-0184';
$activeRequestStatus = $activeRequest['status']        ?? 'In fabrication';
$activeRequestSub    = 'Request #' . (is_string($activeRequestId) ? substr($activeRequestId, 0, 15) : $activeRequestId) . ' &middot; Submitted Apr 22';

// --- Announcements --------------------------------------------------------
$announcements = $supabase
    ->from('announcements')
    ->select('*')
    ->eq('status', 'Published')
    ->order('created_at', false)
    ->limit(2)
    ->get();
?>

<!-- ======================================================================
     CLIENT DASHBOARD CONTENT
     (Rendered inside <main class="page-content-wrapper"> from header.php)
     ====================================================================== -->

<!-- GREETING HEADER ROW -->
<section class="client-greeting-row" aria-label="Greeting header">
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

<!-- ROW 1: TODAY'S QUEUE CARD + UPCOMING APPOINTMENT -->
<section class="client-hero-grid" aria-label="Queue and upcoming appointment">

    <!-- Left: Teal queue number hero card -->
    <div class="client-queue-card">
        <div class="queue-card-watermark"></div>
        <div class="queue-card-left">
            <div class="queue-card-eyebrow">TODAY'S QUEUE NUMBER</div>
            <div class="queue-card-number"><?= htmlspecialchars($queueTicketDisplay) ?></div>
            <div class="queue-status-pill">
                <span class="queue-dot-pulse"></span>
                <span>Now serving <?= htmlspecialchars($nowServingDisplay) ?></span>
            </div>
        </div>
        <div class="queue-card-divider"></div>
        <div class="queue-card-right">
            <div class="queue-wait-label">Estimated wait</div>
            <div class="queue-wait-value"><?= htmlspecialchars($waitDisplay) ?></div>
            <div class="queue-wait-note">Check in at reception</div>
        </div>
    </div>

    <!-- Right: Upcoming appointment card -->
    <div class="client-appointment-card">
        <div>
            <div class="card-header-label-row">
                <span class="card-tiny-eyebrow">UPCOMING APPOINTMENT</span>
                <button type="button" style="background:none;border:none;color:var(--color-text-light);cursor:pointer;padding:4px;display:flex;align-items:center;" title="More options" aria-label="More options">
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
                    <span class="appointment-subtext"><?= $upcomingDoctor ?></span>
                    <span class="appointment-subtext"><?= $upcomingRoom ?></span>
                </div>
            </div>
        </div>
        <a href="appointments.php" class="link-view-action">
            <span>View appointment</span>
            <span>&rarr;</span>
        </a>
    </div>

</section>

<!-- ROW 2: ACTIVE REQUEST STEPPER + ANNOUNCEMENTS -->
<section class="client-secondary-grid" aria-label="Active request and announcements">

    <!-- Left: Active request with 5-step stepper -->
    <div class="client-active-request-card">
        <div>
            <div class="request-card-header">
                <span class="card-tiny-eyebrow">ACTIVE REQUEST</span>
                <span class="status-pill-amber"><?= htmlspecialchars($activeRequestStatus) ?></span>
            </div>
            <h2 class="request-card-title"><?= htmlspecialchars($activeRequestTitle) ?></h2>
            <p class="request-card-subtitle"><?= $activeRequestSub ?></p>

            <!-- 5-Step Horizontal Timeline Stepper -->
            <div class="stepper-timeline-container">
                <div class="stepper-track-line"></div>
                <div class="stepper-track-progress"></div>

                <!-- Step 1: Request received (done) -->
                <div class="stepper-node-item">
                    <div class="stepper-node-circle node-completed">
                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="stepper-node-label">Request received</span>
                    <span class="stepper-node-date">Apr 22</span>
                </div>

                <!-- Step 2: Assessment (done) -->
                <div class="stepper-node-item">
                    <div class="stepper-node-circle node-completed">
                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="stepper-node-label">Assessment</span>
                    <span class="stepper-node-date">Apr 25</span>
                </div>

                <!-- Step 3: Approved (done) -->
                <div class="stepper-node-item">
                    <div class="stepper-node-circle node-completed">
                        <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </div>
                    <span class="stepper-node-label">Approved</span>
                    <span class="stepper-node-date">Apr 28</span>
                </div>

                <!-- Step 4: Fabrication (active) -->
                <div class="stepper-node-item">
                    <div class="stepper-node-circle node-active">4</div>
                    <span class="stepper-node-label">Fabrication</span>
                    <span class="stepper-node-date">In progress</span>
                </div>

                <!-- Step 5: Ready (upcoming) -->
                <div class="stepper-node-item">
                    <div class="stepper-node-circle node-upcoming">5</div>
                    <span class="stepper-node-label">Ready</span>
                    <span class="stepper-node-date">Est. May 24</span>
                </div>
            </div>
        </div>

        <a href="requests.php" class="link-view-action">
            <span>View request details</span>
            <span>&rarr;</span>
        </a>
    </div>

    <!-- Right: Announcements card -->
    <div class="client-announcements-card">
        <div class="announcements-header-row">
            <h2 class="announcements-title">Announcements</h2>
            <a href="notifications.php" class="link-view-action" style="margin-top:0;">View all</a>
        </div>

        <?php if (!empty($announcements)): ?>
            <?php foreach ($announcements as $ann):
                $isCare = stripos($ann['category'] ?? '', 'Care') !== false;
            ?>
                <div class="announcement-list-item">
                    <div class="announcement-icon-box <?= $isCare ? 'icon-box-teal' : 'icon-box-orange' ?>">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <?php if ($isCare): ?>
                                <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path>
                                <path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path>
                            <?php else: ?>
                                <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                            <?php endif; ?>
                        </svg>
                    </div>
                    <div class="announcement-content-block">
                        <span class="announcement-tag-pill <?= $isCare ? 'tag-teal' : 'tag-orange' ?>"><?= htmlspecialchars($ann['category'] ?? 'Clinic update') ?></span>
                        <span class="announcement-item-title"><?= htmlspecialchars($ann['title'] ?? '') ?></span>
                        <span class="announcement-item-text"><?= htmlspecialchars($ann['message'] ?? '') ?></span>
                        <span class="announcement-timestamp">
                            <?php
                                $timeAgo = '2 days ago';
                                if (!empty($ann['created_at'])) {
                                    $createdTs = strtotime($ann['created_at']);
                                    $diffDays  = floor((time() - $createdTs) / 86400);
                                    $timeAgo   = $diffDays > 0 ? "{$diffDays} days ago" : 'Just now';
                                }
                                echo htmlspecialchars($timeAgo);
                            ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Default fallback announcements matching design spec -->
            <div class="announcement-list-item">
                <div class="announcement-icon-box icon-box-orange">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                </div>
                <div class="announcement-content-block">
                    <span class="announcement-tag-pill tag-orange">Clinic update</span>
                    <span class="announcement-item-title">Holiday hours this month</span>
                    <span class="announcement-item-text">We'll be closed on May 26 for the public holiday.</span>
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
                    <span class="announcement-item-title">Summer prosthetic care</span>
                    <span class="announcement-item-text">Simple ways to protect your liner in warmer weather.</span>
                    <span class="announcement-timestamp">May 8</span>
                </div>
            </div>
        <?php endif; ?>
    </div>

</section>

<!-- PAGE FOOTER -->
<footer style="margin-top: 8px; padding-top: 20px; border-top: 1px solid var(--color-border); display: flex; align-items: center; justify-content: space-between; font-size: 12px; color: var(--color-text-light);">
    <div>&copy; 2025 ProstheCare Clinic</div>
    <div>
        <a href="#" style="color: var(--color-text-light); text-decoration: none;">Privacy</a>
        &middot;
        <a href="#" style="color: var(--color-text-light); text-decoration: none;">Help center</a>
        &middot;
        <span>v2.4.1</span>
    </div>
</footer>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
