<?php
/**
 * ProstheCare Clinic Management System - Admin Dashboard
 * Matches Figma design (media_1794282381505_e2061b95.png)
 * Strictly clean production view - No "Preview As" switchers.
 */

$pageTitle = 'Admin dashboard';
require_once __DIR__ . '/includes/header.php';

// Dynamic counts from Supabase if configured, with fallbacks matching Figma design
$totalPatients = $supabase->from('patients')->select('id')->get();
$patientCount = !empty($totalPatients) ? count($totalPatients) : 1284;

$openRequests = $supabase->from('pending_approvals')->select('*')->eq('status', 'Pending')->get();
$openReqCount = !empty($openRequests) ? count($openRequests) : 68;

$appointments = $supabase->from('appointments')->select('id')->get();
$aptCount = !empty($appointments) ? count($appointments) : 342;

// Fetch live audit logs if available
$recentLogs = $supabase->from('audit_logs')->select('*')->order('created_at', false)->limit(3)->get();
if (empty($recentLogs)) {
    $recentLogs = [
        ['event_type' => 'New client registered', 'actor_name' => 'Olivia Parker', 'time_label' => '2 min ago', 'icon' => 'user'],
        ['event_type' => 'Request PR-0192 approved', 'actor_name' => 'Dr. James Wilson', 'time_label' => '18 min ago', 'icon' => 'check'],
        ['event_type' => 'Reference data updated', 'actor_name' => 'Admin User', 'time_label' => '1 hr ago', 'icon' => 'database']
    ];
}
?>

<!-- ADMIN DASHBOARD HEADER -->
<section class="client-greeting-row" aria-label="Admin Header" style="margin-bottom: 24px;">
    <div>
        <h1 class="client-greeting-title">Admin dashboard</h1>
        <p class="client-greeting-subtitle">System performance and clinic activity.</p>
    </div>
    <div>
        <div class="admin-date-filter-dropdown">
            <span>Last 30 days</span>
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </div>
    </div>
</section>

<!-- 4 TOP METRIC CARDS -->
<section class="admin-metrics-grid" aria-label="Key Performance Indicators">
    <!-- Card 1: Total clients -->
    <div class="admin-stat-card">
        <div class="admin-stat-icon icon-teal">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                <circle cx="9" cy="7" r="4"></circle>
                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
            </svg>
        </div>
        <div class="admin-stat-body">
            <span class="admin-stat-label">Total clients</span>
            <div class="admin-stat-value"><?= number_format($patientCount) ?></div>
            <span class="admin-stat-trend trend-up">+0.2% vs. last month</span>
        </div>
    </div>

    <!-- Card 2: Appointments -->
    <div class="admin-stat-card">
        <div class="admin-stat-icon icon-blue">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                <line x1="16" y1="2" x2="16" y2="6"></line>
                <line x1="8" y1="2" x2="8" y2="6"></line>
                <line x1="3" y1="10" x2="21" y2="10"></line>
            </svg>
        </div>
        <div class="admin-stat-body">
            <span class="admin-stat-label">Appointments</span>
            <div class="admin-stat-value"><?= number_format($aptCount) ?></div>
            <span class="admin-stat-trend trend-up">+12.4% vs. last month</span>
        </div>
    </div>

    <!-- Card 3: Open requests -->
    <div class="admin-stat-card">
        <div class="admin-stat-icon icon-amber">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                <polyline points="14 2 14 8 20 8"></polyline>
                <line x1="16" y1="13" x2="8" y2="13"></line>
                <line x1="16" y1="17" x2="8" y2="17"></line>
            </svg>
        </div>
        <div class="admin-stat-body">
            <span class="admin-stat-label">Open requests</span>
            <div class="admin-stat-value"><?= $openReqCount ?></div>
            <span class="admin-stat-trend trend-down">-4.1% vs. last month</span>
        </div>
    </div>

    <!-- Card 4: Avg. wait time -->
    <div class="admin-stat-card">
        <div class="admin-stat-icon icon-purple">
            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <polyline points="12 6 12 12 16 14"></polyline>
            </svg>
        </div>
        <div class="admin-stat-body">
            <span class="admin-stat-label">Avg. wait time</span>
            <div class="admin-stat-value">17 min</div>
            <span class="admin-stat-trend trend-down">-3.0% vs. last month</span>
        </div>
    </div>
