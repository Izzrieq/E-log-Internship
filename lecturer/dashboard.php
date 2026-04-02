<?php require '../config/config.php'; checkAuth(); if(!isLecturer()) die(); ?>
<?php
/* ── Stats ── */
$res = $conn->query("SELECT COUNT(*) c FROM reports");
$total = $res->fetch_assoc()['c'];

$res = $conn->query("SELECT COUNT(*) c FROM reports WHERE status='pending'");
$pending = $res->fetch_assoc()['c'];

$res = $conn->query("SELECT COUNT(*) c FROM reports WHERE status='approved'");
$approved = $res->fetch_assoc()['c'];

$res = $conn->query("SELECT COUNT(*) c FROM reports WHERE status='rejected'");
$rejected = $res->fetch_assoc()['c'];

/* ── Reports ── */
$reports = [];
$res = $conn->query("SELECT reports.*, users.name FROM reports JOIN users ON users.id = reports.student_id ORDER BY reports.id DESC");
while ($r = $res->fetch_assoc()) $reports[] = $r;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecturer Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Syne:wght@400;600;700;800&family=Epilogue:wght@300;400;500;600&display=swap"
        rel="stylesheet">

    <style>
    :root {
        --navy: #0f172a;
        --navy2: #1e293b;
        --navy3: #273549;
        --slate: #334155;
        --muted: #64748b;
        --border: #e2e8f0;
        --bg: #f8fafc;
        --surface: #ffffff;
        --amber: #f59e0b;
        --teal: #0d9488;
        --rose: #e11d48;
        --sky: #0ea5e9;
        --text: #0f172a;
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'Epilogue', sans-serif;
        background: var(--bg);
        color: var(--text);
        display: flex;
        min-height: 100vh;
        overflow-x: hidden;
    }

    /* ─── SIDEBAR ─── */
    #sidebar {
        width: 260px;
        min-height: 100vh;
        background: var(--navy);
        display: flex;
        flex-direction: column;
        position: fixed;
        left: 0;
        top: 0;
        z-index: 40;
        transition: transform 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #sidebar.collapsed {
        transform: translateX(-260px);
    }

    .sidebar-logo {
        padding: 28px 24px 20px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    }

    .sidebar-logo .role-tag {
        font-family: 'Syne', sans-serif;
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.2em;
        text-transform: uppercase;
        color: var(--amber);
        margin-bottom: 6px;
    }

    .sidebar-logo h1 {
        font-family: 'Syne', sans-serif;
        font-size: 20px;
        font-weight: 800;
        color: #fff;
        line-height: 1.2;
    }

    /* Avatar block */
    .sidebar-avatar {
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 12px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
    }

    .avatar-ring {
        width: 42px;
        height: 42px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--amber), #ef4444);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Syne', sans-serif;
        font-weight: 800;
        font-size: 15px;
        color: #fff;
        flex-shrink: 0;
    }

    .avatar-info small {
        display: block;
        font-size: 10px;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 2px;
    }

    .avatar-info span {
        font-size: 13px;
        font-weight: 600;
        color: #e2e8f0;
    }

    /* Nav */
    .sidebar-nav {
        padding: 16px 12px;
        flex: 1;
    }

    .nav-section-label {
        font-size: 10px;
        font-weight: 700;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: #475569;
        padding: 8px 12px 6px;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        color: #94a3b8;
        font-size: 13.5px;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.2s, color 0.2s;
        position: relative;
        margin-bottom: 2px;
    }

    .nav-link:hover {
        background: rgba(255, 255, 255, 0.06);
        color: #fff;
    }

    .nav-link.active {
        background: rgba(245, 158, 11, 0.12);
        color: var(--amber);
    }

    .nav-link.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        width: 3px;
        height: 60%;
        background: var(--amber);
        border-radius: 0 4px 4px 0;
    }

    .nav-link svg {
        width: 17px;
        height: 17px;
        flex-shrink: 0;
    }

    /* Pending pill in sidebar */
    .nav-badge {
        margin-left: auto;
        background: rgba(245, 158, 11, 0.2);
        color: var(--amber);
        font-size: 10px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 99px;
    }

    /* Logout */
    .sidebar-footer {
        padding: 12px;
        border-top: 1px solid rgba(255, 255, 255, 0.06);
    }

    .logout-btn {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 12px;
        border-radius: 8px;
        color: #f87171;
        font-size: 13px;
        font-weight: 500;
        text-decoration: none;
        transition: background 0.2s;
    }

    .logout-btn:hover {
        background: rgba(239, 68, 68, 0.1);
    }

    .logout-btn svg {
        width: 17px;
        height: 17px;
    }

    /* ─── MAIN ─── */
    #main {
        margin-left: 260px;
        flex: 1;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        transition: margin-left 0.35s cubic-bezier(0.4, 0, 0.2, 1);
    }

    #main.expanded {
        margin-left: 0;
    }

    /* Top bar */
    .topbar {
        background: var(--surface);
        border-bottom: 1px solid var(--border);
        padding: 0 28px;
        height: 60px;
        display: flex;
        align-items: center;
        gap: 16px;
        position: sticky;
        top: 0;
        z-index: 30;
    }

    .toggle-btn {
        background: none;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        color: var(--slate);
        transition: border-color 0.2s, background 0.2s;
    }

    .toggle-btn:hover {
        border-color: var(--navy);
        background: var(--bg);
    }

    .topbar-title {
        font-family: 'Syne', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: var(--text);
        flex: 1;
    }

    .topbar-date {
        font-size: 12px;
        color: var(--muted);
        font-weight: 400;
    }

    /* Content wrapper */
    .content-wrap {
        padding: 32px 28px;
        flex: 1;
    }

    /* Page header */
    .page-header {
        margin-bottom: 28px;
    }

    .page-header .eyebrow {
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.18em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 6px;
    }

    .page-header h2 {
        font-family: 'Syne', sans-serif;
        font-size: 30px;
        font-weight: 800;
        color: var(--text);
        line-height: 1.15;
    }

    /* ─── STAT CARDS ─── */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }

    @media(max-width:900px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media(max-width:500px) {
        .stats-grid {
            grid-template-columns: 1fr;
        }
    }

    .stat-card {
        background: var(--surface);
        border-radius: 14px;
        padding: 22px 22px 18px;
        border: 1px solid var(--border);
        border-top: 3px solid transparent;
        position: relative;
        overflow: hidden;
        transition: transform 0.2s, box-shadow 0.2s;
        animation: fadeUp 0.5s both;
    }

    .stat-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .stat-card.total {
        border-top-color: var(--sky);
    }

    .stat-card.pending {
        border-top-color: var(--amber);
    }

    .stat-card.approved {
        border-top-color: var(--teal);
    }

    .stat-card.rejected {
        border-top-color: var(--rose);
    }

    .stat-card .stat-icon {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
        font-size: 16px;
    }

    .stat-card.total .stat-icon {
        background: rgba(14, 165, 233, 0.1);
        color: var(--sky);
    }

    .stat-card.pending .stat-icon {
        background: rgba(245, 158, 11, 0.1);
        color: var(--amber);
    }

    .stat-card.approved.stat-icon {
        background: rgba(13, 148, 136, 0.1);
        color: var(--teal);
    }

    .stat-card.rejected .stat-icon {
        background: rgba(225, 29, 72, 0.1);
        color: var(--rose);
    }

    .stat-value {
        font-family: 'Syne', sans-serif;
        font-size: 36px;
        font-weight: 800;
        line-height: 1;
        margin-bottom: 4px;
        color: var(--text);
    }

    .stat-label {
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .stat-bar {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--border);
        overflow: hidden;
    }

    .stat-bar-fill {
        height: 100%;
        border-radius: 2px;
        animation: barGrow 1s 0.4s both cubic-bezier(0.4, 0, 0.2, 1);
        transform-origin: left;
    }

    .stat-card.total .stat-bar-fill {
        background: var(--sky);
        width: 100%;
    }

    .stat-card.pending .stat-bar-fill {
        background: var(--amber);
    }

    .stat-card.approved .stat-bar-fill {
        background: var(--teal);
    }

    .stat-card.rejected .stat-bar-fill {
        background: var(--rose);
    }

    @keyframes barGrow {
        from {
            transform: scaleX(0);
        }

        to {
            transform: scaleX(1);
        }
    }

    /* ─── SEARCH / FILTER BAR ─── */
    .table-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 16px;
        flex-wrap: wrap;
    }

    .search-wrap {
        position: relative;
        flex: 1;
        min-width: 200px;
    }

    .search-wrap svg {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        width: 15px;
        height: 15px;
        color: var(--muted);
        pointer-events: none;
    }

    .search-input {
        width: 100%;
        padding: 9px 12px 9px 36px;
        border: 1.5px solid var(--border);
        border-radius: 9px;
        font-family: 'Epilogue', sans-serif;
        font-size: 13px;
        color: var(--text);
        background: var(--surface);
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .search-input:focus {
        border-color: var(--navy);
        box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.06);
    }

    .filter-select {
        padding: 9px 14px;
        border: 1.5px solid var(--border);
        border-radius: 9px;
        font-family: 'Epilogue', sans-serif;
        font-size: 13px;
        color: var(--text);
        background: var(--surface);
        outline: none;
        cursor: pointer;
        transition: border-color 0.2s;
    }

    .filter-select:focus {
        border-color: var(--navy);
    }

    /* ─── TABLE CARD ─── */
    .table-card {
        background: var(--surface);
        border-radius: 16px;
        border: 1px solid var(--border);
        overflow: hidden;
        animation: fadeUp 0.5s 0.25s both;
    }

    .table-card-header {
        padding: 18px 22px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .table-card-header h3 {
        font-family: 'Syne', sans-serif;
        font-size: 16px;
        font-weight: 700;
        color: var(--text);
        flex: 1;
    }

    .count-pill {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 99px;
        padding: 3px 10px;
        font-size: 12px;
        font-weight: 600;
        color: var(--muted);
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead tr {
        background: var(--bg);
        border-bottom: 1px solid var(--border);
    }

    thead th {
        padding: 11px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--muted);
    }

    tbody tr {
        border-bottom: 1px solid var(--border);
        transition: background 0.15s;
    }

    tbody tr:last-child {
        border-bottom: none;
    }

    tbody tr:hover {
        background: #f1f5f9;
    }

    tbody td {
        padding: 13px 16px;
        font-size: 13.5px;
        vertical-align: middle;
    }

    /* Student cell */
    .student-cell {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .student-dot {
        width: 34px;
        height: 34px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--navy2), var(--slate));
        color: #fff;
        font-family: 'Syne', sans-serif;
        font-size: 12px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .student-name {
        font-weight: 600;
        color: var(--text);
    }

    /* Week badge */
    .week-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 3px 9px;
        font-size: 12px;
        font-weight: 700;
        color: var(--slate);
    }

    /* Status pill */
    .status-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 99px;
        padding: 4px 11px;
        font-size: 11.5px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: capitalize;
    }

    .status-pill .dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
    }

    .pill-none {
        background: #f1f5f9;
        color: #64748b;
    }

    .pill-none .dot {
        background: #94a3b8;
    }

    .pill-pending {
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
    }

    .pill-pending .dot {
        background: var(--amber);
        box-shadow: 0 0 0 3px rgba(245, 158, 11, 0.25);
    }

    .pill-approved {
        background: rgba(13, 148, 136, 0.10);
        color: #0f766e;
    }

    .pill-approved .dot {
        background: var(--teal);
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.2);
    }

    .pill-rejected {
        background: rgba(225, 29, 72, 0.10);
        color: #be123c;
    }

    .pill-rejected .dot {
        background: var(--rose);
        box-shadow: 0 0 0 3px rgba(225, 29, 72, 0.2);
    }

    /* Action row */
    .action-form {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-wrap: nowrap;
    }

    .action-select,
    .action-input {
        padding: 7px 10px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        font-family: 'Epilogue', sans-serif;
        font-size: 12.5px;
        color: var(--text);
        background: var(--surface);
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .action-select {
        min-width: 110px;
        cursor: pointer;
    }

    .action-input {
        flex: 1;
        min-width: 120px;
    }

    .action-select:focus,
    .action-input:focus {
        border-color: var(--navy);
        box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.06);
    }

    .save-btn {
        padding: 7px 16px;
        background: var(--navy);
        color: #fff;
        border: none;
        border-radius: 8px;
        font-family: 'Syne', sans-serif;
        font-size: 12px;
        font-weight: 700;
        cursor: pointer;
        white-space: nowrap;
        transition: background 0.2s, transform 0.15s;
    }

    .save-btn:hover {
        background: var(--slate);
        transform: translateY(-1px);
    }

    .save-btn:active {
        transform: translateY(0);
    }

    /* Empty state */
    .empty-state {
        padding: 60px 20px;
        text-align: center;
        color: var(--muted);
    }

    .empty-state svg {
        width: 48px;
        height: 48px;
        margin: 0 auto 16px;
        opacity: 0.35;
    }

    /* Overlay */
    #overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.35);
        z-index: 35;
    }

    /* Toast */
    #toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        background: var(--navy);
        color: #fff;
        padding: 12px 20px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 500;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.2);
        z-index: 99;
        opacity: 0;
        transform: translateY(10px);
        transition: opacity 0.3s, transform 0.3s;
        pointer-events: none;
    }

    #toast.show {
        opacity: 1;
        transform: translateY(0);
    }

    /* Animations */
    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(18px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .stat-card:nth-child(1) {
        animation-delay: 0.05s;
    }

    .stat-card:nth-child(2) {
        animation-delay: 0.12s;
    }

    .stat-card:nth-child(3) {
        animation-delay: 0.19s;
    }

    .stat-card:nth-child(4) {
        animation-delay: 0.26s;
    }

    /* Responsive */
    @media(max-width:768px) {
        #sidebar {
            transform: translateX(-260px);
        }

        #sidebar.open {
            transform: translateX(0);
        }

        #main {
            margin-left: 0 !important;
        }

        .action-form {
            flex-wrap: wrap;
        }

        thead th:nth-child(4),
        tbody td:nth-child(4) {
            min-width: 280px;
        }
    }
    </style>
