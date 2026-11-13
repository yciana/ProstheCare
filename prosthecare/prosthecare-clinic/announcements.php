<?php
/**
 * ProstheCare Clinic Management System - Announcements
 * Matches Figma design (Screenshot 4: Announcements)
 */

$pageTitle = 'Announcements';
require_once __DIR__ . '/includes/header.php';

$message = '';
$isError = false;

// Handle Form Submission (Publish or Save Draft)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $actionType = $_POST['announcement_action'] ?? 'publish';
    $title = trim($_POST['title'] ?? '');
    $announcementMsg = trim($_POST['message'] ?? '');
    $category = trim($_POST['category'] ?? 'Clinic update');
    $audience = trim($_POST['audience'] ?? 'Everyone');
    $inApp = isset($_POST['in_app_notification']) ? true : false;
    $emailNotif = isset($_POST['email_notification']) ? true : false;
    $smsNotif = isset($_POST['sms_notification']) ? true : false;
    $isScheduled = isset($_POST['is_scheduled']) ? true : false;
    $status = ($actionType === 'draft') ? 'Draft' : ($isScheduled ? 'Scheduled' : 'Published');

    if (empty($title) || empty($announcementMsg)) {
        $message = 'Please provide both an announcement title and message content.';
        $isError = true;
    } else {
        $announcementData = [
            'title' => $title,
            'message' => $announcementMsg,
            'category' => $category,
            'audience' => $audience,
            'in_app_notification' => $inApp,
            'email_notification' => $emailNotif,
            'sms_notification' => $smsNotif,
            'is_scheduled' => $isScheduled,
            'status' => $status,
            'created_by' => 'ProstheCare Clinic'
        ];

        // Insert into Supabase
        $supabase->from('announcements')->insert($announcementData);

        if (!isset($_SESSION['announcements_list'])) {
            $_SESSION['announcements_list'] = [];
        }
        array_unshift($_SESSION['announcements_list'], $announcementData);

        if ($actionType === 'draft') {
            $message = "Announcement draft \"{$title}\" has been saved successfully.";
        } else {
            $message = "Announcement \"{$title}\" has been published to {$audience}!";
        }
    }
}

// Fetch published announcements for "View published" modal/drawer
$publishedList = $supabase->from('announcements')->select('*')->order('created_at', false)->get();
if (empty($publishedList)) {
    $publishedList = [
        ['title' => 'Holiday hours this month', 'message' => "We'll be closed on May 26 for the public holiday.", 'category' => 'Clinic update', 'audience' => 'Everyone', 'status' => 'Published', 'created_at' => date('Y-m-d H:i:s', strtotime('-2 days'))],
        ['title' => 'Summer prosthetic care', 'message' => 'Simple ways to protect your liner in warmer weather.', 'category' => 'Care tip', 'audience' => 'Clients only', 'status' => 'Published', 'created_at' => date('Y-m-d H:i:s', strtotime('-5 days'))]
    ];
}
?>

<!-- Header Flash Alert -->
<?php if (!empty($message)): ?>
    <div class="auth-alert-box <?= $isError ? 'alert-error' : 'alert-success' ?>" style="margin-bottom: 20px;">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <?php if ($isError): ?>
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            <?php else: ?>
                <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                <polyline points="22 4 12 14.01 9 11.01"></polyline>
            <?php endif; ?>
        </svg>
        <span><?= htmlspecialchars($message) ?></span>
    </div>
<?php endif; ?>

<!-- ANNOUNCEMENTS HEADER ROW (Screenshot 4) -->
<section class="staff-overview-header" aria-label="Announcements Header">
    <div>
        <h1 class="client-greeting-title">Announcements</h1>
        <p class="client-greeting-subtitle">Create and schedule clinic-wide updates.</p>
    </div>
    <div>
        <button type="button" class="btn-approval-reject" onclick="document.getElementById('publishedModal').style.display='flex';">
            View published
        </button>
    </div>
</section>

