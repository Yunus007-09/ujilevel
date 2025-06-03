<?php
require_once 'config/session.php';
require_once 'classes/Jurusan.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: unauthorized.php');
    exit();
}

$jurusan = new Jurusan();
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    $data = [
                        'nama_jurusan' => $_POST['nama_jurusan'],
                        'kode_jurusan' => $_POST['kode_jurusan'],
                        'kuota' => (int)$_POST['kuota'],
                        'deskripsi' => $_POST['deskripsi'],
                        'status_aktif' => isset($_POST['status_aktif']) ? 1 : 0
                    ];
                    
                    if ($jurusan->tambahJurusan($data)) {
                        $message = 'Jurusan berhasil ditambahkan!';
                    } else {
                        $error = 'Gagal menambahkan jurusan!';
                    }
                    break;
                    
                case 'edit':
                    $data = [
                        'id_jurusan' => (int)$_POST['id_jurusan'],
                        'nama_jurusan' => $_POST['nama_jurusan'],
                        'kode_jurusan' => $_POST['kode_jurusan'],
                        'kuota' => (int)$_POST['kuota'],
                        'deskripsi' => $_POST['deskripsi'],
                        'status_aktif' => isset($_POST['status_aktif']) ? 1 : 0
                    ];
                    
                    if ($jurusan->updateJurusan($data)) {
                        $message = 'Jurusan berhasil diperbarui!';
                    } else {
                        $error = 'Gagal memperbarui jurusan!';
                    }
                    break;
                    
                case 'delete':
                    $id = (int)$_POST['id_jurusan'];
                    if ($jurusan->hapusJurusan($id)) {
                        $message = 'Jurusan berhasil dihapus!';
                    } else {
                        $error = 'Gagal menghapus jurusan! Mungkin masih ada data terkait.';
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Get all jurusan
$dataJurusan = $jurusan->getAllJurusan();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jurusan - PPDB SMK IGASAR PINDAD</title>
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
        .badge-success { background-color: #28a745; }
        .badge-danger { background-color: #dc3545; }
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
                        <h2><i class="fas fa-graduation-cap me-2"></i>Kelola Jurusan</h2>
                        <p class="text-muted">Manajemen data jurusan SMK IGASAR PINDAD</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJurusanModal">
                        <i class="fas fa-plus me-2"></i>Tambah Jurusan
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

                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-graduation-cap fa-2x text-primary mb-2"></i>
                                <h4><?= count($dataJurusan) ?></h4>
                                <p class="text-muted">Total Jurusan</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h4><?= count(array_filter($dataJurusan, function($j) { return $j['status_aktif']; })) ?></h4>
                                <p class="text-muted">Jurusan Aktif</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-users fa-2x text-info mb-2"></i>
                                <h4><?= array_sum(array_column($dataJurusan, 'kuota')) ?></h4>
                                <p class="text-muted">Total Kuota</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-user-graduate fa-2x text-warning mb-2"></i>
                                <h4><?= array_sum(array_column($dataJurusan, 'jumlah_pendaftar')) ?></h4>
                                <p class="text-muted">Total Pendaftar</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-table me-2"></i>Data Jurusan</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="jurusanTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Kode</th>
                                        <th>Nama Jurusan</th>
                                        <th>Kuota</th>
                                        <th>Pendaftar</th>
                                        <th>Sisa Kuota</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dataJurusan as $index => $row): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['kode_jurusan']) ?></span></td>
                                            <td>
                                                <strong><?= htmlspecialchars($row['nama_jurusan']) ?></strong>
                                                <?php if ($row['deskripsi']): ?>
                                                    <br><small class="text-muted"><?= htmlspecialchars(substr($row['deskripsi'], 0, 50)) ?>...</small>
                                                <?php endif; ?>
                                            </td>
                                            <td><span class="badge bg-info"><?= $row['kuota'] ?></span></td>
                                            <td><span class="badge bg-primary"><?= $row['jumlah_pendaftar'] ?></span></td>
                                            <td>
                                                <?php 
                                                $sisa = $row['kuota'] - $row['jumlah_pendaftar'];
                                                $badgeClass = $sisa > 10 ? 'bg-success' : ($sisa > 0 ? 'bg-warning' : 'bg-danger');
                                                ?>
                                                <span class="badge <?= $badgeClass ?>"><?= $sisa ?></span>
                                            </td>
                                            <td>
                                                <?php if ($row['status_aktif']): ?>
                                                    <span class="badge bg-success">Aktif</span>
                                                <?php else: ?>
                                                    <span class="badge bg-danger">Nonaktif</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-warning" onclick="editJurusan(<?= htmlspecialchars(json_encode($row)) ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deleteJurusan(<?= $row['id_jurusan'] ?>, '<?= htmlspecialchars($row['nama_jurusan']) ?>')">
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

    <!-- Add Jurusan Modal -->
    <div class="modal fade" id="addJurusanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Tambah Jurusan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kode Jurusan *</label>
                                    <input type="text" class="form-control" name="kode_jurusan" required maxlength="10">
                                    <div class="form-text">Contoh: TKJ, RPL, MM</div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kuota *</label>
                                    <input type="number" class="form-control" name="kuota" required min="1" max="100">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Jurusan *</label>
                            <input type="text" class="form-control" name="nama_jurusan" required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" rows="3" maxlength="500"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="status_aktif" id="status_aktif" checked>
                                <label class="form-check-label" for="status_aktif">
                                    Status Aktif
                                </label>
                            </div>
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

    <!-- Edit Jurusan Modal -->
    <div class="modal fade" id="editJurusanModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Jurusan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" id="editForm">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id_jurusan" id="edit_id_jurusan">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kode Jurusan *</label>
                                    <input type="text" class="form-control" name="kode_jurusan" id="edit_kode_jurusan" required maxlength="10">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Kuota *</label>
                                    <input type="number" class="form-control" name="kuota" id="edit_kuota" required min="1" max="100">
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nama Jurusan *</label>
                            <input type="text" class="form-control" name="nama_jurusan" id="edit_nama_jurusan" required maxlength="100">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="deskripsi" id="edit_deskripsi" rows="3" maxlength="500"></textarea>
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="status_aktif" id="edit_status_aktif">
                                <label class="form-check-label" for="edit_status_aktif">
                                    Status Aktif
                                </label>
                            </div>
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

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-exclamation-triangle me-2"></i>Konfirmasi Hapus</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah Anda yakin ingin menghapus jurusan <strong id="deleteJurusanName"></strong>?</p>
                    <p class="text-danger"><small>Tindakan ini tidak dapat dibatalkan!</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id_jurusan" id="deleteJurusanId">
                        <button type="submit" class="btn btn-danger">Hapus</button>
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
            $('#jurusanTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                pageLength: 10,
                order: [[1, 'asc']]
            });
        });

        function editJurusan(data) {
            document.getElementById('edit_id_jurusan').value = data.id_jurusan;
            document.getElementById('edit_kode_jurusan').value = data.kode_jurusan;
            document.getElementById('edit_nama_jurusan').value = data.nama_jurusan;
            document.getElementById('edit_kuota').value = data.kuota;
            document.getElementById('edit_deskripsi').value = data.deskripsi || '';
            document.getElementById('edit_status_aktif').checked = data.status_aktif == 1;
            
            new bootstrap.Modal(document.getElementById('editJurusanModal')).show();
        }

        function deleteJurusan(id, nama) {
            document.getElementById('deleteJurusanId').value = id;
            document.getElementById('deleteJurusanName').textContent = nama;
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
