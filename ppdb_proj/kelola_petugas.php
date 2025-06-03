<?php
require_once 'config/session.php';
require_once 'classes/Petugas.php';
require_once 'classes/User.php';

// Check if user is admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: unauthorized.php');
    exit();
}

$petugas = new Petugas();
$user = new User();
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['action'])) {
            switch ($_POST['action']) {
                case 'add':
                    // First create user account
                    $userData = [
                        'username' => $_POST['username'],
                        'password' => $_POST['password'],
                        'email' => $_POST['email'],
                        'nama_lengkap' => $_POST['nama_lengkap'],
                        'role' => $_POST['role']
                    ];
                    
                    $userId = $user->createUser($userData);
                    if ($userId) {
                        // Then create petugas record
                        $petugasData = [
                            'user_id' => $userId,
                            'nip' => $_POST['nip'],
                            'nama_lengkap' => $_POST['nama_lengkap'],
                            'jabatan' => $_POST['jabatan'],
                            'no_telepon' => $_POST['no_telepon'],
                            'alamat' => $_POST['alamat'],
                            'status_aktif' => isset($_POST['status_aktif']) ? 1 : 0
                        ];
                        
                        if ($petugas->tambahPetugas($petugasData)) {
                            $message = 'Petugas berhasil ditambahkan!';
                        } else {
                            $error = 'Gagal menambahkan data petugas!';
                            // Delete user if petugas creation failed
                            $user->deleteUser($userId);
                        }
                    } else {
                        $error = 'Gagal membuat akun user!';
                    }
                    break;
                    
                case 'edit':
                    $petugasData = [
                        'id_petugas' => (int)$_POST['id_petugas'],
                        'nip' => $_POST['nip'],
                        'nama_lengkap' => $_POST['nama_lengkap'],
                        'jabatan' => $_POST['jabatan'],
                        'no_telepon' => $_POST['no_telepon'],
                        'alamat' => $_POST['alamat'],
                        'status_aktif' => isset($_POST['status_aktif']) ? 1 : 0
                    ];
                    
                    if ($petugas->updatePetugas($petugasData)) {
                        // Update user data if provided
                        if (!empty($_POST['email'])) {
                            $userUpdateData = [
                                'user_id' => (int)$_POST['user_id'],
                                'email' => $_POST['email'],
                                'nama_lengkap' => $_POST['nama_lengkap']
                            ];
                            if (!empty($_POST['password'])) {
                                $userUpdateData['password'] = $_POST['password'];
                            }
                            $user->updateUser($userUpdateData);
                        }
                        $message = 'Data petugas berhasil diperbarui!';
                    } else {
                        $error = 'Gagal memperbarui data petugas!';
                    }
                    break;
                    
                case 'delete':
                    $id = (int)$_POST['id_petugas'];
                    $petugasData = $petugas->getPetugasById($id);
                    
                    if ($petugas->hapusPetugas($id)) {
                        // Also delete user account
                        if ($petugasData && $petugasData['user_id']) {
                            $user->deleteUser($petugasData['user_id']);
                        }
                        $message = 'Petugas berhasil dihapus!';
                    } else {
                        $error = 'Gagal menghapus petugas!';
                    }
                    break;
                    
                case 'toggle_status':
                    $id = (int)$_POST['id_petugas'];
                    $status = (int)$_POST['status'];
                    
                    if ($petugas->toggleStatus($id, $status)) {
                        $message = 'Status petugas berhasil diubah!';
                    } else {
                        $error = 'Gagal mengubah status petugas!';
                    }
                    break;
            }
        }
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

