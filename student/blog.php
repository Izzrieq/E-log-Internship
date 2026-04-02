<?php require '../config/config.php'; checkAuth(); if(!isStudent()) die(); ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Weekly Blog — E-Log Intern</title>
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
    }

    *,
    *::before,
    *::after {
        box-sizing: border-box;
        margin: 0;
        padding: 0;
    }

    body {
        font-family: 'DM Sans', sans-serif;
        background: var(--ink);
        color: var(--text);
        min-height: 100vh;
        overflow-x: hidden;
        display: flex;
    }

    body::before {
        content: '';
        position: fixed;
        inset: 0;
        background:
            radial-gradient(ellipse 80% 50% at 70% -10%, rgba(240, 165, 0, 0.07) 0%, transparent 60%),
            radial-gradient(ellipse 50% 60% at -10% 80%, rgba(99, 102, 241, 0.05) 0%, transparent 60%);
        pointer-events: none;
        z-index: 0;
    }

    /* ── SIDEBAR ── */
    #sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        z-index: 40;
        width: 256px;
        background: linear-gradient(180deg, var(--ink2) 0%, var(--ink) 100%);
        border-right: 1px solid var(--border);
        display: flex;
        flex-direction: column;
        padding: 0;
        transition: width 0.35s cubic-bezier(.16, 1, .3, 1);
        overflow: hidden;
    }

    #sidebar.collapsed {
        width: 64px;
    }

    .sidebar-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 16px 16px;
        border-bottom: 1px solid var(--border);
        min-height: 64px;
        flex-shrink: 0;
    }

    .brand-row {
        display: flex;
        align-items: center;
        gap: 10px;
        overflow: hidden;
        white-space: nowrap;
    }

    .brand-icon {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        flex-shrink: 0;
        background: rgba(240, 165, 0, 0.15);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .brand-text {
        font-family: 'DM Serif Display', serif;
        font-size: 1.05rem;
        color: var(--amber);
        transition: opacity 0.2s, width 0.3s;
        overflow: hidden;
    }

    #sidebar.collapsed .brand-text {
        opacity: 0;
        width: 0;
    }

    .collapse-btn {
        width: 28px;
        height: 28px;
        border-radius: 7px;
        flex-shrink: 0;
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--border);
        color: var(--muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .collapse-btn:hover {
        background: rgba(255, 255, 255, 0.09);
        color: var(--amber);
    }

    .collapse-btn svg {
        transition: transform 0.35s cubic-bezier(.16, 1, .3, 1);
    }

    #sidebar.collapsed .collapse-btn svg {
        transform: rotate(180deg);
    }

    /* Student info block */
    .sidebar-student {
        margin: 14px 12px 4px;
        padding: 12px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.03);
        border: 1px solid var(--border);
        display: flex;
        align-items: center;
        gap: 10px;
        overflow: hidden;
        flex-shrink: 0;
        transition: padding 0.3s;
    }

    #sidebar.collapsed .sidebar-student {
        padding: 12px 0;
        justify-content: center;
        margin: 14px 8px 4px;
    }

    .avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        flex-shrink: 0;
        background: linear-gradient(135deg, #f0a500, #e09500);
        color: #0d0f14;
        font-weight: 700;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .sidebar-student-info {
        overflow: hidden;
        white-space: nowrap;
        transition: opacity 0.2s, width 0.3s;
    }

    #sidebar.collapsed .sidebar-student-info {
        opacity: 0;
        width: 0;
    }

    .sidebar-student-info p:first-child {
        font-size: 0.82rem;
        font-weight: 600;
        color: var(--text);
    }

    .sidebar-student-info p:last-child {
        font-size: 0.68rem;
        color: var(--muted);
    }

    /* Nav */
    nav.sidebar-nav {
        flex: 1;
        padding: 10px 10px;
        display: flex;
        flex-direction: column;
        gap: 2px;
        overflow: hidden;
    }

    .nav-section-label {
        font-size: 0.62rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--muted);
        padding: 8px 8px 4px;
        white-space: nowrap;
        overflow: hidden;
        transition: opacity 0.2s;
    }

    #sidebar.collapsed .nav-section-label {
        opacity: 0;
    }

    .nav-link {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 12px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 500;
        color: var(--text2);
        text-decoration: none;
        transition: all 0.2s;
        position: relative;
        overflow: visible;
        white-space: nowrap;
    }

    .nav-link svg {
        flex-shrink: 0;
    }

    .nav-link-label {
        transition: opacity 0.2s, width 0.3s;
        overflow: hidden;
    }

    #sidebar.collapsed .nav-link-label {
        opacity: 0;
        width: 0;
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
        top: 20%;
        bottom: 20%;
        width: 3px;
        background: var(--amber);
        border-radius: 0 3px 3px 0;
    }

    .nav-link-danger:hover {
        color: #f87171;
        background: rgba(248, 113, 113, 0.1);
    }

    /* Tooltip on collapsed */
    #sidebar.collapsed .nav-link:hover .nav-tooltip {
        display: flex;
    }

    .nav-tooltip {
        display: none;
        position: absolute;
        left: 56px;
        top: 50%;
        transform: translateY(-50%);
        background: var(--ink3);
        border: 1px solid var(--border);
        color: var(--text);
        font-size: 0.78rem;
        font-weight: 500;
        padding: 5px 10px;
        border-radius: 8px;
        white-space: nowrap;
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
        z-index: 100;
        pointer-events: none;
    }

    .nav-tooltip::before {
        content: '';
        position: absolute;
        left: -5px;
        top: 50%;
        transform: translateY(-50%);
        border: 5px solid transparent;
        border-right-color: var(--ink3);
        border-left: 0;
        margin-left: 0;
    }

    .sidebar-bottom {
        padding: 12px 10px;
        border-top: 1px solid var(--border);
        flex-shrink: 0;
    }

    /* ── MAIN ── */
    #mainContent {
        flex: 1;
        min-height: 100vh;
        margin-left: 256px;
        transition: margin-left 0.35s cubic-bezier(.16, 1, .3, 1);
        position: relative;
        z-index: 10;
    }

    body.sidebar-collapsed #mainContent {
        margin-left: 64px;
    }

    @media(max-width: 768px) {
        #sidebar {
            width: 256px;
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(.16, 1, .3, 1);
        }

        #sidebar.mobile-open {
            transform: translateX(0);
            width: 256px !important;
        }

        #sidebar.collapsed {
            width: 256px;
        }

        #sidebar.collapsed .brand-text,
        #sidebar.collapsed .sidebar-student-info,
        #sidebar.collapsed .nav-link-label,
        #sidebar.collapsed .nav-section-label {
            opacity: 1;
            width: auto;
        }

        #mainContent {
            margin-left: 0 !important;
        }

        #mobileToggle {
            display: flex !important;
        }

        .collapse-btn {
            display: none;
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
        background: var(--ink2);
        border: 1px solid var(--border);
        color: var(--text);
        cursor: pointer;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    #mobileToggle:hover {
        border-color: rgba(240, 165, 0, 0.3);
        color: var(--amber);
    }

    #overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 35;
        backdrop-filter: blur(2px);
    }

    #overlay.active {
        display: block;
    }

    /* ── TOPBAR ── */
    .topbar {
        position: sticky;
        top: 0;
        z-index: 20;
        background: rgba(13, 15, 20, 0.85);
        backdrop-filter: blur(16px);
        border-bottom: 1px solid var(--border);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 32px;
        height: 60px;
    }

    .topbar-title {
        font-family: 'DM Serif Display', serif;
        font-size: 1.1rem;
        color: var(--text);
    }

    .topbar-date {
        font-size: 0.75rem;
        color: var(--muted);
    }

    /* ── PAGE CONTENT ── */
    .page-body {
        padding: 32px;
        max-width: 1200px;
    }

    /* ── COMPOSER CARD ── */
    .composer {
        background: var(--ink2);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 28px;
        margin-bottom: 40px;
        position: relative;
        overflow: hidden;
    }

    .composer::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(240, 165, 0, 0.5), transparent);
    }

    .composer-title {
        font-family: 'DM Serif Display', serif;
        font-size: 1.5rem;
        color: var(--text);
        margin-bottom: 4px;
    }

    .composer-sub {
        font-size: 0.8rem;
        color: var(--muted);
        margin-bottom: 24px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        margin-bottom: 14px;
    }

    @media(max-width: 600px) {
        .form-row {
            grid-template-columns: 1fr;
        }
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-label {
        font-size: 0.7rem;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .form-input {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 11px 14px;
        color: var(--text);
        font-family: 'DM Sans', sans-serif;
        font-size: 0.875rem;
        outline: none;
        transition: all 0.2s;
    }

    .form-input:focus {
        border-color: rgba(240, 165, 0, 0.45);
        background: rgba(240, 165, 0, 0.04);
    }

    .form-input::placeholder {
        color: var(--muted);
    }

    input[type=number].form-input::-webkit-inner-spin-button {
        opacity: 0.3;
    }

    /* Image upload zone */
    .upload-zone {
        border: 2px dashed rgba(255, 255, 255, 0.1);
        border-radius: 14px;
        cursor: pointer;
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
        background: rgba(255, 255, 255, 0.02);
        min-height: 130px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .upload-zone:hover {
        border-color: rgba(240, 165, 0, 0.4);
        background: rgba(240, 165, 0, 0.03);
    }

    .upload-zone.has-preview {
        border-style: solid;
        border-color: rgba(52, 211, 153, 0.35);
        padding: 0;
    }

    .upload-zone input[type=file] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
        width: 100%;
        height: 100%;
    }

    .upload-zone-icon {
        color: var(--muted);
        transition: color 0.2s;
    }

    .upload-zone:hover .upload-zone-icon {
        color: var(--amber);
    }

    .upload-zone-text {
        font-size: 0.8rem;
        color: var(--text2);
        font-weight: 500;
    }

    .upload-zone-hint {
        font-size: 0.7rem;
        color: var(--muted);
    }

    #imagePreview {
        width: 100%;
        height: 160px;
        object-fit: cover;
        border-radius: 12px;
        display: none;
    }

    .btn-post {
        background: linear-gradient(135deg, var(--amber), #e09500);
        color: #0d0f14;
        font-weight: 700;
        font-size: 0.875rem;
        letter-spacing: 0.03em;
        padding: 13px 32px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        box-shadow: 0 4px 16px rgba(240, 165, 0, 0.25);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .btn-post:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 24px rgba(240, 165, 0, 0.35);
    }

    /* ── SECTION HEADER ── */
    .section-header {
        display: flex;
        align-items: flex-end;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .section-label {
        font-size: 0.7rem;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: var(--muted);
        margin-bottom: 2px;
    }

    .section-title {
        font-family: 'DM Serif Display', serif;
        font-size: 1.6rem;
        color: var(--text);
        line-height: 1.1;
    }

    .section-title span {
        color: var(--amber);
    }

    .post-count {
        font-size: 0.75rem;
        color: var(--muted);
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--border);
        padding: 4px 12px;
        border-radius: 100px;
    }

    /* ── BLOG GRID ── */
    .blog-grid {
        columns: 3 220px;
        gap: 16px;
    }

    @media(max-width:700px) {
        .blog-grid {
            columns: 1;
        }
    }

    .blog-card {
        break-inside: avoid;
        margin-bottom: 16px;
        background: var(--ink2);
        border: 1px solid var(--border);
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.25s cubic-bezier(.16, 1, .3, 1);
        animation: fadeUp 0.5s cubic-bezier(.16, 1, .3, 1) both;
        cursor: pointer;
        position: relative;
    }

    .blog-card:hover {
        transform: translateY(-4px);
        border-color: rgba(240, 165, 0, 0.2);
        box-shadow: 0 16px 40px rgba(0, 0, 0, 0.45);
    }

    .blog-card img {
        width: 100%;
        display: block;
        transition: transform 0.4s cubic-bezier(.16, 1, .3, 1);
    }

    .blog-card:hover img {
        transform: scale(1.03);
    }

    .blog-card-img-wrap {
        overflow: hidden;
    }

    .blog-card-body {
        padding: 16px;
    }

    .blog-card-week {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 0.68rem;
        font-weight: 700;
        letter-spacing: 0.08em;
        text-transform: uppercase;
        color: var(--amber);
        background: rgba(240, 165, 0, 0.1);
        border: 1px solid rgba(240, 165, 0, 0.2);
        padding: 3px 9px;
        border-radius: 100px;
        margin-bottom: 10px;
    }

    .blog-card-caption {
        font-size: 0.875rem;
        color: var(--text2);
        line-height: 1.55;
    }

    .blog-card-meta {
        display: flex;
        align-items: center;
        gap: 6px;
        font-size: 0.7rem;
        color: var(--muted);
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid var(--border);
    }

    /* Empty state */
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        border: 2px dashed rgba(255, 255, 255, 0.07);
        border-radius: 20px;
    }

    .empty-state-icon {
        color: var(--muted);
        margin: 0 auto 16px;
        opacity: 0.4;
    }

    .empty-state-title {
        font-family: 'DM Serif Display', serif;
        font-size: 1.4rem;
        color: var(--text2);
        margin-bottom: 6px;
    }

    .empty-state-sub {
        font-size: 0.83rem;
        color: var(--muted);
    }

    /* Success toast */
    #toast {
        position: fixed;
        bottom: 28px;
        right: 28px;
        z-index: 99;
        background: var(--ink2);
        border: 1px solid rgba(52, 211, 153, 0.3);
        border-radius: 12px;
        padding: 14px 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.4);
        transform: translateY(20px);
        opacity: 0;
        transition: all 0.4s cubic-bezier(.16, 1, .3, 1);
        pointer-events: none;
    }

    #toast.show {
        transform: translateY(0);
        opacity: 1;
    }

    .toast-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #34d399;
        box-shadow: 0 0 8px rgba(52, 211, 153, 0.6);
        flex-shrink: 0;
    }

    .toast-text {
        font-size: 0.82rem;
        color: var(--text);
        font-weight: 500;
    }

    /* Lightbox */
    #lightbox {
        position: fixed;
        inset: 0;
        z-index: 99;
        background: rgba(0, 0, 0, 0.85);
        backdrop-filter: blur(12px);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    #lightbox.open {
        display: flex;
    }

    #lightbox img {
        max-width: 90vw;
        max-height: 85vh;
        border-radius: 16px;
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.7);
        animation: modalIn 0.3s cubic-bezier(.16, 1, .3, 1);
    }

    .lightbox-close {
        position: absolute;
        top: 20px;
        right: 20px;
        width: 40px;
        height: 40px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.15);
        color: white;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .lightbox-close:hover {
        background: rgba(255, 255, 255, 0.18);
    }

    /* ── ANIMATIONS ── */
    @keyframes fadeUp {
        from {
            opacity: 0;
            transform: translateY(16px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes modalIn {
        from {
            transform: scale(0.94);
            opacity: 0;
        }

        to {
            transform: scale(1);
            opacity: 1;
        }
    }

    .fade-up {
        opacity: 0;
        transform: translateY(16px);
        animation: fadeUp 0.5s cubic-bezier(.16, 1, .3, 1) forwards;
    }

    /* ── NEW: CARD ACTION BUTTONS ── */
    .card-actions {
        position: absolute;
        top: 10px;
        right: 10px;
        display: flex;
        gap: 6px;
        opacity: 0;
        transform: translateY(-4px);
        transition: opacity 0.2s, transform 0.2s;
        z-index: 5;
    }

    .blog-card:hover .card-actions {
        opacity: 1;
        transform: translateY(0);
    }

    .card-action-btn {
        width: 32px;
        height: 32px;
        border-radius: 8px;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        backdrop-filter: blur(8px);
    }

    .card-action-btn.edit {
        background: rgba(240, 165, 0, 0.15);
        border: 1px solid rgba(240, 165, 0, 0.3);
        color: var(--amber);
    }

    .card-action-btn.edit:hover {
        background: rgba(240, 165, 0, 0.28);
        transform: scale(1.08);
    }

    .card-action-btn.delete {
        background: rgba(248, 113, 113, 0.15);
        border: 1px solid rgba(248, 113, 113, 0.3);
        color: #f87171;
    }

    .card-action-btn.delete:hover {
        background: rgba(248, 113, 113, 0.28);
        transform: scale(1.08);
    }

    /* ── NEW: EDIT MODAL ── */
    #editModal {
        position: fixed;
        inset: 0;
        z-index: 98;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(10px);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    #editModal.open {
        display: flex;
    }

    .edit-modal-box {
        background: var(--ink2);
        border: 1px solid var(--border);
        border-radius: 20px;
        padding: 28px;
        width: 100%;
        max-width: 480px;
        position: relative;
        overflow: hidden;
        animation: modalIn 0.3s cubic-bezier(.16, 1, .3, 1);
    }

    .edit-modal-box::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(240, 165, 0, 0.5), transparent);
    }

    .edit-modal-title {
        font-family: 'DM Serif Display', serif;
        font-size: 1.35rem;
        color: var(--text);
        margin-bottom: 4px;
    }

    .edit-modal-sub {
        font-size: 0.78rem;
        color: var(--muted);
        margin-bottom: 22px;
    }

    .edit-modal-close {
        position: absolute;
        top: 16px;
        right: 16px;
        width: 30px;
        height: 30px;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.06);
        border: 1px solid var(--border);
        color: var(--muted);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .edit-modal-close:hover {
        background: rgba(255, 255, 255, 0.12);
        color: var(--text);
    }

    .edit-modal-actions {
        display: flex;
        gap: 10px;
        margin-top: 20px;
    }

    .btn-save {
        flex: 1;
        background: linear-gradient(135deg, var(--amber), #e09500);
        color: #0d0f14;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 11px 20px;
        border-radius: 10px;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
        box-shadow: 0 4px 14px rgba(240, 165, 0, 0.22);
    }

    .btn-save:hover {
        transform: translateY(-1px);
        box-shadow: 0 8px 22px rgba(240, 165, 0, 0.32);
    }

    .btn-cancel {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--border);
        color: var(--text2);
        font-size: 0.85rem;
        font-weight: 500;
        padding: 11px 20px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
        font-family: 'DM Sans', sans-serif;
    }

    .btn-cancel:hover {
        background: rgba(255, 255, 255, 0.09);
        color: var(--text);
    }

    /* ── NEW: DELETE CONFIRM MODAL ── */
    #deleteModal {
        position: fixed;
        inset: 0;
        z-index: 98;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(10px);
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    #deleteModal.open {
        display: flex;
    }

    .delete-modal-box {
        background: var(--ink2);
        border: 1px solid rgba(248, 113, 113, 0.2);
        border-radius: 20px;
        padding: 28px;
        width: 100%;
        max-width: 400px;
        position: relative;
        overflow: hidden;
        animation: modalIn 0.3s cubic-bezier(.16, 1, .3, 1);
        text-align: center;
    }

    .delete-modal-box::after {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(248, 113, 113, 0.5), transparent);
    }

    .delete-modal-icon {
        width: 52px;
        height: 52px;
        border-radius: 14px;
        background: rgba(248, 113, 113, 0.1);
        border: 1px solid rgba(248, 113, 113, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 16px;
        color: #f87171;
    }

    .delete-modal-title {
        font-family: 'DM Serif Display', serif;
        font-size: 1.3rem;
        color: var(--text);
        margin-bottom: 6px;
    }

    .delete-modal-sub {
        font-size: 0.8rem;
        color: var(--muted);
        margin-bottom: 22px;
        line-height: 1.5;
    }

    .delete-modal-actions {
        display: flex;
        gap: 10px;
    }

    .btn-delete-confirm {
        flex: 1;
        background: rgba(248, 113, 113, 0.15);
        border: 1px solid rgba(248, 113, 113, 0.35);
        color: #f87171;
        font-weight: 700;
        font-size: 0.85rem;
        padding: 11px 20px;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s;
        font-family: 'DM Sans', sans-serif;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 7px;
    }

    .btn-delete-confirm:hover {
        background: rgba(248, 113, 113, 0.25);
        transform: translateY(-1px);
    }

    /* ── NEW: TOAST VARIANTS ── */
    #toast.danger {
        border-color: rgba(248, 113, 113, 0.3);
    }

    #toast.danger .toast-dot {
        background: #f87171;
        box-shadow: 0 0 8px rgba(248, 113, 113, 0.6);
    }

    #toast.warning {
        border-color: rgba(240, 165, 0, 0.3);
    }

    #toast.warning .toast-dot {
        background: var(--amber);
        box-shadow: 0 0 8px rgba(240, 165, 0, 0.6);
    }
    </style>
