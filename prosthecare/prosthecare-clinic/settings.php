<?php
/**
 * ProstheCare Clinic Management System - Settings Page
 * Matches Figma design (Screenshot 5)
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

<!-- SETTINGS HEADER ROW -->
<div class="client-greeting-row" style="margin-bottom: 24px;">
    <div>
        <h1 class="client-greeting-title">Settings</h1>
        <p class="client-greeting-subtitle">Manage your profile, security, and notifications.</p>
    </div>
</div>

<!-- 2-COLUMN SETTINGS SPLIT -->
<div class="settings-split-grid" style="display: grid; grid-template-columns: 280px 1fr; gap: 24px; align-items: start;">
    <!-- LEFT: SETTINGS SUBNAV -->
    <div style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 8px; box-shadow: var(--shadow-sm);">
        <button type="button" class="settings-nav-item active" data-tab="profile" style="width: 100%; border: none; background: none; text-align: left; cursor: pointer;">
            <div class="settings-item-left">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                    <circle cx="12" cy="7" r="4"></circle>
                </svg>
                <span>Profile</span>
            </div>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-chevron">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>

        <button type="button" class="settings-nav-item" data-tab="security" style="width: 100%; border: none; background: none; text-align: left; cursor: pointer;">
            <div class="settings-item-left">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                </svg>
                <span>Security</span>
            </div>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-chevron">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>

        <button type="button" class="settings-nav-item" data-tab="notifications" style="width: 100%; border: none; background: none; text-align: left; cursor: pointer;">
            <div class="settings-item-left">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <span>Notifications</span>
            </div>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-chevron">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>

        <button type="button" class="settings-nav-item" data-tab="accessibility" style="width: 100%; border: none; background: none; text-align: left; cursor: pointer;">
            <div class="settings-item-left">
                <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <line x1="12" y1="8" x2="12" y2="12"></line>
                    <line x1="12" y1="16" x2="12.01" y2="16"></line>
                </svg>
                <span>Accessibility</span>
            </div>
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-chevron">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </button>
    </div>

    <!-- RIGHT: SETTINGS MAIN CONTENT CARD -->
    <div class="settings-card-content">
        <!-- TAB 1: PROFILE (Image 5) -->
        <div class="settings-tab-pane active" id="tabPaneProfile">
            <h2 class="designer-card-title">Profile</h2>
            <p class="designer-card-subtitle" style="margin-bottom: 24px;">Update your profile preferences.</p>
            <div style="height: 1px; background: var(--color-surface-muted); margin-bottom: 24px;"></div>

            <!-- Avatar change block -->
            <div class="avatar-edit-row">
                <div class="large-avatar-circle" id="profileInitialsCircle">
                    <?= htmlspecialchars($initials) ?>
                </div>
                <div>
                    <input type="file" id="avatarFileInput" accept="image/png, image/jpeg" style="display: none;">
                    <button type="button" class="btn-secondary" id="changePhotoBtn">Change photo</button>
                    <div style="font-size: 11px; color: var(--color-text-light); margin-top: 6px;">
                        JPG or PNG. Maximum 2 MB.
                    </div>
                </div>
            </div>

            <!-- Profile form -->
            <form method="POST" action="settings.php">
                <input type="hidden" name="action" value="save_profile">
                <div class="measurements-2col-grid">
                    <div>
                        <label class="form-field-label" style="font-size: 13px; font-weight: 600; color: var(--color-text-main); margin-bottom: 6px; display: block;">First name</label>
                        <input type="text" name="first_name" class="form-input-control" value="<?= htmlspecialchars($firstNameVal) ?>" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--color-border); font-size: 14px;">
                    </div>
                    <div>
                        <label class="form-field-label" style="font-size: 13px; font-weight: 600; color: var(--color-text-main); margin-bottom: 6px; display: block;">Last name</label>
                        <input type="text" name="last_name" class="form-input-control" value="<?= htmlspecialchars($lastNameVal) ?>" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--color-border); font-size: 14px;">
                    </div>
                    <div>
                        <label class="form-field-label" style="font-size: 13px; font-weight: 600; color: var(--color-text-main); margin-bottom: 6px; display: block;">Email</label>
                        <input type="email" name="email" class="form-input-control" value="<?= htmlspecialchars($emailVal) ?>" required style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--color-border); font-size: 14px;">
                    </div>
                    <div>
                        <label class="form-field-label" style="font-size: 13px; font-weight: 600; color: var(--color-text-main); margin-bottom: 6px; display: block;">Phone</label>
                        <input type="text" name="phone" class="form-input-control" value="<?= htmlspecialchars($phoneVal) ?>" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--color-border); font-size: 14px;">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 32px;">
                    <button type="submit" class="btn-primary-action">Save changes</button>
                </div>
            </form>
        </div>

        <!-- TAB 2: SECURITY -->
        <div class="settings-tab-pane" id="tabPaneSecurity" style="display: none;">
            <h2 class="designer-card-title">Security Settings</h2>
            <p class="designer-card-subtitle" style="margin-bottom: 24px;">Manage account credentials and security preferences.</p>
            <div style="height: 1px; background: var(--color-surface-muted); margin-bottom: 24px;"></div>

            <div style="max-width: 480px;">
                <div style="margin-bottom: 16px;">
                    <label class="form-field-label" style="font-size: 13px; font-weight: 600; color: var(--color-text-main); margin-bottom: 6px; display: block;">Current Password</label>
                    <input type="password" class="form-input-control" placeholder="••••••••" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--color-border); font-size: 14px;">
                </div>
                <div style="margin-bottom: 16px;">
                    <label class="form-field-label" style="font-size: 13px; font-weight: 600; color: var(--color-text-main); margin-bottom: 6px; display: block;">New Password</label>
                    <input type="password" class="form-input-control" placeholder="Min. 8 characters" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--color-border); font-size: 14px;">
                </div>
                <div style="margin-bottom: 24px;">
                    <label class="form-field-label" style="font-size: 13px; font-weight: 600; color: var(--color-text-main); margin-bottom: 6px; display: block;">Confirm New Password</label>
                    <input type="password" class="form-input-control" placeholder="Repeat new password" style="width: 100%; padding: 10px 14px; border-radius: 8px; border: 1px solid var(--color-border); font-size: 14px;">
                </div>
                <button type="button" class="btn-primary-action" id="updatePasswordBtn">Update Password</button>
            </div>
        </div>

        <!-- TAB 3: NOTIFICATIONS -->
        <div class="settings-tab-pane" id="tabPaneNotifications" style="display: none;">
            <h2 class="designer-card-title">Notification Preferences</h2>
            <p class="designer-card-subtitle" style="margin-bottom: 24px;">Choose how and when ProstheCare contacts you.</p>
            <div style="height: 1px; background: var(--color-surface-muted); margin-bottom: 24px;"></div>

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
            <h2 class="designer-card-title">Accessibility</h2>
            <p class="designer-card-subtitle" style="margin-bottom: 24px;">Adjust interface appearance for readability and comfort.</p>
            <div style="height: 1px; background: var(--color-surface-muted); margin-bottom: 24px;"></div>

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
    changePhotoBtn.addEventListener('click', () => fileInput.click());

    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            showToast('Photo uploaded: ' + this.files[0].name);
        }
    });

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
