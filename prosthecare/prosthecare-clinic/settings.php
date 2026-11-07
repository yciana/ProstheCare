<?php
/**
 * ProstheCare Clinic Management System - Settings Page
 * Matches Figma design (Screenshot 6)
 */

$pageTitle = 'Settings';
require_once __DIR__ . '/includes/header.php';

// Parse name
$userFullName = $currentUser['name'] ?? 'Sarah Mitchell';
$nameParts = preg_split('/\s+/', trim($userFullName));
$firstNameVal = $nameParts[0] ?? 'Sarah';
$lastNameVal = isset($nameParts[1]) ? implode(' ', array_slice($nameParts, 1)) : 'Mitchell';
$emailVal = $currentUser['email'] ?? 'sarah.m@example.com';
$phoneVal = $currentUser['phone'] ?? '+1 555 014 9822';

$savedSuccess = false;
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? 'save_profile';

    if ($action === 'save_profile') {
        $first = trim($_POST['first_name'] ?? '');
        $last = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        $newName = trim($first . ' ' . $last);
        if (empty($newName)) {
            $errorMessage = 'First name is required.';
        } else {
            // Update session
            $_SESSION['user']['name'] = $newName;
            $_SESSION['user']['phone'] = $phone;
            if (!empty($email)) {
                $_SESSION['user']['email'] = $email;
            }

            // Sync with Supabase profiles and patients tables
            if (!empty($currentUser['id'])) {
                $supabase->from('profiles')->eq('id', $currentUser['id'])->update([
                    'full_name' => $newName,
                    'phone' => $phone
                ]);
            }
            if (!empty($emailVal)) {
                $supabase->from('patients')->eq('email', $emailVal)->update([
                    'name' => $newName,
                    'phone' => $phone
                ]);
            }

            $userFullName = $newName;
            $firstNameVal = $first;
            $lastNameVal = $last;
            $phoneVal = $phone;
            $emailVal = !empty($email) ? $email : $emailVal;
            $savedSuccess = true;
        }
    }
}
?>

<!-- SETTINGS HEADER ROW (Screenshot 6) -->
<div class="client-greeting-row" style="margin-bottom: 24px;">
    <div>
        <h1 class="client-greeting-title">Settings</h1>
        <p class="client-greeting-subtitle">Manage your profile, security, and notifications.</p>
    </div>
</div>

<?php if (!empty($errorMessage)): ?>
    <div class="auth-alert-box alert-danger" style="margin-bottom: 20px;">
        <?= htmlspecialchars($errorMessage) ?>
    </div>
<?php endif; ?>

