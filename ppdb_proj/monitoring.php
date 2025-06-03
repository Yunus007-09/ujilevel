<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'classes/MonitoringPenerimaan.php';
require_once 'classes/BiayaTahunan.php';

checkPetugas(); // Hanya admin dan petugas yang bisa akses

$database = new Database();
$db = $database->getConnection();
$monitoring = new MonitoringPenerimaan($db);
$biaya = new BiayaTahunan($db);

// Default tahun ajaran
$tahun_ajaran = isset($_GET['tahun_ajaran']) ? $_GET['tahun_ajaran'] : '2024/2025';

// Get monitoring data
$stats = $monitoring->tampilkanStatistik($tahun_ajaran);

// Get tahun ajaran list for dropdown
$tahun_query = "SELECT DISTINCT tahun_ajaran FROM biaya_tahunan ORDER BY tahun_ajaran DESC";
$tahun_stmt = $db->prepare($tahun_query);
$tahun_stmt->execute();
$tahun_list = $tahun_stmt->fetchAll(PDO::FETCH_ASSOC);

// Get additional statistics
$additional_stats = [];

// Status distribution
$status_query = "SELECT status_pendaftaran, COUNT(*) as total FROM pendaftar WHERE tahun_ajaran = ? GROUP BY status_pendaftaran";
$status_stmt = $db->prepare($status_query);
$status_stmt->bindParam(1, $tahun_ajaran);
$status_stmt->execute();
$status_data = $status_stmt->fetchAll(PDO::FETCH_ASSOC);

// Gender distribution
$gender_query = "SELECT jk, COUNT(*) as total FROM pendaftar WHERE tahun_ajaran = ? GROUP BY jk";
$gender_stmt = $db->prepare($gender_query);
$gender_stmt->bindParam(1, $tahun_ajaran);
$gender_stmt->execute();
$gender_data = $gender_stmt->fetchAll(PDO::FETCH_ASSOC);

// Registration trend (last 30 days)
$trend_query = "SELECT DATE(tanggal_daftar) as date, COUNT(*) as total 
               FROM pendaftar 
               WHERE tahun_ajaran = ? AND tanggal_daftar >= DATE_SUB(NOW(), INTERVAL 30 DAY)
               GROUP BY DATE(tanggal_daftar) 
               ORDER BY date ASC";
$trend_stmt = $db->prepare($trend_query);
$trend_stmt->bindParam(1, $tahun_ajaran);
$trend_stmt->execute();
$trend_data = $trend_stmt->fetchAll(PDO::FETCH_ASSOC);

// Top performing schools
$school_query = "SELECT asal_sekolah, COUNT(*) as total, AVG(nilai_rata) as avg_nilai
                FROM pendaftar 
                WHERE tahun_ajaran = ? 
                GROUP BY asal_sekolah 
                HAVING total >= 2
                ORDER BY total DESC, avg_nilai DESC 
                LIMIT 10";
$school_stmt = $db->prepare($school_query);
$school_stmt->bindParam(1, $tahun_ajaran);
$school_stmt->execute();
$school_data = $school_stmt->fetchAll(PDO::FETCH_ASSOC);

// Recent registrations
$recent_query = "SELECT p.*, j.nama_jurusan 
                FROM pendaftar p 
                LEFT JOIN jurusan j ON p.kode_jur = j.kode_jur
                WHERE p.tahun_ajaran = ? 
                ORDER BY p.tanggal_daftar DESC 
                LIMIT 10";
$recent_stmt = $db->prepare($recent_query);
$recent_stmt->bindParam(1, $tahun_ajaran);
$recent_stmt->execute();
$recent_data = $recent_stmt->fetchAll(PDO::FETCH_ASSOC);

// Average score by major
$score_query = "SELECT j.nama_jurusan, AVG(p.nilai_rata) as avg_score, COUNT(p.no_daftar) as total
               FROM pendaftar p
               JOIN jurusan j ON p.kode_jur = j.kode_jur
               WHERE p.tahun_ajaran = ?
               GROUP BY j.kode_jur, j.nama_jurusan
               ORDER BY avg_score DESC";