</head>

<body>

    <!-- Sidebar -->
    <aside id="sidebar">
        <div class="sidebar-logo">
            <div class="role-tag">Lecturer Panel</div>
            <h1>Review<br>Console</h1>
        </div>

        <div class="sidebar-avatar">
            <div class="avatar-ring">LC</div>
            <div class="avatar-info">
                <small>Signed in as</small>
                <span>Lecturer</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-label">Menu</div>

            <a href="#" class="nav-link active">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0h4" />
                </svg>
                Dashboard
            </a>

            <a href="blogviewer.php" class="nav-link">
                <svg width="16" height="16" fill="none" viewBox="0 0 16 16">
                    <path
                        d="M2 3.5A1.5 1.5 0 013.5 2h9A1.5 1.5 0 0114 3.5v9A1.5 1.5 0 0112.5 14h-9A1.5 1.5 0 012 12.5v-9z"
                        stroke="currentColor" stroke-width="1.5" />
                    <path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
                <span class="nav-link-label">Student Blogs</span>
            </a>

            <a href="studentmanager.php" class="nav-link">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z" />
                </svg>
                Student Manager
                <?php if($pending > 0): ?>
                <span class="nav-badge"><?= $pending ?></span>
                <?php endif; ?>
            </a>
        </nav>

        <div class="sidebar-footer">
            <a href="../auth/logout.php" class="logout-btn">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                Sign Out
            </a>
        </div>
    </aside>

    <!-- Mobile overlay -->
    <div id="overlay" onclick="closeSidebar()"></div>

    <!-- Main -->
    <main id="main">

        <!-- Topbar -->
        <div class="topbar">
            <button class="toggle-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <span class="topbar-title">Lecturer Dashboard</span>
            <span class="topbar-date" id="topDate"></span>
        </div>

        <!-- Content -->
        <div class="content-wrap">

            <!-- Header -->
            <div class="page-header" style="animation: fadeUp 0.45s both;">
                <div class="eyebrow">Overview</div>
                <h2>Report Review Centre</h2>
            </div>

            <!-- Stat Cards -->
            <div class="stats-grid">

                <!-- Total -->
                <div class="stat-card total">
                    <div class="stat-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="stat-value"><?= $total ?></div>
                    <div class="stat-label">Total Reports</div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill" style="width:100%"></div>
                    </div>
                </div>

                <!-- Pending -->
                <?php $pendingPct = $total > 0 ? round(($pending/$total)*100) : 0; ?>
                <div class="stat-card pending">
                    <div class="stat-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="stat-value"><?= $pending ?></div>
                    <div class="stat-label">Awaiting Review</div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill" style="width:<?= $pendingPct ?>%"></div>
                    </div>
                </div>

                <!-- Approved -->
                <?php $approvedPct = $total > 0 ? round(($approved/$total)*100) : 0; ?>
                <div class="stat-card approved">
                    <div class="stat-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="stat-value"><?= $approved ?></div>
                    <div class="stat-label">Approved</div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill" style="width:<?= $approvedPct ?>%"></div>
                    </div>
                </div>

                <!-- Rejected -->
                <?php $rejectedPct = $total > 0 ? round(($rejected/$total)*100) : 0; ?>
                <div class="stat-card rejected">
                    <div class="stat-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="stat-value"><?= $rejected ?></div>
                    <div class="stat-label">Rejected</div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill" style="width:<?= $rejectedPct ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Table toolbar -->
            <div class="table-toolbar">
                <div class="search-wrap">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
                    </svg>
                    <input type="text" class="search-input" id="searchInput" placeholder="Search student name…"
                        oninput="filterTable()">
                </div>
                <select class="filter-select" id="filterStatus" onchange="filterTable()">
                    <option value="all">All Statuses</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <!-- Table -->
            <div class="table-card">
                <div class="table-card-header">
                    <h3>Submission Reports</h3>
                    <span class="count-pill" id="rowCount"><?= count($reports) ?> entries</span>
                </div>

                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Week</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="tableBody">
                            <?php foreach($reports as $r):
              $s = $r['status'];
              $pillClass = match($s) {
                'pending'  => 'pill-pending',
                'approved' => 'pill-approved',
                'rejected' => 'pill-rejected',
                default    => 'pill-none',
              };
              $initials = implode('', array_map(fn($w) => strtoupper($w[0]), array_slice(explode(' ', $r['name']), 0, 2)));
            ?>
                            <tr data-name="<?= strtolower(htmlspecialchars($r['name'])) ?>" data-status="<?= $s ?>">

                                <!-- Student -->
                                <td>
                                    <div class="student-cell">
                                        <div class="student-dot"><?= $initials ?></div>
                                        <span class="student-name"><?= htmlspecialchars($r['name']) ?></span>
                                    </div>
                                </td>

                                <!-- Week -->
                                <td>
                                    <span class="week-badge">
                                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                                            <line x1="16" y1="2" x2="16" y2="6" />
                                            <line x1="8" y1="2" x2="8" y2="6" />
                                            <line x1="3" y1="10" x2="21" y2="10" />
                                        </svg>
                                        Week <?= $r['week'] ?>
                                    </span>
                                </td>

                                <!-- Status -->
                                <td>
                                    <span class="status-pill <?= $pillClass ?>">
                                        <span class="dot"></span>
                                        <?= ucfirst($s ?: 'none') ?>
                                    </span>
                                </td>

                                <!-- Action -->
                                <td>
                                    <form action="review.php" method="POST" class="action-form"
                                        onsubmit="handleSubmit(event, this)">
                                        <input type="hidden" name="id" value="<?= $r['id'] ?>">
                                        <select name="status" class="action-select">
                                            <option value="approved" <?= $s=='approved'?'selected':'' ?>>✓ Approve
                                            </option>
                                            <option value="rejected" <?= $s=='rejected'?'selected':'' ?>>✗ Reject
                                            </option>
                                        </select>
                                        <input type="text" name="comment" class="action-input"
                                            placeholder="Add a comment…">
                                        <button type="submit" class="save-btn">Save</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                            <?php if(empty($reports)): ?>
                            <tr>
                                <td colspan="4">
                                    <div class="empty-state">
                                        <svg fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414A1 1 0 0119 9.414V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p style="font-weight:600; margin-bottom:4px;">No reports yet</p>
                                        <p style="font-size:12px;">Reports will appear here once students upload them.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div><!-- /table-card -->

        </div><!-- /content-wrap -->
    </main>

    <!-- Toast -->
    <div id="toast"></div>

    <script>
    /* ── DATE ── */
    const d = new Date();
    document.getElementById('topDate').textContent =
        d.toLocaleDateString('en-GB', {
            weekday: 'long',
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        });

    /* ── SIDEBAR ── */
    const sidebar = document.getElementById('sidebar');
    const mainEl = document.getElementById('main');
    const overlay = document.getElementById('overlay');
    let isMobile = window.innerWidth <= 768;

    function toggleSidebar() {
        if (isMobile) {
            sidebar.classList.toggle('open');
            overlay.style.display = sidebar.classList.contains('open') ? 'block' : 'none';
        } else {
            sidebar.classList.toggle('collapsed');
            mainEl.classList.toggle('expanded');
        }
    }

    function closeSidebar() {
        sidebar.classList.remove('open');
        overlay.style.display = 'none';
    }
    window.addEventListener('resize', () => {
        isMobile = window.innerWidth <= 768;
    });

    /* ── SEARCH / FILTER ── */
    function filterTable() {
        const q = document.getElementById('searchInput').value.toLowerCase();
        const status = document.getElementById('filterStatus').value;
        const rows = document.querySelectorAll('#tableBody tr[data-name]');
        let visible = 0;
        rows.forEach(row => {
            const nameMatch = row.dataset.name.includes(q);
            const statusMatch = status === 'all' || row.dataset.status === status;
            const show = nameMatch && statusMatch;
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        document.getElementById('rowCount').textContent = visible + ' entries';
    }

    /* ── TOAST ── */
    function showToast(msg) {
        const t = document.getElementById('toast');
        t.textContent = msg;
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 3000);
    }

    /* ── FORM SUBMIT (optional AJAX feel) ── */
    function handleSubmit(e, form) {
        // Remove comment to use AJAX instead of full page reload:
        // e.preventDefault();
        // fetch('review.php', { method:'POST', body: new FormData(form) })
        //   .then(() => showToast('Report updated successfully'));
        showToast('Saving changes…');
    }
    </script>

</body>

</html>