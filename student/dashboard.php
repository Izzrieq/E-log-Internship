<?php require '../config/config.php'; checkAuth(); if(!isStudent()) die(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600&display=swap"
        rel="stylesheet">

    <style>
    :root {
        --ink: #0d0f14;
        --ink2: #161a24;
        --ink3: #1e2330;
        --border: rgba(255, 255, 255, 0.07);
        --amber: #f0a500;
        --amber2: #ffc84a;
        --muted: #6b7280;
        --text: #e8e9ec;
        --text2: #9ca3af;
        --sw-open: 256px;
        --sw-collapsed: 64px;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
    }

    html,
    body {
        height: 100%;
        margin: 0;
    }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--ink);
        color: var(--text);
        overflow-x: hidden;
        display: flex;
    }

    body::before {
        content: '';
        position: fixed;
        inset: 0;
        background:
            radial-gradient(ellipse 80% 50% at 70% -10%, rgba(240, 165, 0, 0.08) 0%, transparent 60%),
            radial-gradient(ellipse 50% 60% at -10% 80%, rgba(99, 102, 241, 0.06) 0%, transparent 60%);
        pointer-events: none;
        z-index: 0;
    }

    /* ══ SIDEBAR ══ */
    #sidebar {
        position: fixed;
        top: 0;
        left: 0;
        bottom: 0;
        width: var(--sw-open);
        z-index: 40;
        display: flex;
        flex-direction: column;
        padding: 20px 16px;
        background: linear-gradient(180deg, var(--ink2) 0%, var(--ink) 100%);
        border-right: 1px solid var(--border);
        transition: width 0.3s cubic-bezier(.16, 1, .3, 1);
        overflow: hidden;
    }

    #sidebar.collapsed {
        width: var(--sw-collapsed);
    }

    @media (max-width: 767px) {
        #sidebar {
            width: var(--sw-open) !important;
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(.16, 1, .3, 1);
        }

        #sidebar.mobile-open {
            transform: translateX(0);
        }
    }

    .sidebar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 28px;
        min-height: 36px;
        flex-shrink: 0;
    }

    .brand-wrap {
        display: flex;
        align-items: center;
        gap: 8px;
        overflow: hidden;
        flex: 1;
    }

    .brand-icon {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(240, 165, 0, 0.18);
        flex-shrink: 0;
    }

    .brand-text {
        font-family: 'DM Serif Display', serif;
        font-size: 1rem;
        color: var(--amber);
        white-space: nowrap;
        opacity: 1;
        transition: opacity 0.2s, width 0.2s;
        overflow: hidden;
    }

    #collapseBtn {
        width: 28px;
        height: 28px;
        border-radius: 8px;
        border: 1px solid var(--border);
        background: rgba(255, 255, 255, 0.04);
        color: var(--text2);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        transition: background 0.2s, color 0.2s, transform 0.3s;
    }

    #collapseBtn:hover {
        background: rgba(240, 165, 0, 0.1);
        border-color: rgba(240, 165, 0, 0.3);
        color: var(--amber);
    }

    #sidebar.collapsed #collapseBtn {
        transform: rotate(180deg);
    }

    .user-card {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--border);
        margin-bottom: 20px;
        overflow: hidden;
        flex-shrink: 0;
        transition: padding 0.3s;
    }

    .avatar {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg, #f0a500, #e09500);
        color: #0d0f14;
        font-weight: 700;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .user-info {
        overflow: hidden;
        white-space: nowrap;
        transition: opacity 0.2s, width 0.2s;
    }

    .user-name {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text);
    }

    .user-role {
        font-size: 0.68rem;
        color: var(--muted);
    }

    #sidebar.collapsed .brand-text,
    #sidebar.collapsed .user-info,
    #sidebar.collapsed .nav-label,
    #sidebar.collapsed .sidebar-footer-label {
        opacity: 0;
        width: 0;
        pointer-events: none;
    }

    #sidebar.collapsed .user-card {
        padding: 10px 6px;
        justify-content: center;
    }

    #sidebar.collapsed .nav-link {
        justify-content: center;
        padding: 11px 0;
    }

    #sidebar.collapsed .logout-link {
        justify-content: center;
        padding: 11px 0;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 11px 12px;
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text2);
        text-decoration: none;
        transition: background 0.2s, color 0.2s, padding 0.3s;
        position: relative;
        overflow: hidden;
        white-space: nowrap;
    }

    .nav-link svg {
        flex-shrink: 0;
    }

    .nav-link:hover {
        color: var(--text);
        background: rgba(255, 255, 255, 0.06);
    }

    .nav-link.active {
        color: var(--amber);
        background: rgba(240, 165, 0, 0.1);
    }

    .nav-link.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 3px;
        background: var(--amber);
        border-radius: 0 3px 3px 0;
    }

    .nav-label {
        transition: opacity 0.2s, width 0.2s;
        overflow: hidden;
    }

    .logout-link {
        display: flex;
        align-items: center;
        gap: 11px;
        padding: 11px 12px;
        border-radius: 10px;
        font-size: 0.875rem;
        font-weight: 500;
        color: var(--text2);
        text-decoration: none;
        transition: background 0.2s, color 0.2s, padding 0.3s;
        white-space: nowrap;
    }

    .logout-link svg {
        flex-shrink: 0;
    }

    .logout-link:hover {
        color: #f87171;
        background: rgba(248, 113, 113, 0.1);
    }

    .nav-link[data-tip],
    .logout-link[data-tip] {
        position: relative;
    }

    #sidebar.collapsed .nav-link[data-tip]:hover::after,
    #sidebar.collapsed .logout-link[data-tip]:hover::after {
        content: attr(data-tip);
        position: absolute;
        left: calc(100% + 12px);
        top: 50%;
        transform: translateY(-50%);
        background: var(--ink3);
        border: 1px solid var(--border);
        color: var(--text);
        font-size: 0.75rem;
        font-weight: 500;
        padding: 5px 10px;
        border-radius: 7px;
        white-space: nowrap;
        pointer-events: none;
        z-index: 100;
        box-shadow: 0 4px 14px rgba(0, 0, 0, 0.4);
    }

    /* ══ MAIN ══ */
    #mainContent {
        flex: 1;
        margin-left: var(--sw-open);
        transition: margin-left 0.3s cubic-bezier(.16, 1, .3, 1);
        position: relative;
        z-index: 10;
        min-height: 100vh;
        padding: 36px 32px 60px;
    }

    body.sidebar-collapsed #mainContent {
        margin-left: var(--sw-collapsed);
    }

    @media (max-width: 767px) {
        #mainContent {
            margin-left: 0 !important;
            padding: 24px 18px 60px;
        }

        #mobileToggle {
            display: flex !important;
        }
    }

    #mobileToggle {
        display: none;
        position: fixed;
        top: 14px;
        left: 14px;
        z-index: 50;
        width: 38px;
        height: 38px;
        border-radius: 10px;
        border: 1px solid var(--border);
        background: var(--ink2);
        color: var(--text);
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: background 0.2s, color 0.2s;
    }

    #mobileToggle:hover {
        background: var(--ink3);
        color: var(--amber);
        border-color: rgba(240, 165, 0, 0.3);
    }

    #overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(2px);
        z-index: 30;
    }

    /* ══ STAT CARDS ══ */
    .stat-card {
        background: var(--ink2);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 24px;
        position: relative;
        overflow: hidden;
        transition: transform 0.25s, border-color 0.25s;
    }

    .stat-card::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(240, 165, 0, 0.4), transparent);
    }

    .stat-card:hover {
        transform: translateY(-2px);
        border-color: rgba(240, 165, 0, 0.2);
    }

    .stat-label {
        font-size: 0.72rem;
        font-weight: 600;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 8px;
    }

    .stat-value {
        font-family: 'DM Serif Display', serif;
        font-size: 2.4rem;
        line-height: 1;
        color: var(--text);
    }

    /* ══ PROGRESS ══ */
    .progress-track {
        background: rgba(255, 255, 255, 0.06);
        border-radius: 100px;
        height: 6px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        border-radius: 100px;
        background: linear-gradient(90deg, var(--amber), var(--amber2));
        box-shadow: 0 0 12px rgba(240, 165, 0, 0.4);
        transition: width 1.2s cubic-bezier(.16, 1, .3, 1);
    }

    /* ══ WEEK CARDS ══ */
    .week-card {
        background: var(--ink2);
        border: 1px solid var(--border);
        border-radius: 14px;
        padding: 14px 12px;
        cursor: pointer;
        transition: all 0.22s cubic-bezier(.16, 1, .3, 1);
        position: relative;
        overflow: hidden;
        animation: fadeUp 0.4s cubic-bezier(.16, 1, .3, 1) forwards;
        opacity: 0;
    }

    .week-card::before {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0;
        transition: opacity 0.2s;
        background: radial-gradient(circle at center, rgba(240, 165, 0, 0.08), transparent 70%);
    }

    .week-card:hover {
        transform: translateY(-4px) scale(1.01);
        border-color: rgba(240, 165, 0, 0.25);
        box-shadow: 0 12px 30px rgba(0, 0, 0, 0.4);
    }

    .week-card:hover::before {
        opacity: 1;
    }

    .week-card.status-pending {
        border-color: rgba(251, 191, 36, 0.35);
    }

    .week-card.status-approved {
        border-color: rgba(52, 211, 153, 0.35);
    }

    .week-card.status-rejected {
        border-color: rgba(248, 113, 113, 0.35);
    }

    /* ── Approved: locked state ── */
    .week-card.status-approved {
        cursor: not-allowed;
        /* subtle green-tinted glass lock feel */
        background: linear-gradient(135deg, #161a24 0%, #0e1f18 100%);
    }

    /* Remove hover lift & glow for approved cards */
    .week-card.status-approved:hover {
        transform: none;
        box-shadow: none;
        border-color: rgba(52, 211, 153, 0.35);
    }

    .week-card.status-approved:hover::before {
        opacity: 0;
    }

    /* Lock icon badge on approved cards */
    .approved-lock {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 5px;
        background: rgba(52, 211, 153, 0.12);
        color: #34d399;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
        margin-right: 5px;
        flex-shrink: 0;
    }

    .dot-none {
        background: #374151;
    }

    .dot-pending {
        background: #fbbf24;
        box-shadow: 0 0 6px rgba(251, 191, 36, 0.6);
    }

    .dot-approved {
        background: #34d399;
        box-shadow: 0 0 6px rgba(52, 211, 153, 0.6);
    }

    .dot-rejected {
        background: #f87171;
        box-shadow: 0 0 6px rgba(248, 113, 113, 0.6);
    }

    .week-number {
        font-family: 'DM Serif Display', serif;
        font-size: 1.2rem;
        color: var(--text);
        line-height: 1;
        margin-bottom: 8px;
    }

    .week-status-label {
        font-size: 0.62rem;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
    }

    /* ══ MODAL ══ */
    #modal {
        backdrop-filter: blur(8px);
        background: rgba(0, 0, 0, 0.65);
    }

    .modal-box {
        background: var(--ink2);
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 20px;
        padding: 32px;
        width: 100%;
        max-width: 420px;
        position: relative;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.6);
        animation: modalIn 0.3s cubic-bezier(.16, 1, .3, 1);
    }

    @keyframes modalIn {
        from {
            transform: translateY(20px) scale(0.97);
            opacity: 0;
        }

        to {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
    }

    .modal-title {
        font-family: 'DM Serif Display', serif;
        font-size: 1.6rem;
        margin-bottom: 6px;
    }

    /* Approved modal: locked file zone */
    .file-zone {
        border: 2px dashed rgba(255, 255, 255, 0.12);
        border-radius: 12px;
        padding: 28px;
        text-align: center;
        transition: all 0.2s;
        cursor: pointer;
        position: relative;
        overflow: hidden;
    }

    .file-zone:not(.locked):hover {
        border-color: rgba(240, 165, 0, 0.4);
        background: rgba(240, 165, 0, 0.04);
    }

    .file-zone.locked {
        cursor: not-allowed;
        opacity: 0.5;
        pointer-events: none;
        border-color: rgba(52, 211, 153, 0.2);
        background: rgba(52, 211, 153, 0.03);
    }

    .file-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }

    /* Approved notice banner */
    .approved-notice {
        display: flex;
        align-items: center;
        gap: 10px;
        background: rgba(52, 211, 153, 0.08);
        border: 1px solid rgba(52, 211, 153, 0.25);
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 20px;
        font-size: 0.82rem;
        color: #34d399;
        font-weight: 500;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--amber), #e09500);
        color: #0d0f14;
        font-weight: 700;
        font-size: 0.875rem;
        letter-spacing: 0.03em;
        padding: 13px 24px;
        border-radius: 10px;
        width: 100%;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 16px rgba(240, 165, 0, 0.25);
    }

    .btn-primary:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(240, 165, 0, 0.35);
    }

    .btn-primary:disabled {
        opacity: 0.35;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }

    .btn-ghost {
        background: transparent;
        color: var(--muted);
        font-size: 0.875rem;
        padding: 11px 24px;
        border-radius: 10px;
        width: 100%;
        border: 1px solid var(--border);
        cursor: pointer;
        transition: all 0.2s;
    }

    .btn-ghost:hover {
        background: rgba(255, 255, 255, 0.04);
        color: var(--text);
    }

    /* ══ ANIMATIONS ══ */
    .fade-up {
        opacity: 0;
        transform: translateY(16px);
        animation: fadeUp 0.5s cubic-bezier(.16, 1, .3, 1) forwards;
    }

    @keyframes fadeUp {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.75rem;
        color: var(--muted);
    }

    .section-heading {
        font-family: 'DM Serif Display', serif;
        font-size: 1.9rem;
        line-height: 1.1;
    }
    </style>
