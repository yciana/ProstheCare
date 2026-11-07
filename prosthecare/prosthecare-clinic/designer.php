<?php
/**
 * ProstheCare Clinic Management System - Leg Designer Page
 * Matches Figma design (Screenshot 4)
 */

$pageTitle = 'Leg Designer';
require_once __DIR__ . '/includes/header.php';

// Handle submit design action
$designSubmitted = false;
$submittedId = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit_design') {
    $socketColor = $_POST['socket_color'] ?? '#0d797f';
    $pylonColor = $_POST['pylon_color'] ?? '#94a3b8';
    $footColor = $_POST['foot_color'] ?? '#1e293b';
    $side = $_POST['side'] ?? 'Left';
    
    $specs = "Transtibial Prosthesis ({$side} leg). Socket: {$socketColor}, Pylon: {$pylonColor}, Foot: {$footColor}. Measurements: Residual: {$_POST['residual_len']}cm, Knee: {$_POST['knee_circ']}cm, Calf: {$_POST['calf_circ']}cm, Ankle: {$_POST['ankle_circ']}cm, Foot: {$_POST['foot_len']}cm.";
    $submittedId = 'PR-' . date('Y') . '-' . str_pad(rand(100, 999), 4, '0', STR_PAD_LEFT);

    // Insert into care_plans in Supabase
    $supabase->from('care_plans')->insert([
        'patient_id' => $currentUser['id'] ?? null,
        'device_type' => 'Transtibial prosthesis',
        'target_alignment' => "Custom {$side} limb finish",
        'clinical_notes' => $specs,
        'status' => 'Pending Fabrication'
    ]);
    $designSubmitted = true;
}
?>

<!-- LEG DESIGNER HEADER ROW -->
<div class="client-greeting-row" style="margin-bottom: 24px;">
    <div>
        <h1 class="client-greeting-title">Leg designer</h1>
        <p class="client-greeting-subtitle">Personalize the look of your prosthetic leg.</p>
    </div>
    <div style="display: flex; gap: 12px;">
        <button type="button" class="btn-secondary" id="saveDraftBtn">Save draft</button>
        <button type="button" class="btn-primary-action" id="openSubmitModalBtn">Submit design</button>
    </div>
</div>

