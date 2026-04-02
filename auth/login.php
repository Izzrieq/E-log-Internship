<?php
require '../config/config.php';

// ── MUST run before ANY HTML output ──
if (isset($_POST['login'])) {
    $email    = $_POST['email'];
    $password = md5($_POST['password']);
    $stmt = $conn->prepare("SELECT * FROM users WHERE email=? AND password=?");
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $_SESSION['user'] = $res->fetch_assoc();
        header("Location: ../index.php");
        exit;
    } else {
        $loginError = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In — E-Log Intern</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Serif+Display:ital@0;1&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500;9..40,600;9..40,700&display=swap"
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
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        position: relative;
    }

    /* ── Ambient background ── */
    .bg-glow {
        position: fixed;
        inset: 0;
        pointer-events: none;
        z-index: 0;
        background:
            radial-gradient(ellipse 70% 55% at 60% -5%, rgba(240, 165, 0, 0.09) 0%, transparent 60%),
            radial-gradient(ellipse 55% 60% at -5% 90%, rgba(99, 102, 241, 0.06) 0%, transparent 60%),
            radial-gradient(ellipse 40% 40% at 100% 60%, rgba(240, 165, 0, 0.04) 0%, transparent 55%);
    }

    /* Grain overlay */
    .bg-grain {
        position: fixed;
        inset: 0;
        pointer-events: none;
        z-index: 1;
        opacity: 0.025;
        background-image: url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='noise'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='0.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23noise)'/%3E%3C/svg%3E");
        background-size: 180px;
    }

    /* Orbit rings */
    .orbit-wrap {
        position: fixed;
        inset: 0;
        z-index: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        pointer-events: none;
    }

    .orbit {
        position: absolute;
        border-radius: 50%;
        border: 1px solid rgba(240, 165, 0, 0.055);
        animation: spin linear infinite;
    }

    .orbit:nth-child(1) {
        width: 420px;
        height: 420px;
        animation-duration: 38s;
    }

    .orbit:nth-child(2) {
        width: 640px;
        height: 640px;
        animation-duration: 58s;
        animation-direction: reverse;
    }

    .orbit:nth-child(3) {
        width: 860px;
        height: 860px;
        animation-duration: 82s;
        border-color: rgba(240, 165, 0, 0.028);
    }

    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    /* ── Card ── */
    .card {
        position: relative;
        z-index: 10;
        width: 100%;
        max-width: 400px;
        margin: 20px;
        background: var(--ink2);
        border: 1px solid var(--border);
        border-radius: 24px;
        padding: 40px 36px 36px;
        box-shadow:
            0 0 0 1px rgba(255, 255, 255, 0.03),
            0 32px 80px rgba(0, 0, 0, 0.55),
            0 0 60px rgba(240, 165, 0, 0.04);
        animation: cardUp 0.65s cubic-bezier(.16, 1, .3, 1) both;
    }

    /* Top accent line */
    .card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 16px;
        right: 16px;
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(240, 165, 0, 0.55), transparent);
        border-radius: 1px;
    }

    @keyframes cardUp {
        from {
            opacity: 0;
            transform: translateY(28px) scale(0.97);
        }

        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* ── Brand ── */
    .brand {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        margin-bottom: 28px;
        animation: cardUp 0.65s 0.08s cubic-bezier(.16, 1, .3, 1) both;
    }

    .brand-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        background: rgba(240, 165, 0, 0.12);
        border: 1px solid rgba(240, 165, 0, 0.2);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .brand-name {
        font-family: 'DM Serif Display', serif;
        font-size: 1.25rem;
        color: var(--amber);
        letter-spacing: -0.01em;
    }

    /* ── Headings ── */
    .card-title {
        font-family: 'DM Serif Display', serif;
        font-size: 1.7rem;
        color: var(--text);
        text-align: center;
        line-height: 1.15;
        margin-bottom: 4px;
        animation: cardUp 0.65s 0.13s cubic-bezier(.16, 1, .3, 1) both;
    }

    .card-sub {
        text-align: center;
        font-size: 0.8rem;
        color: var(--muted);
        margin-bottom: 28px;
        animation: cardUp 0.65s 0.17s cubic-bezier(.16, 1, .3, 1) both;
    }

    /* ── Form ── */
    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
        margin-bottom: 14px;
        animation: cardUp 0.6s cubic-bezier(.16, 1, .3, 1) both;
    }

    .form-group:nth-child(1) {
        animation-delay: 0.20s;
    }

    .form-group:nth-child(2) {
        animation-delay: 0.25s;
    }

    .form-label {
        font-size: 0.69rem;
        font-weight: 600;
        letter-spacing: 0.09em;
        text-transform: uppercase;
        color: var(--muted);
    }

    .input-wrap {
        position: relative;
        display: flex;
        align-items: center;
    }

    .input-icon {
        position: absolute;
        left: 13px;
        color: var(--muted);
        pointer-events: none;
        transition: color 0.2s;
        display: flex;
    }

    .form-input {
        width: 100%;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid var(--border);
        border-radius: 11px;
        padding: 12px 14px 12px 40px;
        color: var(--text);
        font-family: 'DM Sans', sans-serif;
        font-size: 0.875rem;
        outline: none;
        transition: all 0.22s;
    }

    .form-input:focus {
        border-color: rgba(240, 165, 0, 0.5);
        background: rgba(240, 165, 0, 0.04);
        box-shadow: 0 0 0 3px rgba(240, 165, 0, 0.08);
    }

    .form-input:focus+.input-icon,
    .input-wrap:focus-within .input-icon {
        color: var(--amber);
    }

    .form-input::placeholder {
        color: var(--muted);
    }

    /* Password toggle */
    .pw-toggle {
        position: absolute;
        right: 12px;
        background: none;
        border: none;
        color: var(--muted);
        cursor: pointer;
        padding: 4px;
        border-radius: 6px;
        display: flex;
        transition: color 0.2s;
    }

    .pw-toggle:hover {
        color: var(--amber);
    }

    /* ── Error banner ── */
    .error-banner {
        display: flex;
        align-items: center;
        gap: 9px;
        background: rgba(248, 113, 113, 0.08);
        border: 1px solid rgba(248, 113, 113, 0.25);
        border-radius: 10px;
        padding: 11px 14px;
        margin-bottom: 16px;
        font-size: 0.8rem;
        color: #fca5a5;
        animation: shake 0.45s cubic-bezier(.36, .07, .19, .97), cardUp 0.4s cubic-bezier(.16, 1, .3, 1);
    }

    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        20% {
            transform: translateX(-5px);
        }

        40% {
            transform: translateX(5px);
        }

        60% {
            transform: translateX(-4px);
        }

        80% {
            transform: translateX(4px);
        }
    }

    /* ── Submit button ── */
    .btn-login {
        width: 100%;
        padding: 13px;
        border-radius: 11px;
        border: none;
        background: linear-gradient(135deg, var(--amber), #e09500);
        color: #0d0f14;
        font-family: 'DM Sans', sans-serif;
        font-weight: 700;
        font-size: 0.9rem;
        letter-spacing: 0.03em;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        transition: all 0.22s;
        box-shadow: 0 4px 18px rgba(240, 165, 0, 0.28);
        margin-top: 8px;
        animation: cardUp 0.65s 0.30s cubic-bezier(.16, 1, .3, 1) both;
    }

    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(240, 165, 0, 0.38);
    }

    .btn-login:active {
        transform: translateY(0);
    }

    /* Shimmer sweep */
    .btn-login::after {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 60%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.25), transparent);
        transform: skewX(-20deg);
        transition: left 0.55s ease;
    }

    .btn-login:hover::after {
        left: 160%;
    }

    /* ── Footer hint ── */
    .card-footer-note {
        text-align: center;
        font-size: 0.72rem;
        color: var(--muted);
        margin-top: 22px;
        padding-top: 18px;
        border-top: 1px solid var(--border);
        animation: cardUp 0.65s 0.35s cubic-bezier(.16, 1, .3, 1) both;
    }

    .card-footer-note strong {
        color: var(--amber);
        font-weight: 600;
    }
    </style>