</head>

<body>

    <?php
    $id      = $_SESSION['user']['id'];
    $total   = 20; /* ← changed from 14 to 20 */

    $reports = [];
    $res = $conn->query("SELECT week, status FROM reports WHERE student_id='$id'");
    while ($r = $res->fetch_assoc()) {
        $reports[$r['week']] = $r['status'];
    }

    $done    = count($reports);
    $percent = ($done / $total) * 100;

    $approved = 0; $pending = 0; $rejected = 0;
    foreach ($reports as $w => $s) {
        if ($s === 'approved') $approved++;
        if ($s === 'pending')  $pending++;
        if ($s === 'rejected') $rejected++;
    }

    $studentName = $_SESSION['user']['name'] ?? 'Student';
    $firstName   = explode(' ', trim($studentName))[0];
    $initials    = strtoupper(substr($firstName, 0, 1));
?>

    <!-- MOBILE HAMBURGER -->
    <button id="mobileToggle" onclick="mobileOpen()" aria-label="Open menu">
        <svg width="16" height="13" viewBox="0 0 16 13" fill="none">
            <rect width="16" height="2" rx="1" fill="currentColor" />
            <rect y="5.5" width="11" height="2" rx="1" fill="currentColor" />
            <rect y="11" width="16" height="2" rx="1" fill="currentColor" />
        </svg>
    </button>

    <!-- SIDEBAR -->
    <aside id="sidebar">
        <div class="sidebar-header">
            <div class="brand-wrap">
                <div class="brand-icon">
                    <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                        <path d="M7 1L9.2 5.5L14 6.2L10.5 9.6L11.4 14L7 11.7L2.6 14L3.5 9.6L0 6.2L4.8 5.5L7 1Z"
                            fill="#f0a500" />
                    </svg>
                </div>
                <span class="brand-text">E-Log Intern</span>
            </div>
            <button id="collapseBtn" onclick="toggleCollapse()" aria-label="Collapse sidebar">
                <svg width="14" height="14" viewBox="0 0 14 14" fill="none">
                    <path d="M9 11L5 7l4-4" stroke="currentColor" stroke-width="1.7" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
        </div>

        <div class="user-card">
            <div class="avatar"><?= $initials ?></div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($studentName) ?></div>
                <div class="user-role">Internship Trainee</div>
            </div>
        </div>

        <nav style="display:flex; flex-direction:column; gap:4px; flex:1;">
            <a href="#" class="nav-link active" data-tip="Dashboard">
                <svg width="16" height="16" fill="none" viewBox="0 0 16 16" style="flex-shrink:0">
                    <rect x="1" y="1" width="6" height="6" rx="1.5" fill="currentColor" opacity=".8" />
                    <rect x="9" y="1" width="6" height="6" rx="1.5" fill="currentColor" opacity=".8" />
                    <rect x="1" y="9" width="6" height="6" rx="1.5" fill="currentColor" opacity=".8" />
                    <rect x="9" y="9" width="6" height="6" rx="1.5" fill="currentColor" opacity=".8" />
                </svg>
                <span class="nav-label">Dashboard</span>
            </a>
            <a href="blog.php" class="nav-link" data-tip="Blog">
                <svg width="16" height="16" fill="none" viewBox="0 0 16 16" style="flex-shrink:0">
                    <path
                        d="M2 3.5A1.5 1.5 0 013.5 2h9A1.5 1.5 0 0114 3.5v9A1.5 1.5 0 0112.5 14h-9A1.5 1.5 0 012 12.5v-9z"
                        stroke="currentColor" stroke-width="1.5" />
                    <path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
                <span class="nav-label">Blog</span>
            </a>
        </nav>

        <a href="../auth/logout.php" class="logout-link" data-tip="Sign Out"
            style="margin-top:8px; border-top:1px solid var(--border); padding-top:16px;">
            <svg width="16" height="16" fill="none" viewBox="0 0 16 16" style="flex-shrink:0">
                <path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3M10 11l3-3-3-3M13 8H6" stroke="currentColor"
                    stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
            </svg>
            <span class="nav-label sidebar-footer-label">Sign Out</span>
        </a>
    </aside>

    <div id="overlay" onclick="mobileClose()"></div>

    <!-- MAIN CONTENT -->
    <main id="mainContent">

        <!-- Header -->
        <div class="fade-up mb-8" style="animation-delay:0.05s; padding-top:4px;">
            <p
                style="font-size:0.75rem; color:var(--muted); letter-spacing:0.1em; text-transform:uppercase; margin-bottom:4px;">
                Welcome back</p>
            <h1 class="section-heading">
                <?= htmlspecialchars($firstName) ?>'s Reports<span style="color:var(--amber)">.</span>
            </h1>
        </div>

        <!-- Stat Cards -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6 fade-up" style="animation-delay:0.1s">
            <div class="stat-card">
                <div class="stat-label">Total Weeks</div>
                <div class="stat-value"><?= $total ?></div>
                <div style="font-size:0.75rem; color:var(--muted); margin-top:8px;">internship period</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Submitted</div>
                <div class="stat-value"><?= $done ?></div>
                <div style="font-size:0.75rem; color:var(--muted); margin-top:8px;"><?= $total - $done ?> remaining
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Approved</div>
                <div class="stat-value" style="color:#34d399"><?= $approved ?></div>
                <div style="font-size:0.75rem; color:var(--muted); margin-top:8px;">confirmed reports</div>
            </div>
            <div class="stat-card">
                <div class="stat-label">Completion</div>
                <div class="stat-value" style="color:var(--amber)"><?= round($percent) ?><span
                        style="font-size:1.2rem; color:var(--muted)">%</span></div>
                <div style="font-size:0.75rem; color:var(--muted); margin-top:8px;">overall progress</div>
            </div>
        </div>

        <!-- Progress Panel -->
        <div class="fade-up mb-8"
            style="animation-delay:0.15s; background:var(--ink2); border:1px solid var(--border); border-radius:16px; padding:20px 24px;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
                <div>
                    <p
                        style="font-size:0.72rem; color:var(--muted); letter-spacing:0.08em; text-transform:uppercase; margin-bottom:2px;">
                        Submission Progress</p>
                    <p style="font-size:0.85rem; color:var(--text2)">
                        <strong style="color:var(--text)"><?= $done ?></strong> of
                        <strong style="color:var(--text)"><?= $total ?></strong> weekly reports uploaded
                    </p>
                </div>
                <div style="font-family:'DM Serif Display',serif; font-size:1.4rem; color:var(--amber)">
                    <?= round($percent) ?>%</div>
            </div>
            <div class="progress-track">
                <div class="progress-fill" id="progressFill" style="width:0%"></div>
            </div>
            <div style="display:flex; flex-wrap:wrap; gap:16px; margin-top:14px;">
                <div class="legend-item"><span class="status-dot dot-none"></span> Not submitted</div>
                <div class="legend-item"><span class="status-dot dot-pending"></span> Pending (<?= $pending ?>)</div>
                <div class="legend-item"><span class="status-dot dot-approved"></span> Approved (<?= $approved ?>)</div>
                <div class="legend-item"><span class="status-dot dot-rejected"></span> Rejected (<?= $rejected ?>)</div>
            </div>
        </div>

        <!-- Weekly Cards -->
        <div class="fade-up" style="animation-delay:0.2s; margin-bottom:16px;">
            <p style="font-size:0.72rem; color:var(--muted); letter-spacing:0.1em; text-transform:uppercase;">Weekly
                Reports</p>
        </div>

        <!-- ↓ grid changed to lg:grid-cols-10 to better accommodate 20 cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 lg:grid-cols-10 gap-3">
            <?php
            for ($i = 1; $i <= 20; $i++) {
                $status      = $reports[$i] ?? 'none';
                $dotClass    = "dot-$status";
                $statusLabel = ['none' => 'Not Submitted', 'pending' => 'Under Review', 'approved' => 'Approved',  'rejected' => 'Resubmit'][$status];
                $statusColor = ['none' => 'var(--text2)',  'pending' => '#fbbf24',       'approved' => '#34d399',   'rejected' => '#f87171'][$status];
                $delay       = 0.22 + ($i * 0.025);

                if ($status === 'approved') {
                    /* Approved: no onclick, cursor not-allowed via CSS, shows lock icon */
                    $actionAttr = "title='This report has been approved and is locked.'";
                    $iconHtml   = '<div class="approved-lock">
                                        <svg width="10" height="10" fill="none" viewBox="0 0 12 12">
                                            <rect x="2" y="5" width="8" height="6" rx="1.5" stroke="#34d399" stroke-width="1.4"/>
                                            <path d="M4 5V3.5a2 2 0 114 0V5" stroke="#34d399" stroke-width="1.4" stroke-linecap="round"/>
                                        </svg>
                                   </div>';
                } else {
                    /* Pending / Rejected / None: clickable */
                    $actionAttr = "onclick=\"openModal($i)\"";
                    $iconHtml   = $status === 'none'
                        ? '<svg width="13" height="13" fill="none" viewBox="0 0 14 14" style="color:var(--muted)">
                               <path d="M7 9V3M4 6l3-3 3 3" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/>
                               <path d="M2 11h10" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/>
                           </svg>'
                        : '';
                }

                echo "
                <div class='week-card status-$status' $actionAttr style='animation-delay:{$delay}s'>
                    <div class='week-number'>W{$i}</div>
                    <div style='display:flex; align-items:center; margin-bottom:6px;'>
                        <span class='status-dot $dotClass'></span>
                        <span class='week-status-label' style='color:$statusColor'>$statusLabel</span>
                    </div>
                    <div>$iconHtml</div>
                </div>";
            }
            ?>
        </div>

    </main>

    <!-- MODAL -->
    <div id="modal" class="fixed inset-0 hidden flex items-center justify-center z-50 p-4">
        <div class="modal-box">
            <button onclick="closeModal()"
                style="position:absolute; top:16px; right:16px; width:32px; height:32px; border-radius:8px;
                           background:rgba(255,255,255,0.06); border:1px solid var(--border); color:var(--text2);
                           cursor:pointer; display:flex; align-items:center; justify-content:center; transition:all 0.2s;"
                onmouseover="this.style.background='rgba(255,255,255,0.1)'"
                onmouseout="this.style.background='rgba(255,255,255,0.06)'">
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                    <path d="M1 1l10 10M11 1L1 11" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                </svg>
            </button>

            <p
                style="font-size:0.72rem; color:var(--muted); letter-spacing:0.1em; text-transform:uppercase; margin-bottom:4px;">
                Report Upload</p>
            <h3 class="modal-title" id="modalTitle">Week —</h3>
            <p style="font-size:0.83rem; color:var(--text2); margin-bottom:20px;">Submit your internship report for this
                week.</p>

            <!-- Approved-only notice (hidden by default, shown via JS) -->
            <div id="approvedNotice" class="approved-notice" style="display:none;">
                <svg width="16" height="16" fill="none" viewBox="0 0 16 16">
                    <rect x="3" y="7" width="10" height="8" rx="2" stroke="#34d399" stroke-width="1.4" />
                    <path d="M5.5 7V5a2.5 2.5 0 015 0v2" stroke="#34d399" stroke-width="1.4" stroke-linecap="round" />
                    <circle cx="8" cy="11" r="1" fill="#34d399" />
                </svg>
                This report has been <strong style="margin-left:3px;">approved</strong> — no changes allowed.
            </div>

            <form action="upload.php" method="POST" enctype="multipart/form-data" id="uploadForm">
                <input type="hidden" name="week" id="weekInput">

                <div class="file-zone mb-5" id="fileZone">
                    <input type="file" name="file" id="fileInput" required onchange="updateFileName(this)">
                    <svg width="28" height="28" fill="none" viewBox="0 0 28 28"
                        style="margin:0 auto 10px; display:block; color:var(--muted)">
                        <path d="M14 18V8M9 13l5-5 5 5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                            stroke-linejoin="round" />
                        <path d="M5 22h18" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                    </svg>
                    <p id="fileName" style="font-size:0.8rem; color:var(--text2); font-weight:500;">Click or drag to
                        upload</p>
                    <p style="font-size:0.7rem; color:var(--muted); margin-top:4px;">PDF, DOCX up to 10MB</p>
                </div>

                <button type="submit" class="btn-primary mb-2" id="uploadBtn">Upload Report →</button>
            </form>
            <button onclick="closeModal()" class="btn-ghost">Cancel</button>
        </div>
    </div>

    <script>
    // ══ SIDEBAR — DESKTOP COLLAPSE ══
    const sidebar = document.getElementById('sidebar');
    const body = document.body;
    const STORAGE_KEY = 'sidebarCollapsed';

    if (localStorage.getItem(STORAGE_KEY) === 'true') {
        sidebar.classList.add('collapsed');
        body.classList.add('sidebar-collapsed');
    }

    function toggleCollapse() {
        const isCollapsed = sidebar.classList.toggle('collapsed');
        body.classList.toggle('sidebar-collapsed', isCollapsed);
        localStorage.setItem(STORAGE_KEY, isCollapsed);
    }

    // ══ SIDEBAR — MOBILE ══
    const overlay = document.getElementById('overlay');

    function mobileOpen() {
        sidebar.classList.add('mobile-open');
        overlay.style.display = 'block';
    }

    function mobileClose() {
        sidebar.classList.remove('mobile-open');
        overlay.style.display = 'none';
    }

    function checkMobile() {
        const isMobile = window.innerWidth < 768;
        document.getElementById('collapseBtn').style.display = isMobile ? 'none' : 'flex';
        document.getElementById('mobileToggle').style.display = isMobile ? 'flex' : 'none';
    }
    checkMobile();
    window.addEventListener('resize', checkMobile);

    // ══ MODAL ══
    // PHP passes approved weeks into JS so openModal() can check on the client side too
    const approvedWeeks = new Set(<?= json_encode(
        array_keys(array_filter($reports, fn($s) => $s === 'approved'))
    ) ?>);

    function openModal(week) {
        // Guard: never open for approved weeks (belt-and-suspenders)
        if (approvedWeeks.has(week)) return;

        const isApproved = false; // reached only for non-approved weeks

        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('weekInput').value = week;
        document.getElementById('modalTitle').textContent = `Week ${week}`;
        document.getElementById('fileName').textContent = 'Click or drag to upload';
        document.getElementById('fileZone').style.borderColor = '';

        // Reset to unlocked state (safe default)
        const fileZone = document.getElementById('fileZone');
        const fileInput = document.getElementById('fileInput');
        const uploadBtn = document.getElementById('uploadBtn');
        const notice = document.getElementById('approvedNotice');

        fileZone.classList.remove('locked');
        fileInput.disabled = false;
        fileInput.required = true;
        uploadBtn.disabled = false;
        notice.style.display = 'none';
        document.getElementById('uploadForm').style.display = '';
    }

    function closeModal() {
        document.getElementById('modal').classList.add('hidden');
    }

    document.getElementById('modal').addEventListener('click', function(e) {
        if (e.target === this) closeModal();
    });

    function updateFileName(input) {
        const name = input.files[0]?.name;
        if (name) {
            document.getElementById('fileName').textContent = name;
            document.getElementById('fileZone').style.borderColor = 'rgba(240,165,0,0.5)';
        }
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeModal();
    });

    // ══ PROGRESS BAR ══
    window.addEventListener('load', () => {
        setTimeout(() => {
            document.getElementById('progressFill').style.width = '<?= $percent ?>%';
        }, 400);
    });
    </script>
</body>

</html>