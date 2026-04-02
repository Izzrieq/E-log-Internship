<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require '../config/config.php';
checkAuth();
if (!isLecturer()) die();

/* ── AJAX DELETE HANDLER ── */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_blog') {
    header('Content-Type: application/json');
    $id = intval($_POST['id'] ?? 0);
    if ($id <= 0) { echo json_encode(['success' => false, 'message' => 'Invalid ID']); exit; }

    /* Fetch image path before deleting */
    $stmt = $conn->prepare("SELECT image FROM blogs WHERE id = ?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) { echo json_encode(['success' => false, 'message' => 'Post not found']); exit; }

    /* Delete from DB */
    $del = $conn->prepare("DELETE FROM blogs WHERE id = ?");
    $del->bind_param('i', $id);
    if ($del->execute()) {
        /* Optionally remove the image file */
        $imgPath = '../' . $row['image'];
        if (file_exists($imgPath)) @unlink($imgPath);
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Database error']);
    }
    $del->close();
    exit;
}

/* ── Fetch all blog posts with student name ── */
$posts = [];
$res = $conn->query("SELECT blogs.*, users.name FROM blogs JOIN users ON users.id = blogs.student_id ORDER BY blogs.id DESC");
while ($r = $res->fetch_assoc()) $posts[] = $r;

/* ── Student list for filter ── */
$students = [];
$res2 = $conn->query("SELECT DISTINCT users.id, users.name FROM users JOIN blogs ON blogs.student_id = users.id ORDER BY users.name");
while ($s = $res2->fetch_assoc()) $students[] = $s;

