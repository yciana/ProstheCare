<?php
/**
 * ProstheCare Clinic Management System - My Requests Page
 * Matches Figma design (Screenshot 3)
 */

$pageTitle = 'My Requests';
require_once __DIR__ . '/includes/header.php';

// Handle POST to create a new request
$newRequestCreated = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'new_request') {
    $service = trim($_POST['service'] ?? 'Transtibial prosthesis');
    $notes = trim($_POST['notes'] ?? '');
    $newId = 'PR-' . date('Y') . '-' . str_pad(rand(100, 999), 4, '0', STR_PAD_LEFT);
    
    // Save to care_plans or patients in Supabase if logged in
    $supabase->from('care_plans')->insert([
        'patient_id' => $currentUser['id'] ?? null,
        'device_type' => $service,
        'clinical_notes' => $notes,
        'status' => 'Pending Approval'
    ]);
    $newRequestCreated = true;
}
?>

<!-- MY REQUESTS HEADER ROW -->
<div class="client-greeting-row" style="margin-bottom: 24px;">
    <div>
        <h1 class="client-greeting-title">My requests</h1>
        <p class="client-greeting-subtitle">Track your prosthetic requests and service history.</p>
    </div>
    <div>
        <button type="button" class="btn-primary-action" id="openNewRequestBtn">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>New request</span>
        </button>
    </div>
</div>

<!-- REQUESTS TOOLBAR & FILTERS -->
<div class="requests-panel-card" style="background: var(--color-surface); border: 1px solid var(--color-border); border-radius: var(--radius-lg); padding: 24px; box-shadow: var(--shadow-sm);">
    <div class="requests-toolbar-row">
        <!-- Filter pills -->
        <div class="filter-pills-bar" style="margin-bottom: 0;">
            <button type="button" class="filter-pill-btn active" data-filter="all">
                All requests <span class="badge-tag-count" id="countAll">4</span>
            </button>
            <button type="button" class="filter-pill-btn" data-filter="active">
                Active <span class="badge-tag-count" id="countActive">1</span>
            </button>
            <button type="button" class="filter-pill-btn" data-filter="completed">
                Completed <span class="badge-tag-count" id="countCompleted">2</span>
            </button>
        </div>

        <!-- Search input -->
        <div class="header-search-box" style="width: 240px; padding: 6px 12px;">
            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="8"></circle>
                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
            </svg>
            <input type="text" class="header-search-input" id="searchRequestsInput" placeholder="Search requests" aria-label="Search requests">
        </div>
    </div>

    <!-- Requests Table -->
    <div class="requests-table-container" style="margin-top: 16px;">
        <table class="clinic-table" id="requestsTable">
            <thead>
                <tr>
                    <th style="font-size: 11px; font-weight: 700; letter-spacing: 0.08em; color: var(--color-text-light); text-transform: uppercase;">REQUEST ID</th>
                    <th style="font-size: 11px; font-weight: 700; letter-spacing: 0.08em; color: var(--color-text-light); text-transform: uppercase;">SERVICE</th>
                    <th style="font-size: 11px; font-weight: 700; letter-spacing: 0.08em; color: var(--color-text-light); text-transform: uppercase;">SUBMITTED</th>
                    <th style="font-size: 11px; font-weight: 700; letter-spacing: 0.08em; color: var(--color-text-light); text-transform: uppercase;">STATUS</th>
                    <th style="text-align: right;"></th>
                </tr>
            </thead>
            <tbody>
                <!-- Row 1 -->
                <tr data-status="active" data-id="PR-2025-0184" data-service="Transtibial prosthesis" data-submitted="Apr 22, 2025" data-badge="In fabrication" data-notes="Custom carbon composite socket, dynamic response foot, vacuum suspension system. Approved by Dr. James Wilson.">
                    <td style="font-weight: 700; color: var(--color-text-main);">PR-2025-0184</td>
                    <td style="color: var(--color-text-main);">Transtibial prosthesis</td>
                    <td style="color: var(--color-text-muted);">Apr 22, 2025</td>
                    <td><span class="status-pill-amber">In fabrication</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="view-request-btn" style="background:none; border:none; color:var(--color-primary); font-size:13px; font-weight:600; cursor:pointer;">
                            View &gt;
                        </button>
                    </td>
                </tr>
                <!-- Row 2 -->
                <tr data-status="completed" data-id="SR-2025-0091" data-service="Socket adjustment" data-submitted="Mar 10, 2025" data-badge="Completed" data-notes="Relief on distal tibia pressure area. Patient reported full comfort and restored gait symmetry.">
                    <td style="font-weight: 700; color: var(--color-text-main);">SR-2025-0091</td>
                    <td style="color: var(--color-text-main);">Socket adjustment</td>
                    <td style="color: var(--color-text-muted);">Mar 10, 2025</td>
                    <td><span class="status-pill-teal">Completed</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="view-request-btn" style="background:none; border:none; color:var(--color-primary); font-size:13px; font-weight:600; cursor:pointer;">
                            View &gt;
                        </button>
                    </td>
                </tr>
                <!-- Row 3 -->
                <tr data-status="completed" data-id="MR-2025-0062" data-service="Routine maintenance" data-submitted="Jan 18, 2025" data-badge="Completed" data-notes="Torque verified on all modular adaptors. Pylon alignment inspected. Foot shell cleaned and lubricated.">
                    <td style="font-weight: 700; color: var(--color-text-main);">MR-2025-0062</td>
                    <td style="color: var(--color-text-main);">Routine maintenance</td>
                    <td style="color: var(--color-text-muted);">Jan 18, 2025</td>
                    <td><span class="status-pill-teal">Completed</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="view-request-btn" style="background:none; border:none; color:var(--color-primary); font-size:13px; font-weight:600; cursor:pointer;">
                            View &gt;
                        </button>
                    </td>
                </tr>
                <!-- Row 4 -->
                <tr data-status="cancelled" data-id="PR-2024-0247" data-service="Cosmetic cover" data-submitted="Nov 02, 2024" data-badge="Cancelled" data-notes="Client elected for high-definition 3D printed fairing instead. Initial cosmetic cover order closed.">
                    <td style="font-weight: 700; color: var(--color-text-main);">PR-2024-0247</td>
                    <td style="color: var(--color-text-main);">Cosmetic cover</td>
                    <td style="color: var(--color-text-muted);">Nov 02, 2024</td>
                    <td><span class="status-pill-gray">Cancelled</span></td>
                    <td style="text-align: right;">
                        <button type="button" class="view-request-btn" style="background:none; border:none; color:var(--color-primary); font-size:13px; font-weight:600; cursor:pointer;">
                            View &gt;
                        </button>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Table Footer with Showing count and pagination -->
    <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--color-border); font-size: 13px; color: var(--color-text-muted);">
        <div id="showingCountText">Showing 4 of 4 requests</div>
        <div style="display: flex; gap: 6px;">
            <button type="button" style="width: 32px; height: 32px; border-radius: 6px; border: 1px solid var(--color-border); background: var(--color-surface); cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--color-text-light);" aria-label="Previous page">
                &lt;
            </button>
            <button type="button" style="width: 32px; height: 32px; border-radius: 6px; border: 1px solid var(--color-border); background: var(--color-surface); cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--color-text-light);" aria-label="Next page">
                &gt;
            </button>
        </div>
    </div>
