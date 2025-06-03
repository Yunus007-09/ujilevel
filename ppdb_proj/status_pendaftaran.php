<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'classes/Pendaftar.php';

checkLogin();

// Hanya pendaftar yang bisa akses
if ($_SESSION['role'] != 'pendaftar') {
    header("Location: unauthorized.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$pendaftar = new Pendaftar($db);

// Get data pendaftar
$data = $pendaftar->getByUserId($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pendaftaran - PPDB SMK IGASAR PINDAD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Poppins', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            min-height: 100vh;
        }
        
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        
        .sidebar {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 4px 0 20px rgba(0,0,0,0.05);
            min-height: calc(100vh - 56px);
        }
        
        .nav-link {
            color: #4a5568 !important;
            font-weight: 500;
            padding: 12px 20px;
            margin: 2px 10px;
            border-radius: 10px;
            transition: all 0.3s ease;
        }
        
        .nav-link:hover, .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white !important;
            transform: translateX(5px);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .status-card {
            text-align: center;
            padding: 40px 20px;
        }
        
        .status-icon {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            font-size: 2.5rem;
        }
        
        .status-pending {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
        }
        
        .status-diterima {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }
        
        .status-ditolak {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .data-table td:first-child {
            font-weight: 600;
            color: #4a5568;
            width: 200px;
        }
        
        .btn-print {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-print:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
            color: white;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 no-print">
                    <div>
                        <h1 class="h2 text-primary fw-bold">
                            <i class="fas fa-clipboard-check me-3"></i>Status Pendaftaran
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Status Pendaftaran</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <?php if ($data): ?>
                <!-- Status Card -->
                <div class="card mb-4">
                    <div class="card-body status-card">
                        <div class="status-icon status-<?php echo $data['status_pendaftaran']; ?>">
                            <i class="fas fa-<?php 
                                echo $data['status_pendaftaran'] == 'diterima' ? 'check-circle' : 
                                    ($data['status_pendaftaran'] == 'ditolak' ? 'times-circle' : 'clock'); 
                            ?>"></i>
                        </div>
                        <h3 class="fw-bold mb-2">
                            Status: <?php echo strtoupper($data['status_pendaftaran']); ?>
                        </h3>
                        <p class="text-muted mb-0">
                            <?php if ($data['status_pendaftaran'] == 'pending'): ?>
                                Pendaftaran Anda sedang dalam proses verifikasi oleh tim PPDB.
                            <?php elseif ($data['status_pendaftaran'] == 'diterima'): ?>
                                Selamat! Anda diterima di jurusan <?php echo $data['nama_jurusan']; ?>.
                            <?php else: ?>
                                Mohon maaf, pendaftaran Anda tidak dapat kami terima saat ini.
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
                
                <!-- Data Pendaftaran -->
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">
                            <i class="fas fa-user me-2 text-primary"></i>
                            Data Pendaftaran Lengkap
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless data-table">
                                    <tr>
                                        <td>No. Pendaftaran</td>
                                        <td>: <span class="text-primary fw-bold"><?php echo $data['no_daftar']; ?></span></td>
                                    </tr>
                                    <tr>
                                        <td>Nama Lengkap</td>
                                        <td>: <?php echo $data['nama']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>NISN</td>
                                        <td>: <?php echo $data['nisn']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Jenis Kelamin</td>
                                        <td>: <?php echo $data['jk'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Tempat, Tanggal Lahir</td>
                                        <td>: <?php echo $data['tempat_lahir'] . ', ' . date('d F Y', strtotime($data['tanggal_lahir'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td>Alamat</td>
                                        <td>: <?php echo $data['alamat']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>No. HP</td>
                                        <td>: <?php echo $data['no_hp']; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless data-table">
                                    <tr>
                                        <td>Nama Ayah</td>
                                        <td>: <?php echo $data['nama_ayah']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Nama Ibu</td>
                                        <td>: <?php echo $data['nama_ibu']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Asal Sekolah</td>
                                        <td>: <?php echo $data['asal_sekolah']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Nilai Rata-rata</td>
                                        <td>: <span class="fw-bold text-success"><?php echo $data['nilai_rata']; ?></span></td>
                                    </tr>
                                    <tr>
                                        <td>Jurusan Pilihan</td>
                                        <td>: <span class="fw-bold text-primary"><?php echo $data['nama_jurusan']; ?></span></td>
                                    </tr>
                                    <tr>
                                        <td>Tahun Ajaran</td>
                                        <td>: <?php echo $data['tahun_ajaran']; ?></td>
                                    </tr>
                                    <tr>
                                        <td>Tanggal Pendaftaran</td>
                                        <td>: <?php echo date('d F Y, H:i', strtotime($data['tanggal_daftar'])); ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        
                        <div class="row mt-4 no-print">
                            <div class="col-12">
                                <div class="d-flex gap-3 justify-content-center">
                                    <?php if ($data['status_pendaftaran'] == 'pending'): ?>
                                    <a href="pendaftaran.php" class="btn btn-warning">
                                        <i class="fas fa-edit me-2"></i>Edit Data
                                    </a>
                                    <?php endif; ?>
                                    <button onclick="window.print()" class="btn btn-print">
                                        <i class="fas fa-print me-2"></i>Cetak Bukti Pendaftaran
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php else: ?>
                <!-- Belum Daftar -->
                <div class="card">
                    <div class="card-body text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-exclamation-triangle fa-4x text-warning"></i>
                        </div>
                        <h3 class="fw-bold text-warning mb-3">Belum Ada Data Pendaftaran</h3>
                        <p class="text-muted mb-4">
                            Anda belum melakukan pendaftaran sebagai calon siswa baru.<br>
                            Silakan lengkapi formulir pendaftaran terlebih dahulu.
                        </p>
                        <a href="pendaftaran.php" class="btn btn-primary btn-lg">
                            <i class="fas fa-user-plus me-2"></i>Daftar Sekarang
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
