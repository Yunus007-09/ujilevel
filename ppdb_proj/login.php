<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Login.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$message = '';

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    $login = new Login($db);
    
    $login->username = trim($_POST['username']);
    $login->password = $_POST['password'];
    
    if (empty($login->username) || empty($login->password)) {
        $message = "Username dan password harus diisi!";
    } else {
        if ($login->autentikasi()) {
            header("Location: dashboard.php");
            exit();
        } else {
            $message = "Username atau password salah, atau akun tidak aktif!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - PPDB SMK IGASAR PINDAD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px 0;
        }
        
        .login-container {
            width: 100%;
            max-width: 450px;
        }
        
        .login-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            position: relative;
        }
        
        .login-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #667eea, #764ba2, #f093fb);
        }
        
        .school-logo {
            width: 80px;
            height: 80px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }
        
        .school-title {
            color: #2d3748;
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 5px;
        }
        
        .school-subtitle {
            color: #667eea;
            font-weight: 500;
            font-size: 0.9rem;
            margin-bottom: 20px;
        }
        
        .form-floating > .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            height: 55px;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }
        
        .form-floating > .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
            background: white;
        }
        
        .form-floating > label {
            color: #718096;
            font-weight: 500;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            height: 50px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .register-section {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border-radius: 12px;
            padding: 15px;
            margin-top: 20px;
            border: 1px solid #e2e8f0;
        }
        
        .btn-register {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 16px;
            font-size: 0.9rem;
        }
        
        .btn-register:hover {
            transform: translateY(-1px);
            box-shadow: 0 5px 15px rgba(72, 187, 120, 0.3);
            color: white;
        }
        
        .demo-info {
            background: linear-gradient(135deg, #fef5e7 0%, #fed7aa 100%);
            border: 1px solid #f6ad55;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
            font-size: 0.8rem;
        }
        
        .demo-info small {
            color: #c05621;
            font-weight: 500;
        }
        
        .alert {
            border-radius: 12px;
            border: none;
            font-weight: 500;
            padding: 12px 15px;
            font-size: 0.9rem;
        }
        
        .floating-shapes {
            position: fixed;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
            top: 0;
            left: 0;
        }
        
        .shape {
            position: absolute;
            opacity: 0.1;
            animation: float 6s ease-in-out infinite;
        }
        
        .shape:nth-child(1) {
            top: 20%;
            left: 10%;
            animation-delay: 0s;
        }
        
        .shape:nth-child(2) {
            top: 60%;
            right: 10%;
            animation-delay: 2s;
        }
        
        .shape:nth-child(3) {
            bottom: 20%;
            left: 20%;
            animation-delay: 4s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(10deg); }
        }
        
        @media (max-width: 576px) {
            .login-container {
                padding: 0 15px;
            }
            
            .school-title {
                font-size: 1.3rem;
            }
            
            .demo-info {
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
    <div class="floating-shapes">
        <div class="shape">
            <i class="fas fa-graduation-cap fa-3x"></i>
        </div>
        <div class="shape">
            <i class="fas fa-cogs fa-2x"></i>
        </div>
        <div class="shape">
            <i class="fas fa-laptop-code fa-2x"></i>
        </div>
    </div>
    
    <div class="login-container">
        <div class="login-card p-4">
            <div class="text-center mb-4">
                <div class="school-logo">
                    <i class="fas fa-graduation-cap fa-2x text-white"></i>
                </div>
                <h2 class="school-title">SMK IGASAR PINDAD</h2>
                <p class="school-subtitle">PPDB Online 2024/2025</p>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="demo-info text-center">
                <small>
                    <i class="fas fa-info-circle me-1"></i>
                    <strong>Demo:</strong> admin/admin123, petugas1/petugas123, siswa1/siswa123
                </small>
            </div>
            
            <form method="POST">
                <div class="form-floating mb-3">
                    <input type="text" class="form-control" id="username" name="username" placeholder="Username" required>
                    <label for="username">
                        <i class="fas fa-user me-2"></i>Username
                    </label>
                </div>
                
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                    <label for="password">
                        <i class="fas fa-lock me-2"></i>Password
                    </label>
                </div>
                
                <button type="submit" class="btn btn-login w-100 mb-3">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Masuk ke Sistem
                </button>
            </form>
            
            <div class="register-section text-center">
                <p class="mb-2 text-muted small">
                    <i class="fas fa-user-plus me-1"></i>
                    Belum memiliki akun?
                </p>
                <div class="d-grid gap-2">
                    <a href="register.php?type=pendaftar" class="btn btn-register btn-sm">
                        <i class="fas fa-user-graduate me-2"></i>
                        Daftar Sebagai Siswa
                    </a>
                    <a href="register.php?type=petugas" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-user-tie me-2"></i>
                        Daftar Sebagai Petugas
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