</head>

<body>

    <?php
    $id = $_SESSION['user']['id'];
    $studentName = $_SESSION['user']['name'] ?? 'Student';
    $firstName   = explode(' ', $studentName)[0];

    $uploadSuccess = false;
    $editSuccess   = false;
    $deleteSuccess = false;

    /* ── NEW: HANDLE DELETE ── */
    if(isset($_POST['delete'])){
        $del_id = intval($_POST['post_id']);
        $row = $conn->query("SELECT * FROM blogs WHERE id='$del_id' AND student_id='$id'")->fetch_assoc();
        if($row){
            $filePath = '../' . $row['image'];
            if(file_exists($filePath)) unlink($filePath);
            $conn->query("DELETE FROM blogs WHERE id='$del_id' AND student_id='$id'");
            $deleteSuccess = true;
        }
    }

    /* ── NEW: HANDLE EDIT ── */
    if(isset($_POST['edit'])){
        $edit_id  = intval($_POST['post_id']);
        $new_week = intval($_POST['week']);
        $new_cap  = htmlspecialchars(trim($_POST['caption']));
        $stmt = $conn->prepare("UPDATE blogs SET week=?, caption=? WHERE id=? AND student_id=?");
        $stmt->bind_param("isii", $new_week, $new_cap, $edit_id, $id);
        $stmt->execute();
        $editSuccess = true;
    }

    if(isset($_POST['upload'])){
        $week = intval($_POST['week']);
        $cap  = htmlspecialchars(trim($_POST['caption']));
        $img  = $_FILES['image']['name'];
        $tmp  = $_FILES['image']['tmp_name'];
        if($tmp && $img){
            $path = "uploads/".time()."_".basename($img);
            move_uploaded_file($tmp,"../".$path);
            $stmt = $conn->prepare("INSERT INTO blogs(student_id,week,image,caption) VALUES(?,?,?,?)");
            $stmt->bind_param("iiss",$id,$week,$path,$cap);
            $stmt->execute();
            $uploadSuccess = true;
        }
    }

    $posts = [];
    $res = $conn->query("SELECT * FROM blogs WHERE student_id='$id' ORDER BY week ASC, id DESC");
    while($b = $res->fetch_assoc()) $posts[] = $b;
    $postCount = count($posts);