<!-- 3-COLUMN DESIGNER GRID LAYOUT -->
<div class="designer-grid-layout">
    <!-- COLUMN 1: MEASUREMENTS CARD -->
    <div class="designer-card-box">
        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 4px;">
            <h2 class="designer-card-title" style="margin-bottom: 0;">Measurements</h2>
            <span style="background: #e0f2fe; color: #0284c7; font-size: 11px; font-weight: 700; padding: 2px 8px; border-radius: 12px;">Saved</span>
        </div>
        <p class="designer-card-subtitle">All measurements in centimeters</p>

        <form id="measurementsForm">
            <div class="measurements-2col-grid">
                <div>
                    <label class="form-field-label" style="font-size: 12px; font-weight: 600; color: var(--color-text-main); margin-bottom: 6px; display: block;">Residual limb length</label>
                    <input type="text" name="residual_len" class="form-input-control" value="18.5" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--color-border); font-size: 14px;">
                </div>
                <div>
                    <label class="form-field-label" style="font-size: 12px; font-weight: 600; color: var(--color-text-main); margin-bottom: 6px; display: block;">Knee circumference</label>
                    <input type="text" name="knee_circ" class="form-input-control" value="36.2" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--color-border); font-size: 14px;">
                </div>
                <div>
                    <label class="form-field-label" style="font-size: 12px; font-weight: 600; color: var(--color-text-main); margin-bottom: 6px; display: block;">Calf circumference</label>
                    <input type="text" name="calf_circ" class="form-input-control" value="31.8" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--color-border); font-size: 14px;">
                </div>
                <div>
                    <label class="form-field-label" style="font-size: 12px; font-weight: 600; color: var(--color-text-main); margin-bottom: 6px; display: block;">Ankle circumference</label>
                    <input type="text" name="ankle_circ" class="form-input-control" value="22.4" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--color-border); font-size: 14px;">
                </div>
                <div>
                    <label class="form-field-label" style="font-size: 12px; font-weight: 600; color: var(--color-text-main); margin-bottom: 6px; display: block;">Foot length</label>
                    <input type="text" name="foot_len" class="form-input-control" value="25.5" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--color-border); font-size: 14px;">
                </div>
                <div>
                    <label class="form-field-label" style="font-size: 12px; font-weight: 600; color: var(--color-text-main); margin-bottom: 6px; display: block;">Side</label>
                    <select name="side" id="limbSideSelect" class="form-input-control" style="width: 100%; padding: 8px 12px; border-radius: 8px; border: 1px solid var(--color-border); font-size: 14px; background: var(--color-surface);">
                        <option value="Left" selected>Left</option>
                        <option value="Right">Right</option>
                    </select>
                </div>
            </div>
        </form>

        <!-- Measurements reviewed alert -->
        <div class="review-verified-box">
            <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                <polyline points="9 12 11 14 15 10"></polyline>
            </svg>
            <div>
                <div class="verified-title">Measurements reviewed</div>
                <div class="verified-desc">Verified by Dr. Wilson on May 8, 2025.</div>
            </div>
        </div>
    </div>

    <!-- COLUMN 2: LIVE PREVIEW CARD -->
    <div class="designer-card-box preview-stage-container">
        <div class="preview-top-bar">
            <span style="font-size: 10px; font-weight: 700; letter-spacing: 0.12em; text-transform: uppercase; color: var(--color-text-light);">LIVE PREVIEW</span>
            <button type="button" id="resetDesignBtn" style="background: none; border: none; font-size: 12px; font-weight: 600; color: var(--color-primary); cursor: pointer; display: flex; align-items: center; gap: 4px;">
                <span>&#8635; Reset</span>
            </button>
        </div>

        <!-- High-Fidelity SVG Graphic of Transtibial Prosthesis matching Image 4 -->
        <div style="width: 100%; max-width: 240px; margin: 30px auto 10px; display: flex; justify-content: center;">
            <svg viewBox="0 0 240 400" width="220" height="340" xmlns="http://www.w3.org/2000/svg" id="prostheticLegSvg">
                <defs>
                    <filter id="shadowFilter" x="-10%" y="-10%" width="120%" height="120%">
                        <feDropShadow dx="0" dy="4" stdDeviation="4" flood-opacity="0.1" />
                    </filter>
                    <linearGradient id="pylonGrad" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#94a3b8" />
                        <stop offset="50%" stop-color="#cbd5e1" />
                        <stop offset="100%" stop-color="#64748b" />
                    </linearGradient>
                </defs>

                <!-- Ground Shadow Oval -->
                <ellipse cx="120" cy="370" rx="65" ry="10" fill="#e2e8f0" opacity="0.8" />

                <!-- 1. Socket Shell (Top Modular Shell) -->
                <g id="svgSocketShellGroup" style="transition: fill 0.3s ease;">
                    <path id="svgSocketShell" d="M 85 45 C 85 30, 155 30, 155 45 L 152 145 C 150 175, 90 175, 88 145 Z" 
                          fill="#0d797f" filter="url(#shadowFilter)" />
                    <!-- Subtle Socket Specular Highlight -->
                    <path d="M 92 50 C 92 40, 140 40, 148 48 L 146 140 C 144 158, 96 158, 94 140 Z" 
                          fill="white" opacity="0.12" />
                    <!-- Top rim band -->
                    <ellipse cx="120" cy="45" rx="35" ry="9" fill="#095458" />
                </g>

                <!-- 2. Connector / Pyramid Alignment Joint -->
                <g id="svgJointGroup">
                    <rect x="110" y="172" width="20" height="12" rx="3" fill="#1e293b" />
                    <circle cx="120" cy="198" r="16" fill="#1e293b" />
                    <circle cx="120" cy="198" r="8" fill="#e2e8f0" stroke="#0f172a" stroke-width="2" />
                    <!-- Joint bolt -->
                    <circle cx="120" cy="198" r="3" fill="#0f172a" />
                </g>

                <!-- 3. Pylon Tube / Rod -->
                <g id="svgPylonGroup">
                    <!-- Dual-rod modern clinical pylon -->
                    <rect id="svgPylonRod1" x="114" y="214" width="5" height="110" rx="2" fill="url(#pylonGrad)" />
                    <rect id="svgPylonRod2" x="121" y="214" width="5" height="110" rx="2" fill="url(#pylonGrad)" />
                    <!-- Ankle clamp adaptor -->
                    <rect x="111" y="324" width="18" height="10" rx="2" fill="#1e293b" />
                </g>

                <!-- 4. Carbon Foot Shell -->
                <g id="svgFootShellGroup" style="transition: fill 0.3s ease;">
                    <!-- Sleek Dynamic Response Prosthetic Carbon Foot -->
                    <path id="svgFootShell" d="M 120 334 C 120 334, 110 348, 95 352 C 90 354, 90 365, 96 368 L 148 368 C 158 368, 160 358, 150 352 C 138 346, 130 334, 120 334 Z" 
                          fill="#1e293b" filter="url(#shadowFilter)" />
                    <!-- Dynamic curved carbon keel line -->
                    <path d="M 98 365 C 112 360, 135 360, 146 365" stroke="#475569" stroke-width="2" fill="none" />
                </g>
            </svg>
        </div>

        <div class="preview-bottom-caption">
            <div class="caption-model-title">Your custom transtibial prosthesis</div>
            <div class="caption-model-sub" id="previewLimbLabel">Front view &middot; Left leg</div>
        </div>
    </div>

    <!-- COLUMN 3: CUSTOMIZE PARTS CARD -->
    <div class="designer-card-box">
        <h2 class="designer-card-title">Customize parts</h2>
        <p class="designer-card-subtitle">Select a part, then choose a finish.</p>

        <!-- Selectable Parts List -->
        <div class="parts-selector-container">
            <!-- Part 1: Socket shell (Active by default) -->
            <div class="part-select-item selected" data-part="socket" id="partItemSocket">
                <div class="part-item-left">
                    <div class="part-preview-icon" style="background: #e6f6f6; color: #0b7479;">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="6" y="3" width="12" height="18" rx="6"></rect>
                        </svg>
                    </div>
                    <div>
                        <div class="part-name-text">Socket shell</div>
                        <div class="part-finish-text">Matte composite</div>
                    </div>
                </div>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-chevron">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>

            <!-- Part 2: Pylon cover -->
            <div class="part-select-item" data-part="pylon" id="partItemPylon">
                <div class="part-item-left">
                    <div class="part-preview-icon" style="background: #f1f5f9; color: #64748b;">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="2" x2="12" y2="22"></line>
                            <circle cx="12" cy="7" r="2"></circle>
                        </svg>
                    </div>
                    <div>
                        <div class="part-name-text">Pylon cover</div>
                        <div class="part-finish-text">Brushed alloy</div>
                    </div>
                </div>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-chevron">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>

            <!-- Part 3: Foot shell -->
            <div class="part-select-item" data-part="foot" id="partItemFoot">
                <div class="part-item-left">
                    <div class="part-preview-icon" style="background: #f1f5f9; color: #64748b;">
                        <svg viewBox="0 0 24 24" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 16c1-2 4-2 6-2s5 2 10 2"></path>
                            <path d="M20 16v2a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-2"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="part-name-text">Foot shell</div>
                        <div class="part-finish-text">Natural flex</div>
                    </div>
                </div>
                <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="nav-chevron">
                    <polyline points="9 18 15 12 9 6"></polyline>
                </svg>
            </div>
        </div>

        <!-- Color Swatches Grid matching Image 4 -->
        <div style="margin-top: 24px; padding-top: 16px; border-top: 1px solid var(--color-surface-muted);">
            <div class="form-field-label" id="colorPickerLabel" style="font-size: 13px; font-weight: 600; color: var(--color-text-main); margin-bottom: 12px;">
                Socket shell color
            </div>
            
            <div class="color-swatches-grid">
                <!-- Swatch 1: Teal (#0d797f) - active by default -->
                <div class="color-swatch-circle active" data-color="#0d797f" style="background-color: #0d797f;" title="Teal" aria-label="Teal finish">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <!-- Swatch 2: Dark Slate (#1e293b) -->
                <div class="color-swatch-circle" data-color="#1e293b" style="background-color: #1e293b;" title="Carbon Black" aria-label="Carbon Black finish">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <!-- Swatch 3: Warm Amber (#f59e0b) -->
                <div class="color-swatch-circle" data-color="#f59e0b" style="background-color: #f59e0b;" title="Warm Amber" aria-label="Warm Amber finish">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <!-- Swatch 4: Ice Blue (#bae6fd) -->
                <div class="color-swatch-circle" data-color="#bae6fd" style="background-color: #bae6fd;" title="Ice Blue" aria-label="Ice Blue finish">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#0f172a" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <!-- Swatch 5: Coral Red (#ef4444) -->
                <div class="color-swatch-circle" data-color="#ef4444" style="background-color: #ef4444;" title="Coral Red" aria-label="Coral Red finish">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
                <!-- Swatch 6: Lavender/Purple (#8b5cf6) -->
                <div class="color-swatch-circle" data-color="#8b5cf6" style="background-color: #8b5cf6;" title="Lavender" aria-label="Lavender finish">
                    <svg viewBox="0 0 24 24" width="14" height="14" fill="none" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"></polyline>
                    </svg>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Confirm & Submit Design -->
<div class="modal-overlay" id="submitDesignModal">
    <div class="modal-card">
        <form method="POST" action="designer.php" id="submitDesignForm">
            <input type="hidden" name="action" value="submit_design">
            <input type="hidden" name="socket_color" id="hiddenSocketColor" value="#0d797f">
            <input type="hidden" name="pylon_color" id="hiddenPylonColor" value="#94a3b8">
            <input type="hidden" name="foot_color" id="hiddenFootColor" value="#1e293b">
            <input type="hidden" name="residual_len" id="hiddenResidual" value="18.5">
            <input type="hidden" name="knee_circ" id="hiddenKnee" value="36.2">
            <input type="hidden" name="calf_circ" id="hiddenCalf" value="31.8">
            <input type="hidden" name="ankle_circ" id="hiddenAnkle" value="22.4">
            <input type="hidden" name="foot_len" id="hiddenFoot" value="25.5">
            <input type="hidden" name="side" id="hiddenSide" value="Left">

            <div class="modal-header">
                <h3 class="modal-title">Confirm Design Specifications</h3>
                <button type="button" class="modal-close-btn" id="closeSubmitModal">&times;</button>
            </div>
            <div class="modal-body">
                <p style="font-size: 14px; color: var(--color-text-body); margin-bottom: 16px;">
                    Your custom prosthetic specifications will be transmitted directly to Dr. James Wilson and our digital fabrication team.
                </p>
                <div style="background: var(--color-surface-muted); padding: 14px; border-radius: 8px; font-size: 13px; line-height: 1.6;">
                    <div><strong>Device:</strong> Custom Transtibial Prosthesis</div>
                    <div><strong>Side:</strong> <span id="summarySideText">Left leg</span></div>
                    <div><strong>Socket Color:</strong> <span id="summarySocketColor">#0d797f</span></div>
                    <div><strong>Limb Verification:</strong> Dr. Wilson (Approved)</div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" id="cancelSubmitModalBtn">Review Edits</button>
                <button type="submit" class="btn-primary-action">Confirm &amp; Send to Clinic</button>
            </div>
        </form>
    </div>
</div>

<!-- Toast -->
<div class="app-toast" id="appToast">Design draft saved successfully!</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Current part selection state
    let selectedPart = 'socket'; // 'socket' | 'pylon' | 'foot'
    const colors = {
        socket: '#0d797f',
        pylon: '#94a3b8',
        foot: '#1e293b'
    };

    const partItems = document.querySelectorAll('.part-select-item');
    const colorLabel = document.getElementById('colorPickerLabel');
    const swatches = document.querySelectorAll('.color-swatch-circle');
    const svgSocket = document.getElementById('svgSocketShell');
    const svgFoot = document.getElementById('svgFootShell');
    const sideSelect = document.getElementById('limbSideSelect');
    const previewLimbLabel = document.getElementById('previewLimbLabel');
    const toast = document.getElementById('appToast');

    function showToast(msg) {
        toast.textContent = msg;
        toast.classList.add('show');
        setTimeout(() => toast.classList.remove('show'), 3000);
    }

    // Part selection handler
    partItems.forEach(item => {
        item.addEventListener('click', function() {
            partItems.forEach(i => i.classList.remove('selected'));
            this.classList.add('selected');
            selectedPart = this.getAttribute('data-part');

            if (selectedPart === 'socket') {
                colorLabel.textContent = 'Socket shell color';
            } else if (selectedPart === 'pylon') {
                colorLabel.textContent = 'Pylon cover color';
            } else {
                colorLabel.textContent = 'Foot shell color';
            }

            // Sync swatches active checkmark to current part's color
            updateSwatchSelection(colors[selectedPart]);
        });
    });

    function updateSwatchSelection(currentColor) {
        swatches.forEach(s => {
            if (s.getAttribute('data-color').toLowerCase() === currentColor.toLowerCase()) {
                s.classList.add('active');
            } else {
                s.classList.remove('active');
            }
        });
    }

    // Swatch click handler
    swatches.forEach(swatch => {
        swatch.addEventListener('click', function() {
            const chosenColor = this.getAttribute('data-color');
            colors[selectedPart] = chosenColor;
            updateSwatchSelection(chosenColor);

            // Dynamically update SVG elements in real-time
            if (selectedPart === 'socket' && svgSocket) {
                svgSocket.setAttribute('fill', chosenColor);
                document.getElementById('hiddenSocketColor').value = chosenColor;
            } else if (selectedPart === 'pylon') {
                document.getElementById('svgPylonRod1').setAttribute('fill', chosenColor);
                document.getElementById('svgPylonRod2').setAttribute('fill', chosenColor);
                document.getElementById('hiddenPylonColor').value = chosenColor;
            } else if (selectedPart === 'foot' && svgFoot) {
                svgFoot.setAttribute('fill', chosenColor);
                document.getElementById('hiddenFootColor').value = chosenColor;
            }
        });
    });

    // Side selection update
    sideSelect.addEventListener('change', function() {
        const sideVal = this.value;
        previewLimbLabel.textContent = `Front view · ${sideVal} leg`;
        document.getElementById('hiddenSide').value = sideVal;
        document.getElementById('summarySideText').textContent = `${sideVal} leg`;
    });

    // Reset button
    document.getElementById('resetDesignBtn').addEventListener('click', function() {
        colors.socket = '#0d797f';
        colors.pylon = '#94a3b8';
        colors.foot = '#1e293b';

        if (svgSocket) svgSocket.setAttribute('fill', '#0d797f');
        if (svgFoot) svgFoot.setAttribute('fill', '#1e293b');
        document.getElementById('svgPylonRod1').setAttribute('fill', 'url(#pylonGrad)');
        document.getElementById('svgPylonRod2').setAttribute('fill', 'url(#pylonGrad)');

        updateSwatchSelection(colors[selectedPart]);
        showToast('Design colors reset to default');
    });

    // Save draft button
    document.getElementById('saveDraftBtn').addEventListener('click', function() {
        const draft = {
            colors: colors,
            side: sideSelect.value,
            date: new Date().toISOString()
        };
        localStorage.setItem('prosthecare_leg_draft', JSON.stringify(draft));
        showToast('Design draft saved successfully!');
    });

    // Submit modal handling
    const submitModal = document.getElementById('submitDesignModal');
    const openSubmitBtn = document.getElementById('openSubmitModalBtn');
    const closeSubmitModal = document.getElementById('closeSubmitModal');
    const cancelSubmitBtn = document.getElementById('cancelSubmitModalBtn');

    openSubmitBtn.addEventListener('click', function() {
        document.getElementById('summarySocketColor').textContent = colors.socket;
        submitModal.classList.add('active');
    });

    function hideSubmitModal() {
        submitModal.classList.remove('active');
    }
    closeSubmitModal.addEventListener('click', hideSubmitModal);
    cancelSubmitBtn.addEventListener('click', hideSubmitModal);
    window.addEventListener('click', function(e) {
        if (e.target === submitModal) hideSubmitModal();
    });

    <?php if ($designSubmitted): ?>
    showToast('Design <?= htmlspecialchars($submittedId) ?> submitted to clinic!');
    <?php endif; ?>
});
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