$score_stmt = $db->prepare($score_query);
$score_stmt->bindParam(1, $tahun_ajaran);
$score_stmt->execute();
$score_data = $score_stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monitoring - PPDB SMK IGASAR PINDAD</title>
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
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
        }
        
        .chart-container {
            position: relative;
            height: 400px;
        }
        
        .chart-container-small {
            position: relative;
            height: 300px;
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
        
        .stats-card.danger {
            --gradient-start: #ef4444;
            --gradient-end: #dc2626;
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
        
        .stats-icon.danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
        }
        
        .progress-bar-container {
            height: 10px;
            background-color: #e2e8f0;
            border-radius: 5px;
            overflow: hidden;
            margin-top: 8px;
        }
        
        .progress-bar {
            height: 100%;
            border-radius: 5px;
            transition: width 0.3s ease;
        }
        
        .monitoring-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .monitoring-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 300px;
            height: 300px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="%23ffffff10"/></svg>');
            background-size: contain;
            opacity: 0.1;
        }
        
        .monitoring-header .content {
            position: relative;
            z-index: 2;
        }
        
        .real-time-indicator {
            display: inline-flex;
            align-items: center;
            background: rgba(255,255,255,0.2);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            margin-left: 10px;
        }
        
        .real-time-dot {
            width: 8px;
            height: 8px;
            background: #48bb78;
            border-radius: 50%;
            margin-right: 6px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.5; }
            100% { opacity: 1; }
        }
        
        .metric-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        }
        
        .metric-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 5px;
        }
        
        .metric-label {
            color: #6b7280;
            font-size: 0.9rem;
            margin-bottom: 10px;
        }
        
        .metric-change {
            font-size: 0.8rem;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 500;
        }
        
        .metric-change.positive {
            background: #dcfce7;
            color: #166534;
        }
        
        .metric-change.negative {
            background: #fef2f2;
            color: #991b1b;
        }
        
        .recent-activity {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .activity-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f3f4f6;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            margin-right: 12px;
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-name {
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 2px;
        }
        
        .activity-details {
            font-size: 0.85rem;
            color: #6b7280;
        }
        
        .activity-time {
            font-size: 0.8rem;
            color: #9ca3af;
        }
        
        .filter-section {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.05);
        }
        
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        
        .table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            font-weight: 600;
            padding: 15px;
        }
        
        .table td {
            padding: 12px 15px;
            vertical-align: middle;
        }
        
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.8rem;
        }
        
        .auto-refresh {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 12px 20px;
            font-size: 0.9rem;
            font-weight: 500;
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.3);
            z-index: 1000;
        }
        
        .auto-refresh:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <!-- Header -->
                <div class="monitoring-header">
                    <div class="content">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <h1 class="fw-bold mb-2">
                                    <i class="fas fa-chart-line me-3"></i>
                                    Monitoring PPDB Real-time
                                </h1>
                                <p class="mb-0">Dashboard monitoring penerimaan peserta didik baru tahun ajaran <?php echo $tahun_ajaran; ?></p>
                                <span class="real-time-indicator">
                                    <span class="real-time-dot"></span>
                                    Live Data
                                </span>
                            </div>
                            <div class="col-md-4 text-end">
                                <div class="filter-section d-inline-block">
                                    <form method="GET" class="d-flex align-items-center">
                                        <select class="form-select form-select-sm me-2" name="tahun_ajaran" onchange="this.form.submit()" style="min-width: 150px;">
                                            <?php foreach ($tahun_list as $tahun): ?>
                                            <option value="<?php echo $tahun['tahun_ajaran']; ?>" <?php echo $tahun['tahun_ajaran'] == $tahun_ajaran ? 'selected' : ''; ?>>
                                                <?php echo $tahun['tahun_ajaran']; ?>
                                            </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn btn-light btn-sm">
                                            <i class="fas fa-filter"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Key Metrics -->
                <div class="row mb-4">
                    <div class="col-md-3 mb-3">
                        <div class="card stats-card primary">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="fw-bold text-primary mb-1"><?php echo $stats['total_pendaftar']; ?></h3>
                                        <p class="text-muted mb-0">Total Pendaftar</p>
                                        <div class="metric-change positive">
                                            <i class="fas fa-arrow-up me-1"></i>+5.2%
                                        </div>
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
                                        $diterima_count = 0;
                                        foreach ($status_data as $status) {
                                            if ($status['status_pendaftaran'] == 'diterima') {
                                                $diterima_count = $status['total'];
                                                break;
                                            }
                                        }
                                        ?>
                                        <h3 class="fw-bold text-success mb-1"><?php echo $diterima_count; ?></h3>
                                        <p class="text-muted mb-0">Siswa Diterima</p>
                                        <div class="metric-change positive">
                                            <i class="fas fa-arrow-up me-1"></i>+12.8%
                                        </div>
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
                                        $pending_count = 0;
                                        foreach ($status_data as $status) {
                                            if ($status['status_pendaftaran'] == 'pending') {
                                                $pending_count = $status['total'];
                                                break;
                                            }
                                        }
                                        ?>
                                        <h3 class="fw-bold text-warning mb-1"><?php echo $pending_count; ?></h3>
                                        <p class="text-muted mb-0">Menunggu Verifikasi</p>
                                        <div class="metric-change negative">
                                            <i class="fas fa-arrow-down me-1"></i>-2.1%
                                        </div>
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
                                        <p class="text-muted mb-0">Total Biaya Terkumpul</p>
                                        <div class="metric-change positive">
                                            <i class="fas fa-arrow-up me-1"></i>+8.7%
                                        </div>
                                    </div>
                                    <div class="stats-icon info">
                                        <i class="fas fa-money-bill-wave"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Charts Section -->
                <div class="row mb-4">
                    <!-- Registration Trend -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-line me-2 text-primary"></i>
                                    Trend Pendaftaran (30 Hari Terakhir)
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="trendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Distribution -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-pie me-2 text-primary"></i>
                                    Status Pendaftaran
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container-small">
                                    <canvas id="statusChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Jurusan Progress & Gender Distribution -->
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-tasks me-2 text-primary"></i>
                                    Progress Pendaftaran per Jurusan
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>Jurusan</th>
                                                <th>Kuota</th>
                                                <th>Pendaftar</th>
                                                <th>Diterima</th>
                                                <th>Progress</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $jurusan_stmt = $stats['per_jurusan'];
                                            while ($row = $jurusan_stmt->fetch(PDO::FETCH_ASSOC)):
                                                $percentage = ($row['jumlah_pendaftar'] / $row['kuota']) * 100;
                                                $percentage = min($percentage, 100);
                                                
                                                $color_class = 'bg-success';
                                                $status_text = 'Normal';
                                                $status_class = 'success';
                                                
                                                if ($percentage >= 100) {
                                                    $color_class = 'bg-danger';
                                                    $status_text = 'Penuh';
                                                    $status_class = 'danger';
                                                } elseif ($percentage >= 80) {
                                                    $color_class = 'bg-warning';
                                                    $status_text = 'Hampir Penuh';
                                                    $status_class = 'warning';
                                                } elseif ($percentage >= 50) {
                                                    $color_class = 'bg-info';
                                                }
                                            ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo $row['nama_jurusan']; ?></strong>
                                                    <br><small class="text-muted"><?php echo $row['kode_jur']; ?></small>
                                                </td>
                                                <td><?php echo $row['kuota']; ?></td>
                                                <td><?php echo $row['jumlah_pendaftar']; ?></td>
                                                <td><?php echo $row['diterima'] ?? 0; ?></td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="progress-bar-container flex-grow-1">
                                                            <div class="progress-bar <?php echo $color_class; ?>" style="width: <?php echo $percentage; ?>%"></div>
                                                        </div>
                                                        <span class="ms-2 fw-bold"><?php echo round($percentage); ?>%</span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-<?php echo $status_class; ?>">
                                                        <?php echo $status_text; ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card mb-3">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-venus-mars me-2 text-primary"></i>
                                    Distribusi Gender
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container-small">
                                    <canvas id="genderChart"></canvas>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-star me-2 text-primary"></i>
                                    Rata-rata Nilai per Jurusan
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($score_data as $score): ?>
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <strong><?php echo substr($score['nama_jurusan'], 0, 20); ?><?php echo strlen($score['nama_jurusan']) > 20 ? '...' : ''; ?></strong>
                                        <br><small class="text-muted"><?php echo $score['total']; ?> pendaftar</small>
                                    </div>
                                    <span class="badge bg-<?php echo $score['avg_score'] >= 85 ? 'success' : ($score['avg_score'] >= 75 ? 'warning' : 'danger'); ?> fs-6">
                                        <?php echo number_format($score['avg_score'], 1); ?>
                                    </span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Activity & Top Schools -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-clock me-2 text-primary"></i>
                                    Pendaftaran Terbaru
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="recent-activity">
                                    <?php foreach ($recent_data as $recent): ?>
                                    <div class="activity-item">
                                        <div class="activity-avatar">
                                            <?php echo strtoupper(substr($recent['nama'], 0, 1)); ?>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-name"><?php echo htmlspecialchars($recent['nama']); ?></div>
                                            <div class="activity-details">
                                                <?php echo $recent['nama_jurusan']; ?> • Nilai: <?php echo $recent['nilai_rata']; ?>
                                            </div>
                                        </div>
                                        <div class="activity-time">
                                            <?php echo date('d/m H:i', strtotime($recent['tanggal_daftar'])); ?>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-school me-2 text-primary"></i>
                                    Top 10 Asal Sekolah
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-sm">
                                        <thead>
                                            <tr>
                                                <th>Rank</th>
                                                <th>Asal Sekolah</th>
                                                <th>Jumlah</th>
                                                <th>Avg Nilai</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            $rank = 1;
                                            foreach ($school_data as $school): 
                                            ?>
                                            <tr>
                                                <td>
                                                    <span class="badge bg-<?php echo $rank <= 3 ? 'warning' : 'secondary'; ?>">
                                                        #<?php echo $rank; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($school['asal_sekolah']); ?></strong>
                                                </td>
                                                <td><?php echo $school['total']; ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $school['avg_nilai'] >= 85 ? 'success' : ($school['avg_nilai'] >= 75 ? 'warning' : 'danger'); ?>">
                                                        <?php echo number_format($school['avg_nilai'], 1); ?>
                                                    </span>
                                                </td>
                                            </tr>
                                            <?php 
                                            $rank++;
                                            endforeach; 
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Jurusan Statistics Chart -->
                <div class="card mb-4">
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
            </main>
        </div>
    </div>
    
    <!-- Auto Refresh Button -->
    <button class="auto-refresh" onclick="location.reload()">
        <i class="fas fa-sync-alt me-2"></i>
        Refresh Data
    </button>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
    
    <script>
        // Auto refresh every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000);
        
        // Chart configurations
        Chart.defaults.font.family = 'Poppins';
        Chart.defaults.font.size = 12;
        
        // Registration Trend Chart
        <?php
        $trend_labels = [];
        $trend_counts = [];
        
        // Fill missing dates with 0
        $start_date = date('Y-m-d', strtotime('-30 days'));
        $end_date = date('Y-m-d');
        
        $period = new DatePeriod(
            new DateTime($start_date),
            new DateInterval('P1D'),
            new DateTime($end_date . ' +1 day')
        );
        
        $trend_data_indexed = [];
        foreach ($trend_data as $trend) {
            $trend_data_indexed[$trend['date']] = $trend['total'];
        }
        
        foreach ($period as $date) {
            $date_str = $date->format('Y-m-d');
            $trend_labels[] = $date->format('d/m');
            $trend_counts[] = isset($trend_data_indexed[$date_str]) ? $trend_data_indexed[$date_str] : 0;
        }
        ?>
        
        const trendCtx = document.getElementById('trendChart').getContext('2d');
        const trendChart = new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($trend_labels); ?>,
                datasets: [{
                    label: 'Pendaftar per Hari',
                    data: <?php echo json_encode($trend_counts); ?>,
                    backgroundColor: 'rgba(102, 126, 234, 0.1)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 3,
                    tension: 0.4,
                    fill: true,
                    pointBackgroundColor: 'rgba(102, 126, 234, 1)',
                    pointBorderColor: 'white',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: { weight: '600' },
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { maxTicksLimit: 10 }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
        
        // Status Chart
        <?php
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
        
        const statusCtx = document.getElementById('statusChart').getContext('2d');
        const statusChart = new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($status_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($status_counts); ?>,
                    backgroundColor: <?php echo json_encode($status_colors); ?>,
                    borderColor: 'white',
                    borderWidth: 3,
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
                            font: { weight: '500' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: { weight: '600' },
                        cornerRadius: 8
                    }
                }
            }
        });
        
        // Gender Chart
        <?php
        $gender_labels = [];
        $gender_counts = [];
        $gender_colors = [];
        
        foreach ($gender_data as $gender) {
            $gender_labels[] = $gender['jk'] == 'L' ? 'Laki-laki' : 'Perempuan';
            $gender_counts[] = $gender['total'];
            
            if ($gender['jk'] == 'L') {
                $gender_colors[] = 'rgba(66, 153, 225, 0.8)';
            } else {
                $gender_colors[] = 'rgba(237, 100, 166, 0.8)';
            }
        }
        ?>
        
        const genderCtx = document.getElementById('genderChart').getContext('2d');
        const genderChart = new Chart(genderCtx, {
            type: 'pie',
            data: {
                labels: <?php echo json_encode($gender_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($gender_counts); ?>,
                    backgroundColor: <?php echo json_encode($gender_colors); ?>,
                    borderColor: 'white',
                    borderWidth: 3,
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
                            font: { weight: '500' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: { weight: '600' },
                        cornerRadius: 8
                    }
                }
            }
        });
        
        // Jurusan Chart
        <?php
        $jurusan_stmt = $stats['per_jurusan'];
        $labels = [];
        $data_pendaftar = [];
        $data_kuota = [];
        $data_diterima = [];
        
        // Reset the pointer to the beginning
        $jurusan_stmt->execute();
        
        while ($row = $jurusan_stmt->fetch(PDO::FETCH_ASSOC)) {
            $labels[] = $row['nama_jurusan'];
            $data_pendaftar[] = $row['jumlah_pendaftar'];
            $data_kuota[] = $row['kuota'];
            $data_diterima[] = $row['diterima'] ?? 0;
        }
        ?>
        
        const jurusanCtx = document.getElementById('jurusanChart').getContext('2d');
        const jurusanChart = new Chart(jurusanCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($labels); ?>,
                datasets: [{
                    label: 'Pendaftar',
                    data: <?php echo json_encode($data_pendaftar); ?>,
                    backgroundColor: 'rgba(102, 126, 234, 0.8)',
                    borderColor: 'rgba(102, 126, 234, 1)',
                    borderWidth: 1,
                    borderRadius: 8,
                    borderSkipped: false,
                }, {
                    label: 'Diterima',
                    data: <?php echo json_encode($data_diterima); ?>,
                    backgroundColor: 'rgba(72, 187, 120, 0.8)',
                    borderColor: 'rgba(72, 187, 120, 1)',
                    borderWidth: 1,
                    borderRadius: 8,
                    borderSkipped: false,
                }, {
                    label: 'Kuota',
                    data: <?php echo json_encode($data_kuota); ?>,
                    backgroundColor: 'rgba(237, 137, 54, 0.4)',
                    borderColor: 'rgba(237, 137, 54, 1)',
                    borderWidth: 2,
                    borderRadius: 8,
                    borderSkipped: false,
                    type: 'line',
                    order: 0
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
                            font: { weight: '500' }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        titleFont: { weight: '600' },
                        cornerRadius: 8
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: 'rgba(0, 0, 0, 0.05)' }
                    }
                }
            }
        });
    </script>
</body>
</html>