<!-- MAIN 2-COLUMN GRID (Screenshot 4) -->
<form method="POST" action="announcements.php" id="announcementForm">
    <input type="hidden" name="announcement_action" id="announcementAction" value="publish">

    <div class="announcements-grid">
        <!-- LEFT COLUMN: COMPOSE ANNOUNCEMENT -->
        <div class="staff-card">
            <div class="staff-card-header" style="margin-bottom: 16px;">
                <div>
                    <h2 class="staff-card-title">Compose announcement</h2>
                    <p class="staff-card-subtitle">Share an update with clients and clinic staff.</p>
                </div>
            </div>

            <!-- Title Field -->
            <div style="margin-bottom: 16px;">
                <label style="font-size: 11px; font-weight: 600; color: var(--color-text-light); display: block; margin-bottom: 6px;">Title</label>
                <input 
                    type="text" 
                    name="title" 
                    id="inputTitle"
                    class="find-search-input" 
                    placeholder="Enter a clear, concise title" 
                    oninput="updateLivePreview()"
                    required
                >
            </div>

            <!-- Message Field with Formatting Toolbar -->
            <div style="margin-bottom: 16px;">
                <label style="font-size: 11px; font-weight: 600; color: var(--color-text-light); display: block; margin-bottom: 6px;">Message</label>
                <div class="formatting-bar">
                    <button type="button" style="background: none; border: none; cursor: pointer; padding: 4px; color: var(--color-text-muted); font-weight: 700;">B</button>
                    <button type="button" style="background: none; border: none; cursor: pointer; padding: 4px; color: var(--color-text-muted); font-style: italic;">I</button>
                    <button type="button" style="background: none; border: none; cursor: pointer; padding: 4px; color: var(--color-text-muted); text-decoration: underline;">U</button>
                    <button type="button" style="background: none; border: none; cursor: pointer; padding: 4px; color: var(--color-text-muted); font-size: 13px;">&bull; List</button>
                </div>
                <textarea 
                    name="message" 
                    id="inputMessage"
                    class="find-search-input" 
                    rows="6" 
                    style="border-top-left-radius: 0; border-top-right-radius: 0; resize: vertical; line-height: 1.6;" 
                    placeholder="Write your announcement..."
                    oninput="updateLivePreview()"
                    required
                ></textarea>
            </div>

            <!-- Category and Audience Selectors -->
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div>
                    <label style="font-size: 11px; font-weight: 600; color: var(--color-text-light); display: block; margin-bottom: 6px;">Category</label>
                    <select name="category" id="selectCategory" class="condition-select" style="padding: 10px 12px; font-size: 13px;" onchange="updateLivePreview()">
                        <option value="Clinic update">Clinic update</option>
                        <option value="Care tip">Care tip</option>
                        <option value="Urgent alert">Urgent alert</option>
                        <option value="General news">General news</option>
                    </select>
                </div>
                <div>
                    <label style="font-size: 11px; font-weight: 600; color: var(--color-text-light); display: block; margin-bottom: 6px;">Audience</label>
                    <select name="audience" id="selectAudience" class="condition-select" style="padding: 10px 12px; font-size: 13px;" onchange="syncAudiencePills(this.value)">
                        <option value="Everyone">Everyone</option>
                        <option value="Clients only">Clients only</option>
                        <option value="Staff only">Staff only</option>
                    </select>
                </div>
            </div>

            <!-- Audience Pills (Matching Figma Design) -->
            <div style="display: flex; gap: 8px; margin-bottom: 20px;">
                <button type="button" class="audience-pill active" id="pillEveryone" onclick="selectAudiencePill('Everyone')" style="padding: 5px 14px; border-radius: 9999px; border: 1px solid #0b7479; background: #e6f6f6; color: #075053; font-size: 12px; font-weight: 600; cursor: pointer;">Everyone</button>
                <button type="button" class="audience-pill" id="pillClients" onclick="selectAudiencePill('Clients only')" style="padding: 5px 14px; border-radius: 9999px; border: 1px solid #e2e8f0; background: #ffffff; color: var(--color-text-muted); font-size: 12px; font-weight: 600; cursor: pointer;">Clients only</button>
                <button type="button" class="audience-pill" id="pillStaff" onclick="selectAudiencePill('Staff only')" style="padding: 5px 14px; border-radius: 9999px; border: 1px solid #e2e8f0; background: #ffffff; color: var(--color-text-muted); font-size: 12px; font-weight: 600; cursor: pointer;">Staff only</button>
            </div>

            <!-- Schedule for later Checkbox -->
            <div style="background-color: #f8fafc; border: 1px solid #f1f5f9; border-radius: var(--radius-md); padding: 14px 16px; margin-bottom: 24px;">
                <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                    <input type="checkbox" name="is_scheduled" value="1" style="margin-top: 2px; accent-color: var(--color-primary);">
                    <div>
                        <div style="font-size: 13px; font-weight: 600; color: var(--color-text-main);">Schedule for later</div>
                        <div style="font-size: 12px; color: var(--color-text-light);">Publish automatically at a chosen date and time.</div>
                    </div>
                </label>
            </div>

            <!-- Action Buttons -->
            <div style="display: flex; justify-content: flex-end; align-items: center; gap: 12px;">
                <button type="button" class="btn-approval-reject" onclick="submitAnnouncement('draft')">
                    Save draft
                </button>
                <button type="button" class="btn-approval-approve" style="display: flex; align-items: center; gap: 8px;" onclick="submitAnnouncement('publish')">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <span>Publish announcement</span>
                </button>
            </div>
        </div>

        <!-- RIGHT COLUMN: LIVE PREVIEW & DELIVERY OPTIONS -->
        <div style="display: flex; flex-direction: column; gap: 20px;">
            <!-- CARD 1: LIVE PREVIEW -->
            <div class="live-preview-card">
                <div class="preview-eyebrow">LIVE PREVIEW</div>
                <div class="preview-megaphone-icon">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                </div>
                <span class="preview-pill" id="livePreviewCategory">Clinic update</span>
                <h3 class="preview-announcement-title" id="livePreviewTitle">Your announcement title</h3>
                <p class="preview-announcement-message" id="livePreviewMessage">Your message will appear here as you type. Keep it clear and helpful for your audience.</p>
                <div class="preview-footer-timestamp">Just now &middot; ProstheCare Clinic</div>
            </div>

            <!-- CARD 2: DELIVERY OPTIONS -->
            <div class="delivery-options-card">
                <h3 class="delivery-options-title">Delivery options</h3>

                <div class="delivery-toggle-item">
                    <div>
                        <div class="toggle-label-title">In-app notification</div>
                        <div class="toggle-label-subtext">Shown in the notification center</div>
                    </div>
                    <label class="toggle-switch-control">
                        <input type="checkbox" name="in_app_notification" value="1" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div class="delivery-toggle-item">
                    <div>
                        <div class="toggle-label-title">Email notification</div>
                        <div class="toggle-label-subtext">Send a copy by email</div>
                    </div>
                    <label class="toggle-switch-control">
                        <input type="checkbox" name="email_notification" value="1" checked>
                        <span class="toggle-slider"></span>
                    </label>
                </div>

                <div class="delivery-toggle-item">
                    <div>
                        <div class="toggle-label-title">SMS notification</div>
                        <div class="toggle-label-subtext">For urgent updates only</div>
                    </div>
                    <label class="toggle-switch-control">
                        <input type="checkbox" name="sms_notification" value="1">
                        <span class="toggle-slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- MODAL: VIEW PUBLISHED ANNOUNCEMENTS -->
