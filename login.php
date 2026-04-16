<?php
session_start();
include 'includes/config.php';

// Redirect if already logged in
if (isset($_SESSION['user'])) {
    header("Location: " . ($_SESSION['user']['role'] === 'admin' ? 'admin/' : 'user/') . "index.php");
    exit();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Check user in database
    $query = "SELECT * FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set user session
            $_SESSION['user'] = $user;
            
            // Redirect based on role
            header("Location: " . ($user['role'] === 'admin' ? 'admin/' : 'user/') . "index.php");
            exit();
        } else {
            $error = "Password yang Anda masukkan salah";
        }
    } else {
        $error = "Email tidak ditemukan";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - FoodOrder</title>
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
            margin-bottom: 1rem;
            text-align: center;
            display: block;
        }
        }
        .form-group {
            margin-bottom: 25px;
            position: relative;
            z-index: 2;
        }
        
        .form-control {
            height: 50px;
            border-radius: 12px;
            padding: 12px 20px 12px 45px;
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
        
        .input-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 18px;
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
        
        .btn-login:active {
            transform: translateY(2px);
        }
        }
        .btn-login:hover {
            background: #ff814e;
            transform: translateY(-2px);
        }
        .register-link {
            text-align: center;
            margin-top: 25px;
            color: #6c757d;
            font-size: 15px;
        }
        .error-message {
            color: #dc3545;
            text-align: center;
            margin-bottom: 20px;
            background: rgba(220, 53, 69, 0.1);
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
            animation: shake 0.5s ease-in-out;
        }
        
        .login-footer {
            text-align: center;
            margin-top: 30px;
            color: #6c757d;
            font-size: 14px;
        }
        
      
        }
    </style>
</head>
<body>
    <div class="login-container" style="position: relative; z-index: 1;">
        <div class="login-box" style="position: relative; z-index: 2;">
            <h1 class="login-title">🍔 FoodOrder</h1>
            <p class="text-center text-muted mb-4">Masuk ke akun Anda</p>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <form method="post" action="" style="position: relative; z-index: 10;">
                <div class="form-group mb-3">
                    <input type="email" class="form-control" name="email" placeholder="Email" required style="position: relative; z-index: 10;">
                </div>
                
                <div class="form-group mb-3">
                    <input type="password" class="form-control" name="password" placeholder="Password" required style="position: relative; z-index: 10;">
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-4" style="position: relative; z-index: 10;">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="rememberMe" style="position: relative; z-index: 10;">
                        <label class="form-check-label small" for="rememberMe" style="position: relative; z-index: 10;">Ingat Saya</label>
                    </div>
                    <a href="#" class="small text-decoration-none" style="position: relative; z-index: 10;">Lupa Password?</a>
                </div>
                
                <button type="submit" name="login" class="btn btn-login w-100" style="position: relative; z-index: 10;">
                    Masuk
                </button>
                
                <div class="text-center mt-3" style="position: relative; z-index: 10;">
                    <span class="text-muted small">Belum punya akun? </span>
                    <a href="user/register.php" class="text-decoration-none">Daftar Sekarang</a>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
