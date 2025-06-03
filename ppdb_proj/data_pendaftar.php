<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'classes/Pendaftar.php';

checkPetugas(); // Hanya admin dan petugas yang bisa akses

$database = new Database();
$db = $database->getConnection();
$pendaftar = new Pendaftar($db);

$message = '';
$message_type = '';

// Handle status update
if ($_POST && isset($_POST['action']) && $_POST['action'] == 'update_status') {
    $no_daftar = $_POST['no_daftar'];
    $status = $_POST['status'];
    
    if ($pendaftar->updateStatus($no_daftar, $status)) {
        $message = "Status pendaftar berhasil diupdate!";
        $message_type = "success";
    } else {
        $message = "Gagal mengupdate status!";
        $message_type = "danger";
    }
}

// Get all pendaftar
$pendaftar_list = $pendaftar->getAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pendaftar - PPDB SMK IGASAR PINDAD</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css" rel="stylesheet">
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
                            <i class="fas fa-users me-3"></i>Data Pendaftar
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                                <li class="breadcrumb-item active">Data Pendaftar</li>
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
                
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">
                            <i class="fas fa-table me-2 text-primary"></i>
                            Daftar Calon Siswa
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
                                        <th>Aksi</th>
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
                                        <td>
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-info" onclick="viewDetail('<?php echo $row['no_daftar']; ?>')">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if ($_SESSION['role'] == 'admin' || $_SESSION['role'] == 'petugas'): ?>
                                                <button class="btn btn-sm btn-warning" onclick="updateStatus('<?php echo $row['no_daftar']; ?>', '<?php echo $row['status_pendaftaran']; ?>')">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Status Pendaftar</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="no_daftar" id="status_no_daftar">
                        
                        <div class="mb-3">
                            <label for="status" class="form-label">Status Pendaftaran</label>
                            <select class="form-select" name="status" id="status" required>
                                <option value="pending">Pending</option>
                                <option value="diterima">Diterima</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    
    <script>
        $(document).ready(function() {
            $('#pendaftarTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/id.json"
                },
                "order": [[ 6, "desc" ]]
            });
        });
        
        function updateStatus(no_daftar, current_status) {
            document.getElementById('status_no_daftar').value = no_daftar;
            document.getElementById('status').value = current_status;
            new bootstrap.Modal(document.getElementById('statusModal')).show();
        }
        
        function viewDetail(no_daftar) {
            // Implement view detail functionality
            alert('Detail untuk ' + no_daftar);
        }
    </script>
</body>
</html>
