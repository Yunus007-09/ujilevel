<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i>
                    Dashboard
                </a>
            </li>
            
            <?php if ($_SESSION['role'] == 'pendaftar'): ?>
            <li class="nav-item">
                <a class="nav-link" href="Pendaftaran.php">
                    <i class="fas fa-user-plus me-2"></i>
                    Pendaftaran
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="status_pendaftaran.php">
                    <i class="fas fa-clipboard-check me-2"></i>
                    Status Pendaftaran
                </a>
            </li>
            <?php endif; ?>
            
            <?php if (in_array($_SESSION['role'], ['admin', 'petugas'])): ?>
            <li class="nav-item">
                <a class="nav-link" href="data_pendaftar.php">
                    <i class="fas fa-users me-2"></i>
                    Data Pendaftar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="monitoring.php">
                    <i class="fas fa-chart-line me-2"></i>
                    Monitoring
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="laporan.php">
                    <i class="fas fa-file-alt me-2"></i>
                    Laporan
                </a>
            </li>
            <?php endif; ?>
            
            <?php if ($_SESSION['role'] == 'admin'): ?>
            <li class="nav-item">
                <a class="nav-link" href="kelola_jurusan.php">
                    <i class="fas fa-cogs me-2"></i>
                    Kelola Jurusan
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="kelola_petugas.php">
                    <i class="fas fa-user-tie me-2"></i>
                    Kelola Petugas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="kelola_biaya.php">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    Kelola Biaya
                </a>
            </li>
            <?php endif; ?>
            
            <li class="nav-item">
                <a class="nav-link" href="informasi.php">
                    <i class="fas fa-info-circle me-2"></i>
                    Informasi
                </a>
            </li>
        </ul>
    </div>
</nav>
