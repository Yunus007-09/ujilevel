<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Registrasi.php';

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}

$registration_type = isset($_GET['type']) ? $_GET['type'] : 'pendaftar';
$valid_types = ['pendaftar', 'petugas', 'admin', 'tu', 'kepala_sekolah'];

if (!in_array($registration_type, $valid_types)) {
    $registration_type = 'pendaftar';
}

$message = '';
$message_type = '';

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    $registrasi = new Registrasi($db);
    
    $registrasi->username = trim($_POST['username']);
    $registrasi->password = $_POST['password'];
    $registrasi->email = trim($_POST['email']);
    $registrasi->role = $_POST['role'];
    
    $result = $registrasi->buatAkun();
    
    if ($result === true) {
        $message = "Registrasi berhasil! Silakan login dengan akun Anda.";
        $message_type = "success";
    } else {
        $message = is_string($result) ? $result : "Terjadi kesalahan saat registrasi.";
        $message_type = "danger";
    }
}

function getRoleInfo($role) {
    $roles = [
        'pendaftar' => [
            'name' => 'Pendaftar (Siswa)',
            'description' => 'Untuk calon siswa yang ingin mendaftar',
            'icon' => 'fa-user-graduate',
            'color' => 'success'
        ],
        'petugas' => [
            'name' => 'Petugas',
            'description' => 'Staff input dan verifikasi data',
            'icon' => 'fa-user-tie',
            'color' => 'primary'
        ],
        'admin' => [
            'name' => 'Administrator',
            'description' => 'Admin kelola sistem',
            'icon' => 'fa-user-shield',
            'color' => 'danger'
        ],
        'tu' => [
            'name' => 'Tata Usaha',
            'description' => 'Staff TU laporan',
            'icon' => 'fa-user-edit',
            'color' => 'warning'
        ],
        'kepala_sekolah' => [
            'name' => 'Kepala Sekolah',
            'description' => 'Monitoring penerimaan',
            'icon' => 'fa-user-crown',
            'color' => 'info'
        ]
    ];
    return $roles[$role] ?? $roles['pendaftar'];
}

$current_role_info = getRoleInfo($registration_type);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - PPDB SMK IGASAR PINDAD</title>
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
            padding: 20px 0;
        }
        
        .register-container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .register-card {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            overflow: hidden;
            position: relative;
        }
        
        .register-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #48bb78, #38a169, #68d391);
        }
        
        .school-logo {
            width: 70px;
            height: 70px;
            background: linear-gradient(45deg, #48bb78, #38a169);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 10px 30px rgba(72, 187, 120, 0.3);
        }
        
        .form-floating > .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            height: 55px;
            background: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
        }
        
        .form-floating > .form-control:focus {
            border-color: #48bb78;
            box-shadow: 0 0 0 0.2rem rgba(72, 187, 120, 0.15);
            background: white;
        }
        
        .btn-register {
            background: linear-gradient(135deg, #48bb78 0%, #38a169 100%);
            border: none;
            border-radius: 12px;
            height: 50px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(72, 187, 120, 0.3);
            color: white;
        }
        
        .role-info {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid #e2e8f0;
        }
        
        .role-selector {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 8px;
            margin-bottom: 15px;
        }
        
        .role-option {
            padding: 10px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            text-decoration: none;
            transition: all 0.3s ease;
            text-align: center;
            font-size: 0.85rem;
        }
        
        .role-option:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .role-option.active {
            border-color: #48bb78;
            background: rgba(72, 187, 120, 0.1);
        }
        
        @media (max-width: 768px) {
            .role-selector {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 576px) {
            .role-selector {
                grid-template-columns: 1fr;
            }
            
            .register-container {
                padding: 0 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container register-container">
        <div class="register-card p-4">
            <div class="text-center mb-4">
                <div class="school-logo">
                    <i class="fas fa-user-plus fa-2x text-white"></i>
                </div>
                <h2 class="fw-bold text-success">Registrasi Akun</h2>
                <p class="text-muted">SMK IGASAR PINDAD - PPDB 2024/2025</p>
            </div>
            
            <!-- Role Selection -->
            <div class="role-info">
                <h6 class="mb-3"><i class="fas fa-users me-2"></i>Pilih Jenis Akun</h6>
                <div class="role-selector">
                    <?php foreach (['pendaftar', 'petugas', 'admin', 'tu', 'kepala_sekolah'] as $role): 
                        $role_info = getRoleInfo($role);
                        $is_active = $registration_type === $role;
                    ?>
                        <a href="?type=<?php echo $role; ?>" 
                           class="role-option <?php echo $is_active ? 'active' : ''; ?> text-<?php echo $role_info['color']; ?>">
                            <i class="fas <?php echo $role_info['icon']; ?> mb-1"></i>
                            <div class="fw-bold small"><?php echo $role_info['name']; ?></div>
                        </a>
                    <?php endforeach; ?>
                </div>
                
                <div class="alert alert-info py-2 mb-0">
                    <i class="fas <?php echo $current_role_info['icon']; ?> me-2"></i>
                    <strong><?php echo $current_role_info['name']; ?>:</strong>
                    <?php echo $current_role_info['description']; ?>
                </div>
            </div>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                    <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <form method="POST" id="registerForm">
                <input type="hidden" name="role" value="<?php echo $registration_type; ?>">
                
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="text" class="form-control" id="username" name="username" required minlength="3">
                            <label for="username"><i class="fas fa-user me-2"></i>Username</label>
                            <div class="form-text">Minimal 3 karakter</div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" name="email" required>
                            <label for="email"><i class="fas fa-envelope me-2"></i>Email</label>
                        </div>
                    </div>
                </div>
                
                <div class="form-floating mb-4">
                    <input type="password" class="form-control" id="password" name="password" required minlength="6">
                    <label for="password"><i class="fas fa-lock me-2"></i>Password</label>
                    <div class="form-text">Minimal 6 karakter</div>
                </div>
                
                <?php if ($registration_type !== 'pendaftar'): ?>
                    <div class="alert alert-warning py-2">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <small><strong>Perhatian:</strong> Registrasi untuk role <?php echo $current_role_info['name']; ?> 
                        memerlukan persetujuan administrator.</small>
                    </div>
                <?php endif; ?>
                
                <button type="submit" class="btn btn-register w-100 mb-3">
                    <i class="fas fa-user-plus me-2"></i>
                    Daftar Sebagai <?php echo $current_role_info['name']; ?>
                </button>
            </form>
            
            <div class="text-center">
                <small class="text-muted">
                    Sudah punya akun? 
                    <a href="login.php" class="text-success fw-bold text-decoration-none">Login di sini</a>
                </small>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

