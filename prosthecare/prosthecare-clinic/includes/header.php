<?php
/**
 * ProstheCare Clinic Management System - Workspace Header Component
 * Strictly External CSS (No inline styles)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/supabase.php';
require_once __DIR__ . '/SupabaseClient.php';

// Auth check - if session is missing, check cookie or provide default demo staff session
if (!isset($_SESSION['user'])) {
    if (isset($_COOKIE['prosthecare_user'])) {
        $cookieData = json_decode($_COOKIE['prosthecare_user'], true);
        if ($cookieData) {
            $_SESSION['user'] = $cookieData;
        } else {
            $_SESSION['user'] = [
                'id' => 'SM-STAFF-1048',
                'name' => 'Sarah Mitchell',
                'email' => 'sarah.m@example.com',
                'role' => 'staff'
            ];
        }
    } else {
        // Default staff practitioner account
        $_SESSION['user'] = [
            'id' => 'SM-STAFF-1048',
            'name' => 'Sarah Mitchell',
            'email' => 'sarah.m@example.com',
            'role' => 'staff'
        ];
    }
}

$currentUser = $_SESSION['user'];
$supabase = new SupabaseClient();
$currentScript = basename($_SERVER['PHP_SELF']);

// Determine active role from authenticated user (defaults to staff)
$userRole = strtolower($currentUser['role'] ?? 'staff');
if ($userRole === 'patient') {
    $userRole = 'client';
}
if (in_array($userRole, ['prosthetist', 'technician', 'doctor', 'staff', 'admin'])) {
    $userRole = 'staff';
}

$isClient = ($userRole === 'client');
$isStaff = ($userRole === 'staff');
$isAdmin = ($userRole === 'admin');

// Derive display name, first name and initials
$displayName = $currentUser['name'] ?? 'Sarah Mitchell';
$nameParts = preg_split('/\s+/', trim($displayName));
$initials = '';
if (!empty($nameParts[0])) {
    $initials .= strtoupper(substr($nameParts[0], 0, 1));
}
if (count($nameParts) > 1) {
    $initials .= strtoupper(substr(end($nameParts), 0, 1));
} else {
    $initials .= 'M';
}
$firstName = !empty($nameParts[0]) ? $nameParts[0] : 'Sarah';
$accountLabel = $isClient ? 'Client account' : 'Staff account';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Workspace') ?> - ProstheCare Management System</title>
    <link rel="stylesheet" href="css/variables.css">
    <link rel="stylesheet" href="css/dashboard.css">
</head>
<body>
    <div class="app-layout">
        <!-- SIDEBAR NAVIGATION -->
        <aside class="app-sidebar" aria-label="Sidebar Navigation">
            <div class="sidebar-header">
                <div class="sidebar-brand-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                </div>
                <div>
                    <h2 class="sidebar-brand-title">ProstheCare</h2>
                    <span class="sidebar-brand-subtitle">MANAGEMENT SYSTEM</span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <span class="nav-section-title">WORKSPACE</span>
                
                <a href="dashboard.php" class="nav-link-item <?= $currentScript === 'dashboard.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <span>Dashboard</span>
                    <?php if ($currentScript === 'dashboard.php'): ?>
                        <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    <?php endif; ?>
                </a>

                <?php if ($isClient): ?>
                    <a href="appointments.php" class="nav-link-item <?= $currentScript === 'appointments.php' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                        <span>Book appointment</span>
                    </a>

                    <a href="notifications.php" class="nav-link-item <?= $currentScript === 'notifications.php' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span>Notifications</span>
                    </a>

                    <a href="requests.php" class="nav-link-item <?= $currentScript === 'requests.php' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                            <polyline points="10 9 9 9 8 9"></polyline>
                        </svg>
                        <span>My requests</span>
                    </a>

                    <a href="designer.php" class="nav-link-item <?= $currentScript === 'designer.php' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 19l7-7 3 3-7 7-3-3z"></path>
                            <path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"></path>
                            <path d="M2 2l7.586 7.586"></path>
                            <circle cx="11" cy="11" r="2"></circle>
                        </svg>
                        <span>Leg designer</span>
                    </a>
                <?php else: ?>
                    <!-- STAFF WORKSPACE NAVIGATION (Screenshots 1 - 5) -->
                    <a href="patients.php" class="nav-link-item <?= in_array($currentScript, ['patients.php', 'client-record.php']) ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Client records</span>
                        <?php if (in_array($currentScript, ['patients.php', 'client-record.php'])): ?>
                            <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        <?php endif; ?>
                    </a>

                    <a href="maintenance.php" class="nav-link-item <?= $currentScript === 'maintenance.php' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                        </svg>
                        <span>Maintenance</span>
                        <?php if ($currentScript === 'maintenance.php'): ?>
                            <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        <?php endif; ?>
                    </a>

                    <a href="announcements.php" class="nav-link-item <?= $currentScript === 'announcements.php' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span>Announcements</span>
                        <?php if ($currentScript === 'announcements.php'): ?>
                            <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <a href="settings.php" class="nav-link-item <?= $currentScript === 'settings.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"></path>
                    </svg>
                    <span>Settings</span>
                    <?php if ($currentScript === 'settings.php'): ?>
                        <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    <?php endif; ?>
                </a>
            </nav>

            <!-- SIDEBAR FOOTER (Matching Screenshots 1-5 without preview switcher) -->
            <div class="sidebar-footer">
                <a href="settings.php" class="user-profile-badge">
                    <div class="user-avatar">
                        <?= htmlspecialchars($initials) ?>
                    </div>
                    <div class="user-details">
                        <span class="user-name-text"><?= htmlspecialchars($displayName) ?></span>
                        <span class="user-role-text"><?= htmlspecialchars($accountLabel) ?></span>
                    </div>
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-chevron">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </a>
                <a href="logout.php" class="sidebar-signout-btn" title="Sign out" style="display: flex; align-items: center; gap: 8px; margin-top: 10px; padding: 6px 8px; color: var(--color-text-muted); font-size: 13px; font-weight: 500; text-decoration: none;">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Sign out</span>
                </a>
            </div>
        </aside>

        <!-- MAIN VIEW CONTAINER -->
        <div class="app-main">
            <!-- Top App Bar (Search box, Kbd shortcut, Bell with unread dot, User Avatar) -->
            <header class="top-navbar">
                <form action="patients.php" method="GET" class="header-search-box">
                    <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"></circle>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                    </svg>
                    <input type="text" name="search" class="header-search-input" placeholder="Search clients, records, requests..." aria-label="Search" id="globalHeaderSearch">
                    <kbd class="kbd-shortcut-badge">&#8984; K</kbd>
                </form>

                <div class="header-user-actions">
                    <a href="announcements.php" class="header-bell-btn" title="Notifications" aria-label="Notifications">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                        </svg>
                        <span class="bell-unread-dot" id="headerUnreadDot"></span>
                    </a>
                    <a href="settings.php" class="header-avatar-circle" title="Account Settings">
                        <?= htmlspecialchars($initials) ?>
                    </a>
                </div>
            </header>

            <!-- Page Body Content Starts Here -->
            <main class="page-content-wrapper">
