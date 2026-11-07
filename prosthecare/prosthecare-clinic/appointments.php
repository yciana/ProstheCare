<?php
/**
 * ProstheCare Clinic Management System - Appointments & Booking
 * Matches Figma design (Screenshot 2: Book an appointment for Clients)
 * Retains full clinical scheduling table for Staff/Admin
 */

require_once __DIR__ . '/config/supabase.php';
require_once __DIR__ . '/includes/SupabaseClient.php';

$supabase = new SupabaseClient();
$message = '';
$isError = false;

// Handle Client Appointment Booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'client_book_appointment') {
    $serviceType = trim($_POST['service_type'] ?? 'Prosthetic fitting');
    $aptDate = trim($_POST['appointment_date'] ?? '2025-05-15');
    $aptTime = trim($_POST['appointment_time'] ?? '10:30 AM');
    $room = 'Fitting Room 2';

    // Format ISO date for Supabase
    $timestamp = strtotime($aptDate);
    $formattedDate = $timestamp ? date('Y-m-d', $timestamp) : '2025-05-15';

    $newAppointment = [
        'patient_id' => $_SESSION['user']['id'] ?? 'SM-2025',
        'patient_name' => $_SESSION['user']['name'] ?? 'Sarah Mitchell',
        'appointment_date' => $formattedDate,
        'appointment_time' => $aptTime,
        'appointment_type' => $serviceType,
        'status' => 'Scheduled',
        'room' => $room,
        'practitioner_notes' => 'Booked online via ProstheCare Client Portal'
    ];

    // Insert into Supabase if configured
    $supabase->from('appointments')->insert($newAppointment);

    // Save in session so Dashboard immediately updates
    $_SESSION['booked_appointment'] = $newAppointment;

    $message = "Your appointment for {$serviceType} on " . date('l, M j', $timestamp) . " at {$aptTime} has been successfully scheduled!";
}

// Handle Staff Clinical Schedule creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create_appointment') {
    $patientId = trim($_POST['patient_id'] ?? '');
    $date = trim($_POST['appointment_date'] ?? '');
    $time = trim($_POST['appointment_time'] ?? '');
    $type = trim($_POST['appointment_type'] ?? '');
    $room = trim($_POST['room'] ?? 'Fitting Suite 2');
    $notes = trim($_POST['practitioner_notes'] ?? '');

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
            'room' => $room,
            'practitioner_notes' => $notes
        ];

        $insertResult = $supabase->from('appointments')->insert($appointmentData);
        if ($insertResult) {
            $message = 'Clinical fitting appointment scheduled successfully in Supabase!';
        } else {
            $message = 'Failed to schedule appointment in Supabase.';
            $isError = true;
        }
    }
}

$pageTitle = $isClient ? 'Book an appointment' : 'Fitting & Alignment Schedule';
require_once __DIR__ . '/includes/header.php';

// Retrieve appointments and patients from Supabase
$appointments = $supabase->from('appointments')->select('*')->order('appointment_date', true)->get();
$patients = $supabase->from('patients')->select('*')->order('first_name', true)->get();
?>