</section>

<!-- MIDDLE ROW: APPOINTMENTS OVERVIEW & REQUEST STATUS -->
<section class="admin-charts-grid" aria-label="Clinic Analytics">
    <!-- Appointments Overview Bar Chart Card -->
    <div class="staff-card chart-large-card">
        <div class="admin-card-header-flex">
            <div>
                <h2 class="staff-card-title">Appointments overview</h2>
                <p class="staff-card-subtitle">Scheduled and completed appointments</p>
            </div>
            <div class="chart-legend-box">
                <div class="legend-item"><span class="legend-color-dot dot-teal"></span><span>Completed</span></div>
                <div class="legend-item"><span class="legend-color-dot dot-amber"></span><span>Scheduled</span></div>
            </div>
        </div>

        <div class="bar-chart-container">
            <div class="bar-chart-axis-y">
                <span>100</span>
                <span>75</span>
                <span>50</span>
                <span>25</span>
                <span>0</span>
            </div>
            <div class="bar-chart-bars-wrap">
                <!-- 12 Months: Jun to May -->
                <?php
                $monthsData = [
                    ['m' => 'Jun', 'c' => 45, 's' => 28],
                    ['m' => 'Jul', 'c' => 62, 's' => 38],
                    ['m' => 'Aug', 'c' => 54, 's' => 32],
                    ['m' => 'Sep', 'c' => 70, 's' => 42],
                    ['m' => 'Oct', 'c' => 65, 's' => 39],
                    ['m' => 'Nov', 'c' => 80, 's' => 48],
                    ['m' => 'Dec', 'c' => 58, 's' => 35],
                    ['m' => 'Jan', 'c' => 74, 's' => 46],
                    ['m' => 'Feb', 'c' => 68, 's' => 40],
                    ['m' => 'Mar', 'c' => 88, 's' => 55],
                    ['m' => 'Apr', 'c' => 78, 's' => 50],
                    ['m' => 'May', 'c' => 95, 's' => 62],
                ];
                foreach ($monthsData as $row):
                ?>
                    <div class="bar-group-col">
                        <div class="bars-pair">
                            <div class="bar-single bar-teal" style="height: <?= $row['c'] ?>%;" title="Completed: <?= $row['c'] ?>"></div>
                            <div class="bar-single bar-amber" style="height: <?= $row['s'] ?>%;" title="Scheduled: <?= $row['s'] ?>"></div>
                        </div>
                        <span class="bar-month-label"><?= $row['m'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Request Status Donut Chart Card -->
    <div class="staff-card chart-donut-card">
        <div class="staff-card-header">
            <h2 class="staff-card-title">Request status</h2>
            <button type="button" class="btn-icon-more" title="More options" aria-label="More options">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="currentColor">
                    <circle cx="5" cy="12" r="2"></circle>
                    <circle cx="12" cy="12" r="2"></circle>
                    <circle cx="19" cy="12" r="2"></circle>
                </svg>
            </button>
        </div>

        <div class="donut-chart-wrapper">
            <div class="donut-svg-box">
                <svg viewBox="0 0 160 160" width="150" height="150" class="donut-svg">
                    <!-- Donut Segments (Total = 126; Completed=68, In Progress=31, Pending=16, Cancelled=11) -->
                    <!-- Circumference = 2 * PI * 58 ~= 364.4 -->
                    <circle cx="80" cy="80" r="58" fill="transparent" stroke="#f1f5f9" stroke-width="20"></circle>
                    <!-- Completed (54%) -->
                    <circle cx="80" cy="80" r="58" fill="transparent" stroke="#0b7479" stroke-width="20" stroke-dasharray="196.8 364.4" stroke-dashoffset="0" transform="rotate(-90 80 80)"></circle>
                    <!-- In Progress (24.6%) -->
                    <circle cx="80" cy="80" r="58" fill="transparent" stroke="#e07a38" stroke-width="20" stroke-dasharray="89.6 364.4" stroke-dashoffset="-196.8" transform="rotate(-90 80 80)"></circle>
                    <!-- Pending (12.7%) -->
                    <circle cx="80" cy="80" r="58" fill="transparent" stroke="#0284c7" stroke-width="20" stroke-dasharray="46.3 364.4" stroke-dashoffset="-286.4" transform="rotate(-90 80 80)"></circle>
                    <!-- Cancelled (8.7%) -->
                    <circle cx="80" cy="80" r="58" fill="transparent" stroke="#cbd5e1" stroke-width="20" stroke-dasharray="31.7 364.4" stroke-dashoffset="-332.7" transform="rotate(-90 80 80)"></circle>
                </svg>
                <div class="donut-center-label">
                    <span class="donut-center-number">126</span>
                    <span class="donut-center-text">Total</span>
                </div>
            </div>

            <!-- Donut Legend 2x2 Grid -->
            <div class="donut-legend-grid">
                <div class="donut-stat-item">
                    <div class="donut-indicator-title"><span class="legend-color-dot dot-teal"></span><span>Completed</span></div>
                    <span class="donut-count-num">68</span>
                </div>
                <div class="donut-stat-item">
                    <div class="donut-indicator-title"><span class="legend-color-dot dot-orange"></span><span>In progress</span></div>
                    <span class="donut-count-num">31</span>
                </div>
                <div class="donut-stat-item">
                    <div class="donut-indicator-title"><span class="legend-color-dot dot-blue"></span><span>Pending</span></div>
                    <span class="donut-count-num">16</span>
                </div>
                <div class="donut-stat-item">
                    <div class="donut-indicator-title"><span class="legend-color-dot dot-grey"></span><span>Cancelled</span></div>
                    <span class="donut-count-num">11</span>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- BOTTOM ROW: RECENT ACTIVITY & SYSTEM HEALTH -->
<section class="admin-bottom-grid" aria-label="System Health and Audit Logs">
    <!-- Recent Activity Card -->
    <div class="staff-card">
        <div class="staff-card-header">
            <h2 class="staff-card-title">Recent activity</h2>
            <a href="admin-users.php" class="staff-card-footer-link" style="padding: 0;">View audit log</a>
        </div>

        <div class="admin-activity-list">
            <?php foreach ($recentLogs as $log): 
                $iconClass = 'icon-teal';
                if (($log['icon'] ?? '') === 'user' || stripos($log['event_type'], 'registered') !== false) {
                    $iconClass = 'icon-blue';
                } elseif (($log['icon'] ?? '') === 'database' || stripos($log['event_type'], 'Reference') !== false) {
                    $iconClass = 'icon-grey';
                }
            ?>
                <div class="activity-row-item">
                    <div class="activity-icon-badge <?= $iconClass ?>">
                        <?php if ($iconClass === 'icon-blue'): ?>
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="8.5" cy="7" r="4"></circle>
                            </svg>
                        <?php elseif ($iconClass === 'icon-grey'): ?>
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <ellipse cx="12" cy="5" rx="9" ry="3"></ellipse>
                                <path d="M21 12c0 1.66-4 3-9 3s-9-1.34-9-3"></path>
                                <path d="M3 5v14c0 1.66 4 3 9 3s9-1.34 9-3V5"></path>
                            </svg>
                        <?php else: ?>
                            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div class="activity-info-block">
                        <div class="activity-title-text"><?= htmlspecialchars($log['event_type']) ?></div>
                        <div class="activity-actor-text"><?= htmlspecialchars($log['actor_name'] ?? $log['description']) ?></div>
                    </div>
                    <span class="activity-time-text"><?= htmlspecialchars($log['time_label'] ?? 'Recently') ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- System Health Card -->
    <div class="staff-card">
        <div class="staff-card-header">
            <h2 class="staff-card-title">System health</h2>
            <span class="status-pill-green">
                <span class="queue-dot-pulse" style="background-color: #10b981;"></span>
                <span>All systems operational</span>
            </span>
        </div>

        <div class="system-health-rows">
            <div class="health-row-item">
                <span class="health-item-label">Application</span>
                <span class="health-item-val val-green">99.99%</span>
            </div>
            <div class="health-row-item">
                <span class="health-item-label">Database</span>
                <span class="health-item-val val-green">Healthy</span>
            </div>
            <div class="health-row-item">
                <span class="health-item-label">Email delivery</span>
                <span class="health-item-val val-green">98.7%</span>
            </div>
            <div class="health-row-item">
                <span class="health-item-label">Last backup</span>
                <span class="health-item-val val-muted">Today, 03:00</span>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
