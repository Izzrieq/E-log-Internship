<?php ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL); require '../config/config.php'; checkAuth(); if(!isLecturer()) die(); ?>
<?php
$errors   = [];
$success  = '';

/* ── CREATE USER ── */
if(isset($_POST['create_user'])){
  $name  = trim($_POST['name']);
  $email = trim($_POST['email']);
  $pass  = $_POST['password'];
  $role  = $_POST['role'];

  if(!$name)  $errors[] = 'Name is required.';
  if(!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';
  if(strlen($pass) < 6) $errors[] = 'Password must be at least 6 characters.';

  if(empty($errors)){
    /* Check duplicate email */
    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->store_result();
    if($stmt->num_rows > 0){
      $errors[] = 'An account with that email already exists.';
    } else {
      $hash = password_hash($pass, PASSWORD_BCRYPT);
      $ins  = $conn->prepare("INSERT INTO users(name,email,password,role) VALUES(?,?,?,?)");
      $ins->bind_param('ssss', $name, $email, $hash, $role);
      $ins->execute();
      $success = "Account for <strong>".htmlspecialchars($name)."</strong> created successfully.";
    }
    $stmt->close();
  }
}

/* ── DELETE USER ── */
if(isset($_POST['delete_user'])){
  $del_id = (int)$_POST['user_id'];
  $conn->query("DELETE FROM users WHERE id = $del_id AND role != 'lecturer'");
}

/* ── Fetch all students ── */
$users = [];
$res = $conn->query("SELECT id, name, email, role FROM users WHERE role = 'student' ORDER BY id DESC");
while($r = $res->fetch_assoc()) $users[] = $r;

$totalStudents = count($users);
$res2 = $conn->query("SELECT COUNT(DISTINCT student_id) c FROM reports"); $active = $res2->fetch_assoc()['c'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users — Lecturer</title>
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

    .nav-badge {
        margin-left: auto;
        background: rgba(245, 158, 11, 0.2);
        color: var(--amber);
        font-size: 10px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 99px;
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

    .stat-card.total-s {
        border-top-color: var(--sky);
    }

    .stat-card.active-s {
        border-top-color: var(--teal);
    }

    .stat-card.inactive-s {
        border-top-color: var(--muted);
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

    .stat-card.total-s .stat-icon {
        background: rgba(14, 165, 233, 0.1);
        color: var(--sky);
    }

    .stat-card.active-s .stat-icon {
        background: rgba(13, 148, 136, 0.1);
        color: var(--teal);
    }

    .stat-card.inactive-s .stat-icon {
        background: rgba(100, 116, 139, 0.1);
        color: var(--muted);
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

    .stat-card.total-s .stat-bar-fill {
        background: var(--sky);
        width: 100%;
    }

    .stat-card.active-s .stat-bar-fill {
        background: var(--teal);
    }

    .stat-card.inactive-s .stat-bar-fill {
        background: var(--muted);
    }

    @keyframes barGrow {
        from {
            transform: scaleX(0)
        }

        to {
            transform: scaleX(1)
        }
    }

    /* ─── TWO-COL LAYOUT ─── */
    .two-col {
        display: grid;
        grid-template-columns: 380px 1fr;
        gap: 20px;
        align-items: start;
    }

    @media(max-width:960px) {
        .two-col {
            grid-template-columns: 1fr;
        }
    }

    /* ─── FORM CARD ─── */
    .form-card {
        background: var(--surface);
        border-radius: 16px;
        border: 1px solid var(--border);
        overflow: hidden;
        position: sticky;
        top: 80px;
        animation: fadeUp 0.5s 0.1s both;
    }

    .form-card-header {
        padding: 18px 22px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-card-header .icon-wrap {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        background: rgba(245, 158, 11, 0.1);
        color: var(--amber);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .form-card-header h3 {
        font-family: 'Syne', sans-serif;
        font-size: 15px;
        font-weight: 700;
        color: var(--text);
    }

    .form-body {
        padding: 22px;
    }

    .form-group {
        margin-bottom: 16px;
    }

    .form-label {
        display: block;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.06em;
        text-transform: uppercase;
        color: var(--slate);
        margin-bottom: 6px;
    }

    .form-input,
    .form-select {
        width: 100%;
        padding: 10px 13px;
        border: 1.5px solid var(--border);
        border-radius: 9px;
        font-family: 'Epilogue', sans-serif;
        font-size: 13.5px;
        color: var(--text);
        background: var(--surface);
        outline: none;
        transition: border-color 0.2s, box-shadow 0.2s;
    }

    .form-input:focus,
    .form-select:focus {
        border-color: var(--navy);
        box-shadow: 0 0 0 3px rgba(15, 23, 42, 0.06);
    }

    .form-select {
        cursor: pointer;
    }

    /* Password wrapper */
    .pw-wrap {
        position: relative;
    }

    .pw-wrap .form-input {
        padding-right: 42px;
    }

    .pw-toggle {
        position: absolute;
        right: 10px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: var(--muted);
        padding: 4px;
        transition: color 0.2s;
    }

    .pw-toggle:hover {
        color: var(--text);
    }

    /* Strength meter */
    .strength-meter {
        display: flex;
        gap: 4px;
        margin-top: 8px;
    }

    .strength-bar {
        flex: 1;
        height: 3px;
        border-radius: 2px;
        background: var(--border);
        transition: background 0.3s;
    }

    .strength-bar.weak {
        background: #ef4444;
    }

    .strength-bar.fair {
        background: var(--amber);
    }

    .strength-bar.good {
        background: var(--sky);
    }

    .strength-bar.strong {
        background: var(--teal);
    }

    .strength-label {
        font-size: 11px;
        color: var(--muted);
        margin-top: 5px;
        text-align: right;
    }

    /* Role tabs */
    .role-tabs {
        display: flex;
        gap: 8px;
    }

    .role-tab {
        flex: 1;
        padding: 9px 0;
        text-align: center;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        font-family: 'Syne', sans-serif;
        font-size: 12px;
        font-weight: 700;
        color: var(--muted);
        cursor: pointer;
        background: transparent;
        transition: all 0.2s;
    }

    .role-tab.selected {
        background: var(--navy);
        border-color: var(--navy);
        color: #fff;
    }

    /* Submit btn */
    .submit-btn {
        width: 100%;
        padding: 12px;
        background: var(--navy);
        color: #fff;
        border: none;
        border-radius: 9px;
        font-family: 'Syne', sans-serif;
        font-size: 13px;
        font-weight: 700;
        cursor: pointer;
        transition: background 0.2s, transform 0.15s;
        margin-top: 4px;
    }

    .submit-btn:hover {
        background: var(--slate);
        transform: translateY(-1px);
    }

    .submit-btn:active {
        transform: translateY(0);
    }

    /* Alert */
    .alert {
        padding: 12px 14px;
        border-radius: 9px;
        font-size: 13px;
        margin-bottom: 16px;
        line-height: 1.5;
    }

    .alert-error {
        background: rgba(225, 29, 72, 0.08);
        border: 1px solid rgba(225, 29, 72, 0.2);
        color: #be123c;
    }

    .alert-success {
        background: rgba(13, 148, 136, 0.08);
        border: 1px solid rgba(13, 148, 136, 0.2);
        color: #0f766e;
    }

    /* ─── TABLE CARD ─── */
    .table-card {
        background: var(--surface);
        border-radius: 16px;
        border: 1px solid var(--border);
        overflow: hidden;
        animation: fadeUp 0.5s 0.18s both;
    }

    .table-card-header {
        padding: 18px 22px;
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 12px;
        flex-wrap: wrap;
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

    .table-toolbar {
        padding: 14px 22px;
        border-bottom: 1px solid var(--border);
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .search-wrap {
        position: relative;
        flex: 1;
        min-width: 180px;
    }

    .search-wrap svg {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 14px;
        height: 14px;
        color: var(--muted);
        pointer-events: none;
    }

    .search-input {
        width: 100%;
        padding: 8px 10px 8px 32px;
        border: 1.5px solid var(--border);
        border-radius: 8px;
        font-family: 'Epilogue', sans-serif;
        font-size: 13px;
        color: var(--text);
        background: var(--surface);
        outline: none;
        transition: border-color 0.2s;
    }

    .search-input:focus {
        border-color: var(--navy);
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

    .student-email {
        font-size: 12px;
        color: var(--muted);
    }

    .role-pill {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        border-radius: 99px;
        padding: 3px 10px;
        font-size: 11px;
        font-weight: 700;
    }

    .role-student {
        background: rgba(14, 165, 233, 0.1);
        color: #0369a1;
    }

    .role-lecturer {
        background: rgba(245, 158, 11, 0.12);
        color: #b45309;
    }

    .delete-btn {
        padding: 6px 12px;
        background: transparent;
        border: 1.5px solid rgba(225, 29, 72, 0.2);
        border-radius: 7px;
        color: #be123c;
        font-family: 'Syne', sans-serif;
        font-size: 11px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.2s;
    }

    .delete-btn:hover {
        background: rgba(225, 29, 72, 0.08);
        border-color: rgba(225, 29, 72, 0.4);
    }

    .empty-state {
        padding: 60px 20px;
        text-align: center;
        color: var(--muted);
    }

    .empty-state svg {
        width: 48px;
        height: 48px;
        margin: 0 auto 16px;
        opacity: 0.3;
    }

    /* ─── CONFIRM MODAL ─── */
    #confirmModal {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 99;
        background: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(4px);
        align-items: center;
        justify-content: center;
    }

    #confirmModal.open {
        display: flex;
    }

    .confirm-box {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 28px;
        width: 100%;
        max-width: 360px;
        box-shadow: 0 24px 60px rgba(0, 0, 0, 0.15);
        animation: fadeUp 0.3s both;
    }

    .confirm-box h4 {
        font-family: 'Syne', sans-serif;
        font-size: 18px;
        font-weight: 800;
        margin-bottom: 8px;
    }

    .confirm-box p {
        font-size: 13px;
        color: var(--muted);
        margin-bottom: 22px;
    }

    .confirm-actions {
        display: flex;
        gap: 10px;
    }

    .confirm-cancel {
        flex: 1;
        padding: 10px;
        background: var(--bg);
        border: 1.5px solid var(--border);
        border-radius: 8px;
        font-family: 'Syne', sans-serif;
        font-size: 12px;
        font-weight: 700;
        color: var(--muted);
        cursor: pointer;
        transition: all 0.2s;
    }

    .confirm-cancel:hover {
        border-color: var(--navy);
        color: var(--navy);
    }

    .confirm-delete {
        flex: 1;
        padding: 10px;
        background: var(--rose);
        border: none;
        border-radius: 8px;
        font-family: 'Syne', sans-serif;
        font-size: 12px;
        font-weight: 700;
        color: #fff;
        cursor: pointer;
        transition: all 0.2s;
    }

    .confirm-delete:hover {
        background: #be123c;
    }

    /* ─── OVERLAY ─── */
    #overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.35);
        z-index: 35;
    }

    /* ─── TOAST ─── */
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
            <a href="blogviewer.php" class="nav-link">
                <svg fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                Student Blogs
            </a>
            <a href="manage_users.php" class="nav-link active">
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
            <span class="topbar-title">Manage Users</span>
            <span class="topbar-date" id="topDate"></span>
        </div>

        <div class="content-wrap">

            <!-- Header -->
            <div class="page-header" style="animation:fadeUp 0.45s both;">
                <div class="eyebrow">Administration</div>
                <h2>User Management</h2>
            </div>

            <!-- Stats -->
            <?php
      $inactive = $totalStudents - $active;
      $activePct   = $totalStudents > 0 ? round(($active/$totalStudents)*100) : 0;
      $inactivePct = $totalStudents > 0 ? round(($inactive/$totalStudents)*100) : 0;
    ?>
            <div class="stats-grid">
                <div class="stat-card total-s">
                    <div class="stat-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 100 8 4 4 0 000-8zM23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75" />
                        </svg>
                    </div>
                    <div class="stat-value"><?= $totalStudents ?></div>
                    <div class="stat-label">Total Students</div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill" style="width:100%"></div>
                    </div>
                </div>
                <div class="stat-card active-s">
                    <div class="stat-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="stat-value"><?= $active ?></div>
                    <div class="stat-label">With Reports</div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill" style="width:<?= $activePct ?>%"></div>
                    </div>
                </div>
                <div class="stat-card inactive-s">
                    <div class="stat-icon">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div class="stat-value"><?= $inactive ?></div>
                    <div class="stat-label">No Reports Yet</div>
                    <div class="stat-bar">
                        <div class="stat-bar-fill" style="width:<?= $inactivePct ?>%"></div>
                    </div>
                </div>
            </div>

            <!-- Two-col layout -->
            <div class="two-col">

                <!-- ── CREATE USER FORM ── -->
                <div>
                    <div class="form-card">
                        <div class="form-card-header">
                            <div class="icon-wrap">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                            </div>
                            <h3>Create New Account</h3>
                        </div>

                        <div class="form-body">

                            <?php if(!empty($errors)): ?>
                            <div class="alert alert-error">
                                <?php foreach($errors as $e): ?>
                                <div>• <?= $e ?></div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>

                            <?php if($success): ?>
                            <div class="alert alert-success">✓ <?= $success ?></div>
                            <?php endif; ?>

                            <form method="POST" id="createForm">
                                <!-- Role toggle -->
                                <div class="form-group">
                                    <label class="form-label">Account Role</label>
                                    <div class="role-tabs">
                                        <button type="button" class="role-tab selected" id="tabStudent"
                                            onclick="selectRole('student')">
                                            Student
                                        </button>
                                        <button type="button" class="role-tab" id="tabLecturer"
                                            onclick="selectRole('lecturer')">
                                            Lecturer
                                        </button>
                                    </div>
                                    <input type="hidden" name="role" id="roleInput" value="student">
                                </div>

                                <!-- Name -->
                                <div class="form-group">
                                    <label class="form-label">Full Name</label>
                                    <input type="text" name="name" class="form-input" placeholder="e.g. Ahmad bin Ali"
                                        value="<?= htmlspecialchars($_POST['name'] ?? '') ?>" required>
                                </div>

                                <!-- Email -->
                                <div class="form-group">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email" class="form-input"
                                        placeholder="e.g. ahmad@university.edu"
                                        value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
                                </div>

                                <!-- Password -->
                                <div class="form-group">
                                    <label class="form-label">Password</label>
                                    <div class="pw-wrap">
                                        <input type="password" name="password" id="pwInput" class="form-input"
                                            placeholder="Min. 6 characters" oninput="checkStrength(this.value)"
                                            required>
                                        <button type="button" class="pw-toggle" onclick="togglePw()" title="Show/hide">
                                            <svg id="eyeIcon" width="16" height="16" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" />
                                                <circle cx="12" cy="12" r="3" />
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="strength-meter">
                                        <div class="strength-bar" id="s1"></div>
                                        <div class="strength-bar" id="s2"></div>
                                        <div class="strength-bar" id="s3"></div>
                                        <div class="strength-bar" id="s4"></div>
                                    </div>
                                    <div class="strength-label" id="strengthLabel">Enter a password</div>
                                </div>

                                <button type="submit" name="create_user" class="submit-btn">
                                    Create Account →
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- ── USERS TABLE ── -->
                <div>
                    <div class="table-card">
                        <div class="table-card-header">
                            <h3>Student Accounts</h3>
                            <span class="count-pill" id="rowCount"><?= $totalStudents ?> students</span>
                        </div>

                        <div class="table-toolbar">
                            <div class="search-wrap">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
                                </svg>
                                <input type="text" class="search-input" id="searchInput"
                                    placeholder="Search name or email…" oninput="filterUsers()">
                            </div>
                        </div>

                        <div style="overflow-x:auto;">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Student</th>
                                        <th>Role</th>
                                        <th>Joined</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="userTableBody">
                                    <?php if(empty($users)): ?>
                                    <tr>
                                        <td colspan="4">
                                            <div class="empty-state">
                                                <svg fill="none" stroke="currentColor" stroke-width="1.2"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2M9 7a4 4 0 100 8 4 4 0 000-8z" />
                                                </svg>
                                                <p style="font-weight:600;margin-bottom:4px;">No students yet</p>
                                                <p style="font-size:12px;">Create the first student account using the
                                                    form.</p>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php else: ?>
                                    <?php foreach($users as $i => $u):
                  $initials = implode('', array_map(fn($w)=>strtoupper($w[0]), array_slice(explode(' ',$u['name']),0,2)));
                  $joined   = date('d M Y', strtotime($u['created_at'] ?? 'now'));
                ?>
                                    <tr data-search="<?= strtolower(htmlspecialchars($u['name'].' '.$u['email'])) ?>">
                                        <td>
                                            <div class="student-cell">
                                                <div class="student-dot"><?= $initials ?></div>
                                                <div>
                                                    <div class="student-name"><?= htmlspecialchars($u['name']) ?></div>
                                                    <div class="student-email"><?= htmlspecialchars($u['email']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="role-pill role-<?= $u['role'] ?>">
                                                <?= ucfirst($u['role']) ?>
                                            </span>
                                        </td>
                                        <td style="font-size:12.5px;color:var(--muted);"><?= $joined ?></td>
                                        <td>
                                            <button class="delete-btn"
                                                onclick="confirmDelete(<?= $u['id'] ?>, '<?= htmlspecialchars($u['name']) ?>')">
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div><!-- /two-col -->

        </div>
    </main>

    <!-- Hidden delete form -->
    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="user_id" id="deleteUserId">
        <input type="hidden" name="delete_user" value="1">
    </form>

    <!-- Confirm Modal -->
    <div id="confirmModal">
        <div class="confirm-box">
            <h4>Delete Account?</h4>
            <p id="confirmMsg">This will permanently remove the student account and cannot be undone.</p>
            <div class="confirm-actions">
                <button class="confirm-cancel" onclick="closeConfirm()">Cancel</button>
                <button class="confirm-delete" onclick="submitDelete()">Delete</button>
            </div>
        </div>
    </div>

    <div id="toast"></div>

    <script>
    /* ── DATE ── */
    document.getElementById('topDate').textContent =
        new Date().toLocaleDateString('en-GB', {
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

    /* ── ROLE TABS ── */
    function selectRole(role) {
        document.getElementById('roleInput').value = role;
        document.getElementById('tabStudent').classList.toggle('selected', role === 'student');
        document.getElementById('tabLecturer').classList.toggle('selected', role === 'lecturer');
    }

    /* ── PASSWORD VISIBILITY ── */
    function togglePw() {
        const inp = document.getElementById('pwInput');
        inp.type = inp.type === 'password' ? 'text' : 'password';
    }

    /* ── STRENGTH METER ── */
    function checkStrength(val) {
        const bars = [document.getElementById('s1'), document.getElementById('s2'),
            document.getElementById('s3'), document.getElementById('s4')
        ];
        const label = document.getElementById('strengthLabel');
        bars.forEach(b => b.className = 'strength-bar');
        if (!val) {
            label.textContent = 'Enter a password';
            return;
        }
        let score = 0;
        if (val.length >= 6) score++;
        if (val.length >= 10) score++;
        if (/[A-Z]/.test(val) && /[0-9]/.test(val)) score++;
        if (/[^A-Za-z0-9]/.test(val)) score++;
        const levels = ['weak', 'fair', 'good', 'strong'];
        const labels = ['Weak', 'Fair', 'Good', 'Strong'];
        const cls = levels[score - 1] || 'weak';
        for (let i = 0; i < score; i++) bars[i].classList.add(cls);
        label.textContent = labels[score - 1] || 'Too short';
        label.style.color = {
            weak: '#ef4444',
            fair: '#f59e0b',
            good: '#0ea5e9',
            strong: '#0d9488'
        } [cls] || '#64748b';
    }

    /* ── SEARCH ── */
    function filterUsers() {
        const q = document.getElementById('searchInput').value.toLowerCase();
        const rows = document.querySelectorAll('#userTableBody tr[data-search]');
        let visible = 0;
        rows.forEach(row => {
            const show = row.dataset.search.includes(q);
            row.style.display = show ? '' : 'none';
            if (show) visible++;
        });
        document.getElementById('rowCount').textContent = visible + ' students';
    }

    /* ── DELETE CONFIRM ── */
    let pendingDeleteId = null;

    function confirmDelete(id, name) {
        pendingDeleteId = id;
        document.getElementById('confirmMsg').textContent =
            `This will permanently delete "${name}"'s account. This cannot be undone.`;
        document.getElementById('confirmModal').classList.add('open');
    }

    function closeConfirm() {
        document.getElementById('confirmModal').classList.remove('open');
        pendingDeleteId = null;
    }

    function submitDelete() {
        if (!pendingDeleteId) return;
        document.getElementById('deleteUserId').value = pendingDeleteId;
        document.getElementById('deleteForm').submit();
    }
    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') closeConfirm();
    });

    /* ── SUCCESS TOAST ── */
    <?php if($success): ?>
        (function() {
            const t = document.getElementById('toast');
            t.textContent = 'Account created successfully!';
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 3500);
        })();
    <?php endif; ?>
    </script>
</body>

</html>