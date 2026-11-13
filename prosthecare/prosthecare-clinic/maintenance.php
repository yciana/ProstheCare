<?php
/**
 * ProstheCare Clinic Management System - Maintenance Record
 * Matches Figma design (Screenshot 3: Maintenance record)
 */

$pageTitle = 'Maintenance Record';
require_once __DIR__ . '/includes/header.php';

$message = '';
$isError = false;

// Handle Form Submission (Complete Record or Save as Draft)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actionType = $_POST['form_action'] ?? 'complete';
    $clientName = trim($_POST['client_name'] ?? 'Sarah Mitchell');
    $deviceSerial = trim($_POST['device_serial'] ?? 'TT-2023-8841');
    $serviceDate = trim($_POST['service_date'] ?? '2025-05-14');
    $serviceType = trim($_POST['service_type'] ?? 'Routine maintenance');

    $socketCond = trim($_POST['socket_liner_condition'] ?? 'Good');
    $socketAction = trim($_POST['socket_liner_action'] ?? '');

    $pylonsCond = trim($_POST['pylon_adapters_condition'] ?? 'Monitor');
    $pylonsAction = trim($_POST['pylon_adapters_action'] ?? 'retorqued to spec');

    $footCond = trim($_POST['foot_module_condition'] ?? 'Good');
    $footAction = trim($_POST['foot_module_action'] ?? '');

    $coverCond = trim($_POST['cosmetic_cover_condition'] ?? 'Good');
    $coverAction = trim($_POST['cosmetic_cover_action'] ?? '');

    $serviceNotes = trim($_POST['service_notes'] ?? '');
    $isSafe = isset($_POST['is_safe_for_use']) ? true : false;
    $recordStatus = ($actionType === 'draft') ? 'Draft' : 'Completed';

    $maintenanceData = [
        'client_name' => $clientName,
        'client_code' => 'PC-1048',
        'device_serial' => $deviceSerial,
        'device_type' => 'Transtibial - Left',
        'technician_name' => 'Alex Morgan',
        'service_date' => $serviceDate,
        'service_type' => $serviceType,
        'socket_liner_condition' => $socketCond,
        'socket_liner_action' => $socketAction,
        'pylon_adapters_condition' => $pylonsCond,
        'pylon_adapters_action' => $pylonsAction,
        'foot_module_condition' => $footCond,
        'foot_module_action' => $footAction,
        'cosmetic_cover_condition' => $coverCond,
        'cosmetic_cover_action' => $coverAction,
        'service_notes' => $serviceNotes,
        'is_safe_for_use' => $isSafe,
        'next_service_due' => '2025-11-14',
        'status' => $recordStatus
    ];

    // Save in Supabase
    $supabase->from('maintenance_records')->insert($maintenanceData);
    $_SESSION['latest_maintenance'] = $maintenanceData;

    if ($actionType === 'draft') {
        $message = "Maintenance record draft for {$clientName} ({$deviceSerial}) has been auto-saved.";
    } else {
        $message = "Maintenance record for {$clientName} has been successfully completed and filed!";
    }
}

// Fetch latest record or use defaults matching Screenshot 3
$savedRecord = $_SESSION['latest_maintenance'] ?? null;
if (!$savedRecord) {
    $dbRecords = $supabase->from('maintenance_records')->select('*')->order('created_at', false)->limit(1)->get();
    if (!empty($dbRecords)) {
        $savedRecord = $dbRecords[0];
    }
}

$clientValue = $savedRecord['client_name'] ?? 'Sarah Mitchell - PC-1048';
$serialValue = $savedRecord['device_serial'] ?? 'TT-2023-8841';
$dateValue = !empty($savedRecord['service_date']) ? $savedRecord['service_date'] : '2025-05-14';
$typeValue = $savedRecord['service_type'] ?? 'Routine maintenance';

$socketCondVal = $savedRecord['socket_liner_condition'] ?? 'Good';
$socketActionVal = $savedRecord['socket_liner_action'] ?? '';

$pylonCondVal = $savedRecord['pylon_adapters_condition'] ?? 'Monitor';
$pylonActionVal = $savedRecord['pylon_adapters_action'] ?? 'retorqued to spec';

$footCondVal = $savedRecord['foot_module_condition'] ?? 'Good';
$footActionVal = $savedRecord['foot_module_action'] ?? '';

$coverCondVal = $savedRecord['cosmetic_cover_condition'] ?? 'Good';
$coverActionVal = $savedRecord['cosmetic_cover_action'] ?? '';

$notesVal = $savedRecord['service_notes'] ?? 'Completed full device inspection. Cleaned and checked all fasteners. Pylon adapter retorqued to manufacturer specification. Gait alignment remains satisfactory.';
$isSafeVal = isset($savedRecord['is_safe_for_use']) ? (bool)$savedRecord['is_safe_for_use'] : true;
?>

