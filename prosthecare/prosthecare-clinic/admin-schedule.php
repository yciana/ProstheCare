<?php
/**
 * ProstheCare Clinic Management System - Clinic Schedule
 * Matches Figma design (media_1794282404683_88c071f6.png)
 * Strictly clean production view - No "Preview As" switchers.
 */

$pageTitle = 'Clinic schedule';
require_once __DIR__ . '/includes/header.php';

$message = '';
$isError = false;

// Handle New Appointment Creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_schedule_appointment') {
    $patientName = trim($_POST['patient_name'] ?? '');
    $procedureType = trim($_POST['procedure_type'] ?? 'Prosthetic fitting');
    $time = trim($_POST['appointment_time'] ?? '10:30 AM');
    $practitioner = trim($_POST['practitioner'] ?? 'Dr. James Wilson');
    $room = trim($_POST['room'] ?? 'Room 2');

    if (empty($patientName)) {
        $message = 'Client name is required.';
        $isError = true;
    } else {
        $newApt = [
            'patient_name' => $patientName,
            'appointment_type' => $procedureType,
            'appointment_time' => $time,
            'room' => $room,
            'status' => 'Confirmed',
            'practitioner_notes' => "Assigned to {$practitioner}"
        ];
        $supabase->from('appointments')->insert($newApt);
        $message = "Appointment scheduled for {$patientName} at {$time} ({$room}).";
    }
}

// Canonical schedule rows matching Figma design
$scheduleRows = [
    [
        'time' => '9:00 AM',
        'client' => 'Daniel Lee',
        'procedure' => 'Gait assessment',
        'doctor' => 'Dr. Rachel Kim',
        'room' => 'Room 1',
        'status' => 'Completed'
    ],
    [
        'time' => '10:30 AM',
        'client' => 'Sarah Mitchell',
        'procedure' => 'Prosthetic fitting',
        'doctor' => 'Dr. James Wilson',
        'room' => 'Room 2',
        'status' => 'In progress'
    ],
    [
        'time' => '11:30 AM',
        'client' => 'Robert Chen',
        'procedure' => 'Maintenance',
        'doctor' => 'David Morgan',
        'room' => 'Workshop',
        'status' => 'Confirmed'
    ],
    [
        'time' => '1:00 PM',
        'client' => 'Maria Santos',
        'procedure' => 'Initial consultation',
        'doctor' => 'Amelia Patel',
        'room' => 'Room 3',
        'status' => 'Confirmed'
    ],
    [
        'time' => '2:30 PM',
        'client' => 'Olivia Parker',
        'procedure' => 'Socket adjustment',
        'doctor' => 'Dr. Rachel Kim',
        'room' => 'Room 2',
        'status' => 'Confirmed'
    ]
];
?>

<!-- HEADER ROW (Screenshot 4) -->
<section class="client-greeting-row" aria-label="Clinic Schedule Header" style="margin-bottom: 24px;">
    <div>
        <h1 class="client-greeting-title">Clinic schedule</h1>
        <p class="client-greeting-subtitle">Review today's appointments, rooms, and practitioners.</p>
    </div>
    <div>
        <button type="button" class="btn-primary-action" onclick="document.getElementById('newScheduleModal').style.display='flex';">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>New appointment</span>
        </button>
    </div>
</section>

<?php if (!empty($message)): ?>
    <div class="auth-alert-box <?= $isError ? 'alert-error' : 'alert-success' ?>" style="margin-bottom: 20px;">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span><?= htmlspecialchars($message) ?></span>
    </div>
<?php endif; ?>