// Get all petugas
$dataPetugas = $petugas->getAllPetugas();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Petugas - PPDB SMK IGASAR PINDAD</title>
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
        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
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
                        <h2><i class="fas fa-users me-2"></i>Kelola Petugas</h2>
                        <p class="text-muted">Manajemen data petugas PPDB SMK IGASAR PINDAD</p>
                    </div>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPetugasModal">
                        <i class="fas fa-plus me-2"></i>Tambah Petugas
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
                                <i class="fas fa-users fa-2x text-primary mb-2"></i>
                                <h4><?= count($dataPetugas) ?></h4>
                                <p class="text-muted">Total Petugas</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-user-check fa-2x text-success mb-2"></i>
                                <h4><?= count(array_filter($dataPetugas, function($p) { return $p['status_aktif']; })) ?></h4>
                                <p class="text-muted">Petugas Aktif</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-user-cog fa-2x text-info mb-2"></i>
                                <h4><?= count(array_filter($dataPetugas, function($p) { return $p['role'] === 'admin'; })) ?></h4>
                                <p class="text-muted">Admin</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card text-center">
                            <div class="card-body">
                                <i class="fas fa-user-tie fa-2x text-warning mb-2"></i>
                                <h4><?= count(array_filter($dataPetugas, function($p) { return in_array($p['role'], ['petugas', 'tu', 'kepala_sekolah']); })) ?></h4>
                                <p class="text-muted">Staff</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card">
                    <div class="card-header">
                        <h5><i class="fas fa-table me-2"></i>Data Petugas</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="petugasTable" class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Avatar</th>
                                        <th>NIP</th>
                                        <th>Nama Lengkap</th>
                                        <th>Jabatan</th>
                                        <th>Role</th>
                                        <th>Kontak</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($dataPetugas as $index => $row): ?>
                                        <tr>
                                            <td><?= $index + 1 ?></td>
                                            <td>
                                                <div class="avatar">
                                                    <?= strtoupper(substr($row['nama_lengkap'], 0, 2)) ?>
                                                </div>
                                            </td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($row['nip']) ?></span></td>
                                            <td>
                                                <strong><?= htmlspecialchars($row['nama_lengkap']) ?></strong>
                                                <br><small class="text-muted"><?= htmlspecialchars($row['email']) ?></small>
                                            </td>
                                            <td><?= htmlspecialchars($row['jabatan']) ?></td>
                                            <td>
                                                <?php
                                                $roleColors = [
                                                    'admin' => 'bg-danger',
                                                    'petugas' => 'bg-primary',
                                                    'tu' => 'bg-info',
                                                    'kepala_sekolah' => 'bg-warning'
                                                ];
                                                $roleNames = [
                                                    'admin' => 'Admin',
                                                    'petugas' => 'Petugas',
                                                    'tu' => 'TU',
                                                    'kepala_sekolah' => 'Kepala Sekolah'
                                                ];
                                                ?>
                                                <span class="badge <?= $roleColors[$row['role']] ?? 'bg-secondary' ?>">
                                                    <?= $roleNames[$row['role']] ?? ucfirst($row['role']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($row['no_telepon']): ?>
                                                    <i class="fas fa-phone me-1"></i><?= htmlspecialchars($row['no_telepon']) ?>
                                                <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" 
                                                           <?= $row['status_aktif'] ? 'checked' : '' ?>
                                                           onchange="toggleStatus(<?= $row['id_petugas'] ?>, this.checked ? 1 : 0)">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <button class="btn btn-sm btn-info" onclick="viewPetugas(<?= htmlspecialchars(json_encode($row)) ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-warning" onclick="editPetugas(<?= htmlspecialchars(json_encode($row)) ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" onclick="deletePetugas(<?= $row['id_petugas'] ?>, '<?= htmlspecialchars($row['nama_lengkap']) ?>')">
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

    <!-- Add Petugas Modal -->
    <div class="modal fade" id="addPetugasModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Tambah Petugas Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="add">
                        
                        <!-- Account Information -->
                        <h6 class="mb-3"><i class="fas fa-user-circle me-2"></i>Informasi Akun</h6>
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Username *</label>
                                    <input type="text" class="form-control" name="username" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Password *</label>
                                    <input type="password" class="form-control" name="password" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Email *</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <h6 class="mb-3"><i class="fas fa-id-card me-2"></i>Informasi Personal</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">NIP *</label>
                                    <input type="text" class="form-control" name="nip" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" name="nama_lengkap" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jabatan *</label>
                                    <input type="text" class="form-control" name="jabatan" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Role *</label>
                                    <select class="form-select" name="role" required>
                                        <option value="">Pilih Role</option>
                                        <option value="admin">Admin</option>
                                        <option value="petugas">Petugas</option>
                                        <option value="tu">TU</option>
                                        <option value="kepala_sekolah">Kepala Sekolah</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">No. Telepon</label>
                                    <input type="text" class="form-control" name="no_telepon">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input class="form-check-input" type="checkbox" name="status_aktif" id="status_aktif" checked>
                                        <label class="form-check-label" for="status_aktif">
                                            Status Aktif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat" rows="3"></textarea>
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

    <!-- Edit Petugas Modal -->
    <div class="modal fade" id="editPetugasModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Petugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id_petugas" id="edit_id_petugas">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        
                        <!-- Account Information -->
                        <h6 class="mb-3"><i class="fas fa-user-circle me-2"></i>Informasi Akun</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" id="edit_email">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Password Baru (kosongkan jika tidak diubah)</label>
                                    <input type="password" class="form-control" name="password">
                                </div>
                            </div>
                        </div>

                        <!-- Personal Information -->
                        <h6 class="mb-3"><i class="fas fa-id-card me-2"></i>Informasi Personal</h6>
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">NIP *</label>
                                    <input type="text" class="form-control" name="nip" id="edit_nip" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Lengkap *</label>
                                    <input type="text" class="form-control" name="nama_lengkap" id="edit_nama_lengkap" required>
                                </div>
                            </div>
                        </div>
                        
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Jabatan *</label>
                                    <input type="text" class="form-control" name="jabatan" id="edit_jabatan" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">No. Telepon</label>
                                    <input type="text" class="form-control" name="no_telepon" id="edit_no_telepon">
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="status_aktif" id="edit_status_aktif">
                                        <label class="form-check-label" for="edit_status_aktif">
                                            Status Aktif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Alamat</label>
                            <textarea class="form-control" name="alamat" id="edit_alamat" rows="3"></textarea>
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

    <!-- View Petugas Modal -->
    <div class="modal fade" id="viewPetugasModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-eye me-2"></i>Detail Petugas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 text-center">
                            <div class="avatar mx-auto mb-3" style="width: 100px; height: 100px; font-size: 2rem;" id="view_avatar"></div>
                            <h5 id="view_nama_lengkap"></h5>
                            <p class="text-muted" id="view_jabatan"></p>
                        </div>
                        <div class="col-md-8">
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>NIP:</strong></td>
                                    <td id="view_nip"></td>
                                </tr>
                                <tr>
                                    <td><strong>Email:</strong></td>
                                    <td id="view_email"></td>
                                </tr>
                                <tr>
                                    <td><strong>Role:</strong></td>
                                    <td id="view_role"></td>
                                </tr>
                                <tr>
                                    <td><strong>No. Telepon:</strong></td>
                                    <td id="view_no_telepon"></td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td id="view_status"></td>
                                </tr>
                                <tr>
                                    <td><strong>Alamat:</strong></td>
                                    <td id="view_alamat"></td>
                                </tr>
                            </table>
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
                    <p>Apakah Anda yakin ingin menghapus petugas <strong id="deletePetugasName"></strong>?</p>
                    <p class="text-danger"><small>Tindakan ini akan menghapus akun user dan tidak dapat dibatalkan!</small></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id_petugas" id="deletePetugasId">
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
            $('#petugasTable').DataTable({
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/id.json'
                },
                pageLength: 10,
                order: [[2, 'asc']]
            });
        });

        function toggleStatus(id, status) {
            const formData = new FormData();
            formData.append('action', 'toggle_status');
            formData.append('id_petugas', id);
            formData.append('status', status);

            fetch('', {
                method: 'POST',
                body: formData
            }).then(() => {
                location.reload();
            });
        }

        function viewPetugas(data) {
            document.getElementById('view_avatar').textContent = data.nama_lengkap.substring(0, 2).toUpperCase();
            document.getElementById('view_nama_lengkap').textContent = data.nama_lengkap;
            document.getElementById('view_jabatan').textContent = data.jabatan;
            document.getElementById('view_nip').textContent = data.nip;
            document.getElementById('view_email').textContent = data.email || '-';
            
            const roleNames = {
                'admin': 'Admin',
                'petugas': 'Petugas',
                'tu': 'TU',
                'kepala_sekolah': 'Kepala Sekolah'
            };
            document.getElementById('view_role').innerHTML = `<span class="badge bg-primary">${roleNames[data.role] || data.role}</span>`;
            
            document.getElementById('view_no_telepon').textContent = data.no_telepon || '-';
            document.getElementById('view_status').innerHTML = data.status_aktif == 1 ? 
                '<span class="badge bg-success">Aktif</span>' : 
                '<span class="badge bg-danger">Nonaktif</span>';
            document.getElementById('view_alamat').textContent = data.alamat || '-';
            
            new bootstrap.Modal(document.getElementById('viewPetugasModal')).show();
        }

        function editPetugas(data) {
            document.getElementById('edit_id_petugas').value = data.id_petugas;
            document.getElementById('edit_user_id').value = data.user_id;
            document.getElementById('edit_nip').value = data.nip;
            document.getElementById('edit_nama_lengkap').value = data.nama_lengkap;
            document.getElementById('edit_jabatan').value = data.jabatan;
            document.getElementById('edit_email').value = data.email || '';
            document.getElementById('edit_no_telepon').value = data.no_telepon || '';
            document.getElementById('edit_alamat').value = data.alamat || '';
            document.getElementById('edit_status_aktif').checked = data.status_aktif == 1;
            
            new bootstrap.Modal(document.getElementById('editPetugasModal')).show();
        }

        function deletePetugas(id, nama) {
            document.getElementById('deletePetugasId').value = id;
            document.getElementById('deletePetugasName').textContent = nama;
            
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
    </script>
</body>
</html>