</div>

<!-- Modal: View Request Details -->
<div class="modal-overlay" id="requestDetailModal">
    <div class="modal-card">
        <div class="modal-header">
            <div>
                <h3 class="modal-title" id="modalReqId">PR-2025-0184</h3>
                <span style="font-size: 12px; color: var(--color-text-muted);" id="modalReqService">Transtibial prosthesis</span>
            </div>
            <button type="button" class="modal-close-btn" id="closeDetailModal">&times;</button>
        </div>
        <div class="modal-body">
            <div style="display: flex; justify-content: space-between; margin-bottom: 16px;">
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; color: var(--color-text-light); font-weight: 700;">Submitted Date</span>
                    <div style="font-size: 14px; font-weight: 600; color: var(--color-text-main);" id="modalReqDate">Apr 22, 2025</div>
                </div>
                <div>
                    <span style="font-size: 11px; text-transform: uppercase; color: var(--color-text-light); font-weight: 700;">Current Status</span>
                    <div id="modalReqStatusBadge" style="margin-top: 2px;"></div>
                </div>
            </div>
            
            <div style="margin-bottom: 16px;">
                <span style="font-size: 11px; text-transform: uppercase; color: var(--color-text-light); font-weight: 700;">Clinical Notes & Specs</span>
                <p style="font-size: 13px; line-height: 1.6; color: var(--color-text-body); background: var(--color-surface-muted); padding: 12px; border-radius: 6px; margin-top: 6px;" id="modalReqNotes"></p>
            </div>

            <div>
                <span style="font-size: 11px; text-transform: uppercase; color: var(--color-text-light); font-weight: 700;">Lead Prosthetist</span>
                <div style="font-size: 13px; font-weight: 600; color: var(--color-text-main); margin-top: 2px;">Dr. James Wilson (Clinical Director)</div>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" id="closeDetailModalBtn">Close</button>
            <a href="designer.php" class="btn-primary-action">Open in Leg Designer</a>
        </div>
    </div>
</div>

