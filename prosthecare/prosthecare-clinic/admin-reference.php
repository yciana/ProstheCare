<?php
/**
 * ProstheCare Clinic Management System - Reference Data
 * Matches Figma design (media_1794282425041_2d4332d9.png)
 * Strictly clean production view - No "Preview As" switchers.
 */

$pageTitle = 'Reference data';
require_once __DIR__ . '/includes/header.php';

$message = '';
$isError = false;

// Active Category Tab
$activeCategory = $_GET['cat'] ?? 'Services';
if (!in_array($activeCategory, ['Services', 'Components', 'Appointment types'])) {
    $activeCategory = 'Services';
}

// Handle Add Item
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_reference_item') {
    $cat = trim($_POST['category'] ?? 'Services');
    $name = trim($_POST['item_name'] ?? '');
    $duration = trim($_POST['duration_text'] ?? '45 min');
    $code = '0' . rand(5, 9);

    if (empty($name)) {
        $message = 'Item name is required.';
        $isError = true;
    } else {
        $supabase->from('reference_data')->insert([
            'category' => $cat,
            'item_code' => $code,
            'name' => $name,
            'duration_text' => $duration,
            'status' => 'Active',
            'display_order' => 10
        ]);
        $message = "New record \"{$name}\" added to {$cat}.";
        $activeCategory = $cat;
    }
}

// Data repository by category matching Figma design
$referenceCatalog = [
    'Services' => [
        ['code' => '01', 'name' => 'Initial consultation', 'duration' => '60 min', 'status' => 'Active'],
        ['code' => '02', 'name' => 'Prosthetic fitting', 'duration' => '45 min', 'status' => 'Active'],
        ['code' => '03', 'name' => 'Maintenance & repair', 'duration' => '30 min', 'status' => 'Active'],
        ['code' => '04', 'name' => 'Gait assessment', 'duration' => '45 min', 'status' => 'Active'],
    ],
    'Components' => [
        ['code' => '01', 'name' => 'Dynamic Response Foot Module', 'duration' => 'Catalog', 'status' => 'Active'],
        ['code' => '02', 'name' => 'Polycentric Pneumatic Knee', 'duration' => 'Catalog', 'status' => 'Active'],
        ['code' => '03', 'name' => 'Total Surface Bearing Socket', 'duration' => 'Catalog', 'status' => 'Active'],
    ],
    'Appointment types' => [
        ['code' => '01', 'name' => 'Consultation', 'duration' => '60 min', 'status' => 'Active'],
        ['code' => '02', 'name' => 'Device Fitting', 'duration' => '45 min', 'status' => 'Active'],
        ['code' => '03', 'name' => 'Repair & Tuning', 'duration' => '30 min', 'status' => 'Active'],
    ]
];

// Query Supabase if present
$dbItems = $supabase->from('reference_data')->select('*')->eq('category', $activeCategory)->order('display_order', true)->get();
if (!empty($dbItems)) {
    $referenceCatalog[$activeCategory] = [];
    foreach ($dbItems as $d) {
        $referenceCatalog[$activeCategory][] = [
            'code' => $d['item_code'] ?? '01',
            'name' => $d['name'],
            'duration' => $d['duration_text'] ?? '45 min',
            'status' => $d['status'] ?? 'Active'
        ];
    }
}

$currentItems = $referenceCatalog[$activeCategory] ?? [];

// Search filter
$searchQuery = strtolower(trim($_GET['q'] ?? ''));
if ($searchQuery !== '') {
    $currentItems = array_filter($currentItems, function($it) use ($searchQuery) {
        return stripos($it['name'], $searchQuery) !== false || stripos($it['code'], $searchQuery) !== false;
    });
}
?>

<!-- HEADER ROW (Screenshot 6) -->
<section class="client-greeting-row" aria-label="Reference Data Header" style="margin-bottom: 24px;">
    <div>
        <h1 class="client-greeting-title">Reference data</h1>
        <p class="client-greeting-subtitle">Maintain services, components, and appointment types.</p>
    </div>
    <div>
        <button type="button" class="btn-primary-action" onclick="document.getElementById('addRefModal').style.display='flex';">
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <line x1="12" y1="5" x2="12" y2="19"></line>
                <line x1="5" y1="12" x2="19" y2="12"></line>
            </svg>
            <span>Add <?= htmlspecialchars(rtrim($activeCategory, 's')) ?></span>
        </button>
    </div>
</section>

<?php if (!empty($message)): ?>
    <div class="auth-alert-box alert-success" style="margin-bottom: 20px;">
        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
        <span><?= htmlspecialchars($message) ?></span>
    </div>
<?php endif; ?>

