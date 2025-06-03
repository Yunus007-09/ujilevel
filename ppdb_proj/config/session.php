<?php
session_start();

function checkLogin() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

function checkRole($allowed_roles) {
    checkLogin();
    if (!in_array($_SESSION['role'], $allowed_roles)) {
        header("Location: unauthorized.php");
        exit();
    }
}

function checkAdmin() {
    checkRole(['admin']);
}

function checkPetugas() {
    checkRole(['admin', 'petugas']);
}

function checkTU() {
    checkRole(['admin', 'tu']);
}

function checkKepalaSekolah() {
    checkRole(['admin', 'kepala_sekolah']);
}

function checkAdminOrTU() {
    checkRole(['admin', 'tu']);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getUserRole() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}

function getRoleName($role) {
    $roles = [
        'pendaftar' => 'Pendaftar',
        'petugas' => 'Petugas',
        'admin' => 'Administrator',
        'tu' => 'Tata Usaha',
        'kepala_sekolah' => 'Kepala Sekolah'
    ];
    return isset($roles[$role]) ? $roles[$role] : 'Unknown';
}

function canAccess($feature) {
    $role = getUserRole();
    
    $permissions = [
        'pendaftaran' => ['pendaftar'],
        'verifikasi_data' => ['admin', 'petugas'],
        'kelola_jurusan' => ['admin'],
        'kelola_biaya' => ['admin'],
        'laporan' => ['admin', 'tu'],
        'monitoring' => ['admin', 'kepala_sekolah'],
        'kelola_petugas' => ['admin']
    ];
    
    return isset($permissions[$feature]) && in_array($role, $permissions[$feature]);
}
?>