<?php if ($isClient): ?>
    <!-- ==========================================================================
         CLIENT VIEW: BOOK AN APPOINTMENT (SCREENSHOT 2)
         ========================================================================== -->
    <div class="client-greeting-row" style="margin-bottom: 24px;">
        <div>
            <h1 class="client-greeting-title">Book an appointment</h1>
            <p class="client-greeting-subtitle">Choose a service, date, and a time that works for you.</p>
        </div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="auth-alert-box alert-success" style="margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between;">
            <div style="display: flex; align-items: center; gap: 10px;">
                <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="20 6 9 17 4 12"></polyline>
                </svg>
                <span><?= htmlspecialchars($message) ?></span>
            </div>
            <a href="dashboard.php" style="color: #07474b; font-weight: 700; text-decoration: underline; font-size: 13px;">View on Dashboard &rarr;</a>
        </div>
    <?php endif; ?>

    <form method="POST" action="appointments.php" id="clientBookingForm">
        <input type="hidden" name="action" value="client_book_appointment">
        <input type="hidden" name="service_type" id="inputServiceType" value="Prosthetic fitting">
        <input type="hidden" name="appointment_date" id="inputAppointmentDate" value="2025-05-15">
        <input type="hidden" name="appointment_time" id="inputAppointmentTime" value="10:30 AM">

        <div class="booking-page-grid">
            <!-- LEFT COLUMN: Step 1 & Step 2 -->
            <div class="booking-left-col">
                <!-- STEP 1: Select a service -->
                <section class="booking-step-block">
                    <div class="step-indicator-header">
                        <div class="step-number-bubble">1</div>
                        <div>
                            <h2 class="step-title-text">Select a service</h2>
                            <p class="step-subtitle-text">What can we help you with?</p>
                        </div>
                    </div>

                    <div class="booking-services-grid">
                        <!-- Service 1: Initial consultation -->
                        <div class="service-card-item" data-service="Initial consultation" data-duration="60 min">
                            <div class="service-card-check-badge">
                                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div>
                                <div class="service-card-icon-box">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                    </svg>
                                </div>
                                <h3 class="service-card-title">Initial consultation</h3>
                                <p class="service-card-desc">Meet your prosthetist and discuss your mobility goals.</p>
                            </div>
                            <div>
                                <span class="service-duration-badge">60 min</span>
                            </div>
                        </div>

                        <!-- Service 2: Prosthetic fitting (Selected by default) -->
                        <div class="service-card-item selected" data-service="Prosthetic fitting" data-duration="45 min">
                            <div class="service-card-check-badge">
                                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div>
                                <div class="service-card-icon-box">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 2a4 4 0 0 1 4 4v4a4 4 0 0 1-4 4 4 4 0 0 1-4-4V6a4 4 0 0 1 4-4z"></path>
                                        <path d="M12 14v8"></path>
                                        <path d="M8 22h8"></path>
                                    </svg>
                                </div>
                                <h3 class="service-card-title">Prosthetic fitting</h3>
                                <p class="service-card-desc">Fit, align, and fine-tune your new prosthesis.</p>
                            </div>
                            <div>
                                <span class="service-duration-badge">45 min</span>
                            </div>
                        </div>

                        <!-- Service 3: Maintenance & repair -->
                        <div class="service-card-item" data-service="Maintenance & repair" data-duration="20 min">
                            <div class="service-card-check-badge">
                                <svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                            </div>
                            <div>
                                <div class="service-card-icon-box">
                                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                                    </svg>
                                </div>
                                <h3 class="service-card-title">Maintenance & repair</h3>
                                <p class="service-card-desc">Routine servicing, adjustments, or repairs.</p>
                            </div>
                            <div>
                                <span class="service-duration-badge">20 min</span>
                            </div>
                        </div>
                    </div>
                </section>

                <!-- STEP 2: Choose a date -->
                <section class="booking-step-block">
                    <div class="step-indicator-header">
                        <div class="step-number-bubble">2</div>
                        <div>
                            <h2 class="step-title-text">Choose a date</h2>
                            <p class="step-subtitle-text">Available days are highlighted.</p>
                        </div>
                    </div>

                    <!-- Interactive Month Calendar -->
                    <div class="calendar-panel-card">
                        <div class="calendar-header-nav">
                            <button type="button" class="calendar-nav-btn" id="prevMonthBtn" title="Previous Month">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="15 18 9 12 15 6"></polyline>
                                </svg>
                            </button>
                            <span class="calendar-month-title" id="calendarMonthLabel">May 2025</span>
                            <button type="button" class="calendar-nav-btn" id="nextMonthBtn" title="Next Month">
                                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </button>
                        </div>

                        <div class="calendar-weekdays-row">
                            <span>SUN</span>
                            <span>MON</span>
                            <span>TUE</span>
                            <span>WED</span>
                            <span>THU</span>
                            <span>FRI</span>
                            <span>SAT</span>
                        </div>

                        <!-- Days Grid (May 2025: May 1 is Thursday -> 4 empty cells) -->
                        <div class="calendar-days-grid" id="calendarDaysGrid">
                            <div class="calendar-day-cell empty"></div>
                            <div class="calendar-day-cell empty"></div>
                            <div class="calendar-day-cell empty"></div>
                            <div class="calendar-day-cell empty"></div>
                            <div class="calendar-day-cell" data-day="1">1</div>
                            <div class="calendar-day-cell" data-day="2">2</div>
                            <div class="calendar-day-cell" data-day="3">3</div>
                            <div class="calendar-day-cell" data-day="4">4</div>
                            <div class="calendar-day-cell" data-day="5">5</div>
                            <div class="calendar-day-cell" data-day="6">6</div>
                            <div class="calendar-day-cell" data-day="7">7</div>
                            <div class="calendar-day-cell" data-day="8">8</div>
                            <div class="calendar-day-cell" data-day="9">9</div>
                            <div class="calendar-day-cell" data-day="10">10</div>
                            <div class="calendar-day-cell" data-day="11">11</div>
                            <div class="calendar-day-cell" data-day="12">12</div>
                            <div class="calendar-day-cell" data-day="13">13</div>
                            <div class="calendar-day-cell" data-day="14">14</div>
                            <div class="calendar-day-cell active" data-day="15">15</div>
                            <div class="calendar-day-cell" data-day="16">16</div>
                            <div class="calendar-day-cell" data-day="17">17</div>
                            <div class="calendar-day-cell" data-day="18">18</div>
                            <div class="calendar-day-cell" data-day="19">19</div>
                            <div class="calendar-day-cell" data-day="20">20</div>
                            <div class="calendar-day-cell" data-day="21">21</div>
                            <div class="calendar-day-cell" data-day="22">22</div>
                            <div class="calendar-day-cell" data-day="23">23</div>
                            <div class="calendar-day-cell" data-day="24">24</div>
                            <div class="calendar-day-cell" data-day="25">25</div>
                            <div class="calendar-day-cell" data-day="26">26</div>
                            <div class="calendar-day-cell" data-day="27">27</div>
                            <div class="calendar-day-cell" data-day="28">28</div>
                            <div class="calendar-day-cell" data-day="29">29</div>
                            <div class="calendar-day-cell" data-day="30">30</div>
                            <div class="calendar-day-cell" data-day="31">31</div>
                        </div>
                    </div>
                </section>
            </div>

            <!-- RIGHT COLUMN: Step 3 (Select a time) -->
            <div class="booking-right-col">
                <div class="time-slot-panel-card">
                    <div class="step-indicator-header">
                        <div class="step-number-bubble">3</div>
                        <div>
                            <h2 class="step-title-text">Select a time</h2>
                            <p class="step-subtitle-text" id="selectedDateDisplay">Thursday, May 15</p>
                        </div>
                    </div>

                    <!-- MORNING SLOTS -->
                    <div class="time-section-label">MORNING</div>
                    <div class="time-slots-grid">
                        <button type="button" class="time-slot-btn" data-time="9:00 AM">9:00 AM</button>
                        <button type="button" class="time-slot-btn" data-time="9:30 AM">9:30 AM</button>
                        <button type="button" class="time-slot-btn selected" data-time="10:30 AM">10:30 AM</button>
                        <button type="button" class="time-slot-btn" data-time="11:00 AM">11:00 AM</button>
                    </div>

                    <!-- AFTERNOON SLOTS -->
                    <div class="time-section-label">AFTERNOON</div>
                    <div class="time-slots-grid">
                        <button type="button" class="time-slot-btn" data-time="1:30 PM">1:30 PM</button>
                        <button type="button" class="time-slot-btn" data-time="2:00 PM">2:00 PM</button>
                        <button type="button" class="time-slot-btn" data-time="3:30 PM">3:30 PM</button>
                        <button type="button" class="time-slot-btn" data-time="4:00 PM">4:00 PM</button>
                    </div>

                    <!-- YOUR SELECTION SUMMARY BOX -->
                    <div class="booking-summary-box">
                        <div class="summary-eyebrow">YOUR SELECTION</div>
                        <div class="summary-service-name" id="summaryServiceText">Prosthetic fitting</div>
                        <div class="summary-datetime-text" id="summaryDateTimeText">May 15, 2025 &middot; 10:30 AM</div>
                    </div>

                    <!-- CONFIRM BUTTON -->
                    <button type="submit" class="btn-confirm-booking">Confirm appointment</button>
                </div>
            </div>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const serviceCards = document.querySelectorAll('.service-card-item');
        const dayCells = document.querySelectorAll('.calendar-day-cell:not(.empty)');
        const timeSlotBtns = document.querySelectorAll('.time-slot-btn');

        const inputService = document.getElementById('inputServiceType');
        const inputDate = document.getElementById('inputAppointmentDate');
        const inputTime = document.getElementById('inputAppointmentTime');

        const summaryService = document.getElementById('summaryServiceText');
        const summaryDateTime = document.getElementById('summaryDateTimeText');
        const selectedDateDisplay = document.getElementById('selectedDateDisplay');

        let currentService = "Prosthetic fitting";
        let currentDay = 15;
        let currentYear = 2025;
        let currentMonth = 4; // 0-indexed May
        let currentTime = "10:30 AM";

        const dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
        const monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

        function updateSummary() {
            const dateObj = new Date(currentYear, currentMonth, currentDay);
            const dayName = dayNames[dateObj.getDay()];
            const monthName = monthNames[currentMonth];
            const formattedIso = `${currentYear}-${String(currentMonth + 1).padStart(2, '0')}-${String(currentDay).padStart(2, '0')}`;

            inputService.value = currentService;
            inputDate.value = formattedIso;
            inputTime.value = currentTime;

            summaryService.textContent = currentService;
            summaryDateTime.textContent = `${monthName} ${currentDay}, ${currentYear} · ${currentTime}`;
            selectedDateDisplay.textContent = `${dayName}, ${monthName} ${currentDay}`;
        }

        // Service card selection
        serviceCards.forEach(card => {
            card.addEventListener('click', function() {
                serviceCards.forEach(c => c.classList.remove('selected'));
                this.classList.add('selected');
                currentService = this.getAttribute('data-service');
                updateSummary();
            });
        });

        // Day cell selection
        dayCells.forEach(cell => {
            cell.addEventListener('click', function() {
                dayCells.forEach(c => c.classList.remove('active'));
                this.classList.add('active');
                currentDay = parseInt(this.getAttribute('data-day'), 10);
                updateSummary();
            });
        });

        // Time slot selection
        timeSlotBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                timeSlotBtns.forEach(b => b.classList.remove('selected'));
                this.classList.add('selected');
                currentTime = this.getAttribute('data-time');
                updateSummary();
            });
        });

        updateSummary();
    });
    </script>