</head>

<body>

    <div class="bg-glow"></div>
    <div class="bg-grain"></div>

    <div class="orbit-wrap">
        <div class="orbit"></div>
        <div class="orbit"></div>
        <div class="orbit"></div>
    </div>

    <div class="card">

        <!-- Brand -->
        <div class="brand">
            <div class="brand-icon">
                <svg width="16" height="16" viewBox="0 0 14 14" fill="none">
                    <path d="M7 1L9.2 5.5L14 6.2L10.5 9.6L11.4 14L7 11.7L2.6 14L3.5 9.6L0 6.2L4.8 5.5L7 1Z"
                        fill="#f0a500" />
                </svg>
            </div>
            <span class="brand-name">E-Log Intern</span>
        </div>

        <h1 class="card-title">Welcome back</h1>
        <p class="card-sub">Sign in to your internship portal</p>

        <!-- Error -->
        <?php if (!empty($loginError)): ?>
        <div class="error-banner">
            <svg width="15" height="15" fill="none" viewBox="0 0 15 15">
                <circle cx="7.5" cy="7.5" r="6.5" stroke="currentColor" stroke-width="1.4" />
                <path d="M7.5 4.5v3.5M7.5 10.5v.5" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" />
            </svg>
            <?= htmlspecialchars($loginError) ?>
        </div>
        <?php endif; ?>

        <!-- Form — method & names unchanged -->
        <form method="POST" novalidate>

            <div class="form-group">
                <label class="form-label">Email</label>
                <div class="input-wrap">
                    <input class="form-input" type="email" name="email" placeholder="you@example.com"
                        value="<?= isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '' ?>"
                        autocomplete="email" required>
                    <span class="input-icon">
                        <svg width="15" height="15" fill="none" viewBox="0 0 15 15">
                            <rect x="1.5" y="3" width="12" height="9" rx="1.5" stroke="currentColor"
                                stroke-width="1.3" />
                            <path d="M1.5 5l6 4 6-4" stroke="currentColor" stroke-width="1.3" stroke-linecap="round" />
                        </svg>
                    </span>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Password</label>
                <div class="input-wrap">
                    <input class="form-input" type="password" name="password" id="passwordInput" placeholder="••••••••"
                        autocomplete="current-password" required>
                    <span class="input-icon">
                        <svg width="15" height="15" fill="none" viewBox="0 0 15 15">
                            <rect x="3" y="6.5" width="9" height="7" rx="1.5" stroke="currentColor"
                                stroke-width="1.3" />
                            <path d="M5 6.5V4.5a2.5 2.5 0 015 0v2" stroke="currentColor" stroke-width="1.3"
                                stroke-linecap="round" />
                        </svg>
                    </span>
                    <button type="button" class="pw-toggle" id="pwToggle" onclick="togglePw()" tabindex="-1"
                        title="Show password">
                        <svg id="eyeIcon" width="15" height="15" fill="none" viewBox="0 0 15 15">
                            <path d="M1 7.5S3.5 3 7.5 3s6.5 4.5 6.5 4.5-2.5 4.5-6.5 4.5S1 7.5 1 7.5z"
                                stroke="currentColor" stroke-width="1.3" />
                            <circle cx="7.5" cy="7.5" r="1.8" stroke="currentColor" stroke-width="1.3" />
                        </svg>
                    </button>
                </div>
            </div>

            <!-- name="login" preserved exactly -->
            <button type="submit" name="login" class="btn-login">Sign In</button>

        </form>

        <p class="card-footer-note">
            Internship Management System &nbsp;·&nbsp; <strong>E-Log Intern</strong>
        </p>

    </div>

    <script>
    function togglePw() {
        const input = document.getElementById('passwordInput');
        const icon = document.getElementById('eyeIcon');
        const show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        icon.innerHTML = show ?
            `<path d="M2 2l11 11M6.5 5.5A3 3 0 0112 9.5M3.5 6.5A6.8 6.8 0 001 9.5S3.5 14 7.5 14c1.3 0 2.5-.4 3.5-1" stroke="currentColor" stroke-width="1.3" stroke-linecap="round"/>` :
            `<path d="M1 7.5S3.5 3 7.5 3s6.5 4.5 6.5 4.5-2.5 4.5-6.5 4.5S1 7.5 1 7.5z" stroke="currentColor" stroke-width="1.3"/><circle cx="7.5" cy="7.5" r="1.8" stroke="currentColor" stroke-width="1.3"/>`;
    }
    </script>

</body>

</html>