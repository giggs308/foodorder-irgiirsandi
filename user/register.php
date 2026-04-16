<?php
session_start();
include '../includes/config.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $nama = trim($_POST['nama'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Basic validation
    if ($nama === '' || $email === '' || $password === '') {
        $error = 'Semua field wajib diisi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Format email tidak valid.';
    } elseif (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter.';
    } else {
        // Check if email already exists - using backticks for safety
        $checkQuery = "SELECT `id_user` FROM `users` WHERE `email` = ? LIMIT 1";
        if ($stmt = mysqli_prepare($conn, $checkQuery)) {
            mysqli_stmt_bind_param($stmt, 's', $email);
            mysqli_stmt_execute($stmt);
            $res = mysqli_stmt_get_result($stmt);
            if ($res && mysqli_fetch_assoc($res)) {
                $error = 'Email sudah terdaftar. Silakan gunakan email lain atau login.';
            } else {
                // Insert new user with prepared statement
                $hashed = password_hash($password, PASSWORD_DEFAULT);
                $insertQuery = "INSERT INTO users (nama, email, password, role) VALUES (?, ?, ?, 'user')";
                if ($insertStmt = mysqli_prepare($conn, $insertQuery)) {
                    mysqli_stmt_bind_param($insertStmt, 'sss', $nama, $email, $hashed);
                    if (mysqli_stmt_execute($insertStmt)) {
                        $success = 'Registrasi berhasil! Silakan login.';
                    } else {
                        $error = 'Gagal registrasi. Coba lagi beberapa saat.';
                    }
                    mysqli_stmt_close($insertStmt);
                } else {
                    $error = 'Terjadi kesalahan pada server (prep insert).';
                }
            }
            mysqli_stmt_close($stmt);
        } else {
            $error = 'Terjadi kesalahan pada server (prep check).';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - FoodOrder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #ff6b35;
            --secondary-color: #ff8c5a;
            --dark-color: #2d3436;
            --light-color: #f8f9fa;
        }
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ff9966, #ff5e62);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .login-container {
            width: 100%;
            max-width: 400px;
            margin: 0 auto;
            animation: fadeInDown 0.5s ease-out;
        }
        .login-box {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            position: relative;
            width: 100%;
        }
        .login-box::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
            transform: rotate(45deg);
            z-index: 1;
            animation: shine 3s infinite;
        }
        .login-header {
            text-align: center;
            margin-bottom: 35px;
            position: relative;
            z-index: 2;
        }
        .login-title {
            color: #ff6b35;
            font-weight: 700;
            font-size: 2.2rem;
            margin-bottom: 0.25rem;
            text-align: center;
            display: block;
        }
        .form-group {
            margin-bottom: 25px;
            position: relative;
            z-index: 2;
        }
        .form-control {
            height: 50px;
            border-radius: 12px;
            padding: 12px 20px;
            border: 2px solid #e9ecef;
            font-size: 15px;
            transition: all 0.3s ease;
            background-color: #f8f9fa;
        }
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(255, 107, 53, 0.25);
            background-color: #fff;
        }
        .btn-login {
            background: #ff6b35;
            color: white;
            border: none;
            border-radius: 8px;
            padding: 12px;
            width: 100%;
            font-weight: 600;
            font-size: 16px;
            transition: all 0.3s ease;
        }
        .btn-login:hover {
            background: #ff5e2e;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 107, 53, 0.3);
        }
        .register-link {
            text-align: center;
            margin-top: 25px;
            color: #6c757d;
            font-size: 15px;
        }
    </style>
    </head>
<body>
    <div class="login-container" style="position: relative; z-index: 1;">
        <div class="login-box" style="position: relative; z-index: 2;">
            <h1 class="login-title">🍔 FoodOrder</h1>
            <p class="text-center text-muted mb-4">Buat akun baru</p>

            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php elseif ($success): ?>
                <div class="alert alert-success d-flex justify-content-between align-items-center">
                    <span><?= htmlspecialchars($success) ?></span>
                    <a href="../login.php" class="btn btn-sm btn-success">Login</a>
                </div>
            <?php endif; ?>

            <form method="post" action="" style="position: relative; z-index: 10;">
                <div class="form-group mb-3">
                    <input type="text" class="form-control" name="nama" placeholder="Nama Lengkap" required value="<?= htmlspecialchars($_POST['nama'] ?? '') ?>">
                </div>
                <div class="form-group mb-3">
                    <input type="email" class="form-control" name="email" placeholder="Email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
                <div class="form-group mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Password" required>
                </div>
                <button type="submit" name="register" class="btn btn-login w-100">Daftar</button>
                <div class="text-center mt-3">
                    <span class="text-muted small">Sudah punya akun? </span>
                    <a href="../login.php" class="text-decoration-none">Masuk</a>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