<div id="publishedModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 12px; width: 100%; max-width: 560px; max-height: 80vh; overflow-y: auto; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-size: 17px; font-weight: 700; color: var(--color-text-main);">Published Announcements</h3>
            <button type="button" onclick="document.getElementById('publishedModal').style.display='none';" style="background: none; border: none; font-size: 20px; cursor: pointer; color: var(--color-text-muted);">&times;</button>
        </div>
        <div style="display: flex; flex-direction: column; gap: 14px;">
            <?php foreach ($publishedList as $pub): ?>
                <div style="padding: 16px; border: 1px solid #e2e8f0; border-radius: 8px; background: #fafafa;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <span style="font-size: 11px; font-weight: 700; color: #0b7479; background: #e6f6f6; padding: 2px 8px; border-radius: 9999px;"><?= htmlspecialchars($pub['category'] ?? 'Update') ?></span>
                        <span style="font-size: 11px; color: var(--color-text-light);"><?= htmlspecialchars($pub['audience'] ?? 'Everyone') ?></span>
                    </div>
                    <h4 style="font-size: 14px; font-weight: 700; color: var(--color-text-main); margin-bottom: 4px;"><?= htmlspecialchars($pub['title'] ?? '') ?></h4>
                    <p style="font-size: 13px; color: var(--color-text-muted); line-height: 1.5;"><?= htmlspecialchars($pub['message'] ?? '') ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
function updateLivePreview() {
    const titleVal = document.getElementById('inputTitle').value.trim();
    const msgVal = document.getElementById('inputMessage').value.trim();
    const catVal = document.getElementById('selectCategory').value;

    document.getElementById('livePreviewTitle').innerText = titleVal || 'Your announcement title';
    document.getElementById('livePreviewMessage').innerText = msgVal || 'Your message will appear here as you type. Keep it clear and helpful for your audience.';
    document.getElementById('livePreviewCategory').innerText = catVal;
}

function selectAudiencePill(aud) {
    document.getElementById('selectAudience').value = aud;
    syncAudiencePills(aud);
}

function syncAudiencePills(aud) {
    const pills = {
        'Everyone': document.getElementById('pillEveryone'),
        'Clients only': document.getElementById('pillClients'),
        'Staff only': document.getElementById('pillStaff')
    };
    for (const [key, el] of Object.entries(pills)) {
        if (key === aud) {
            el.style.borderColor = '#0b7479';
            el.style.backgroundColor = '#e6f6f6';
            el.style.color = '#075053';
        } else {
            el.style.borderColor = '#e2e8f0';
            el.style.backgroundColor = '#ffffff';
            el.style.color = 'var(--color-text-muted)';
        }
    }
}

function submitAnnouncement(action) {
    document.getElementById('announcementAction').value = action;
    document.getElementById('announcementForm').submit();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