?>

    <!-- ── SIDEBAR ── -->
    <aside id="sidebar">
        <div class="sidebar-header">
            <div class="brand-row">
                <div class="brand-icon">
                    <svg width="16" height="16" viewBox="0 0 14 14" fill="none">
                        <path d="M7 1L9.2 5.5L14 6.2L10.5 9.6L11.4 14L7 11.7L2.6 14L3.5 9.6L0 6.2L4.8 5.5L7 1Z"
                            fill="#f0a500" />
                    </svg>
                </div>
                <span class="brand-text">E-Log Intern</span>
            </div>
            <button class="collapse-btn" id="collapseBtn" onclick="toggleCollapse()" title="Toggle sidebar">
                <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                    <path d="M8 2L4 6L8 10" stroke="currentColor" stroke-width="1.6" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </button>
        </div>

        <div class="sidebar-student">
            <div class="avatar"><?= strtoupper(substr($firstName,0,1)) ?></div>
            <div class="sidebar-student-info">
                <p><?= htmlspecialchars($studentName) ?></p>
                <p>Internship Trainee</p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="dashboard.php" class="nav-link">
                <svg width="16" height="16" fill="none" viewBox="0 0 16 16" flex-shrink="0">
                    <rect x="1" y="1" width="6" height="6" rx="1.5" fill="currentColor" opacity=".8" />
                    <rect x="9" y="1" width="6" height="6" rx="1.5" fill="currentColor" opacity=".8" />
                    <rect x="1" y="9" width="6" height="6" rx="1.5" fill="currentColor" opacity=".8" />
                    <rect x="9" y="9" width="6" height="6" rx="1.5" fill="currentColor" opacity=".8" />
                </svg>
                <span class="nav-link-label">Dashboard</span>
                <span class="nav-tooltip">Dashboard</span>
            </a>
            <a href="blog.php" class="nav-link active">
                <svg width="16" height="16" fill="none" viewBox="0 0 16 16">
                    <path
                        d="M2 3.5A1.5 1.5 0 013.5 2h9A1.5 1.5 0 0114 3.5v9A1.5 1.5 0 0112.5 14h-9A1.5 1.5 0 012 12.5v-9z"
                        stroke="currentColor" stroke-width="1.4" />
                    <path d="M5 6h6M5 9h4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" />
                </svg>
                <span class="nav-link-label">Blog</span>
                <span class="nav-tooltip">Blog</span>
            </a>
        </nav>

        <div class="sidebar-bottom">
            <a href="../auth/logout.php" class="nav-link nav-link-danger">
                <svg width="16" height="16" fill="none" viewBox="0 0 16 16">
                    <path d="M6 2H3a1 1 0 00-1 1v10a1 1 0 001 1h3M10 11l3-3-3-3M13 8H6" stroke="currentColor"
                        stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                <span class="nav-link-label">Sign Out</span>
                <span class="nav-tooltip">Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- Mobile toggle -->
    <button id="mobileToggle" onclick="mobileOpen()">
        <svg width="16" height="12" viewBox="0 0 16 12" fill="none">
            <rect width="16" height="2" rx="1" fill="currentColor" />
            <rect y="5" width="11" height="2" rx="1" fill="currentColor" />
            <rect y="10" width="16" height="2" rx="1" fill="currentColor" />
        </svg>
    </button>

    <div id="overlay" onclick="mobileClose()"></div>

    <!-- ── MAIN ── -->
    <div id="mainContent">

        <!-- Topbar -->
        <div class="topbar">
            <span class="topbar-title">Weekly Blog</span>
            <span class="topbar-date" id="topbarDate"></span>
        </div>

        <div class="page-body">

            <!-- Page Heading -->
            <div class="fade-up mb-8" style="animation-delay:0.05s; padding-top:4px;">
                <p
                    style="font-size:0.72rem; color:var(--muted); letter-spacing:0.1em; text-transform:uppercase; margin-bottom:4px;">
                    Field Journal</p>
                <h1 style="font-family:'DM Serif Display',serif; font-size:2rem; line-height:1.1; color:var(--text)">
                    <?= htmlspecialchars($firstName) ?>'s Blog<span style="color:var(--amber)">.</span>
                </h1>
            </div>

            <!-- ── COMPOSER ── -->
            <div class="composer fade-up" style="animation-delay:0.1s;">
                <p class="composer-title">New Post</p>
                <p class="composer-sub">Document your internship experience — one week at a time.</p>

                <form method="POST" enctype="multipart/form-data" id="postForm">
                    <div class="form-row">
                        <!-- Week number -->
                        <div class="form-group">
                            <label class="form-label">Week Number</label>
                            <input type="number" name="week" min="1" max="14" placeholder="e.g. 3" class="form-input"
                                required>
                        </div>
                        <!-- Caption -->
                        <div class="form-group">
                            <label class="form-label">Caption</label>
                            <input type="text" name="caption" placeholder="What happened this week?" class="form-input">
                        </div>
                    </div>

                    <!-- Image Upload -->
                    <div class="form-group" style="margin-bottom:20px;">
                        <label class="form-label">Photo</label>
                        <div class="upload-zone" id="uploadZone">
                            <input type="file" name="image" id="imageInput" accept="image/*" required
                                onchange="previewImage(this)">
                            <img id="imagePreview" src="" alt="preview">
                            <div id="uploadPlaceholder"
                                style="display:flex; flex-direction:column; align-items:center; gap:6px; padding:20px;">
                                <svg class="upload-zone-icon" width="32" height="32" fill="none" viewBox="0 0 32 32">
                                    <path d="M16 20V10M10 16l6-6 6 6" stroke="currentColor" stroke-width="2"
                                        stroke-linecap="round" stroke-linejoin="round" />
                                    <rect x="4" y="24" width="24" height="2" rx="1" fill="currentColor" opacity=".4" />
                                    <rect x="2" y="4" width="28" height="22" rx="4" stroke="currentColor"
                                        stroke-width="1.5" fill="none" />
                                </svg>
                                <span class="upload-zone-text">Click or drag image here</span>
                                <span class="upload-zone-hint">JPG, PNG, WEBP — max 5MB</span>
                            </div>
                        </div>
                    </div>

                    <div style="display:flex; align-items:center; gap:14px; flex-wrap:wrap;">
                        <button type="submit" name="upload" class="btn-post">
                            <svg width="16" height="16" fill="none" viewBox="0 0 16 16">
                                <path d="M8 2v9M4 7l4-4 4 4" stroke="currentColor" stroke-width="1.8"
                                    stroke-linecap="round" stroke-linejoin="round" />
                                <path d="M2 13h12" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" />
                            </svg>
                            Publish Post
                        </button>
                        <span style="font-size:0.75rem; color:var(--muted)">Week 1–14 · Images only</span>
                    </div>
                </form>
            </div>

            <!-- ── POSTS GRID ── -->
            <div class="section-header fade-up" style="animation-delay:0.15s;">
                <div>
                    <p class="section-label">Published Posts</p>
                    <h2 class="section-title">Your Journal <span>Archive</span></h2>
                </div>
                <span class="post-count"><?= $postCount ?> post<?= $postCount != 1 ? 's' : '' ?></span>
            </div>

            <?php if(empty($posts)): ?>
            <div class="empty-state fade-up" style="animation-delay:0.2s;">
                <svg class="empty-state-icon" width="56" height="56" fill="none" viewBox="0 0 56 56">
                    <rect x="8" y="8" width="40" height="40" rx="8" stroke="currentColor" stroke-width="2" />
                    <path d="M20 28h16M20 34h10" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                    <circle cx="28" cy="20" r="3" fill="currentColor" opacity=".4" />
                </svg>
                <p class="empty-state-title">No posts yet</p>
                <p class="empty-state-sub">Upload your first weekly photo above to get started.</p>
            </div>
            <?php else: ?>

            <div class="blog-grid">
                <?php foreach($posts as $idx => $b):
                $delay = 0.18 + ($idx * 0.06);
                $weekNum = $b['week'];
                $bId = $b['id'];
                $bCap = htmlspecialchars($b['caption'] ?? '', ENT_QUOTES);
            ?>
                <div class="blog-card" style="animation-delay:<?= $delay ?>s;">

                    <!-- ── NEW: ACTION BUTTONS ── -->
                    <div class="card-actions" onclick="event.stopPropagation()">
                        <button class="card-action-btn edit" title="Edit post"
                            onclick="openEditModal(<?= $bId ?>, <?= $weekNum ?>, '<?= $bCap ?>')">
                            <svg width="13" height="13" fill="none" viewBox="0 0 13 13">
                                <path d="M9.5 1.5l2 2-7 7H2.5v-2l7-7z" stroke="currentColor" stroke-width="1.4"
                                    stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                        <button class="card-action-btn delete" title="Delete post"
                            onclick="openDeleteModal(<?= $bId ?>, <?= $weekNum ?>)">
                            <svg width="13" height="13" fill="none" viewBox="0 0 13 13">
                                <path d="M2 3.5h9M5 3.5V2.5h3v1M4 3.5l.5 7h4l.5-7" stroke="currentColor"
                                    stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </button>
                    </div>

                    <div onclick="openLightbox('<?= htmlspecialchars('../'.$b['image']) ?>')">
                        <div class="blog-card-img-wrap">
                            <img src="../<?= htmlspecialchars($b['image']) ?>" alt="Week <?= $weekNum ?> photo"
                                loading="lazy"
                                onerror="this.src='data:image/svg+xml,<svg xmlns=\'http://www.w3.org/2000/svg\' width=\'300\' height=\'200\'><rect fill=\'%231e2330\' width=\'300\' height=\'200\'/><text x=\'50%25\' y=\'50%25\' fill=\'%236b7280\' font-size=\'13\' text-anchor=\'middle\' dy=\'.3em\'>Image not found</text></svg>'">
                        </div>
                        <div class="blog-card-body">
                            <span class="blog-card-week">
                                <svg width="10" height="10" fill="none" viewBox="0 0 10 10">
                                    <path d="M5 1L6.2 3.7H9L6.8 5.5L7.6 8.2L5 6.6L2.4 8.2L3.2 5.5L1 3.7H3.8L5 1Z"
                                        fill="currentColor" />
                                </svg>
                                Week <?= $weekNum ?>
                            </span>
                            <?php if($b['caption']): ?>
                            <p class="blog-card-caption"><?= htmlspecialchars($b['caption']) ?></p>
                            <?php endif; ?>
                            <div class="blog-card-meta">
                                <svg width="12" height="12" fill="none" viewBox="0 0 12 12">
                                    <circle cx="6" cy="6" r="4.5" stroke="currentColor" stroke-width="1.2" />
                                    <path d="M6 3.5V6l1.5 1.5" stroke="currentColor" stroke-width="1.2"
                                        stroke-linecap="round" />
                                </svg>
                                Internship Week <?= $weekNum ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div><!-- /page-body -->
    </div><!-- /mainContent -->

    <!-- ── LIGHTBOX ── -->
    <div id="lightbox" onclick="closeLightbox()">
        <button class="lightbox-close" onclick="closeLightbox()">
            <svg width="12" height="12" viewBox="0 0 12 12" fill="none">
                <path d="M1 1l10 10M11 1L1 11" stroke="white" stroke-width="1.8" stroke-linecap="round" />
            </svg>
        </button>
        <img id="lightboxImg" src="" alt="Fullsize">
    </div>

    <!-- ── NEW: EDIT MODAL ── -->
    <div id="editModal">
        <div class="edit-modal-box">
            <button class="edit-modal-close" onclick="closeEditModal()">
                <svg width="10" height="10" viewBox="0 0 10 10" fill="none">
                    <path d="M1 1l8 8M9 1L1 9" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                </svg>
            </button>
            <p class="edit-modal-title">Edit Post</p>
            <p class="edit-modal-sub">Update the week number or caption for this entry.</p>
            <form method="POST" id="editForm">
                <input type="hidden" name="post_id" id="editPostId">
                <div class="form-row" style="margin-bottom:14px;">
                    <div class="form-group">
                        <label class="form-label">Week Number</label>
                        <input type="number" name="week" id="editWeek" min="1" max="14" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Caption</label>
                        <input type="text" name="caption" id="editCaption" placeholder="Update caption…"
                            class="form-input">
                    </div>
                </div>
                <div class="edit-modal-actions">
                    <button type="submit" name="edit" class="btn-save">
                        <svg width="14" height="14" fill="none" viewBox="0 0 14 14">
                            <path d="M2 7l3.5 3.5L12 3" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"
                                stroke-linejoin="round" />
                        </svg>
                        Save Changes
                    </button>
                    <button type="button" class="btn-cancel" onclick="closeEditModal()">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── NEW: DELETE CONFIRM MODAL ── -->
    <div id="deleteModal">
        <div class="delete-modal-box">
            <div class="delete-modal-icon">
                <svg width="22" height="22" fill="none" viewBox="0 0 22 22">
                    <path d="M3 6h16M8 6V4h6v2M5 6l1 13h10l1-13" stroke="currentColor" stroke-width="1.6"
                        stroke-linecap="round" stroke-linejoin="round" />
                    <path d="M9 10v5M13 10v5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
                </svg>
            </div>
            <p class="delete-modal-title">Delete Post?</p>
            <p class="delete-modal-sub" id="deleteModalSub">This will permanently remove Week — post and its image. This
                action cannot be undone.</p>
            <form method="POST" id="deleteForm">
                <input type="hidden" name="post_id" id="deletePostId">
                <div class="delete-modal-actions">
                    <button type="button" class="btn-cancel" onclick="closeDeleteModal()"
                        style="flex:1;">Cancel</button>
                    <button type="submit" name="delete" class="btn-delete-confirm">
                        <svg width="13" height="13" fill="none" viewBox="0 0 13 13">
                            <path d="M2 3.5h9M5 3.5V2.5h3v1M4 3.5l.5 7h4l.5-7" stroke="currentColor" stroke-width="1.4"
                                stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                        Yes, Delete
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ── TOAST ── -->
    <div id="toast">
        <span class="toast-dot"></span>
        <span class="toast-text" id="toastText">Post published successfully!</span>
    </div>

    <script>
    // ── DATE ──
    const d = new Date();
    document.getElementById('topbarDate').textContent =
        d.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

    // ── SIDEBAR COLLAPSE (Desktop) ──
    const STORAGE_KEY = 'blog_sidebar_collapsed';
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');

    function applyCollapse(collapsed) {
        if (collapsed) {
            sidebar.classList.add('collapsed');
            document.body.classList.add('sidebar-collapsed');
        } else {
            sidebar.classList.remove('collapsed');
            document.body.classList.remove('sidebar-collapsed');
        }
    }
    applyCollapse(localStorage.getItem(STORAGE_KEY) === '1');

    function toggleCollapse() {
        const isCollapsed = sidebar.classList.contains('collapsed');
        applyCollapse(!isCollapsed);
        localStorage.setItem(STORAGE_KEY, !isCollapsed ? '1' : '0');
    }

    // ── MOBILE ──
    function mobileOpen() {
        sidebar.classList.add('mobile-open');
        document.getElementById('overlay').classList.add('active');
    }

    function mobileClose() {
        sidebar.classList.remove('mobile-open');
        document.getElementById('overlay').classList.remove('active');
    }

    // ── IMAGE PREVIEW ──
    function previewImage(input) {
        const file = input.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = e => {
            const preview = document.getElementById('imagePreview');
            const placeholder = document.getElementById('uploadPlaceholder');
            const zone = document.getElementById('uploadZone');
            preview.src = e.target.result;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
            zone.classList.add('has-preview');
        };
        reader.readAsDataURL(file);
    }

    const zone = document.getElementById('uploadZone');
    zone.addEventListener('dragover', e => {
        e.preventDefault();
        zone.style.borderColor = 'rgba(240,165,0,0.5)';
    });
    zone.addEventListener('dragleave', () => {
        zone.style.borderColor = '';
    });
    zone.addEventListener('drop', () => {
        zone.style.borderColor = '';
    });

    // ── LIGHTBOX ──
    function openLightbox(src) {
        document.getElementById('lightboxImg').src = src;
        document.getElementById('lightbox').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        document.getElementById('lightbox').classList.remove('open');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', e => {
        if (e.key === 'Escape') {
            closeLightbox();
            closeEditModal();
            closeDeleteModal();
        }
    });

    // ── NEW: TOAST HELPER ──
    function showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const toastText = document.getElementById('toastText');
        toast.className = '';
        if (type === 'danger') toast.classList.add('danger');
        else if (type === 'warning') toast.classList.add('warning');
        toastText.textContent = message;
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => toast.classList.remove('show'), 3500);
    }

    // ── NEW: EDIT MODAL ──
    function openEditModal(id, week, caption) {
        document.getElementById('editPostId').value = id;
        document.getElementById('editWeek').value = week;
        document.getElementById('editCaption').value = caption;
        document.getElementById('editModal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        document.getElementById('editModal').classList.remove('open');
        document.body.style.overflow = '';
    }

    document.getElementById('editModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });

    // ── NEW: DELETE MODAL ──
    function openDeleteModal(id, week) {
        document.getElementById('deletePostId').value = id;
        document.getElementById('deleteModalSub').textContent =
            `This will permanently remove the Week ${week} post and its image. This action cannot be undone.`;
        document.getElementById('deleteModal').classList.add('open');
        document.body.style.overflow = 'hidden';
    }

    function closeDeleteModal() {
        document.getElementById('deleteModal').classList.remove('open');
        document.body.style.overflow = '';
    }

    document.getElementById('deleteModal').addEventListener('click', function(e) {
        if (e.target === this) closeDeleteModal();
    });

    // ── TOAST on PHP events ──
    <?php if($uploadSuccess): ?>
    window.addEventListener('load', () => showToast('Post published successfully!'));
    <?php endif; ?>

    <?php if($editSuccess): ?>
    window.addEventListener('load', () => showToast('Post updated successfully!', 'warning'));
    <?php endif; ?>

    <?php if($deleteSuccess): ?>
    window.addEventListener('load', () => showToast('Post deleted successfully.', 'danger'));
    <?php endif; ?>
    </script>
</body>

</html>