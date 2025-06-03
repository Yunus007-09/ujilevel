<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'classes/LaporanPenerimaan.php';
require_once 'classes/MonitoringPenerimaan.php';
require_once 'classes/BiayaTahunan.php';
require_once 'classes/Pendaftar.php';

checkPetugas(); // Hanya admin dan petugas yang bisa akses

$database = new Database();
$db = $database->getConnection();
$laporan = new LaporanPenerimaan($db);
$monitoring = new MonitoringPenerimaan($db);
$biaya = new BiayaTahunan($db);
$pendaftar = new Pendaftar($db);

$message = '';
$message_type = '';

// Default values
$tahun_ajaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : '2024/2025';
$jenis_laporan = isset($_GET['jenis_laporan']) ? $_GET['jenis_laporan'] : 'ringkasan';
$format_export = isset($_POST['format_export']) ? $_POST['format_export'] : '';

// Handle laporan generation
if ($_POST && isset($_POST['action'])) {
    if ($_POST['action'] == 'generate_laporan') {
        $laporan->tahun_ajaran = $_POST['tahun_ajaran'];
        
        if ($laporan->generateLaporan()) {
            $message = "Laporan berhasil digenerate!";
            $message_type = "success";
        } else {
            $message = "Gagal membuat laporan!";
            $message_type = "danger";
        }
    }
}

// Get tahun ajaran list
$tahun_query = "SELECT DISTINCT tahun_ajaran FROM biaya_tahunan ORDER BY tahun_ajaran DESC";
$tahun_stmt = $db->prepare($tahun_query);
$tahun_stmt->execute();
$tahun_list = $tahun_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get data based on selected filters
$stats = $monitoring->tampilkanStatistik($tahun_ajaran);
$biaya_info = $biaya->getByTahunAjaran($tahun_ajaran);

// Get detailed data for reports
$pendaftar_list = null;
$jurusan_stats = null;
$financial_stats = null;

if ($jenis_laporan == 'detail_pendaftar') {
    $query = "SELECT p.*, j.nama_jurusan, pt.nama_petugas 
             FROM pendaftar p 
             LEFT JOIN jurusan j ON p.kode_jur = j.kode_jur 
             LEFT JOIN petugas pt ON p.kd_petugas = pt.kd_petugas
             WHERE p.tahun_ajaran = ? 
             ORDER BY p.tanggal_daftar DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(1, $tahun_ajaran);
    $stmt->execute();
    $pendaftar_list = $stmt;
} elseif ($jenis_laporan == 'per_jurusan') {
    $jurusan_stats = $stats['per_jurusan'];
} elseif ($jenis_laporan == 'keuangan') {
    $financial_stats = $stats['biaya'];
}