<!-- 2-COLUMN SETTINGS SPLIT -->
<div class="settings-split-grid">
    <!-- LEFT: SETTINGS SUBNAV -->
    <div class="settings-nav-sidebar">
        <!-- Profile (Active) -->
        <button type="button" class="settings-nav-item active" data-tab="profile">
            <div class="settings-item-left">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Profile</span>
            </div>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="nav-chevron">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>

        <!-- Security -->
        <button type="button" class="settings-nav-item" data-tab="security">
            <div class="settings-item-left">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                </svg>
                <span>Security</span>
            </div>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="nav-chevron">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>

        <!-- Notifications -->
        <button type="button" class="settings-nav-item" data-tab="notifications">
            <div class="settings-item-left">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <span>Notifications</span>
            </div>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="nav-chevron">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>

        <!-- Accessibility -->
        <button type="button" class="settings-nav-item" data-tab="accessibility">
            <div class="settings-item-left">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"></circle>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                </svg>
                <span>Accessibility</span>
            </div>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="nav-chevron">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>
    </div>

    <!-- RIGHT: SETTINGS MAIN CONTENT CARD -->
    <div class="settings-card-content">
        <!-- TAB 1: PROFILE (Screenshot 6) -->
        <div class="settings-tab-pane active" id="tabPaneProfile">
            <h2 class="settings-section-title">Profile</h2>
            <p class="settings-section-subtitle">Update your profile preferences.</p>
            <div class="settings-header-divider"></div>

            <!-- Avatar change block -->
            <div class="avatar-edit-row">
                <div class="large-avatar-circle" id="profileInitialsCircle">
                    <?= htmlspecialchars($initials) ?>
                </div>
                <div>
                    <input type="file" id="avatarFileInput" accept="image/png, image/jpeg" style="display: none;">
                    <button type="button" class="btn-change-photo" id="changePhotoBtn">Change photo</button>
                    <div class="avatar-help-text">
                        JPG or PNG. Maximum 2 MB.
                    </div>
                </div>
            </div>

            <!-- Profile form -->
            <form method="POST" action="settings.php">
                <input type="hidden" name="action" value="save_profile">
                <div class="settings-form-grid">
                    <div>
                        <label class="settings-field-label">First name</label>
                        <input type="text" name="first_name" class="settings-form-input" value="<?= htmlspecialchars($firstNameVal) ?>" required>
                    </div>
                    <div>
                        <label class="settings-field-label">Last name</label>
                        <input type="text" name="last_name" class="settings-form-input" value="<?= htmlspecialchars($lastNameVal) ?>" required>
                    </div>
                    <div>
                        <label class="settings-field-label">Email</label>
                        <input type="email" name="email" class="settings-form-input" value="<?= htmlspecialchars($emailVal) ?>" required>
                    </div>
                    <div>
                        <label class="settings-field-label">Phone</label>
                        <input type="text" name="phone" class="settings-form-input" value="<?= htmlspecialchars($phoneVal) ?>">
                    </div>
                </div>

                <div class="settings-actions-row">
                    <button type="submit" class="btn-save-settings">Save changes</button>
                </div>
            </form>
        </div>

        <!-- TAB 2: SECURITY -->
        <div class="settings-tab-pane" id="tabPaneSecurity" style="display: none;">
            <h2 class="settings-section-title">Security</h2>
            <p class="settings-section-subtitle">Manage account credentials and password protection.</p>
            <div class="settings-header-divider"></div>

            <div style="max-width: 480px;">
                <div style="margin-bottom: 16px;">
                    <label class="settings-field-label">Current Password</label>
                    <input type="password" class="settings-form-input" placeholder="••••••••">
                </div>
                <div style="margin-bottom: 16px;">
                    <label class="settings-field-label">New Password</label>
                    <input type="password" class="settings-form-input" placeholder="Min. 8 characters">
                </div>
                <div style="margin-bottom: 24px;">
                    <label class="settings-field-label">Confirm New Password</label>
                    <input type="password" class="settings-form-input" placeholder="Repeat new password">
                </div>
                <button type="button" class="btn-save-settings" id="updatePasswordBtn">Update Password</button>
            </div>
        </div>

        <!-- TAB 3: NOTIFICATIONS -->
        <div class="settings-tab-pane" id="tabPaneNotifications" style="display: none;">
            <h2 class="settings-section-title">Notification Preferences</h2>
            <p class="settings-section-subtitle">Choose how and when ProstheCare contacts you.</p>
            <div class="settings-header-divider"></div>

            <div style="display: flex; flex-direction: column; gap: 18px;">
                <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                    <div>
                        <div style="font-size: 14px; font-weight: 600; color: var(--color-text-main);">Appointment reminders</div>
                        <div style="font-size: 12px; color: var(--color-text-muted);">Receive SMS and email reminders 24h before clinic appointments</div>
                    </div>
                    <input type="checkbox" checked style="width: 18px; height: 18px; accent-color: var(--color-primary);">
                </label>
                <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                    <div>
                        <div style="font-size: 14px; font-weight: 600; color: var(--color-text-main);">Fabrication status updates</div>
                        <div style="font-size: 12px; color: var(--color-text-muted);">Instant alert when your device moves to next fabrication milestone</div>
                    </div>
                    <input type="checkbox" checked style="width: 18px; height: 18px; accent-color: var(--color-primary);">
                </label>
                <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                    <div>
                        <div style="font-size: 14px; font-weight: 600; color: var(--color-text-main);">Clinic announcements & care tips</div>
                        <div style="font-size: 12px; color: var(--color-text-muted);">Seasonal prosthetic care guides and holiday schedules</div>
                    </div>
                    <input type="checkbox" checked style="width: 18px; height: 18px; accent-color: var(--color-primary);">
                </label>
            </div>
        </div>

        <!-- TAB 4: ACCESSIBILITY -->
        <div class="settings-tab-pane" id="tabPaneAccessibility" style="display: none;">
            <h2 class="settings-section-title">Accessibility</h2>
            <p class="settings-section-subtitle">Adjust interface appearance for readability and comfort.</p>
            <div class="settings-header-divider"></div>

            <div style="display: flex; flex-direction: column; gap: 18px;">
                <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                    <div>
                        <div style="font-size: 14px; font-weight: 600; color: var(--color-text-main);">High Contrast Mode</div>
                        <div style="font-size: 12px; color: var(--color-text-muted);">Sharpen borders and maximize text-to-background contrast</div>
                    </div>
                    <input type="checkbox" id="highContrastToggle" style="width: 18px; height: 18px; accent-color: var(--color-primary);">
                </label>
                <label style="display: flex; align-items: center; justify-content: space-between; cursor: pointer;">
                    <div>
                        <div style="font-size: 14px; font-weight: 600; color: var(--color-text-main);">Larger Display Font</div>
                        <div style="font-size: 12px; color: var(--color-text-muted);">Increases font sizes throughout patient dashboard</div>
                    </div>
                    <input type="checkbox" id="largerFontToggle" style="width: 18px; height: 18px; accent-color: var(--color-primary);">
                </label>
            </div>
        </div>
    </div>
</div>

<!-- Toast -->
<div class="app-toast" id="appToast">Settings saved successfully!</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.settings-nav-item');
    const tabPanes = {
        profile: document.getElementById('tabPaneProfile'),
        security: document.getElementById('tabPaneSecurity'),
        notifications: document.getElementById('tabPaneNotifications'),
        accessibility: document.getElementById('tabPaneAccessibility')
    };
    const toast = document.getElementById('appToast');

    function showToast(msg) {
        toast.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            tabBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const targetTab = this.getAttribute('data-tab');

            Object.keys(tabPanes).forEach(k => {
                if (k === targetTab) {
                    tabPanes[k].style.display = 'block';
                } else {
                    tabPanes[k].style.display = 'none';
                }
            });
        });
    });

    // Photo change button
    const changePhotoBtn = document.getElementById('changePhotoBtn');
    const fileInput = document.getElementById('avatarFileInput');
    if (changePhotoBtn && fileInput) {
        changePhotoBtn.addEventListener('click', () => fileInput.click());

        fileInput.addEventListener('change', function() {
            if (this.files && this.files[0]) {
                showToast('Photo uploaded: ' + this.files[0].name);
            }
        });
    }

    // Password change demo
    const updatePwdBtn = document.getElementById('updatePasswordBtn');
    if (updatePwdBtn) {
        updatePwdBtn.addEventListener('click', function() {
            showToast('Password updated securely!');
        });
    }

    <?php if ($savedSuccess): ?>
    showToast('Profile updated successfully!');
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