<!-- 2-COLUMN SPLIT (Screenshot 6) -->
<div class="reference-split-grid">
    <!-- LEFT PANEL: DATA CATEGORIES -->
    <div class="staff-card reference-nav-card">
        <span class="nav-section-title" style="margin-bottom: 12px; display: block;">DATA CATEGORIES</span>
        
        <a href="admin-reference.php?cat=Services" class="ref-cat-nav-item <?= $activeCategory === 'Services' ? 'active' : '' ?>">
            <div class="ref-cat-left">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"></circle>
                    <circle cx="12" cy="12" r="3"></circle>
                </svg>
                <div>
                    <div class="ref-cat-title">Services</div>
                    <div class="ref-cat-sub"><?= count($referenceCatalog['Services']) ?> records</div>
                </div>
            </div>
            <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </a>

        <a href="admin-reference.php?cat=Components" class="ref-cat-nav-item <?= $activeCategory === 'Components' ? 'active' : '' ?>">
            <div class="ref-cat-left">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>
                <div>
                    <div class="ref-cat-title">Components</div>
                    <div class="ref-cat-sub"><?= count($referenceCatalog['Components']) ?> records</div>
                </div>
            </div>
            <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </a>

        <a href="admin-reference.php?cat=Appointment%20types" class="ref-cat-nav-item <?= $activeCategory === 'Appointment types' ? 'active' : '' ?>">
            <div class="ref-cat-left">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                <div>
                    <div class="ref-cat-title">Appointment types</div>
                    <div class="ref-cat-sub"><?= count($referenceCatalog['Appointment types']) ?> records</div>
                </div>
            </div>
            <svg class="nav-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="9 18 15 12 9 6"></polyline>
            </svg>
        </a>
    </div>

    <!-- RIGHT PANEL: CATALOG LIST -->
    <div class="staff-card reference-list-card">
        <div class="reference-list-header-bar">
            <div>
                <h2 class="staff-card-title"><?= htmlspecialchars($activeCategory) ?></h2>
                <p class="staff-card-subtitle">Drag to reorder. Changes apply system-wide.</p>
            </div>
            <form method="GET" action="admin-reference.php" class="toolbar-search-wrap" style="max-width: 260px;">
                <input type="hidden" name="cat" value="<?= htmlspecialchars($activeCategory) ?>">
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
                <input 
                    type="text" 
                    name="q" 
                    class="find-search-input" 
                    placeholder="Search <?= strtolower(htmlspecialchars($activeCategory)) ?>" 
                    value="<?= htmlspecialchars($_GET['q'] ?? '') ?>"
                    oninput="this.form.submit()"
                >
            </form>
        </div>

        <!-- TABLE ROWS -->
        <div class="reference-items-table">
            <div class="ref-table-header-row">
                <span class="ref-th-name">NAME</span>
                <span class="ref-th-duration">TYPE / DURATION</span>
                <span class="ref-th-status">STATUS</span>
                <span class="ref-th-actions"></span>
            </div>

            <?php foreach ($currentItems as $row): ?>
                <div class="ref-table-data-row">
                    <div class="ref-cell-drag-name">
                        <span class="drag-handle-dots" title="Drag to reorder">&#8942;&#8942;</span>
                        <span class="ref-code-badge"><?= htmlspecialchars($row['code']) ?></span>
                        <span class="ref-item-name-text"><?= htmlspecialchars($row['name']) ?></span>
                    </div>

                    <div class="ref-cell-duration">
                        <span><?= htmlspecialchars($row['duration']) ?></span>
                    </div>

                    <div class="ref-cell-status">
                        <span class="status-pill-verified">
                            <span><?= htmlspecialchars($row['status']) ?></span>
                        </span>
                    </div>

                    <div class="ref-cell-actions">
                        <button type="button" class="btn-ref-icon-action" title="Edit record">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 20h9"></path>
                                <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"></path>
                            </svg>
                        </button>
                        <button type="button" class="btn-ref-icon-action" title="More options">
                            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor">
                                <circle cx="5" cy="12" r="2"></circle>
                                <circle cx="12" cy="12" r="2"></circle>
                                <circle cx="19" cy="12" r="2"></circle>
                            </svg>
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ADD REFERENCE ITEM MODAL -->
<div id="addRefModal" class="modal-overlay" style="display: none;">
    <div class="modal-dialog-card">
        <div class="modal-header-row">
            <h3 class="modal-title-text">Add to <?= htmlspecialchars($activeCategory) ?></h3>
            <button type="button" class="modal-close-btn" onclick="document.getElementById('addRefModal').style.display='none';">&times;</button>
        </div>
        <form method="POST" action="admin-reference.php">
            <input type="hidden" name="action" value="add_reference_item">
            <input type="hidden" name="category" value="<?= htmlspecialchars($activeCategory) ?>">
            <div style="margin-bottom: 14px;">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 6px;">Name / Description</label>
                <input type="text" name="item_name" class="find-search-input" placeholder="e.g. Gait & Alignment Analysis" required>
            </div>
            <div style="margin-bottom: 24px;">
                <label style="font-size: 12px; font-weight: 600; display: block; margin-bottom: 6px;">Duration / Catalog spec</label>
                <input type="text" name="duration_text" class="find-search-input" value="45 min" required>
            </div>
            <div style="display: flex; justify-content: flex-end; gap: 10px;">
                <button type="button" class="btn-approval-reject" onclick="document.getElementById('addRefModal').style.display='none';">Cancel</button>
                <button type="submit" class="btn-primary-action">Save to catalog</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