/* ── Stats ── */
$totalPosts    = count($posts);
$totalStudents = count($students);
$res3          = $conn->query("SELECT COUNT(DISTINCT week) c FROM blogs");
$totalWeeks    = $res3->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Blogs — Lecturer</title>
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
        --violet: #7c3aed;
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

    .content-wrap {
        padding: 32px 28px;
        flex: 1;
    }

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
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        margin-bottom: 28px;
    }

    @media(max-width:700px) {
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

    .stat-card.posts {
        border-top-color: var(--violet);
    }

    .stat-card.students {
        border-top-color: var(--teal);
    }

    .stat-card.weeks {
        border-top-color: var(--amber);
    }

    .stat-card .stat-icon {
        width: 36px;
        height: 36px;
        border-radius: 9px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 14px;
    }

    .stat-card.posts .stat-icon {
        background: rgba(124, 58, 237, 0.1);
        color: var(--violet);
    }

    .stat-card.students .stat-icon {
        background: rgba(13, 148, 136, 0.1);
        color: var(--teal);
    }

    .stat-card.weeks .stat-icon {
        background: rgba(245, 158, 11, 0.1);
        color: var(--amber);
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
        width: 100%;
        animation: barGrow 1s 0.4s both cubic-bezier(0.4, 0, 0.2, 1);
        transform-origin: left;
    }

    .stat-card.posts .stat-bar-fill {
        background: var(--violet);
    }

    .stat-card.students .stat-bar-fill {
        background: var(--teal);
    }

    .stat-card.weeks .stat-bar-fill {
        background: var(--amber);
    }

    @keyframes barGrow {
        from {
            transform: scaleX(0)
        }

        to {
            transform: scaleX(1)
        }
    }

    /* ─── TOOLBAR ─── */
    .table-toolbar {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
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

    /* ─── BLOG GRID ─── */
    .blog-grid {
        columns: 3;
        column-gap: 16px;
    }

    @media(max-width:1100px) {
        .blog-grid {
            columns: 2;
        }
    }

    @media(max-width:640px) {
        .blog-grid {
            columns: 1;
        }
    }

    .blog-card {
        break-inside: avoid;
        margin-bottom: 16px;
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 14px;
        overflow: hidden;
        transition: transform 0.22s, box-shadow 0.22s, opacity 0.35s, scale 0.35s;
        animation: fadeUp 0.5s both;
        position: relative;
    }

    .blog-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 14px 36px rgba(0, 0, 0, 0.1);
    }

    .blog-card.removing {
        opacity: 0 !important;
        scale: 0.93 !important;
        pointer-events: none;
    }

    /* ─── CARD IMAGE WRAPPER ─── */
    .card-img-wrap {
        position: relative;
        overflow: hidden;
    }

    .blog-card img {
        width: 100%;
        display: block;
        cursor: pointer;
        transition: transform 0.4s;
    }

    .blog-card:hover img {
        transform: scale(1.02);
    }

    /* ─── DELETE BUTTON on card ─── */
    .card-delete-btn {
        position: absolute;
        top: 8px;
        right: 8px;
        background: rgba(15, 23, 42, 0.65);
        backdrop-filter: blur(4px);
        border: 1px solid rgba(255, 255, 255, 0.15);
        border-radius: 8px;
        color: #fca5a5;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        opacity: 0;
        transform: scale(0.85);
        transition: opacity 0.2s, transform 0.2s, background 0.2s;
        z-index: 5;
    }

    .card-img-wrap:hover .card-delete-btn,
    .blog-card-body .card-delete-inline:hover+.card-delete-btn {
        opacity: 1;
        transform: scale(1);
    }

    .blog-card:hover .card-delete-btn {
        opacity: 1;
        transform: scale(1);
    }

    .card-delete-btn:hover {
        background: rgba(225, 29, 72, 0.85);
        color: #fff;
    }

    .card-delete-btn svg {
        width: 14px;
        height: 14px;
        pointer-events: none;
    }

    .blog-card-body {
        padding: 14px 16px 16px;
    }

    .blog-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 8px;
    }

    .blog-meta-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .student-chip {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        font-size: 12.5px;
        font-weight: 600;
        color: var(--text);
    }

    .student-dot {
        width: 26px;
        height: 26px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--navy2), var(--slate));
        color: #fff;
        font-family: 'Syne', sans-serif;
        font-size: 10px;
        font-weight: 800;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .week-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 6px;
        padding: 3px 8px;
        font-size: 11px;
        font-weight: 700;
        color: var(--slate);
    }

    .blog-caption {
        font-size: 13px;
        color: var(--muted);
        line-height: 1.55;
        margin-top: 6px;
    }

    /* ─── EMPTY STATE ─── */
    .empty-state {
        padding: 70px 20px;
        text-align: center;
        color: var(--muted);
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
    }

    .empty-state svg {
        width: 52px;
        height: 52px;
        margin: 0 auto 16px;
        opacity: 0.3;
    }

    /* ─── LIGHTBOX ─── */
    #lightbox {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 99;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(6px);
        align-items: center;
        justify-content: center;
        padding: 24px;
    }

    #lightbox.open {
        display: flex;
    }

    #lightbox img {
        max-width: min(90vw, 780px);
        max-height: 88vh;
        border-radius: 12px;
        object-fit: contain;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.5);
    }

    #lightboxClose {
        position: fixed;
        top: 20px;
        right: 24px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: #fff;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 18px;
        line-height: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background 0.2s;
    }

    #lightboxClose:hover {
        background: rgba(255, 255, 255, 0.2);
    }

    /* ─── OVERLAY (sidebar) ─── */
    #overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.35);
        z-index: 35;
    }

    /* ─── DELETE CONFIRM MODAL ─── */
    #deleteModal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 100;
        background: rgba(0, 0, 0, 0);
        backdrop-filter: blur(0px);
        align-items: center;
        justify-content: center;
        padding: 24px;
        transition: background 0.25s, backdrop-filter 0.25s;
    }

    #deleteModal.open {
        display: flex;
        background: rgba(0, 0, 0, 0.55);
        backdrop-filter: blur(8px);
    }

    .modal-box {
        background: var(--surface);
        border-radius: 20px;
        padding: 32px 28px 24px;
        max-width: 400px;
        width: 100%;
        box-shadow: 0 32px 80px rgba(0, 0, 0, 0.22);
        border: 1px solid var(--border);
        animation: modalPop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        position: relative;
    }

    @keyframes modalPop {
        from {
            opacity: 0;
            transform: scale(0.88) translateY(14px);
        }

        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    .modal-icon-wrap {
        width: 54px;
        height: 54px;
        border-radius: 16px;
        background: rgba(225, 29, 72, 0.09);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
    }

    .modal-icon-wrap svg {
        width: 26px;
        height: 26px;
        color: var(--rose);
    }

    .modal-box h3 {
        font-family: 'Syne', sans-serif;
        font-size: 19px;
        font-weight: 800;
        color: var(--text);
        text-align: center;
        margin-bottom: 8px;
    }

    .modal-box p {
        font-size: 13.5px;
        color: var(--muted);
        text-align: center;
        line-height: 1.55;
        margin-bottom: 24px;
    }

    .modal-post-preview {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 10px 14px;
        margin-bottom: 22px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .modal-post-preview .mpv-dot {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: linear-gradient(135deg, var(--rose), #f97316);
        display: flex;
        align-items: center;
        justify-content: center;
        font-family: 'Syne', sans-serif;
        font-size: 11px;
        font-weight: 800;
        color: #fff;
        flex-shrink: 0;
    }

    .modal-post-preview .mpv-info {
        flex: 1;
        min-width: 0;
    }

    .modal-post-preview .mpv-name {
        font-size: 13px;
        font-weight: 600;
        color: var(--text);
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .modal-post-preview .mpv-week {
        font-size: 11px;
        color: var(--muted);
        margin-top: 1px;
    }

    .modal-actions {
        display: flex;
        gap: 10px;
    }

    .btn-cancel {
        flex: 1;
        padding: 10px;
        border: 1.5px solid var(--border);
        border-radius: 10px;
        background: var(--surface);
        color: var(--slate);
        font-family: 'Epilogue', sans-serif;
        font-size: 13.5px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s, border-color 0.2s;
    }

    .btn-cancel:hover {
        background: var(--bg);
        border-color: var(--slate);
    }

    .btn-delete-confirm {
        flex: 1;
        padding: 10px;
        border: none;
        border-radius: 10px;
        background: var(--rose);
        color: #fff;
        font-family: 'Epilogue', sans-serif;
        font-size: 13.5px;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
        box-shadow: 0 4px 14px rgba(225, 29, 72, 0.3);
    }

    .btn-delete-confirm:hover {
        background: #c81a40;
        transform: translateY(-1px);
        box-shadow: 0 6px 18px rgba(225, 29, 72, 0.4);
    }

    .btn-delete-confirm:active {
        transform: translateY(0);
    }

    .btn-delete-confirm.loading {
        pointer-events: none;
        opacity: 0.8;
    }

    .spinner {
        width: 14px;
        height: 14px;
        border: 2px solid rgba(255, 255, 255, 0.4);
        border-top-color: #fff;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
        display: none;
    }

    .btn-delete-confirm.loading .spinner {
        display: block;
    }

    .btn-delete-confirm.loading .del-btn-text {
        display: none;
    }

    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    /* ─── TOAST ─── */
    #toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        padding: 12px 18px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 500;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.18);
        z-index: 110;
        opacity: 0;
        transform: translateY(10px);
        transition: opacity 0.3s, transform 0.3s;
        pointer-events: none;
        display: flex;
        align-items: center;
        gap: 9px;
    }

    #toast.show {
        opacity: 1;
        transform: translateY(0);
    }

    #toast.success {
        background: #0f172a;
        color: #fff;
    }

    #toast.error {
        background: #fff1f2;
        color: var(--rose);
        border: 1px solid #fecdd3;
    }

    .toast-icon {
        width: 18px;
        height: 18px;
        flex-shrink: 0;
    }

    /* ─── ANIMATIONS ─── */
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
            <a href="dashboard.php" class="nav-link">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-4 0h4" />
                </svg>
                Dashboard
            </a>
            <a href="blogviewer.php" class="nav-link active">
                <svg width="16" height="16" fill="none" viewBox="0 0 16 16">
                    <path
                        d="M2 3.5A1.5 1.5 0 013.5 2h9A1.5 1.5 0 0114 3.5v9A1.5 1.5 0 0112.5 14h-9A1.5 1.5 0 012 12.5v-9z"
                        stroke="currentColor" stroke-width="1.5" />
                    <path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" />
                </svg>
                Student Blogs
            </a>
            <a href="studentmanager.php" class="nav-link">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                Manage Users
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

    <div id="overlay" onclick="closeSidebar()"></div>

    <!-- Main -->
    <main id="main">
        <div class="topbar">
            <button class="toggle-btn" onclick="toggleSidebar()" aria-label="Toggle sidebar">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>
            <span class="topbar-title">Student Blogs</span>
            <span class="topbar-date" id="topDate"></span>
        </div>

        <div class="content-wrap">

            <div class="page-header" style="animation:fadeUp 0.45s both;">
                <div class="eyebrow">Content Review</div>
                <h2>Weekly Blog Posts</h2>
            </div>

            <!-- Stat Cards -->
            <div class="stats-grid">
                <div class="stat-card posts">
                    <div class="stat-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div class="stat-value" id="statPosts"><?= $totalPosts ?></div>
                    <div class="stat-label">Total Posts</div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill"></div>
                    </div>
                </div>
                <div class="stat-card students">
                    <div class="stat-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                    <div class="stat-value"><?= $totalStudents ?></div>
                    <div class="stat-label">Active Students</div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill"></div>
                    </div>
                </div>
                <div class="stat-card weeks">
                    <div class="stat-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
                            <line x1="16" y1="2" x2="16" y2="6" />
                            <line x1="8" y1="2" x2="8" y2="6" />
                            <line x1="3" y1="10" x2="21" y2="10" />
                        </svg>
                    </div>
                    <div class="stat-value"><?= $totalWeeks ?></div>
                    <div class="stat-label">Weeks Covered</div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill"></div>
                    </div>
                </div>
            </div>

            <!-- Toolbar -->
            <div class="table-toolbar">
                <div class="search-wrap">
                    <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
                    </svg>
                    <input type="text" class="search-input" id="searchInput" placeholder="Search student or caption…"
                        oninput="filterPosts()">
                </div>
                <select class="filter-select" id="filterStudent" onchange="filterPosts()">
                    <option value="all">All Students</option>
                    <?php foreach($students as $s): ?>
                    <option value="<?= htmlspecialchars($s['name']) ?>"><?= htmlspecialchars($s['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select class="filter-select" id="filterWeek" onchange="filterPosts()">
                    <option value="all">All Weeks</option>
                    <?php for($w=1;$w<=14;$w++): ?>
                    <option value="<?= $w ?>">Week <?= $w ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <!-- Blog Grid -->
            <?php if(empty($posts)): ?>
            <div class="empty-state">
                <svg fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <p style="font-weight:600;margin-bottom:4px;">No blog posts yet</p>
                <p style="font-size:12px;">Posts will appear here once students start uploading.</p>
            </div>
            <?php else: ?>
            <div class="blog-grid" id="blogGrid">
                <?php foreach($posts as $i => $b):
                $initials = implode('', array_map(fn($w)=>strtoupper($w[0]), array_slice(explode(' ',$b['name']),0,2)));
                $delay    = 0.05 + ($i * 0.04);
            ?>
                <div class="blog-card" data-id="<?= $b['id'] ?>"
                    data-name="<?= strtolower(htmlspecialchars($b['name'])) ?>"
                    data-caption="<?= strtolower(htmlspecialchars($b['caption'])) ?>" data-week="<?= $b['week'] ?>"
                    data-student="<?= htmlspecialchars($b['name']) ?>" data-initials="<?= $initials ?>"
                    style="animation-delay:<?= $delay ?>s">
                    <div class="card-img-wrap">
                        <img src="../<?= htmlspecialchars($b['image']) ?>" alt="Week <?= $b['week'] ?> blog"
                            onclick="openLightbox(this.src)" onerror="this.parentElement.style.display='none'">
                        <!-- Delete button overlaid on image -->
                        <button class="card-delete-btn" onclick="confirmDelete(this)" aria-label="Delete post"
                            data-id="<?= $b['id'] ?>" data-student="<?= htmlspecialchars($b['name']) ?>"
                            data-week="<?= $b['week'] ?>" data-initials="<?= $initials ?>">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                            </svg>
                        </button>
                    </div>
                    <div class="blog-card-body">
                        <div class="blog-meta">
                            <div class="blog-meta-left">
                                <div class="student-chip">
                                    <div class="student-dot"><?= $initials ?></div>
                                    <?= htmlspecialchars($b['name']) ?>
                                </div>
                            </div>
                            <span class="week-badge">
                                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <rect x="3" y="4" width="18" height="18" rx="2" />
                                    <line x1="3" y1="10" x2="21" y2="10" />
                                </svg>
                                W<?= $b['week'] ?>
                            </span>
                        </div>
                        <?php if($b['caption']): ?>
                        <p class="blog-caption"><?= htmlspecialchars($b['caption']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </main>

    <!-- Lightbox -->
    <div id="lightbox" onclick="closeLightbox()">
        <button id="lightboxClose" onclick="closeLightbox()">✕</button>
        <img id="lightboxImg" src="" alt="">
    </div>

    <!-- ─── DELETE CONFIRM MODAL ─── -->
    <div id="deleteModal">
        <div class="modal-box" onclick="event.stopPropagation()">
            <div class="modal-icon-wrap">
                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                </svg>
            </div>
            <h3>Delete Blog Post?</h3>
            <p>This action is <strong>permanent</strong> and cannot be undone. The post and its image will be removed.
            </p>

            <div class="modal-post-preview">
                <div class="mpv-dot" id="modalInitials">AB</div>
                <div class="mpv-info">
                    <div class="mpv-name" id="modalStudentName">Student Name</div>
                    <div class="mpv-week" id="modalWeek">Week 1 Post</div>
                </div>
            </div>

            <div class="modal-actions">
                <button class="btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button class="btn-delete-confirm" id="confirmDeleteBtn" onclick="executeDelete()">
                    <span class="del-btn-text">Delete Post</span>
                    <div class="spinner"></div>
                </button>
            </div>
        </div>
    </div>

    <div id="toast">
        <svg class="toast-icon" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" id="toastIcon">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
        </svg>
        <span id="toastMsg"></span>
    </div>

    <script>
    /* ── DATE ── */
    document.getElementById('topDate').textContent = new Date().toLocaleDateString('en-GB', {
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

    /* ── FILTER ── */
    function filterPosts() {
        const q = document.getElementById('searchInput').value.toLowerCase();
        const student = document.getElementById('filterStudent').value;
        const week = document.getElementById('filterWeek').value;
        document.querySelectorAll('.blog-card').forEach(card => {
            const nameMatch = card.dataset.name.includes(q) || card.dataset.caption.includes(q);
            const studentMatch = student === 'all' || card.dataset.student === student;
            const weekMatch = week === 'all' || card.dataset.week === week;
            card.style.display = (nameMatch && studentMatch && weekMatch) ? '' : 'none';
        });
    }

    /* ── LIGHTBOX ── */
    function openLightbox(src) {
        document.getElementById('lightboxImg').src = src;
        document.getElementById('lightbox').classList.add('open');
    }

    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('open');
    }
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeLightbox();
            closeDeleteModal();
        }
    });

    /* ── TOAST ── */
    function showToast(msg, type = 'success') {
        const toast = document.getElementById('toast');
        const toastMsg = document.getElementById('toastMsg');
        const toastIcon = document.getElementById('toastIcon');
        toast.className = '';
        toastMsg.textContent = msg;
        if (type === 'success') {
            toast.classList.add('show', 'success');
            toastIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>';
        } else {
            toast.classList.add('show', 'error');
            toastIcon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>';
        }
        clearTimeout(toast._t);
        toast._t = setTimeout(() => toast.classList.remove('show'), 3500);
    }

    /* ── DELETE MODAL ── */
    let pendingDeleteId = null;
    let pendingDeleteCard = null;

    function confirmDelete(btn) {
        pendingDeleteId = btn.dataset.id;
        pendingDeleteCard = btn.closest('.blog-card');

        document.getElementById('modalInitials').textContent = btn.dataset.initials || '??';
        document.getElementById('modalStudentName').textContent = btn.dataset.student || 'Unknown';
        document.getElementById('modalWeek').textContent = 'Week ' + btn.dataset.week + ' Post';

        const modal = document.getElementById('deleteModal');
        modal.style.display = 'flex';
        /* Trigger transition on next frame */
        requestAnimationFrame(() => requestAnimationFrame(() => modal.classList.add('open')));
    }

    function closeDeleteModal() {
        const modal = document.getElementById('deleteModal');
        modal.classList.remove('open');
        setTimeout(() => {
            modal.style.display = 'none';
        }, 260);
        const btn = document.getElementById('confirmDeleteBtn');
        btn.classList.remove('loading');
    }

    /* Close modal on backdrop click */
    document.getElementById('deleteModal').addEventListener('click', closeDeleteModal);

    async function executeDelete() {
        if (!pendingDeleteId) return;

        const btn = document.getElementById('confirmDeleteBtn');
        btn.classList.add('loading');

        try {
            const fd = new FormData();
            fd.append('action', 'delete_blog');
            fd.append('id', pendingDeleteId);

            const res = await fetch(window.location.href, {
                method: 'POST',
                body: fd
            });
            const data = await res.json();

            if (data.success) {
                closeDeleteModal();

                /* Animate card out */
                if (pendingDeleteCard) {
                    pendingDeleteCard.classList.add('removing');
                    setTimeout(() => {
                        pendingDeleteCard.remove();
                        updatePostCount();
                        checkEmpty();
                    }, 380);
                }

                showToast('Blog post deleted successfully.', 'success');
            } else {
                closeDeleteModal();
                showToast(data.message || 'Failed to delete post.', 'error');
            }
        } catch (err) {
            closeDeleteModal();
            showToast('Network error. Please try again.', 'error');
        } finally {
            pendingDeleteId = null;
            pendingDeleteCard = null;
        }
    }

    /* ── Update total posts counter after delete ── */
    function updatePostCount() {
        const statEl = document.getElementById('statPosts');
        if (statEl) {
            const current = parseInt(statEl.textContent, 10);
            if (!isNaN(current) && current > 0) statEl.textContent = current - 1;
        }
    }

    /* ── Show empty state if no cards remain ── */
    function checkEmpty() {
        const grid = document.getElementById('blogGrid');
        const cards = grid ? grid.querySelectorAll('.blog-card') : [];
        if (cards.length === 0 && grid) {
            grid.outerHTML = `
        <div class="empty-state">
            <svg fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p style="font-weight:600;margin-bottom:4px;">No blog posts yet</p>
            <p style="font-size:12px;">All posts have been removed.</p>
        </div>`;
        }
    }
    </script>
</body>

</html>