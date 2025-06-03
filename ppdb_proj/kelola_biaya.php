<?php
require_once 'config/session.php';
require_once 'classes/BiayaTahunan.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: unauthorized.php');
    exit();
}

$biaya = new BiayaTahunan();
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $data = [
                        'tahun_ajaran' => $_POST['tahun_ajaran'],
                        'biaya_pendaftaran' => (float)$_POST['biaya_pendaftaran'],
                        'biaya_spp' => (float)$_POST['biaya_spp'],
                        'biaya_seragam' => (float)$_POST['biaya_seragam'],
                        'biaya_buku' => (float)$_POST['biaya_buku'],
                        'biaya_praktikum' => (float)$_POST['biaya_praktikum'],
                        'biaya_lainnya' => (float)$_POST['biaya_lainnya'],
                        'keterangan' => $_POST['keterangan'],
                        'status_aktif' => isset($_POST['status_aktif']) ? 1 : 0
                    ];
                    
                    if ($biaya->tambahBiaya($data)) {
                        $message = 'Data biaya berhasil ditambahkan!';
                    } else {
                        $error = 'Gagal menambahkan data biaya!';
                    }
                    break;
                    
                case 'edit':
                    $data = [
                        'id_biaya' => (int)$_POST['id_biaya'],
                        'tahun_ajaran' => $_POST['tahun_ajaran'],
                        'biaya_pendaftaran' => (float)$_POST['biaya_pendaftaran'],
                        'biaya_spp' => (float)$_POST['biaya_spp'],
                        'biaya_seragam' => (float)$_POST['biaya_seragam'],
                        'biaya_buku' => (float)$_POST['biaya_buku'],
                        'biaya_praktikum' => (float)$_POST['biaya_praktikum'],
                        'biaya_lainnya' => (float)$_POST['biaya_lainnya'],
                        'keterangan' => $_POST['keterangan'],
                        'status_aktif' => isset($_POST['status_aktif']) ? 1 : 0
                    ];
                    
                    if ($biaya->updateBiaya($data)) {
                        $message = 'Data biaya berhasil diperbarui!';
                    } else {
                        $error = 'Gagal memperbarui data biaya!';
                    }
                    break;
                    
                case 'delete':
                    $id = (int)$_POST['id_biaya'];
                    if ($biaya->hapusBiaya($id)) {
                        $message = 'Data biaya berhasil dihapus!';
                    } else {
                        $error = 'Gagal menghapus data biaya!';
                    }
                    break;
                    
                case 'set_active':
                    $id = (int)$_POST['id_biaya'];
                    if ($biaya->setAktif($id)) {
                        $message = 'Biaya berhasil diaktifkan!';
                    } else {
                        $error = 'Gagal mengaktifkan biaya!';
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Get all biaya
$dataBiaya = $biaya->getAllBiaya();
$biayaAktif = $biaya->getBiayaAktif();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Biaya Tahunan - PPDB SMK IGASAR PINDAD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .main-content {
            background-color: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        .table th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .currency {
            font-family: 'Courier New', monospace;
            font-weight: bold;
        }
        .active-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #28a745;
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <div class="col-md-10 main-content p-4">
                <!-- Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h2><i class="fas fa-money-bill-wave me-2"></i>Kelola Biaya Tahunan</h2>
                        <p class="text-muted">Manajemen biaya pendidikan SMK IGASAR PINDAD</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBiayaModal">
                        <i class="fas fa-plus me-2"></i>Tambah Biaya
                    </button>
                </div>

                <!-- Alert Messages -->
                <?php if ($message): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i><?= htmlspecialchars($message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Current Active Fee -->
                <?php if ($biayaAktif): ?>
                    <div class="card mb-4 border-success">
                        <div class="card-header bg-success text-white">
                            <h5><i class="fas fa-star me-2"></i>Biaya Aktif - <?= htmlspecialchars($biayaAktif['tahun_ajaran']) ?></h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <i class="fas fa-clipboard-list fa-2x text-success mb-2"></i>
                                        <h6>Pendaftaran</h6>
                                        <p class="currency text-success">Rp <?= number_format($biayaAktif['biaya_pendaftaran'], 0, ',', '.') ?></p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <i class="fas fa-calendar-alt fa-2x text-primary mb-2"></i>
                                        <h6>SPP</h6>
                                        <p class="currency text-primary">Rp <?= number_format($biayaAktif['biaya_spp'], 0, ',', '.') ?></p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <i class="fas fa-tshirt fa-2x text-info mb-2"></i>
                                        <h6>Seragam</h6>
                                        <p class="currency text-info">Rp <?= number_format($biayaAktif['biaya_seragam'], 0, ',', '.') ?></p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <i class="fas fa-book fa-2x text-warning mb-2"></i>
                                        <h6>Buku</h6>
                                        <p class="currency text-warning">Rp <?= number_format($biayaAktif['biaya_buku'], 0, ',', '.') ?></p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <i class="fas fa-flask fa-2x text-danger mb-2"></i>
                                        <h6>Praktikum</h6>
                                        <p class="currency text-danger">Rp <?= number_format($biayaAktif['biaya_praktikum'], 0, ',', '.') ?></p>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="text-center">
                                        <i class="fas fa-plus-circle fa-2x text-secondary mb-2"></i>
                                        <h6>Lainnya</h6>
                                        <p class="currency text-secondary">Rp <?= number_format($biayaAktif['biaya_lainnya'], 0, ',', '.') ?></p>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <h5>Total Biaya: <span class="currency text-success">Rp <?= number_format($biayaAktif['total_biaya'], 0, ',', '.') ?></span></h5>
                                </div>
                                <div class="col-md-6 text-end">
                                    <?php if ($biayaAktif['keterangan']): ?>
                                        <small class="text-muted"><?= htmlspecialchars($biayaAktif['keterangan']) ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-money-bill-wave fa-2x text-primary mb-2"></i>
                                <h4><?= count($dataBiaya) ?></h4>
                                <p class="text-muted">Total Periode</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h4><?= count(array_filter($dataBiaya, function($b) { return $b['status_aktif']; })) ?></h4>
                                <p class="text-muted">Periode Aktif</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-calendar fa-2x text-info mb-2"></i>
                                <h4><?= date('Y') ?></h4>
                                <p class="text-muted">Tahun Sekarang</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-chart-line fa-2x text-warning mb-2"></i>
                                <h4>
                                    <?php 
                                    $avgTotal = count($dataBiaya) > 0 ? array_sum(array_column($dataBiaya, 'total_biaya')) / count($dataBiaya) : 0;
                                    echo 'Rp ' . number_format($avgTotal / 1000000, 1) . 'M';
                                    ?>
                                </h4>
                                <p class="text-muted">Rata-rata Biaya</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-table me-2"></i>Data Biaya Tahunan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="biayaTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Tahun Ajaran</th>
                                        <th>Pendaftaran</th>
                                        <th>SPP</th>
                                        <th>Seragam</th>
                                        <th>Buku</th>
                                        <th>Praktikum</th>
                                        <th>Lainnya</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dataBiaya as $index => $row): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <div class="position-relative d-inline-block">
                                                    <strong><?= htmlspecialchars($row['tahun_ajaran']) ?></strong>
                                                    <?php if ($row['status_aktif']): ?>
                                                        <div class="active-badge">
                                                            <i class="fas fa-star"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="currency">Rp <?= number_format($row['biaya_pendaftaran'], 0, ',', '.') ?></td>
                                            <td class="currency">Rp <?= number_format($row['biaya_spp'], 0, ',', '.') ?></td>
                                            <td class="currency">Rp <?= number_format($row['biaya_seragam'], 0, ',', '.') ?></td>
                                            <td class="currency">Rp <?= number_format($row['biaya_buku'], 0, ',', '.') ?></td>
                                            <td class="currency">Rp <?= number_format($row['biaya_praktikum'], 0, ',', '.') ?></td>
                                            <td class="currency">Rp <?= number_format($row['biaya_lainnya'], 0, ',', '.') ?></td>
                                            <td class="currency"><strong>Rp <?= number_format($row['total_biaya'], 0, ',', '.') ?></strong></td>
                                            <td>
                                                <?php if ($row['status_aktif']): ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary">Nonaktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <?php if (!$row['status_aktif']): ?>
                                                        <button class="btn btn-sm btn-success" onclick="setActive(<?= $row['id_biaya'] ?>, '<?= htmlspecialchars($row['tahun_ajaran']) ?>')" title="Aktifkan">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                    <button class="btn btn-sm btn-info" onclick="viewBiaya(<?= htmlspecialchars(json_encode($row)) ?>)" title="Lihat Detail">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-warning" onclick="editBiaya(<?= htmlspecialchars(json_encode($row)) ?>)" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteBiaya(<?= $row['id_biaya'] ?>, '<?= htmlspecialchars($row['tahun_ajaran']) ?>')" title="Hapus">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Biaya Modal -->
    <div class="modal fade" id="addBiayaModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Tambah Biaya Tahunan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tahun Ajaran *</label>
                                    <input type="text" class="form-control" name="tahun_ajaran" required placeholder="2024/2025">
                                    <div class="form-text">Format: YYYY/YYYY</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="status_aktif" id="status_aktif">
                                        <label class="form-check-label" for="status_aktif">
                                            Set sebagai biaya aktif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6 class="mb-3"><i class="fas fa-money-bill-wave me-2"></i>Rincian Biaya</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Biaya Pendaftaran *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" name="biaya_pendaftaran" required min="0" step="1000">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Biaya SPP (per bulan) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" name="biaya_spp" required min="0" step="1000">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Biaya Seragam *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" name="biaya_seragam" required min="0" step="1000">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Biaya Buku *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" name="biaya_buku" required min="0" step="1000">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Biaya Praktikum *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" name="biaya_praktikum" required min="0" step="1000">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Biaya Lainnya</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" name="biaya_lainnya" min="0" step="1000" value="0">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" name="keterangan" rows="3" placeholder="Keterangan tambahan tentang biaya..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Biaya Modal -->
    <div class="modal fade" id="editBiayaModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Biaya Tahunan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id_biaya" id="edit_id_biaya">
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Tahun Ajaran *</label>
                                    <input type="text" class="form-control" name="tahun_ajaran" id="edit_tahun_ajaran" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="status_aktif" id="edit_status_aktif">
                                        <label class="form-check-label" for="edit_status_aktif">
                                            Set sebagai biaya aktif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <h6 class="mb-3"><i class="fas fa-money-bill-wave me-2"></i>Rincian Biaya</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Biaya Pendaftaran *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" name="biaya_pendaftaran" id="edit_biaya_pendaftaran" required min="0" step="1000">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Biaya SPP (per bulan) *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" name="biaya_spp" id="edit_biaya_spp" required min="0" step="1000">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Biaya Seragam *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" name="biaya_seragam" id="edit_biaya_seragam" required min="0" step="1000">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Biaya Buku *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" name="biaya_buku" id="edit_biaya_buku" required min="0" step="1000">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Biaya Praktikum *</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" name="biaya_praktikum" id="edit_biaya_praktikum" required min="0" step="1000">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Biaya Lainnya</label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" class="form-control" name="biaya_lainnya" id="edit_biaya_lainnya" min="0" step="1000">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea class="form-control" name="keterangan" id="edit_keterangan" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- View Biaya Modal -->
    <div class="modal fade" id="viewBiayaModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Detail Biaya - <span id="view_tahun_ajaran"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-clipboard-list fa-3x text-primary mb-3"></i>
                                    <h6>Biaya Pendaftaran</h6>
                                    <h4 class="currency text-primary" id="view_biaya_pendaftaran"></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-calendar-alt fa-3x text-success mb-3"></i>
                                    <h6>SPP per Bulan</h6>
                                    <h4 class="currency text-success" id="view_biaya_spp"></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-tshirt fa-3x text-info mb-3"></i>
                                    <h6>Biaya Seragam</h6>
                                    <h4 class="currency text-info" id="view_biaya_seragam"></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-book fa-3x text-warning mb-3"></i>
                                    <h6>Biaya Buku</h6>
                                    <h4 class="currency text-warning" id="view_biaya_buku"></h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-flask fa-3x text-danger mb-3"></i>
                                    <h6>Biaya Praktikum</h6>
                                    <h4 class="currency text-danger" id="view_biaya_praktikum"></h4>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-body text-center">
                                    <i class="fas fa-plus-circle fa-3x text-secondary mb-3"></i>
                                    <h6>Biaya Lainnya</h6>
                                    <h4 class="currency text-secondary" id="view_biaya_lainnya"></h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3 border-success">
                        <div class="card-body text-center">
                            <h5>Total Biaya</h5>
                            <h2 class="currency text-success" id="view_total_biaya"></h2>
                            <p class="text-muted" id="view_keterangan"></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus data biaya untuk tahun ajaran <strong id="deleteBiayaName"></strong>?</p>
                    <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan!</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id_biaya" id="deleteBiayaId">
                        <button type="submit" class="btn btn-danger">Hapus</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Set Active Confirmation Modal -->
    <div class="modal fade" id="setActiveModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-star me-2"></i>Konfirmasi Aktifkan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin mengaktifkan biaya untuk tahun ajaran <strong id="activeBiayaName"></strong>?</p>
                    <p class="text-warning"><small>Biaya yang sedang aktif akan dinonaktifkan secara otomatis.</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="set_active">
                        <input type="hidden" name="id_biaya" id="activeBiayaId">
                        <button type="submit" class="btn btn-success">Aktifkan</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#biayaTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                pageLength: 10,
                order: [[1, 'desc']]
            });
        });

        function formatCurrency(amount) {
            return 'Rp ' + new Intl.NumberFormat('id-ID').format(amount);
        }

        function viewBiaya(data) {
            document.getElementById('view_tahun_ajaran').textContent = data.tahun_ajaran;
            document.getElementById('view_biaya_pendaftaran').textContent = formatCurrency(data.biaya_pendaftaran);
            document.getElementById('view_biaya_spp').textContent = formatCurrency(data.biaya_spp);
            document.getElementById('view_biaya_seragam').textContent = formatCurrency(data.biaya_seragam);
            document.getElementById('view_biaya_buku').textContent = formatCurrency(data.biaya_buku);
            document.getElementById('view_biaya_praktikum').textContent = formatCurrency(data.biaya_praktikum);
            document.getElementById('view_biaya_lainnya').textContent = formatCurrency(data.biaya_lainnya);
            document.getElementById('view_total_biaya').textContent = formatCurrency(data.total_biaya);
            document.getElementById('view_keterangan').textContent = data.keterangan || 'Tidak ada keterangan';
            
            new bootstrap.Modal(document.getElementById('viewBiayaModal')).show();
        }

        function editBiaya(data) {
            document.getElementById('edit_id_biaya').value = data.id_biaya;
            document.getElementById('edit_tahun_ajaran').value = data.tahun_ajaran;
            document.getElementById('edit_biaya_pendaftaran').value = data.biaya_pendaftaran;
            document.getElementById('edit_biaya_spp').value = data.biaya_spp;
            document.getElementById('edit_biaya_seragam').value = data.biaya_seragam;
            document.getElementById('edit_biaya_buku').value = data.biaya_buku;
            document.getElementById('edit_biaya_praktikum').value = data.biaya_praktikum;
            document.getElementById('edit_biaya_lainnya').value = data.biaya_lainnya;
            document.getElementById('edit_keterangan').value = data.keterangan || '';
            document.getElementById('edit_status_aktif').checked = data.status_aktif == 1;
            
            new bootstrap.Modal(document.getElementById('editBiayaModal')).show();
        }

        function deleteBiaya(id, tahunAjaran) {
            document.getElementById('deleteBiayaId').value = id;
            document.getElementById('deleteBiayaName').textContent = tahunAjaran;
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }

        function setActive(id, tahunAjaran) {
            document.getElementById('activeBiayaId').value = id;
            document.getElementById('activeBiayaName').textContent = tahunAjaran;
            
            new bootstrap.Modal(document.getElementById('setActiveModal')).show();
        }
    </script>
</body>
</html>
