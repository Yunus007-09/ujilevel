<?php
require_once 'config/database.php';
require_once 'config/session.php';
require_once 'classes/BiayaTahunan.php';
require_once 'classes/Jurusan.php';

checkLogin();

$database = new Database();
$db = $database->getConnection();
$biaya = new BiayaTahunan($db);
$jurusan = new Jurusan($db);

// Get biaya tahunan
$biaya_list = $biaya->getAll();

// Get jurusan
$jurusan_list = $jurusan->getAll();

// Get current tahun ajaran
$tahun_ajaran = '2024/2025';
$biaya_info = $biaya->getByTahunAjaran($tahun_ajaran);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informasi - PPDB SMK IGASAR PINDAD</title>
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
        
        .info-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .info-header::before {
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
        
        .info-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 20px;
            font-size: 2rem;
        }
        
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .timeline::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(to bottom, #667eea, #764ba2);
        }
        
        .timeline-item {
            position: relative;
            padding-bottom: 30px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -34px;
            top: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #667eea;
            border: 3px solid white;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.3);
        }
        
        .timeline-date {
            font-weight: 600;
            color: #667eea;
            margin-bottom: 5px;
        }
        
        .timeline-content {
            background: white;
            padding: 15px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }
        
        .table-biaya th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        
        .jurusan-card {
            transition: all 0.3s ease;
        }
        
        .jurusan-card:hover {
            transform: translateY(-5px);
        }
        
        .jurusan-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }
        
        .accordion-button:not(.collapsed) {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .accordion-button:focus {
            box-shadow: 0 0 0 0.25rem rgba(102, 126, 234, 0.25);
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>
    
    <div class="container-fluid">
        <div class="row">
            <?php include 'includes/sidebar.php'; ?>
            
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="info-header mb-4 rounded-3">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <div class="info-icon">
                                <i class="fas fa-info-circle"></i>
                            </div>
                            <h1 class="fw-bold mb-2">Informasi PPDB</h1>
                            <p class="mb-0">Penerimaan Peserta Didik Baru SMK IGASAR PINDAD Bandung Tahun Ajaran <?php echo $tahun_ajaran; ?></p>
                        </div>
                        <div class="col-md-4 text-end">
                            <i class="fas fa-graduation-cap fa-4x opacity-50"></i>
                        </div>
                    </div>
                </div>
                
                <div class="row mb-4">
                    <div class="col-md-8">
                        <div class="card mb-4">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-alt me-2 text-primary"></i>
                                    Jadwal PPDB
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="timeline">
                                    <div class="timeline-item">
                                        <div class="timeline-date">1 - 30 Juni 2024</div>
                                        <div class="timeline-content">
                                            <h6 class="fw-bold">Pendaftaran Online</h6>
                                            <p class="mb-0">Pendaftaran dapat dilakukan secara online melalui website PPDB SMK IGASAR PINDAD.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item">
                                        <div class="timeline-date">1 - 5 Juli 2024</div>
                                        <div class="timeline-content">
                                            <h6 class="fw-bold">Verifikasi Berkas</h6>
                                            <p class="mb-0">Verifikasi berkas pendaftaran oleh panitia PPDB.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item">
                                        <div class="timeline-date">8 Juli 2024</div>
                                        <div class="timeline-content">
                                            <h6 class="fw-bold">Pengumuman Hasil Seleksi</h6>
                                            <p class="mb-0">Pengumuman hasil seleksi dapat dilihat melalui website PPDB atau papan pengumuman sekolah.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item">
                                        <div class="timeline-date">10 - 15 Juli 2024</div>
                                        <div class="timeline-content">
                                            <h6 class="fw-bold">Daftar Ulang</h6>
                                            <p class="mb-0">Daftar ulang bagi calon siswa yang diterima.</p>
                                        </div>
                                    </div>
                                    
                                    <div class="timeline-item">
                                        <div class="timeline-date">17 Juli 2024</div>
                                        <div class="timeline-content">
                                            <h6 class="fw-bold">Masa Pengenalan Lingkungan Sekolah (MPLS)</h6>
                                            <p class="mb-0">Kegiatan pengenalan lingkungan sekolah bagi siswa baru.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-question-circle me-2 text-primary"></i>
                                    Pertanyaan Umum (FAQ)
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="accordion" id="faqAccordion">
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faqHeading1">
                                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse1">
                                                Bagaimana cara mendaftar di SMK IGASAR PINDAD?
                                            </button>
                                        </h2>
                                        <div id="faqCollapse1" class="accordion-collapse collapse show" aria-labelledby="faqHeading1" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                <p>Pendaftaran dapat dilakukan secara online melalui website PPDB SMK IGASAR PINDAD. Calon siswa perlu membuat akun terlebih dahulu, kemudian mengisi formulir pendaftaran dan mengunggah dokumen yang diperlukan.</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faqHeading2">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse2">
                                                Apa saja persyaratan pendaftaran?
                                            </button>
                                        </h2>
                                        <div id="faqCollapse2" class="accordion-collapse collapse" aria-labelledby="faqHeading2" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                <p>Persyaratan pendaftaran meliputi:</p>
                                                <ul>
                                                    <li>Fotokopi Ijazah/SKHUN/Surat Keterangan Lulus</li>
                                                    <li>Fotokopi Rapor semester 1-5</li>
                                                    <li>Fotokopi Kartu Keluarga</li>
                                                    <li>Fotokopi Akta Kelahiran</li>
                                                    <li>Pas foto berwarna ukuran 3x4 (4 lembar)</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faqHeading3">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse3">
                                                Bagaimana sistem seleksi penerimaan siswa baru?
                                            </button>
                                        </h2>
                                        <div id="faqCollapse3" class="accordion-collapse collapse" aria-labelledby="faqHeading3" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                <p>Seleksi penerimaan siswa baru dilakukan berdasarkan:</p>
                                                <ul>
                                                    <li>Nilai rata-rata rapor</li>
                                                    <li>Kuota jurusan yang tersedia</li>
                                                    <li>Kelengkapan berkas pendaftaran</li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faqHeading4">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse4">
                                                Berapa biaya pendaftaran dan biaya sekolah?
                                            </button>
                                        </h2>
                                        <div id="faqCollapse4" class="accordion-collapse collapse" aria-labelledby="faqHeading4" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                <p>Biaya pendaftaran sebesar Rp 100.000. Untuk rincian biaya sekolah dapat dilihat pada tabel biaya di halaman ini.</p>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="faqHeading5">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faqCollapse5">
                                                Bagaimana cara mengetahui hasil seleksi?
                                            </button>
                                        </h2>
                                        <div id="faqCollapse5" class="accordion-collapse collapse" aria-labelledby="faqHeading5" data-bs-parent="#faqAccordion">
                                            <div class="accordion-body">
                                                <p>Hasil seleksi dapat dilihat melalui website PPDB SMK IGASAR PINDAD dengan login menggunakan akun yang telah dibuat saat pendaftaran. Hasil seleksi juga akan ditampilkan di papan pengumuman sekolah.</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-4">
                        <div class="card mb-4">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-money-bill-wave me-2 text-primary"></i>
                                    Rincian Biaya
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if ($biaya_info): ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-biaya">
                                        <thead>
                                            <tr>
                                                <th colspan="2" class="text-center">Biaya Tahun Ajaran <?php echo $tahun_ajaran; ?></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>Pendaftaran</td>
                                                <td class="text-end fw-bold">Rp <?php echo number_format($biaya_info['b_pendaftaran'], 0, ',', '.'); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Awal Tahun</td>
                                                <td class="text-end fw-bold">Rp <?php echo number_format($biaya_info['b_awal_tahun'], 0, ',', '.'); ?></td>
                                            </tr>
                                            <tr>
                                                <td>Seragam</td>
                                                <td class="text-end fw-bold">Rp <?php echo number_format($biaya_info['b_seragam'], 0, ',', '.'); ?></td>
                                            </tr>
                                            <tr>
                                                <td>SPP/Bulan</td>
                                                <td class="text-end fw-bold">Rp <?php echo number_format($biaya_info['b_spp'], 0, ',', '.'); ?></td>
                                            </tr>
                                            <tr class="table-primary">
                                                <td class="fw-bold">Total</td>
                                                <td class="text-end fw-bold">Rp <?php 
                                                $total = $biaya_info['b_pendaftaran'] + $biaya_info['b_awal_tahun'] + $biaya_info['b_seragam'] + $biaya_info['b_spp'];
                                                echo number_format($total, 0, ',', '.'); 
                                                ?></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted">* Biaya dapat diangsur sesuai ketentuan</small>
                                <?php else: ?>
                                <div class="text-center text-muted">
                                    <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                                    <p>Belum ada informasi biaya untuk tahun ajaran ini.</p>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <div class="card mb-4">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-phone-alt me-2 text-primary"></i>
                                    Kontak
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-unstyled">
                                    <li class="mb-3">
                                        <i class="fas fa-map-marker-alt text-primary me-2"></i>
                                        Jl. Cisaranten Kulon No.17, Bandung, Jawa Barat
                                    </li>
                                    <li class="mb-3">
                                        <i class="fas fa-phone text-primary me-2"></i>
                                        (022) 7800912
                                    </li>
                                    <li class="mb-3">
                                        <i class="fas fa-envelope text-primary me-2"></i>
                                        info@smkigasar.sch.id
                                    </li>
                                    <li>
                                        <i class="fas fa-globe text-primary me-2"></i>
                                        www.smkigasar.sch.id
                                    </li>
                                </ul>
                                
                                <div class="d-grid gap-2 mt-4">
                                    <a href="https://wa.me/6281234567890" class="btn btn-success">
                                        <i class="fab fa-whatsapp me-2"></i>
                                        Hubungi via WhatsApp
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-header bg-transparent">
                                <h5 class="mb-0">
                                    <i class="fas fa-download me-2 text-primary"></i>
                                    Unduh Dokumen
                                </h5>
                            </div>
                            <div class="card-body">
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                            Brosur PPDB
                                        </span>
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                            Formulir Pendaftaran
                                        </span>
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between align-items-center">
                                        <span>
                                            <i class="fas fa-file-pdf text-danger me-2"></i>
                                            Panduan PPDB
                                        </span>
                                        <a href="#" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-download"></i>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card mb-4">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">
                            <i class="fas fa-graduation-cap me-2 text-primary"></i>
                            Program Keahlian
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php while ($row = $jurusan_list->fetch(PDO::FETCH_ASSOC)): ?>
                            <div class="col-md-4 mb-4">
                                <div class="card jurusan-card h-100">
                                    <div class="card-body text-center">
                                        <div class="jurusan-icon mx-auto" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                                            <i class="fas fa-<?php 
                                                if (strpos(strtolower($row['nama_jurusan']), 'komputer') !== false || strpos(strtolower($row['nama_jurusan']), 'jaringan') !== false) {
                                                    echo 'laptop-code';
                                                } elseif (strpos(strtolower($row['nama_jurusan']), 'perangkat lunak') !== false || strpos(strtolower($row['nama_jurusan']), 'rekayasa') !== false) {
                                                    echo 'code';
                                                } elseif (strpos(strtolower($row['nama_jurusan']), 'pemesinan') !== false) {
                                                    echo 'cogs';
                                                } elseif (strpos(strtolower($row['nama_jurusan']), 'kendaraan') !== false || strpos(strtolower($row['nama_jurusan']), 'motor') !== false) {
                                                    echo 'car';
                                                } else {
                                                    echo 'tools';
                                                }
                                            ?>"></i>
                                        </div>
                                        <h6 class="fw-bold"><?php echo $row['nama_jurusan']; ?></h6>
                                        <p class="text-muted small mb-2">Kode: <?php echo $row['kode_jur']; ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="badge bg-primary">Kuota: <?php echo $row['kuota']; ?></span>
                                            <span class="badge bg-<?php echo $row['status'] == 'aktif' ? 'success' : 'secondary'; ?>">
                                                <?php echo ucfirst($row['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
                
                <div class="card">
                    <div class="card-header bg-transparent">
                        <h5 class="mb-0">
                            <i class="fas fa-school me-2 text-primary"></i>
                            Tentang SMK IGASAR PINDAD
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-8">
                                <p class="lead">SMK IGASAR PINDAD Bandung adalah sekolah menengah kejuruan yang berfokus pada pengembangan kompetensi teknik dan teknologi.</p>
                                
                                <h6 class="fw-bold mt-4 mb-3">Visi</h6>
                                <p>Menjadi SMK unggulan yang menghasilkan lulusan berkarakter, kompeten, dan siap kerja di era industri 4.0.</p>
                                
                                <h6 class="fw-bold mt-4 mb-3">Misi</h6>
                                <ul>
                                    <li>Menyelenggarakan pendidikan kejuruan yang berkualitas dan relevan dengan kebutuhan industri</li>
                                    <li>Mengembangkan karakter siswa yang berakhlak mulia dan berjiwa entrepreneur</li>
                                    <li>Membangun kemitraan strategis dengan dunia usaha dan dunia industri</li>
                                    <li>Mengoptimalkan pemanfaatan teknologi dalam proses pembelajaran</li>
                                </ul>
                                
                                <h6 class="fw-bold mt-4 mb-3">Fasilitas</h6>
                                <div class="row">
                                    <div class="col-md-6">
                                        <ul>
                                            <li>Laboratorium Komputer</li>
                                            <li>Workshop Pemesinan</li>
                                            <li>Bengkel Otomotif</li>
                                            <li>Ruang Multimedia</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <ul>
                                            <li>Perpustakaan Digital</li>
                                            <li>Aula Serbaguna</li>
                                            <li>Lapangan Olahraga</li>
                                            <li>Kantin Sekolah</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <i class="fas fa-award fa-3x text-primary mb-3"></i>
                                        <h6 class="fw-bold">Prestasi Terbaru</h6>
                                        <ul class="list-unstyled text-start">
                                            <li class="mb-2">
                                                <i class="fas fa-trophy text-warning me-2"></i>
                                                Juara 1 LKS Tingkat Provinsi
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-medal text-success me-2"></i>
                                                Akreditasi A
                                            </li>
                                            <li class="mb-2">
                                                <i class="fas fa-star text-info me-2"></i>
                                                ISO 9001:2015
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