// Handle export
if ($_POST && isset($_POST['export'])) {
    $format = $_POST['format_export'];
    
    if ($format == 'pdf') {
        // Redirect to PDF export
        header("Location: export_pdf.php?tahun_ajaran=" . urlencode($tahun_ajaran) . "&jenis=" . urlencode($jenis_laporan));
        exit();
    } elseif ($format == 'excel') {
        // Redirect to Excel export
        header("Location: export_excel.php?tahun_ajaran=" . urlencode($tahun_ajaran) . "&jenis=" . urlencode($jenis_laporan));
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - PPDB SMK IGASAR PINDAD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
        
        .filter-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .filter-card .form-control, .filter-card .form-select {
            border: 2px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            color: white;
            border-radius: 10px;
        }
        
        .filter-card .form-control::placeholder {
            color: rgba(255,255,255,0.7);
        }
        
        .filter-card .form-control:focus, .filter-card .form-select:focus {
            border-color: rgba(255,255,255,0.8);
            box-shadow: 0 0 0 0.2rem rgba(255,255,255,0.25);
            background: rgba(255,255,255,0.2);
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
        
        .table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-weight: 600;
        }
        
        .badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 500;
        }
        
        .chart-container {
            position: relative;
            height: 400px;
        }
        
        .btn-export {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
            border: none;
            color: white;
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-export:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(139, 92, 246, 0.3);
            color: white;
        }
        
        .report-header {
            background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
            border-left: 5px solid #667eea;
            padding: 20px;
            margin-bottom: 20px;
        }
        
        @media print {
            .no-print {
                display: none !important;
            }
            
            .card {
                box-shadow: none !important;
                border: 1px solid #ddd !important;
            }
            
            body {
                background: white !important;
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
                            <i class="fas fa-file-alt me-3"></i>Laporan PPDB
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Laporan</li>
                            </ol>
                        </nav>
                    </div>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button onclick="window.print()" class="btn btn-outline-primary me-2">
                            <i class="fas fa-print me-2"></i>Print
                        </button>
                        <div class="dropdown">
                            <button class="btn btn-export dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-2"></i>Export
                            </button>
                            <ul class="dropdown-menu">
                                <li>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="export" value="1">
                                        <input type="hidden" name="format_export" value="pdf">
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-file-pdf text-danger me-2"></i>Export PDF
                                        </button>
                                    </form>
                                </li>
                                <li>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="export" value="1">
                                        <input type="hidden" name="format_export" value="excel">
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-file-excel text-success me-2"></i>Export Excel
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show no-print" role="alert">
                        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <!-- Filter Section -->
                <div class="card filter-card mb-4 no-print">
                    <div class="card-body">
                        <h5 class="mb-3">
                            <i class="fas fa-filter me-2"></i>Filter Laporan
                        </h5>
                        <form method="GET" class="row g-3">
                            <div class="col-md-4">
                                <label for="tahun_ajaran" class="form-label">Tahun Ajaran</label>
                                <select class="form-select" name="tahun_ajaran" id="tahun_ajaran">
                                    <?php foreach ($tahun_list as $tahun): ?>
                                    <option value="<?php echo $tahun['tahun_ajaran']; ?>" <?php echo $tahun['tahun_ajaran'] == $tahun_ajaran ? 'selected' : ''; ?>>
                                        <?php echo $tahun['tahun_ajaran']; ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label for="jenis_laporan" class="form-label">Jenis Laporan</label>
                                <select class="form-select" name="jenis_laporan" id="jenis_laporan">
                                    <option value="ringkasan" <?php echo $jenis_laporan == 'ringkasan' ? 'selected' : ''; ?>>Ringkasan</option>
                                    <option value="detail_pendaftar" <?php echo $jenis_laporan == 'detail_pendaftar' ? 'selected' : ''; ?>>Detail Pendaftar</option>
                                    <option value="per_jurusan" <?php echo $jenis_laporan == 'per_jurusan' ? 'selected' : ''; ?>>Per Jurusan</option>
                                    <option value="keuangan" <?php echo $jenis_laporan == 'keuangan' ? 'selected' : ''; ?>>Keuangan</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4 d-flex align-items-end">
                                <button type="submit" class="btn btn-light me-2">
                                    <i class="fas fa-search me-2"></i>Tampilkan
                                </button>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="action" value="generate_laporan">
                                    <input type="hidden" name="tahun_ajaran" value="<?php echo $tahun_ajaran; ?>">
                                    <button type="submit" class="btn btn-warning">
                                        <i class="fas fa-cog me-2"></i>Generate
                                    </button>
                                </form>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Report Header -->
                <div class="report-header">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h3 class="fw-bold mb-2">
                                Laporan PPDB SMK IGASAR PINDAD
                            </h3>
                            <p class="mb-1">Tahun Ajaran: <strong><?php echo $tahun_ajaran; ?></strong></p>
                            <p class="mb-0">Jenis Laporan: <strong><?php 
                                $jenis_names = [
                                    'ringkasan' => 'Ringkasan',
                                    'detail_pendaftar' => 'Detail Pendaftar',
                                    'per_jurusan' => 'Per Jurusan',
                                    'keuangan' => 'Keuangan'
                                ];
                                echo $jenis_names[$jenis_laporan];
                            ?></strong></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <p class="mb-1">Tanggal Cetak: <strong><?php echo date('d F Y'); ?></strong></p>
                            <p class="mb-0">Waktu: <strong><?php echo date('H:i:s'); ?></strong></p>
                        </div>
                    </div>
                </div>
                
                <?php if ($jenis_laporan == 'ringkasan'): ?>
                <!-- Ringkasan Laporan -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="fw-bold text-primary mb-1"><?php echo $stats['total_pendaftar']; ?></h3>
                                        <p class="text-muted mb-0">Total Pendaftar</p>
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
                                        <?php
                                        $diterima_query = "SELECT COUNT(*) as total FROM pendaftar WHERE status_pendaftaran = 'diterima' AND tahun_ajaran = ?";
                                        $diterima_stmt = $db->prepare($diterima_query);
                                        $diterima_stmt->bindParam(1, $tahun_ajaran);
                                        $diterima_stmt->execute();
                                        $diterima_count = $diterima_stmt->fetch(PDO::FETCH_ASSOC);
                                        ?>
                                        <h3 class="fw-bold text-success mb-1"><?php echo $diterima_count['total']; ?></h3>
                                        <p class="text-muted mb-0">Diterima</p>
                                    </div>
                                    <div class="stats-icon success">
                                        <i class="fas fa-user-check"></i>
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
                                        <?php
                                        $pending_query = "SELECT COUNT(*) as total FROM pendaftar WHERE status_pendaftaran = 'pending' AND tahun_ajaran = ?";
                                        $pending_stmt = $db->prepare($pending_query);
                                        $pending_stmt->bindParam(1, $tahun_ajaran);
                                        $pending_stmt->execute();
                                        $pending_count = $pending_stmt->fetch(PDO::FETCH_ASSOC);
                                        ?>
                                        <h3 class="fw-bold text-warning mb-1"><?php echo $pending_count['total']; ?></h3>
                                        <p class="text-muted mb-0">Pending</p>
                                    </div>
                                    <div class="stats-icon warning">
                                        <i class="fas fa-clock"></i>
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
                                        <h3 class="fw-bold text-info mb-1">Rp <?php echo number_format($stats['biaya']['total_biaya_pendaftaran'] ?? 0, 0, ',', '.'); ?></h3>
                                        <p class="text-muted mb-0">Total Biaya</p>
                                    </div>
                                    <div class="stats-icon info">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Chart Ringkasan -->
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
                                    <i class="fas fa-chart-pie me-2 text-primary"></i>
                                    Status Pendaftaran
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="statusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php elseif ($jenis_laporan == 'detail_pendaftar'): ?>
                <!-- Detail Pendaftar -->
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">
                            <i class="fas fa-users me-2 text-primary"></i>
                            Detail Data Pendaftar
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover" id="pendaftarTable">
                                <thead>
                                    <tr>
                                        <th>No. Daftar</th>
                                        <th>Nama</th>
                                        <th>NISN</th>
                                        <th>Jurusan</th>
                                        <th>Nilai Rata-rata</th>
                                        <th>Status</th>
                                        <th>Tanggal Daftar</th>
                                        <th>Petugas</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($row = $pendaftar_list->fetch(PDO::FETCH_ASSOC)): ?>
                                    <tr>
                                        <td class="fw-bold text-primary"><?php echo $row['no_daftar']; ?></td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($row['nama']); ?></strong>
                                                <br><small class="text-muted"><?php echo $row['jk'] == 'L' ? 'Laki-laki' : 'Perempuan'; ?></small>
                                            </div>
                                        </td>
                                        <td><?php echo $row['nisn']; ?></td>
                                        <td>
                                            <small class="text-muted"><?php echo $row['kode_jur']; ?></small><br>
                                            <?php echo $row['nama_jurusan']; ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php echo $row['nilai_rata'] >= 80 ? 'success' : ($row['nilai_rata'] >= 70 ? 'warning' : 'danger'); ?>">
                                                <?php echo $row['nilai_rata']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-<?php 
                                                echo $row['status_pendaftaran'] == 'diterima' ? 'success' : 
                                                    ($row['status_pendaftaran'] == 'ditolak' ? 'danger' : 'warning'); 
                                            ?>">
                                                <?php echo ucfirst($row['status_pendaftaran']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($row['tanggal_daftar'])); ?></td>
                                        <td><?php echo $row['nama_petugas'] ?? '-'; ?></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <?php elseif ($jenis_laporan == 'per_jurusan'): ?>
                <!-- Laporan Per Jurusan -->
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">
                            <i class="fas fa-graduation-cap me-2 text-primary"></i>
                            Laporan Per Jurusan
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Kode Jurusan</th>
                                        <th>Nama Jurusan</th>
                                        <th>Kuota</th>
                                        <th>Total Pendaftar</th>
                                        <th>Diterima</th>
                                        <th>Pending</th>
                                        <th>Persentase Terisi</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $jurusan_stats->execute(); // Reset pointer
                                    $total_kuota = 0;
                                    $total_pendaftar = 0;
                                    $total_diterima = 0;
                                    
                                    while ($row = $jurusan_stats->fetch(PDO::FETCH_ASSOC)):
                                        $percentage = ($row['jumlah_pendaftar'] / $row['kuota']) * 100;
                                        $percentage = min($percentage, 100);
                                        
                                        $total_kuota += $row['kuota'];
                                        $total_pendaftar += $row['jumlah_pendaftar'];
                                        $total_diterima += $row['diterima'];
                                        
                                        $status_class = 'success';
                                        $status_text = 'Normal';
                                        if ($percentage >= 100) {
                                            $status_class = 'danger';
                                            $status_text = 'Penuh';
                                        } elseif ($percentage >= 80) {
                                            $status_class = 'warning';
                                            $status_text = 'Hampir Penuh';
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo $row['kode_jur']; ?></td>
                                        <td><?php echo $row['nama_jurusan']; ?></td>
                                        <td><?php echo $row['kuota']; ?></td>
                                        <td><?php echo $row['jumlah_pendaftar']; ?></td>
                                        <td><?php echo $row['diterima']; ?></td>
                                        <td><?php echo $row['pending']; ?></td>
                                        <td><?php echo round($percentage, 1); ?>%</td>
                                        <td>
                                            <span class="badge bg-<?php echo $status_class; ?>">
                                                <?php echo $status_text; ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                                <tfoot class="table-secondary">
                                    <tr>
                                        <th colspan="2">TOTAL</th>
                                        <th><?php echo $total_kuota; ?></th>
                                        <th><?php echo $total_pendaftar; ?></th>
                                        <th><?php echo $total_diterima; ?></th>
                                        <th><?php echo $total_pendaftar - $total_diterima; ?></th>
                                        <th><?php echo $total_kuota > 0 ? round(($total_pendaftar / $total_kuota) * 100, 1) : 0; ?>%</th>
                                        <th>-</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
                
                <?php elseif ($jenis_laporan == 'keuangan'): ?>
                <!-- Laporan Keuangan -->
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-money-bill-wave me-2 text-primary"></i>
                                    Laporan Keuangan PPDB
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if ($biaya_info): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Jenis Biaya</th>
                                                <th>Biaya per Siswa</th>
                                                <th>Jumlah Siswa</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Biaya Pendaftaran</td>
                                                <td>Rp <?php echo number_format($biaya_info['b_pendaftaran'], 0, ',', '.'); ?></td>
                                                <td><?php echo $stats['total_pendaftar']; ?></td>
                                                <td class="fw-bold">Rp <?php echo number_format($biaya_info['b_pendaftaran'] * $stats['total_pendaftar'], 0, ',', '.'); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Biaya Awal Tahun</td>
                                                <td>Rp <?php echo number_format($biaya_info['b_awal_tahun'], 0, ',', '.'); ?></td>
                                                <td><?php echo $diterima_count['total']; ?></td>
                                                <td class="fw-bold">Rp <?php echo number_format($biaya_info['b_awal_tahun'] * $diterima_count['total'], 0, ',', '.'); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Biaya Seragam</td>
                                                <td>Rp <?php echo number_format($biaya_info['b_seragam'], 0, ',', '.'); ?></td>
                                                <td><?php echo $diterima_count['total']; ?></td>
                                                <td class="fw-bold">Rp <?php echo number_format($biaya_info['b_seragam'] * $diterima_count['total'], 0, ',', '.'); ?></td>
                                            </tr>
                                            <tr>
                                                <td>SPP (per bulan)</td>
                                                <td>Rp <?php echo number_format($biaya_info['b_spp'], 0, ',', '.'); ?></td>
                                                <td><?php echo $diterima_count['total']; ?> x 12 bulan</td>
                                                <td class="fw-bold">Rp <?php echo number_format($biaya_info['b_spp'] * $diterima_count['total'] * 12, 0, ',', '.'); ?></td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="table-primary">
                                            <tr>
                                                <th colspan="3">TOTAL PENDAPATAN TAHUNAN</th>
                                                <th>Rp <?php 
                                                $total_pendapatan = ($biaya_info['b_pendaftaran'] * $stats['total_pendaftar']) + 
                                                                   (($biaya_info['b_awal_tahun'] + $biaya_info['b_seragam']) * $diterima_count['total']) + 
                                                                   ($biaya_info['b_spp'] * $diterima_count['total'] * 12);
                                                echo number_format($total_pendapatan, 0, ',', '.'); 
                                                ?></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <?php else: ?>
                                <div class="text-center text-muted">
                                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                    <p>Belum ada data biaya untuk tahun ajaran ini.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-pie me-2 text-primary"></i>
                                    Distribusi Pendapatan
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container" style="height: 300px;">
                                    <canvas id="pendapatanChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Footer Laporan -->
                <div class="card mt-4">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="fw-bold">Catatan:</h6>
                                <ul class="list-unstyled">
                                    <li>• Data diambil dari sistem PPDB SMK IGASAR PINDAD</li>
                                    <li>• Laporan ini dibuat secara otomatis oleh sistem</li>
                                    <li>• Untuk informasi lebih lanjut hubungi panitia PPDB</li>
                                </ul>
                            </div>
                            <div class="col-md-6 text-end">
                                <p class="mb-1">Bandung, <?php echo date('d F Y'); ?></p>
                                <p class="mb-4">Panitia PPDB</p>
                                <div style="height: 60px;"></div>
                                <p class="mb-0">(_____________________)</p>
                                <p class="mb-0">Kepala Sekolah</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#pendaftarTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
                },
                "order": [[ 6, "desc" ]],
                "pageLength": 25
            });
        });
        
        <?php if ($jenis_laporan == 'ringkasan'): ?>
        // Chart for Jurusan Statistics
        <?php
        $jurusan_stmt = $stats['per_jurusan'];
        $labels = [];
        $data_pendaftar = [];
        $data_kuota = [];
        
        // Reset the pointer to the beginning
        $jurusan_stmt->execute();
        
        while ($row = $jurusan_stmt->fetch(PDO::FETCH_ASSOC)) {
            $labels[] = $row['nama_jurusan'];
            $data_pendaftar[] = $row['jumlah_pendaftar'];
            $data_kuota[] = $row['kuota'];
        }
        
        // Get status distribution
        $status_query = "SELECT status_pendaftaran, COUNT(*) as total FROM pendaftar WHERE tahun_ajaran = ? GROUP BY status_pendaftaran";
        $status_stmt = $db->prepare($status_query);
        $status_stmt->bindParam(1, $tahun_ajaran);
        $status_stmt->execute();
        $status_data = $status_stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $status_labels = [];
        $status_counts = [];
        $status_colors = [];
        
        foreach ($status_data as $status) {
            $status_labels[] = ucfirst($status['status_pendaftaran']);
            $status_counts[] = $status['total'];
            
            if ($status['status_pendaftaran'] == 'diterima') {
                $status_colors[] = 'rgba(72, 187, 120, 0.8)';
            } elseif ($status['status_pendaftaran'] == 'ditolak') {
                $status_colors[] = 'rgba(239, 68, 68, 0.8)';
            } else {
                $status_colors[] = 'rgba(251, 191, 36, 0.8)';
            }
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
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    }
                }
            }
        });
        
        // Status Chart
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($status_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($status_counts); ?>,
                    backgroundColor: <?php echo json_encode($status_colors); ?>,
                    borderColor: 'white',
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                family: 'Poppins',
                                size: 12,
                                weight: '500'
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
        
        <?php if ($jenis_laporan == 'keuangan' && $biaya_info): ?>
        // Pendapatan Chart
        const pendapatanCtx = document.getElementById('pendapatanChart').getContext('2d');
        const pendapatanChart = new Chart(pendapatanCtx, {
            type: 'pie',
            data: {
                labels: ['Pendaftaran', 'Awal Tahun', 'Seragam', 'SPP (Tahunan)'],
                datasets: [{
                    data: [
                        <?php echo $biaya_info['b_pendaftaran'] * $stats['total_pendaftar']; ?>,
                        <?php echo $biaya_info['b_awal_tahun'] * $diterima_count['total']; ?>,
                        <?php echo $biaya_info['b_seragam'] * $diterima_count['total']; ?>,
                        <?php echo $biaya_info['b_spp'] * $diterima_count['total'] * 12; ?>
                    ],
                    backgroundColor: [
                        'rgba(102, 126, 234, 0.8)',
                        'rgba(72, 187, 120, 0.8)',
                        'rgba(251, 191, 36, 0.8)',
                        'rgba(239, 68, 68, 0.8)'
                    ],
                    borderColor: 'white',
                    borderWidth: 2,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                family: 'Poppins',
                                size: 11,
                                weight: '500'
                            }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.label + ': Rp ' + context.parsed.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>