<!-- Header Flash Alert -->
<?php if (!empty($message)): ?>
    <div class="auth-alert-box alert-success" style="margin-bottom: 20px;">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
            <polyline points="22 4 12 14.01 9 11.01"></polyline>
        </svg>
        <span><?= htmlspecialchars($message) ?></span>
    </div>
<?php endif; ?>

<!-- MAINTENANCE RECORD HEADER (Screenshot 3) -->
<section class="staff-overview-header" aria-label="Maintenance Header">
    <div>
        <h1 class="client-greeting-title">Maintenance record</h1>
        <p class="client-greeting-subtitle">Document service details and component condition.</p>
    </div>
    <div>
        <span class="autosave-status-text">Draft &middot; Auto-saved</span>
    </div>
</section>

<!-- MAIN FORM WITH 2-COLUMN SPLIT (Screenshot 3) -->
<form method="POST" action="maintenance.php" id="maintenanceForm" enctype="multipart/form-data">
    <input type="hidden" name="form_action" id="formActionInput" value="complete">

    <div class="maintenance-form-grid">
        <!-- LEFT COLUMN: FORM DETAILS -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- CARD 1: CLIENT & DEVICE -->
            <div class="staff-card">
                <div class="staff-card-header" style="margin-bottom: 16px;">
                    <h2 class="staff-card-title">Client & device</h2>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                    <div>
                        <label style="font-size: 11px; font-weight: 600; color: var(--color-text-light); display: block; margin-bottom: 6px;">Client</label>
                        <input 
                            type="text" 
                            name="client_name" 
                            id="fieldClientName"
                            class="find-search-input" 
                            value="<?= htmlspecialchars($clientValue) ?>" 
                            placeholder="Client name or ID"
                            oninput="updateServiceSummary()"
                        >
                    </div>
                    <div>
                        <label style="font-size: 11px; font-weight: 600; color: var(--color-text-light); display: block; margin-bottom: 6px;">Device serial</label>
                        <input 
                            type="text" 
                            name="device_serial" 
                            class="find-search-input" 
                            value="<?= htmlspecialchars($serialValue) ?>" 
                            placeholder="e.g. TT-2023-8841"
                        >
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
                    <div>
                        <label style="font-size: 11px; font-weight: 600; color: var(--color-text-light); display: block; margin-bottom: 6px;">Service date</label>
                        <input 
                            type="date" 
                            name="service_date" 
                            class="find-search-input" 
                            value="<?= htmlspecialchars($dateValue) ?>"
                        >
                    </div>
                    <div>
                        <label style="font-size: 11px; font-weight: 600; color: var(--color-text-light); display: block; margin-bottom: 6px;">Service type</label>
                        <select name="service_type" class="condition-select" style="padding: 10px 12px; font-size: 13px;">
                            <option value="Routine maintenance" <?= $typeValue === 'Routine maintenance' ? 'selected' : '' ?>>Routine maintenance</option>
                            <option value="Alignment adjustment" <?= $typeValue === 'Alignment adjustment' ? 'selected' : '' ?>>Alignment adjustment</option>
                            <option value="Socket repair" <?= $typeValue === 'Socket repair' ? 'selected' : '' ?>>Socket repair</option>
                            <option value="Component replacement" <?= $typeValue === 'Component replacement' ? 'selected' : '' ?>>Component replacement</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- CARD 2: COMPONENT INSPECTION -->
            <div class="staff-card">
                <div class="staff-card-header" style="margin-bottom: 14px;">
                    <h2 class="staff-card-title">Component inspection</h2>
                </div>

                <table class="inspection-table">
                    <thead>
                        <tr>
                            <th>COMPONENT</th>
                            <th>CONDITION</th>
                            <th>ACTION TAKEN</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Row 1: Socket & liner -->
                        <tr>
                            <td class="component-name-cell">Socket & liner</td>
                            <td class="condition-select-cell">
                                <select name="socket_liner_condition" class="condition-select">
                                    <option value="Good" <?= $socketCondVal === 'Good' ? 'selected' : '' ?>>Good</option>
                                    <option value="Monitor" <?= $socketCondVal === 'Monitor' ? 'selected' : '' ?>>Monitor</option>
                                    <option value="Needs Replacement" <?= $socketCondVal === 'Needs Replacement' ? 'selected' : '' ?>>Replace</option>
                                </select>
                            </td>
                            <td class="action-input-cell">
                                <input type="text" name="socket_liner_action" class="action-input" placeholder="Add notes..." value="<?= htmlspecialchars($socketActionVal) ?>">
                            </td>
                        </tr>

                        <!-- Row 2: Pylons & adapters -->
                        <tr>
                            <td class="component-name-cell">Pylons & adapters</td>
                            <td class="condition-select-cell">
                                <select name="pylon_adapters_condition" class="condition-select">
                                    <option value="Good" <?= $pylonCondVal === 'Good' ? 'selected' : '' ?>>Good</option>
                                    <option value="Monitor" <?= $pylonCondVal === 'Monitor' ? 'selected' : '' ?>>Monitor</option>
                                    <option value="Needs Replacement" <?= $pylonCondVal === 'Needs Replacement' ? 'selected' : '' ?>>Replace</option>
                                </select>
                            </td>
                            <td class="action-input-cell">
                                <input type="text" name="pylon_adapters_action" class="action-input" placeholder="Add notes..." value="<?= htmlspecialchars($pylonActionVal) ?>">
                            </td>
                        </tr>

                        <!-- Row 3: Foot module -->
                        <tr>
                            <td class="component-name-cell">Foot module</td>
                            <td class="condition-select-cell">
                                <select name="foot_module_condition" class="condition-select">
                                    <option value="Good" <?= $footCondVal === 'Good' ? 'selected' : '' ?>>Good</option>
                                    <option value="Monitor" <?= $footCondVal === 'Monitor' ? 'selected' : '' ?>>Monitor</option>
                                    <option value="Needs Replacement" <?= $footCondVal === 'Needs Replacement' ? 'selected' : '' ?>>Replace</option>
                                </select>
                            </td>
                            <td class="action-input-cell">
                                <input type="text" name="foot_module_action" class="action-input" placeholder="Add notes..." value="<?= htmlspecialchars($footActionVal) ?>">
                            </td>
                        </tr>

                        <!-- Row 4: Cosmetic cover -->
                        <tr>
                            <td class="component-name-cell">Cosmetic cover</td>
                            <td class="condition-select-cell">
                                <select name="cosmetic_cover_condition" class="condition-select">
                                    <option value="Good" <?= $coverCondVal === 'Good' ? 'selected' : '' ?>>Good</option>
                                    <option value="Monitor" <?= $coverCondVal === 'Monitor' ? 'selected' : '' ?>>Monitor</option>
                                    <option value="Needs Replacement" <?= $coverCondVal === 'Needs Replacement' ? 'selected' : '' ?>>Replace</option>
                                </select>
                            </td>
                            <td class="action-input-cell">
                                <input type="text" name="cosmetic_cover_action" class="action-input" placeholder="Add notes..." value="<?= htmlspecialchars($coverActionVal) ?>">
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- CARD 3: SERVICE NOTES & ATTACHMENT -->
            <div class="staff-card">
                <div class="staff-card-header" style="margin-bottom: 14px;">
                    <h2 class="staff-card-title">Service notes</h2>
                </div>

                <textarea 
                    name="service_notes" 
                    class="find-search-input" 
                    rows="4" 
                    style="resize: vertical; line-height: 1.6;"
                    placeholder="Enter detailed clinical observation and alignment notes..."
                ><?= htmlspecialchars($notesVal) ?></textarea>

                <!-- File Attachment Dropzone -->
                <label class="attachment-dropzone" style="display: block;">
                    <input type="file" name="attachment_file" style="display: none;" onchange="document.getElementById('attachNotice').innerText = this.files[0] ? this.files[0].name : '+ Attach documents or photos';">
                    <div class="dropzone-icon-text" id="attachNotice">+ Attach documents or photos</div>
                    <div class="dropzone-subtext">PDF, JPG or PNG &middot; Max 10MB</div>
                </label>
            </div>
        </div>

        <!-- RIGHT COLUMN: SERVICE SUMMARY (Screenshot 3) -->
        <div class="service-summary-card">
            <h2 class="staff-card-title" style="margin-bottom: 4px;">Service summary</h2>

            <div class="summary-meta-list">
                <div class="summary-meta-row">
                    <span class="label">Client</span>
                    <span class="value" id="summaryClientName">Sarah Mitchell</span>
                </div>
                <div class="summary-meta-row">
                    <span class="label">Device</span>
                    <span class="value">Transtibial - Left</span>
                </div>
                <div class="summary-meta-row">
                    <span class="label">Technician</span>
                    <span class="value">Alex Morgan</span>
                </div>
                <div class="summary-meta-row">
                    <span class="label">Next service due</span>
                    <span class="value">14 Nov 2025</span>
                </div>
            </div>

            <label class="safe-checkbox-label">
                <input type="checkbox" name="is_safe_for_use" value="1" <?= $isSafeVal ? 'checked' : '' ?>>
                <span>Device is safe for continued use</span>
            </label>

            <button type="button" class="btn-complete-record" onclick="submitMaintenance('complete')">
                Complete record
            </button>

            <button type="button" class="btn-save-draft" onclick="submitMaintenance('draft')">
                Save as draft
            </button>
        </div>
    </div>
</form>

<script>
function submitMaintenance(actionType) {
    document.getElementById('formActionInput').value = actionType;
    document.getElementById('maintenanceForm').submit();
}

function updateServiceSummary() {
    const raw = document.getElementById('fieldClientName').value;
    const nameOnly = raw.split('-')[0].trim();
    document.getElementById('summaryClientName').innerText = nameOnly || 'Sarah Mitchell';
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
