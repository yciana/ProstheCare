<?php
/**
 * ProstheCare Clinic Management System - Workspace Header Component
 * Strictly External CSS (No inline styles)
 */

require_once __DIR__ . '/../config/supabase.php';
require_once __DIR__ . '/SupabaseClient.php';

// Auth check
if (!isset($_SESSION['user'])) {
    if (isset($_COOKIE['prosthecare_user'])) {
        $cookieData = json_decode($_COOKIE['prosthecare_user'], true);
        if ($cookieData) {
            $_SESSION['user'] = $cookieData;
        } else {
            header('Location: index.php');
            exit;
        }
    } else {
        header('Location: index.php');
        exit;
    }
}

$currentUser = $_SESSION['user'];
$supabase = new SupabaseClient();
$currentScript = basename($_SERVER['PHP_SELF']);
$isClient = in_array(strtolower($currentUser['role'] ?? ''), ['client', 'patient']);
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
                    <span class="sidebar-brand-subtitle"><?= $isClient ? 'PATIENT PORTAL' : 'CLINICAL SUITE' ?></span>
                </div>
            </div>

            <nav class="sidebar-nav">
                <span class="nav-section-title"><?= $isClient ? 'My Care Journey' : 'Clinical Operations' ?></span>
                
                <a href="dashboard.php" class="nav-link-item <?= $currentScript === 'dashboard.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="7"></rect>
                        <rect x="14" y="3" width="7" height="7"></rect>
                        <rect x="14" y="14" width="7" height="7"></rect>
                        <rect x="3" y="14" width="7" height="7"></rect>
                    </svg>
                    <span><?= $isClient ? 'My Care Overview' : 'Dashboard' ?></span>
                </a>

                <?php if (!$isClient): ?>
                    <a href="patients.php" class="nav-link-item <?= $currentScript === 'patients.php' ? 'active' : '' ?>">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                            <circle cx="9" cy="7" r="4"></circle>
                            <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                            <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                        </svg>
                        <span>Patients Directory</span>
                    </a>
                <?php endif; ?>

                <a href="appointments.php" class="nav-link-item <?= $currentScript === 'appointments.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span><?= $isClient ? 'My Appointments' : 'Fitting Appointments' ?></span>
                </a>

                <a href="care-plans.php" class="nav-link-item <?= $currentScript === 'care-plans.php' ? 'active' : '' ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                        <polyline points="14 2 14 8 20 8"></polyline>
                        <line x1="16" y1="13" x2="8" y2="13"></line>
                        <line x1="16" y1="17" x2="8" y2="17"></line>
                        <polyline points="10 9 9 9 8 9"></polyline>
                    </svg>
                    <span><?= $isClient ? 'My Device Specs' : 'Prosthetic Devices' ?></span>
                </a>

                <span class="nav-section-title">Account</span>

                <a href="logout.php" class="nav-link-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                        <polyline points="16 17 21 12 16 7"></polyline>
                        <line x1="21" y1="12" x2="9" y2="12"></line>
                    </svg>
                    <span>Sign Out</span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="user-profile-badge">
                    <div class="user-avatar">
                        <?= strtoupper(substr($currentUser['name'] ?? 'C', 0, 1)) ?>
                    </div>
                    <div class="user-details">
                        <span class="user-name-text"><?= htmlspecialchars($currentUser['name'] ?? 'Client') ?></span>
                        <span class="user-role-text"><?= htmlspecialchars($currentUser['role'] ?? 'Client') ?></span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- MAIN VIEW CONTAINER -->
        <div class="app-main">
            <!-- Top App Bar -->
            <header class="top-navbar">
                <div class="top-nav-left">
                    <h1 class="top-page-title"><?= htmlspecialchars($pageTitle ?? 'Dashboard') ?></h1>
                    <div class="sync-status-indicator sync-live" title="Connected to Supabase PostgreSQL">
                        <span class="sync-dot"></span>
                        <span>Supabase Connected</span>
                    </div>
                </div>

                <div class="top-nav-actions">
                    <?php if (!$isClient): ?>
                        <a href="patients.php?action=new" class="btn-primary-action">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>New Patient Case</span>
                        </a>
                    <?php else: ?>
                        <a href="appointments.php" class="btn-primary-action">
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                            <span>Book Fitting Session</span>
                        </a>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Page Body Content Starts Here -->
            <main class="page-content-wrapper">