<!-- SCHEDULE CARD WRAPPER -->
<div class="staff-card schedule-main-card">
    <!-- DATE NAVIGATION HEADER -->
    <div class="schedule-date-nav-bar">
        <button type="button" class="btn-date-nav">&larr; Previous day</button>
        <div class="schedule-date-center">
            <h2 class="schedule-date-heading">Wednesday, May 14</h2>
            <span class="schedule-date-meta">12 appointments &middot; 4 practitioners</span>
        </div>
        <button type="button" class="btn-date-nav">Next day &rarr;</button>
    </div>

    <!-- SCHEDULE LIST ROWS -->
    <div class="schedule-list-items">
        <?php foreach ($scheduleRows as $row): 
            $stLower = strtolower($row['status']);
            $pillClass = 'pill-status-confirmed';
            if ($stLower === 'completed') {
                $pillClass = 'pill-status-completed';
            } elseif ($stLower === 'in progress') {
                $pillClass = 'pill-status-inprogress';
            }
        ?>
            <div class="schedule-item-row">
                <div class="schedule-time-col">
                    <span class="schedule-time-bold"><?= htmlspecialchars($row['time']) ?></span>
                </div>

                <div class="schedule-client-col">
                    <span class="schedule-client-name"><?= htmlspecialchars($row['client']) ?></span>
                    <span class="schedule-procedure-text"><?= htmlspecialchars($row['procedure']) ?></span>
                </div>

                <div class="schedule-doctor-col">
                    <span class="schedule-doctor-name"><?= htmlspecialchars($row['doctor']) ?></span>
                </div>

                <div class="schedule-room-col">
                    <span class="schedule-room-text"><?= htmlspecialchars($row['room']) ?></span>
                </div>

                <div class="schedule-status-col">
                    <span class="schedule-status-pill <?= $pillClass ?>"><?= htmlspecialchars($row['status']) ?></span>
                </div>

                <div class="schedule-action-col">
                    <a href="patients.php" class="link-view-action" style="font-size: 13px;">Open &gt;</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- NEW APPOINTMENT MODAL -->
<div id="newScheduleModal" class="modal-overlay" style="display: none;">
    <div class="modal-dialog-card">
        <div class="modal-header-row">
            <h3 class="modal-title-text">Schedule new appointment</h3>
            <button type="button" class="modal-close-btn" onclick="document.getElementById('newScheduleModal').style.display='none';">&times;</button>
        </div>
        <form method="POST" action="admin-schedule.php">
            <input type="hidden" name="action" value="create_schedule_appointment">
            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 6px;">Client name</label>
                <input type="text" name="patient_name" class="find-search-input" placeholder="e.g. Maria Santos" required>
            </div>
            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 6px;">Procedure / Service</label>
                <select name="procedure_type" class="admin-select-filter" style="width: 100%;">
                    <option value="Initial consultation">Initial consultation (60 min)</option>
                    <option value="Prosthetic fitting">Prosthetic fitting (45 min)</option>
                    <option value="Maintenance & repair">Maintenance & repair (30 min)</option>
                    <option value="Gait assessment">Gait assessment (45 min)</option>
                    <option value="Socket adjustment">Socket adjustment (40 min)</option>
                </select>
            </div>
            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 6px;">Practitioner</label>
                <select name="practitioner" class="admin-select-filter" style="width: 100%;">
                    <option value="Dr. James Wilson">Dr. James Wilson (Prosthetist)</option>
                    <option value="Dr. Rachel Kim">Dr. Rachel Kim (Senior Prosthetist)</option>
                    <option value="David Morgan">David Morgan (Technician)</option>
                    <option value="Amelia Patel">Amelia Patel (Prosthetist)</option>
                </select>
            </div>
            <div style="margin-bottom: 14px; display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 6px;">Time</label>
                    <input type="text" name="appointment_time" class="find-search-input" value="10:30 AM" required>
                </div>
                <div>
                    <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 6px;">Room / Suite</label>
                    <select name="room" class="admin-select-filter" style="width: 100%;">
                        <option value="Room 1">Room 1</option>
                        <option value="Room 2" selected>Room 2</option>
                        <option value="Room 3">Room 3</option>
                        <option value="Workshop">Workshop</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px; margin-top: 20px;">
                <button type="button" class="btn-approval-reject" onclick="document.getElementById('newScheduleModal').style.display='none';">Cancel</button>
                <button type="submit" class="btn-primary-action">Confirm appointment</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
