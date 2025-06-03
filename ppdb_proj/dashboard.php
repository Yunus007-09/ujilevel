<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'classes/MonitoringPenerimaan.php';
require_once 'classes/BiayaTahunan.php';

checkLogin();

$database = new Database();
$db = $database->getConnection();
$monitoring = new MonitoringPenerimaan($db);
$biaya = new BiayaTahunan($db);

$tahun_ajaran = '2024/2025';
$stats = $monitoring->tampilkanStatistik($tahun_ajaran);
$biaya_info = $biaya->getByTahunAjaran($tahun_ajaran);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - PPDB SMK IGASAR PINDAD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.css" rel="stylesheet">
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
            border-bottom: 3px solid rgba(255,255,255,0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.3rem;
        }
        
        .sidebar {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 4px 0 20px rgba(0,0,0,0.05);
            border-right: 1px solid #e2e8f0;
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
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
        }
        
        .stats-card {
            position: relative;
            overflow: hidden;
        }
        
        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--gradient-start), var(--gradient-end));
        }
        
        .stats-card.primary {
            --gradient-start: #667eea;
            --gradient-end: #764ba2;
        }
        
        .stats-card.success {
            --gradient-start: #48bb78;
            --gradient-end: #38a169;
        }
        
        .stats-card.info {
            --gradient-start: #4299e1;
            --gradient-end: #3182ce;
        }
        
        .stats-card.warning {
            --gradient-start: #ed8936;
            --gradient-end: #dd6b20;
        }
        
        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }
        
        .stats-icon.primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }
        
        .stats-icon.success {
            background: linear-gradient(135deg, #48bb78, #38a169);
            color: white;
        }
        
        .stats-icon.info {
            background: linear-gradient(135deg, #4299e1, #3182ce);
            color: white;
        }
        
        .stats-icon.warning {
            background: linear-gradient(135deg, #ed8936, #dd6b20);
            color: white;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .welcome-card .card-body {
            position: relative;
            z-index: 2;
        }
        
        .welcome-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="%23ffffff10"/></svg>');
            background-size: contain;
            opacity: 0.1;
        }
        
        .badge-role {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .page-title {
            color: #2d3748;
            font-weight: 700;
            margin-bottom: 30px;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
        }
        
        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            color: #718096;
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <div>
                        <h1 class="page-title">
                            <i class="fas fa-tachometer-alt me-3"></i>Dashboard PPDB
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Overview</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-outline-primary">
                                <i class="fas fa-calendar me-2"></i><?php echo $tahun_ajaran; ?>
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Welcome Card -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card welcome-card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-8">
                                        <h3 class="mb-2">
                                            <i class="fas fa-hand-wave me-2"></i>
                                            Selamat Datang, <?php echo $_SESSION['username']; ?>!
                                        </h3>
                                        <p class="mb-3">Sistem Penerimaan Peserta Didik Baru SMK IGASAR PINDAD Bandung</p>
                                        <span class="badge-role bg-white text-primary">
                                            <i class="fas fa-user-tag me-1"></i>
                                            <?php echo ucfirst($_SESSION['role']); ?>
                                        </span>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <i class="fas fa-graduation-cap fa-4x opacity-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="fw-bold text-primary mb-1"><?php echo $stats['total_pendaftar']; ?></h3>
                                        <p class="text-muted mb-0">Total Pendaftar</p>
                                        <small class="text-success">
                                            <i class="fas fa-arrow-up me-1"></i>
                                            +12% dari tahun lalu
                                        </small>
                                    </div>
                                    <div class="stats-icon primary">
                                        <i class="fas fa-users"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card success">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="fw-bold text-success mb-1">Rp <?php echo number_format($stats['biaya']['total_biaya_pendaftaran'] ?? 0, 0, ',', '.'); ?></h3>
                                        <p class="text-muted mb-0">Total Biaya Pendaftaran</p>
                                        <small class="text-success">
                                            <i class="fas fa-check-circle me-1"></i>
                                            Terkumpul
                                        </small>
                                    </div>
                                    <div class="stats-icon success">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card info">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="fw-bold text-info mb-1">
                                            <?php 
                                            $jurusan_count_query = "SELECT COUNT(*) as total FROM jurusan WHERE status = 'aktif'";
                                            $jurusan_count_stmt = $db->prepare($jurusan_count_query);
                                            $jurusan_count_stmt->execute();
                                            $jurusan_count = $jurusan_count_stmt->fetch(PDO::FETCH_ASSOC);
                                            echo $jurusan_count['total'];
                                            ?>
                                        </h3>
                                        <p class="text-muted mb-0">Jurusan Aktif</p>
                                        <small class="text-info">
                                            <i class="fas fa-cog me-1"></i>
                                            Program Keahlian
                                        </small>
                                    </div>
                                    <div class="stats-icon info">
                                        <i class="fas fa-list-alt"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card warning">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="fw-bold text-warning mb-1">
                                            <?php 
                                            $diterima_query = "SELECT COUNT(*) as total FROM pendaftar WHERE status_pendaftaran = 'diterima' AND tahun_ajaran = ?";
                                            $diterima_stmt = $db->prepare($diterima_query);
                                            $diterima_stmt->bindParam(1, $tahun_ajaran);
                                            $diterima_stmt->execute();
                                            $diterima_count = $diterima_stmt->fetch(PDO::FETCH_ASSOC);
                                            echo $diterima_count['total'];
                                            ?>
                                        </h3>
                                        <p class="text-muted mb-0">Siswa Diterima</p>
                                        <small class="text-warning">
                                            <i class="fas fa-graduation-cap me-1"></i>
                                            Tahun <?php echo $tahun_ajaran; ?>
                                        </small>
                                    </div>
                                    <div class="stats-icon warning">
                                        <i class="fas fa-user-graduate"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts and Information -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-bar me-2 text-primary"></i>
                                    Statistik Pendaftar per Jurusan
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="jurusanChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-money-bill-wave me-2 text-success"></i>
                                    Rincian Biaya
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if ($biaya_info): ?>
                                <div class="table-responsive">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td>
                                                <i class="fas fa-file-invoice text-primary me-2"></i>
                                                Pendaftaran
                                            </td>
                                            <td class="text-end fw-bold">
                                                Rp <?php echo number_format($biaya_info['b_pendaftaran'], 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <i class="fas fa-school text-info me-2"></i>
                                                Awal Tahun
                                            </td>
                                            <td class="text-end fw-bold">
                                                Rp <?php echo number_format($biaya_info['b_awal_tahun'], 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <i class="fas fa-tshirt text-warning me-2"></i>
                                                Seragam
                                            </td>
                                            <td class="text-end fw-bold">
                                                Rp <?php echo number_format($biaya_info['b_seragam'], 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <i class="fas fa-calendar-alt text-secondary me-2"></i>
                                                SPP/Bulan
                                            </td>
                                            <td class="text-end fw-bold">
                                                Rp <?php echo number_format($biaya_info['b_spp'], 0, ',', '.'); ?>
                                            </td>
                                        </tr>
                                        <tr class="border-top">
                                            <td class="fw-bold text-primary">
                                                <i class="fas fa-calculator me-2"></i>
                                                Total Biaya
                                            </td>
                                            <td class="text-end fw-bold text-primary fs-5">
                                                Rp <?php 
                                                $total = $biaya_info['b_pendaftaran'] + $biaya_info['b_awal_tahun'] + $biaya_info['b_seragam'] + $biaya_info['b_spp'];
                                                echo number_format($total, 0, ',', '.'); 
                                                ?>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="text-center text-muted">
                                    <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                    <p>Belum ada informasi biaya untuk tahun ajaran ini.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Role-based Information -->
                <div class="row">
                    <div class="col-md-12">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-info-circle me-2 text-info"></i>
                                    Informasi & Fitur Akses
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if ($_SESSION['role'] == 'admin'): ?>
                                    <div class="alert alert-primary border-0" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-user-shield me-2"></i>
                                            Panel Administrator
                                        </h6>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <ul class="list-unstyled mb-0">
                                                    <li><i class="fas fa-check text-success me-2"></i>Kelola data pendaftar dan verifikasi</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Kelola jurusan dan kuota</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Kelola petugas dan hak akses</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul class="list-unstyled mb-0">
                                                    <li><i class="fas fa-check text-success me-2"></i>Kelola biaya tahunan</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Generate laporan lengkap</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Monitoring statistik real-time</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <?php elseif ($_SESSION['role'] == 'petugas'): ?>
                                    <div class="alert alert-success border-0" style="background: linear-gradient(135deg, #e8f5e8 0%, #c8e6c8 100%);">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-user-tie me-2"></i>
                                            Panel Petugas
                                        </h6>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <ul class="list-unstyled mb-0">
                                                    <li><i class="fas fa-check text-success me-2"></i>Verifikasi data pendaftar</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Proses penerimaan siswa</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul class="list-unstyled mb-0">
                                                    <li><i class="fas fa-check text-success me-2"></i>Lihat monitoring pendaftaran</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Generate laporan</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="alert alert-warning border-0" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);">
                                        <h6 class="alert-heading">
                                            <i class="fas fa-user me-2"></i>
                                            Panel Calon Siswa
                                        </h6>
                                        <hr>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <ul class="list-unstyled mb-0">
                                                    <li><i class="fas fa-check text-success me-2"></i>Daftar sebagai calon siswa</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Edit data pendaftaran</li>
                                                </ul>
                                            </div>
                                            <div class="col-md-6">
                                                <ul class="list-unstyled mb-0">
                                                    <li><i class="fas fa-check text-success me-2"></i>Cek status pendaftaran</li>
                                                    <li><i class="fas fa-check text-success me-2"></i>Lihat informasi biaya</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    <script>
        // Chart for Jurusan Statistics
        <?php
        $jurusan_stmt = $stats['per_jurusan'];
        $labels = [];
        $data_pendaftar = [];
        $data_kuota = [];
        
        while ($row = $jurusan_stmt->fetch(PDO::FETCH_ASSOC)) {
            $labels[] = $row['nama_jurusan'];
            $data_pendaftar[] = $row['jumlah_pendaftar'];
            $data_kuota[] = $row['kuota'];
        }
        ?>
        
        const ctx = document.getElementById('jurusanChart').getContext('2d');
        const jurusanChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Jumlah Pendaftar',
                    data: <?php echo json_encode($data_pendaftar); ?>,
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 1,
                    borderRadius: 8,
                    borderSkipped: false,
                }, {
                    label: 'Kuota',
                    data: <?php echo json_encode($data_kuota); ?>,
                    backgroundColor: 'rgba(72, 187, 120, 0.8)',
                    borderColor: 'rgba(72, 187, 120, 1)',
                    borderWidth: 1,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                family: 'Poppins',
                                size: 12,
                                weight: '500'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: {
                            family: 'Poppins',
                            size: 14,
                            weight: '600'
                        },
                        bodyFont: {
                            family: 'Poppins',
                            size: 12
                        },
                        cornerRadius: 8,
                        displayColors: true
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                family: 'Poppins',
                                size: 11
                            }
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                family: 'Poppins',
                                size: 11
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
