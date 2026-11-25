<?php
session_start();
include 'koneksi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $error = 'Email dan password wajib diisi.';
    } else {
        $stmt = mysqli_prepare($conn, "SELECT id, nama, email, password, role FROM users WHERE email = ? LIMIT 1");
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $user = mysqli_fetch_assoc($result);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['user_role'] = $user['role'];

            header("Location: index.php");
            exit;
        } else {
            $error = 'Email atau password salah.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Taskify</title>
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --bg-page: #f1f5f9;
            --bg-card: #ffffff;
            --border-card: #e2e8f0;
            --shadow-card: 0 10px 25px rgba(15, 23, 42, 0.06);
            --brand-color: #4f46e5;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --input-bg: #ffffff;
            --input-border: #cbd5e1;
            --btn-bg: #4f46e5;
            --btn-hover: #4338ca;
            --alert-bg: #fee2e2;
            --alert-text: #b91c1c;
            --alert-border: #fecaca;
        }

        body.dark-mode {
            --bg-page: #0f172a;
            --bg-card: #1e293b;
            --border-card: #334155;
            --shadow-card: 0 10px 25px rgba(2, 6, 23, 0.45);
            --brand-color: #818cf8;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --input-bg: #0f172a;
            --input-border: #475569;
            --btn-bg: #6366f1;
            --btn-hover: #4f46e5;
            --alert-bg: #450a0a;
            --alert-text: #fca5a5;
            --alert-border: #7f1d1d;
        }

        * {
            box-sizing: border-box;
        }

        body.login-page {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background: var(--bg-page);
            color: var(--text-main);
            transition: background 0.25s ease, color 0.25s ease;
        }

        .login-card {
            width: 100%;
            max-width: 400px;
            background: var(--bg-card);
            border: 1px solid var(--border-card);
            border-radius: 16px;
            padding: 28px;
            box-shadow: var(--shadow-card);
            transition: background 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }

        .login-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 8px;
        }

        .login-brand {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--brand-color);
            font-size: 22px;
            font-weight: 700;
        }

        .theme-toggle {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 8px;
            border: 1px solid var(--input-border);
            background: var(--input-bg);
            color: var(--text-main);
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }

        .theme-toggle:hover {
            border-color: var(--brand-color);
        }

        .login-subtitle {
            font-size: 13px;
            color: var(--text-muted);
            margin-bottom: 22px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--text-main);
            margin-bottom: 6px;
        }

        .form-group input {
            width: 100%;
            padding: 10px 12px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            font-size: 14px;
            background: var(--input-bg);
            color: var(--text-main);
            transition: background 0.2s ease, border-color 0.2s ease, color 0.2s ease;
        }

        .form-group input::placeholder {
            color: var(--text-muted);
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--brand-color);
        }

        .btn-login {
            width: 100%;
            padding: 11px;
            border: none;
            border-radius: 8px;
            background: var(--btn-bg);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.2s ease;
        }

        .btn-login:hover {
            background: var(--btn-hover);
        }

        .alert-error {
            background: var(--alert-bg);
            color: var(--alert-text);
            border: 1px solid var(--alert-border);
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 16px;
        }
    </style>
</head>
<body class="login-page">
    <div class="login-card">
        <div class="login-top">
            <div class="login-brand">
                <i class="fa-solid fa-list-check"></i>
                <span>Taskify</span>
            </div>
            <button type="button" id="themeToggle" class="theme-toggle" aria-label="Ganti mode gelap atau terang">
                <i class="fa-solid fa-moon" id="themeIcon"></i>
                <span id="themeText">Mode Gelap</span>
            </button>
        </div>
        <p class="login-subtitle">Masuk untuk mengelola daftar tugas, kalender, dan statistik Anda.</p>

        <?php if ($error !== ''): ?>
            <div class="alert-error"><?= htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="nama@email.com" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Masukkan password" required>
            </div>
            <button type="submit" class="btn-login">Masuk</button>
        </form>
    </div>

    <script>
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const themeText = document.getElementById('themeText');

        function setLoginTheme(isDark) {
            document.body.classList.toggle('dark-mode', isDark);
            themeIcon.className = isDark ? 'fa-solid fa-sun' : 'fa-solid fa-moon';
            themeText.textContent = isDark ? 'Mode Terang' : 'Mode Gelap';
        }

        const savedTheme = localStorage.getItem('taskify_login_theme');
        setLoginTheme(savedTheme === 'dark');

        themeToggle.addEventListener('click', () => {
            const nextDark = !document.body.classList.contains('dark-mode');
            localStorage.setItem('taskify_login_theme', nextDark ? 'dark' : 'light');
            setLoginTheme(nextDark);
        });
    </script>
</body>
</html>