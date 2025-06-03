<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'classes/Pendaftar.php';
require_once 'classes/Jurusan.php';

checkLogin();

// Hanya pendaftar yang bisa akses
if ($_SESSION['role'] != 'pendaftar') {
    header("Location: unauthorized.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();
$pendaftar = new Pendaftar($db);
$jurusan = new Jurusan($db);

$message = '';
$message_type = '';

// Cek apakah user sudah pernah daftar
$existing_data = $pendaftar->getByUserId($_SESSION['user_id']);

if ($_POST) {
    $data = [
        'nama' => trim($_POST['nama']),
        'alamat' => trim($_POST['alamat']),
        'jk' => $_POST['jk'],
        'kode_jur' => $_POST['kode_jur'],
        'tahun_ajaran' => $_POST['tahun_ajaran'],
        'user_id' => $_SESSION['user_id'],
        'nisn' => trim($_POST['nisn']),
        'tempat_lahir' => trim($_POST['tempat_lahir']),
        'tanggal_lahir' => $_POST['tanggal_lahir'],
        'nama_ayah' => trim($_POST['nama_ayah']),
        'nama_ibu' => trim($_POST['nama_ibu']),
        'no_hp' => trim($_POST['no_hp']),
        'asal_sekolah' => trim($_POST['asal_sekolah']),
        'nilai_rata' => $_POST['nilai_rata'],
        'kd_petugas' => '' // Will be assigned automatically
    ];
    
    if ($existing_data) {
        // Update data existing
        if ($pendaftar->editData($existing_data['no_daftar'], $data)) {
            $message = "Data berhasil diupdate!";
            $message_type = "success";
            $existing_data = $pendaftar->getByUserId($_SESSION['user_id']); // Refresh data
        } else {
            $message = "Gagal mengupdate data!";
            $message_type = "danger";
        }
    } else {
        // Daftar baru
        $no_daftar = $pendaftar->daftar($data);
        if ($no_daftar) {
            $message = "Pendaftaran berhasil! Nomor pendaftaran Anda: " . $no_daftar;
            $message_type = "success";
            $existing_data = $pendaftar->getByUserId($_SESSION['user_id']); // Get new data
        } else {
            $message = "Gagal melakukan pendaftaran!";
            $message_type = "danger";
        }
    }
}

// Get jurusan list
$jurusan_list = $jurusan->getJurusan();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran - PPDB SMK IGASAR PINDAD</title>
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
        
        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .section-title {
            color: #2d3748;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .info-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        
        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.9rem;
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
                        <h1 class="h2 text-primary fw-bold">
                            <i class="fas fa-user-plus me-3"></i>
                            <?php echo $existing_data ? 'Edit Data Pendaftaran' : 'Form Pendaftaran'; ?>
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Pendaftaran</li>
                            </ol>
                        </nav>
                    </div>
                </div>
                
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show" role="alert">
                        <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if ($existing_data && $existing_data['status_pendaftaran'] != 'pending'): ?>
                    <div class="alert alert-info border-0" style="background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-info-circle fa-2x me-3"></i>
                            <div>
                                <h6 class="mb-1">Status Pendaftaran Anda</h6>
                                <span class="status-badge bg-<?php echo $existing_data['status_pendaftaran'] == 'diterima' ? 'success' : 'danger'; ?>">
                                    <?php echo ucfirst($existing_data['status_pendaftaran']); ?>
                                </span>
                                <?php if ($existing_data['status_pendaftaran'] == 'diterima'): ?>
                                    <p class="mb-0 mt-2">Selamat! Anda diterima di <?php echo $existing_data['nama_jurusan']; ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">
                            <i class="fas fa-edit me-2 text-primary"></i>
                            Formulir Pendaftaran Siswa Baru
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="POST" id="pendaftaranForm">
                            <div class="row">
                                <!-- Data Pribadi -->
                                <div class="col-md-6">
                                    <h5 class="section-title">
                                        <i class="fas fa-user me-2"></i>Data Pribadi
                                    </h5>
                                    
                                    <div class="mb-3">
                                        <label for="nama" class="form-label fw-semibold">Nama Lengkap *</label>
                                        <input type="text" class="form-control" id="nama" name="nama" 
                                               value="<?php echo htmlspecialchars($existing_data['nama'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="nisn" class="form-label fw-semibold">NISN *</label>
                                        <input type="text" class="form-control" id="nisn" name="nisn" 
                                               value="<?php echo htmlspecialchars($existing_data['nisn'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label for="tempat_lahir" class="form-label fw-semibold">Tempat Lahir *</label>
                                            <input type="text" class="form-control" id="tempat_lahir" name="tempat_lahir" 
                                                   value="<?php echo htmlspecialchars($existing_data['tempat_lahir'] ?? ''); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label for="tanggal_lahir" class="form-label fw-semibold">Tanggal Lahir *</label>
                                            <input type="date" class="form-control" id="tanggal_lahir" name="tanggal_lahir" 
                                                   value="<?php echo $existing_data['tanggal_lahir'] ?? ''; ?>" required>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="jk" class="form-label fw-semibold">Jenis Kelamin *</label>
                                        <select class="form-select" id="jk" name="jk" required>
                                            <option value="">Pilih Jenis Kelamin</option>
                                            <option value="L" <?php echo ($existing_data['jk'] ?? '') == 'L' ? 'selected' : ''; ?>>Laki-laki</option>
                                            <option value="P" <?php echo ($existing_data['jk'] ?? '') == 'P' ? 'selected' : ''; ?>>Perempuan</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="alamat" class="form-label fw-semibold">Alamat Lengkap *</label>
                                        <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($existing_data['alamat'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="no_hp" class="form-label fw-semibold">No. HP/WhatsApp *</label>
                                        <input type="tel" class="form-control" id="no_hp" name="no_hp" 
                                               value="<?php echo htmlspecialchars($existing_data['no_hp'] ?? ''); ?>" required>
                                    </div>
                                </div>
                                
                                <!-- Data Orang Tua & Akademik -->
                                <div class="col-md-6">
                                    <h5 class="section-title">
                                        <i class="fas fa-users me-2"></i>Data Orang Tua
                                    </h5>
                                    
                                    <div class="mb-3">
                                        <label for="nama_ayah" class="form-label fw-semibold">Nama Ayah *</label>
                                        <input type="text" class="form-control" id="nama_ayah" name="nama_ayah" 
                                               value="<?php echo htmlspecialchars($existing_data['nama_ayah'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="nama_ibu" class="form-label fw-semibold">Nama Ibu *</label>
                                        <input type="text" class="form-control" id="nama_ibu" name="nama_ibu" 
                                               value="<?php echo htmlspecialchars($existing_data['nama_ibu'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <h5 class="section-title mt-4">
                                        <i class="fas fa-school me-2"></i>Data Akademik
                                    </h5>
                                    
                                    <div class="mb-3">
                                        <label for="asal_sekolah" class="form-label fw-semibold">Asal Sekolah (SMP/MTs) *</label>
                                        <input type="text" class="form-control" id="asal_sekolah" name="asal_sekolah" 
                                               value="<?php echo htmlspecialchars($existing_data['asal_sekolah'] ?? ''); ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="nilai_rata" class="form-label fw-semibold">Nilai Rata-rata Rapor *</label>
                                        <input type="number" class="form-control" id="nilai_rata" name="nilai_rata" 
                                               step="0.01" min="0" max="100"
                                               value="<?php echo $existing_data['nilai_rata'] ?? ''; ?>" required>
                                        <div class="form-text">Masukkan nilai rata-rata rapor semester terakhir</div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="kode_jur" class="form-label fw-semibold">Pilihan Jurusan *</label>
                                        <select class="form-select" id="kode_jur" name="kode_jur" required>
                                            <option value="">Pilih Jurusan</option>
                                            <?php while ($row = $jurusan_list->fetch(PDO::FETCH_ASSOC)): ?>
                                            <option value="<?php echo $row['kode_jur']; ?>" 
                                                    <?php echo ($existing_data['kode_jur'] ?? '') == $row['kode_jur'] ? 'selected' : ''; ?>>
                                                <?php echo $row['nama_jurusan']; ?> (Kuota: <?php echo $row['kuota']; ?>)
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label for="tahun_ajaran" class="form-label fw-semibold">Tahun Ajaran *</label>
                                        <select class="form-select" id="tahun_ajaran" name="tahun_ajaran" required>
                                            <option value="">Pilih Tahun Ajaran</option>
                                            <option value="2024/2025" <?php echo ($existing_data['tahun_ajaran'] ?? '') == '2024/2025' ? 'selected' : ''; ?>>2024/2025</option>
                                            <option value="2025/2026" <?php echo ($existing_data['tahun_ajaran'] ?? '') == '2025/2026' ? 'selected' : ''; ?>>2025/2026</option>
                                            <option value="2026/2027" <?php echo ($existing_data['tahun_ajaran'] ?? '') == '2026/2027' ? 'selected' : ''; ?>>2026/2027</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="row mt-4">
                                <div class="col-12">
                                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                        <a href="dashboard.php" class="btn btn-secondary me-md-2">
                                            <i class="fas fa-arrow-left me-2"></i>Kembali
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save me-2"></i>
                                            <?php echo $existing_data ? 'Update Data' : 'Daftar Sekarang'; ?>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <?php if ($existing_data): ?>
                <div class="card mt-4">
                    <div class="card-header info-card">
                        <h5 class="mb-0 text-white">
                            <i class="fas fa-info-circle me-2"></i>
                            Informasi Pendaftaran Anda
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-semibold">No. Pendaftaran</td>
                                        <td>: <span class="text-primary fw-bold"><?php echo $existing_data['no_daftar']; ?></span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Tanggal Daftar</td>
                                        <td>: <?php echo date('d F Y, H:i', strtotime($existing_data['tanggal_daftar'])); ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Tahun Ajaran</td>
                                        <td>: <?php echo $existing_data['tahun_ajaran']; ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-semibold">Status</td>
                                        <td>: 
                                            <span class="status-badge bg-<?php 
                                                echo $existing_data['status_pendaftaran'] == 'diterima' ? 'success' : 
                                                    ($existing_data['status_pendaftaran'] == 'ditolak' ? 'danger' : 'warning'); 
                                            ?>">
                                                <?php echo ucfirst($existing_data['status_pendaftaran']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Jurusan</td>
                                        <td>: <?php echo $existing_data['nama_jurusan']; ?></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Petugas</td>
                                        <td>: <?php echo $existing_data['nama_petugas'] ?? 'Belum ditentukan'; ?></td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation
        document.getElementById('pendaftaranForm').addEventListener('submit', function(e) {
            const requiredFields = this.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    isValid = false;
                } else {
                    field.classList.remove('is-invalid');
                    field.classList.add('is-valid');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Mohon lengkapi semua field yang wajib diisi!');
                return false;
            }
        });
    </script>
</body>
</html>