<!-- Modal: Create New Request -->
<div class="modal-overlay" id="newRequestModal">
    <div class="modal-card">
        <form method="POST" action="requests.php">
            <input type="hidden" name="action" value="new_request">
            <div class="modal-header">
                <h3 class="modal-title">Submit New Prosthetic Request</h3>
                <button type="button" class="modal-close-btn" id="closeNewModal">&times;</button>
            </div>
            <div class="modal-body">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: var(--color-text-main); margin-bottom: 6px;">Requested Service</label>
                    <select name="service" class="form-input-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--color-border); background: var(--color-surface);">
                        <option value="Transtibial prosthesis">Transtibial prosthesis (Below Knee)</option>
                        <option value="Transfemoral prosthesis">Transfemoral prosthesis (Above Knee)</option>
                        <option value="Socket adjustment">Socket adjustment & Refit</option>
                        <option value="Routine maintenance">Routine maintenance & Alignment</option>
                        <option value="Cosmetic cover">Custom 3D Cosmetic Fairing</option>
                    </select>
                </div>
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 13px; font-weight: 600; color: var(--color-text-main); margin-bottom: 6px;">Specific Needs / Notes</label>
                    <textarea name="notes" rows="3" class="form-input-control" style="width: 100%; padding: 10px; border-radius: 8px; border: 1px solid var(--color-border); font-family: inherit;" placeholder="Describe any discomfort, fitting requirements, or activity goals..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelNewModalBtn">Cancel</button>
                <button type="submit" class="btn-primary-action">Submit Request</button>
            </div>
        </form>
    </div>
</div>

<!-- Toast -->
<div class="app-toast" id="appToast">Request updated successfully</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const filterBtns = document.querySelectorAll('.filter-pill-btn');
    const tableRows = document.querySelectorAll('#requestsTable tbody tr');
    const searchInput = document.getElementById('searchRequestsInput');
    const showingText = document.getElementById('showingCountText');

    let currentFilter = 'all';

    function applyFilters() {
        const query = searchInput.value.toLowerCase().trim();
        let visibleCount = 0;

        tableRows.forEach(row => {
            const status = row.getAttribute('data-status');
            const text = row.innerText.toLowerCase();

            const matchesStatus = (currentFilter === 'all' || status === currentFilter);
            const matchesQuery = (query === '' || text.includes(query));

            if (matchesStatus && matchesQuery) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        showingText.textContent = `Showing ${visibleCount} of ${tableRows.length} requests`;
    }

    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            currentFilter = this.getAttribute('data-filter');
            applyFilters();
        });
    });

    searchInput.addEventListener('input', applyFilters);

    // Detail Modal Handling
    const detailModal = document.getElementById('requestDetailModal');
    const modalReqId = document.getElementById('modalReqId');
    const modalReqService = document.getElementById('modalReqService');
    const modalReqDate = document.getElementById('modalReqDate');
    const modalReqStatusBadge = document.getElementById('modalReqStatusBadge');
    const modalReqNotes = document.getElementById('modalReqNotes');
    const closeDetailModal = document.getElementById('closeDetailModal');
    const closeDetailModalBtn = document.getElementById('closeDetailModalBtn');

    document.querySelectorAll('.view-request-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const row = this.closest('tr');
            modalReqId.textContent = row.getAttribute('data-id');
            modalReqService.textContent = row.getAttribute('data-service');
            modalReqDate.textContent = row.getAttribute('data-submitted');
            modalReqNotes.textContent = row.getAttribute('data-notes');
            
            const badgeType = row.getAttribute('data-badge');
            if (badgeType === 'In fabrication') {
                modalReqStatusBadge.innerHTML = '<span class="status-pill-amber">In fabrication</span>';
            } else if (badgeType === 'Completed') {
                modalReqStatusBadge.innerHTML = '<span class="status-pill-teal">Completed</span>';
            } else {
                modalReqStatusBadge.innerHTML = '<span class="status-pill-gray">Cancelled</span>';
            }

            detailModal.classList.add('active');
        });
    });

    function hideDetailModal() {
        detailModal.classList.remove('active');
    }
    closeDetailModal.addEventListener('click', hideDetailModal);
    closeDetailModalBtn.addEventListener('click', hideDetailModal);

    // New Request Modal Handling
    const newModal = document.getElementById('newRequestModal');
    const openNewBtn = document.getElementById('openNewRequestBtn');
    const closeNewModal = document.getElementById('closeNewModal');
    const cancelNewModalBtn = document.getElementById('cancelNewModalBtn');

    openNewBtn.addEventListener('click', () => newModal.classList.add('active'));
    function hideNewModal() { newModal.classList.remove('active'); }
    closeNewModal.addEventListener('click', hideNewModal);
    cancelNewModalBtn.addEventListener('click', hideNewModal);

    window.addEventListener('click', function(e) {
        if (e.target === detailModal) hideDetailModal();
        if (e.target === newModal) hideNewModal();
    });

    <?php if ($newRequestCreated): ?>
    const toast = document.getElementById('appToast');
    toast.textContent = 'New request submitted successfully!';
    toast.classList.add('show');
    setTimeout(() => toast.classList.remove('show'), 3500);
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