<?php else: ?>
    <!-- ==========================================================================
         STAFF / ADMIN VIEW: CLINICAL FITTING SCHEDULE TABLE
         ========================================================================== -->
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
                        <th>Notes</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($appointments)): ?>
                        <tr>
                            <td colspan="6">No scheduled fitting sessions recorded yet in Supabase.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($appointments as $apt): ?>
                            <tr>
                                <td>
                                    <strong><?= htmlspecialchars($apt['appointment_date'] ?? '') ?></strong>
                                    <br><small><?= htmlspecialchars($apt['appointment_time'] ?? '') ?></small>
                                </td>
                                <td>
                                    <strong><?= htmlspecialchars($apt['patient_name'] ?? $apt['patient_id'] ?? 'Client') ?></strong>
                                </td>
                                <td><?= htmlspecialchars($apt['appointment_type'] ?? 'Fitting') ?></td>
                                <td><?= htmlspecialchars($apt['room'] ?? 'Suite 1') ?></td>
                                <td><?= htmlspecialchars($apt['practitioner_notes'] ?? '-') ?></td>
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
    </div>

    <!-- Modal: Schedule Appointment for Staff -->
    <div class="modal-overlay" id="newAppointmentModal">
        <div class="modal-card">
            <div class="modal-header-bar">
                <h3 class="modal-title">Schedule Clinical Fitting Session</h3>
                <button type="button" class="modal-close-btn" data-modal-close aria-label="Close dialog">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
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
                                    <?= htmlspecialchars(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '') . ' (' . ($p['patient_code'] ?? '') . ')') ?>
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

                    <div class="form-grid-2col">
                        <div class="form-field-group">
                            <label for="appointment_type" class="form-label-text">Procedure Type *</label>
                            <select id="appointment_type" name="appointment_type" class="form-input-control" required>
                                <option value="Initial Clinical Assessment">Initial Clinical Assessment</option>
                                <option value="Diagnostic Check Socket Fitting" selected>Diagnostic Check Socket Fitting</option>
                                <option value="Dynamic Gait Alignment">Dynamic Gait Alignment</option>
                                <option value="Myoelectric Calibration">Myoelectric Calibration</option>
                                <option value="Final Delivery & Training">Final Delivery & Training</option>
                                <option value="Routine 6-Month Review">Routine 6-Month Review</option>
                            </select>
                        </div>

                        <div class="form-field-group">
                            <label for="room" class="form-label-text">Clinic Room / Lab</label>
                            <select id="room" name="room" class="form-input-control">
                                <option value="Gait Analysis Lab A" selected>Gait Analysis Lab A</option>
                                <option value="Fitting Suite 2">Fitting Suite 2</option>
                                <option value="Upper Extremity Lab">Upper Extremity Lab</option>
                                <option value="Casting & Impression Bay">Casting & Impression Bay</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-field-group">
                        <label for="practitioner_notes" class="form-label-text">Session Instructions / Target Goals</label>
                        <textarea id="practitioner_notes" name="practitioner_notes" class="form-input-control" rows="2" placeholder="e.g. Check for lateral gapping in socket during heel-strike..."></textarea>
                    </div>
                </div>

                <div class="modal-footer-bar">
                    <button type="button" class="btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" class="btn-primary-action">Confirm Appointment</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
