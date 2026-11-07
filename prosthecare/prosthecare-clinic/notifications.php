<?php
/**
 * ProstheCare Clinic Management System - Notifications Page
 * Matches Figma design (Screenshot 2)
 */

$pageTitle = 'Notifications';
require_once __DIR__ . '/includes/header.php';
?>

<!-- NOTIFICATIONS HEADER ROW -->
<div class="client-greeting-row" style="margin-bottom: 24px;">
    <div>
        <h1 class="client-greeting-title">Notifications</h1>
        <p class="client-greeting-subtitle">Stay up to date with appointments, requests, and clinic news.</p>
    </div>
    <div>
        <button type="button" class="btn-secondary" id="markAllReadBtn">
            Mark all as read
        </button>
    </div>
</div>

<!-- NOTIFICATIONS PANEL CARD -->
<div class="notifications-panel-card">
    <!-- Filter Pills Bar -->
    <div class="filter-pills-bar" role="tablist">
        <button type="button" class="filter-pill-btn active" data-filter="all">All</button>
        <button type="button" class="filter-pill-btn" data-filter="appointments">Appointments</button>
        <button type="button" class="filter-pill-btn" data-filter="requests">Requests</button>
        <button type="button" class="filter-pill-btn" data-filter="care">Care updates</button>
    </div>

    <!-- Notification List Items -->
    <div class="notifications-list" id="notificationsList">
        <!-- Notification Item 1: Fitting appointment tomorrow -->
        <div class="notification-row-item" data-category="appointments" data-id="1">
            <div class="notification-row-left">
                <div class="announcement-icon-box icon-box-orange">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                </div>
                <div class="notification-main-info">
                    <h2 class="notification-row-title">Fitting appointment tomorrow</h2>
                    <p class="notification-row-desc">Your appointment with Dr. James Wilson is at 10:30 AM in Fitting Room 2.</p>
                    <span class="notification-row-meta">10 min ago</span>
                </div>
            </div>
            <div class="notification-row-right">
                <span class="unread-indicator-dot"></span>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-chevron">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>
        </div>

        <!-- Notification Item 2: Request approved -->
        <div class="notification-row-item" data-category="requests" data-id="2">
            <div class="notification-row-left">
                <div class="announcement-icon-box icon-box-teal">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <div class="notification-main-info">
                    <h2 class="notification-row-title">Request approved</h2>
                    <p class="notification-row-desc">Your transtibial prosthesis request has moved into fabrication.</p>
                    <span class="notification-row-meta">Yesterday</span>
                </div>
            </div>
            <div class="notification-row-right">
                <span class="unread-indicator-dot"></span>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-chevron">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>
        </div>

        <!-- Notification Item 3: Maintenance reminder -->
        <div class="notification-row-item" data-category="care" data-id="3">
            <div class="notification-row-left">
                <div class="announcement-icon-box icon-box-blue">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path>
                        <path d="M12 15l-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path>
                    </svg>
                </div>
                <div class="notification-main-info">
                    <h2 class="notification-row-title">Maintenance reminder</h2>
                    <p class="notification-row-desc">Your next routine device inspection is due in 14 days.</p>
                    <span class="notification-row-meta">May 12</span>
                </div>
            </div>
            <div class="notification-row-right">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-chevron">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>
        </div>

        <!-- Notification Item 4: Clinic holiday hours -->
        <div class="notification-row-item" data-category="care" data-id="4">
            <div class="notification-row-left">
                <div class="announcement-icon-box icon-box-orange">
                    <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                </div>
                <div class="notification-main-info">
                    <h2 class="notification-row-title">Clinic holiday hours</h2>
                    <p class="notification-row-desc">The clinic will be closed on May 26 for the public holiday.</p>
                    <span class="notification-row-meta">May 10</span>
                </div>
            </div>
            <div class="notification-row-right">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-chevron">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>
        </div>
    </div>
</div>

<!-- Interactive Notification Detail Modal -->
<div class="modal-overlay" id="notificationModal">
    <div class="modal-card">
        <div class="modal-header">
            <h3 class="modal-title" id="notifModalTitle">Notification Detail</h3>
            <button type="button" class="modal-close-btn" id="closeNotifModal">&times;</button>
        </div>
        <div class="modal-body" id="notifModalBody">
            <!-- Dynamic content -->
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" id="modalDismissBtn">Close</button>
            <a href="appointments.php" class="btn-primary-action" id="modalActionBtn">Take Action</a>
        </div>
    </div>
</div>

<!-- Toast element -->
<div class="app-toast" id="appToast">All notifications marked as read</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Filter tabs
    const filterBtns = document.querySelectorAll('.filter-pill-btn');
    const items = document.querySelectorAll('.notification-row-item');
    const toast = document.getElementById('appToast');

    function showToast(msg) {
        toast.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filter = this.getAttribute('data-filter');

            items.forEach(item => {
                const cat = item.getAttribute('data-category');
                if (filter === 'all' || cat === filter) {
                    item.style.display = 'flex';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    });

    // Mark all as read
    const markReadBtn = document.getElementById('markAllReadBtn');
    const unreadDots = document.querySelectorAll('.unread-indicator-dot');
    const headerDot = document.getElementById('headerUnreadDot');

    markReadBtn.addEventListener('click', function() {
        unreadDots.forEach(dot => dot.style.display = 'none');
        if (headerDot) headerDot.style.display = 'none';
        showToast('All notifications marked as read');
    });

    // Click item to view details
    const modal = document.getElementById('notificationModal');
    const modalTitle = document.getElementById('notifModalTitle');
    const modalBody = document.getElementById('notifModalBody');
    const closeModal = document.getElementById('closeNotifModal');
    const dismissBtn = document.getElementById('modalDismissBtn');

    items.forEach(item => {
        item.style.cursor = 'pointer';
        item.addEventListener('click', function() {
            const title = this.querySelector('.notification-row-title').textContent;
            const desc = this.querySelector('.notification-row-desc').textContent;
            const meta = this.querySelector('.notification-row-meta').textContent;
            const dot = this.querySelector('.unread-indicator-dot');
            if (dot) dot.style.display = 'none';

            modalTitle.textContent = title;
            modalBody.innerHTML = `
                <div style="font-size: 14px; line-height: 1.6; color: var(--color-text-body); margin-bottom: 12px;">
                    ${desc}
                </div>
                <div style="font-size: 12px; color: var(--color-text-light);">
                    Received: ${meta}
                </div>
            `;
            modal.classList.add('active');
        });
    });

    function hideModal() {
        modal.classList.remove('active');
    }

    closeModal.addEventListener('click', hideModal);
    dismissBtn.addEventListener('click', hideModal);
    modal.addEventListener('click', function(e) {
        if (e.target === modal) hideModal();
    });
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
