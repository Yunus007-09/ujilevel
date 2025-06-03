<?php
class Pendaftar {
    private $conn;
    
    public $no_daftar;
    public $nama;
    public $alamat;
    public $jk;
    public $kode_jur;
    public $kd_petugas;
    public $tahun_ajaran;
    public $nisn;
    public $tempat_lahir;
    public $tanggal_lahir;
    public $nama_ayah;
    public $nama_ibu;
    public $no_hp;
    public $asal_sekolah;
    public $nilai_rata;
    public $user_id;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function daftar($data) {
        // Generate nomor pendaftaran
        $year = substr($data['tahun_ajaran'], 2, 2);
        $query = "SELECT COUNT(*) as total FROM pendaftar WHERE tahun_ajaran = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $data['tahun_ajaran']);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $urutan = str_pad($result['total'] + 1, 4, '0', STR_PAD_LEFT);
        $no_daftar = "11{$year}{$urutan}";
        
        // Assign random petugas if not specified
        if (empty($data['kd_petugas'])) {
            $petugas_query = "SELECT kd_petugas FROM petugas WHERE role IN ('Petugas', 'Admin') ORDER BY RAND() LIMIT 1";
            $petugas_stmt = $this->conn->prepare($petugas_query);
            $petugas_stmt->execute();
            $petugas_result = $petugas_stmt->fetch(PDO::FETCH_ASSOC);
            $data['kd_petugas'] = $petugas_result['kd_petugas'];
        }
        
        $query = "INSERT INTO pendaftar (no_daftar, nama, alamat, jk, kode_jur, kd_petugas, tahun_ajaran, 
                 nisn, tempat_lahir, tanggal_lahir, nama_ayah, nama_ibu, no_hp, asal_sekolah, nilai_rata, user_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $no_daftar);
        $stmt->bindParam(2, $data['nama']);
        $stmt->bindParam(3, $data['alamat']);
        $stmt->bindParam(4, $data['jk']);
        $stmt->bindParam(5, $data['kode_jur']);
        $stmt->bindParam(6, $data['kd_petugas']);
        $stmt->bindParam(7, $data['tahun_ajaran']);
        $stmt->bindParam(8, $data['nisn']);
        $stmt->bindParam(9, $data['tempat_lahir']);
        $stmt->bindParam(10, $data['tanggal_lahir']);
        $stmt->bindParam(11, $data['nama_ayah']);
        $stmt->bindParam(12, $data['nama_ibu']);
        $stmt->bindParam(13, $data['no_hp']);
        $stmt->bindParam(14, $data['asal_sekolah']);
        $stmt->bindParam(15, $data['nilai_rata']);
        $stmt->bindParam(16, $data['user_id']);
        
        return $stmt->execute() ? $no_daftar : false;
    }
    
    public function editData($no_daftar, $data) {
        $query = "UPDATE pendaftar SET nama=?, alamat=?, jk=?, kode_jur=?, tahun_ajaran=?, 
                 nisn=?, tempat_lahir=?, tanggal_lahir=?, nama_ayah=?, nama_ibu=?, no_hp=?, 
                 asal_sekolah=?, nilai_rata=? WHERE no_daftar=?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $data['nama']);
        $stmt->bindParam(2, $data['alamat']);
        $stmt->bindParam(3, $data['jk']);
        $stmt->bindParam(4, $data['kode_jur']);
        $stmt->bindParam(5, $data['tahun_ajaran']);
        $stmt->bindParam(6, $data['nisn']);
        $stmt->bindParam(7, $data['tempat_lahir']);
        $stmt->bindParam(8, $data['tanggal_lahir']);
        $stmt->bindParam(9, $data['nama_ayah']);
        $stmt->bindParam(10, $data['nama_ibu']);
        $stmt->bindParam(11, $data['no_hp']);
        $stmt->bindParam(12, $data['asal_sekolah']);
        $stmt->bindParam(13, $data['nilai_rata']);
        $stmt->bindParam(14, $no_daftar);
        
        return $stmt->execute();
    }
    
    public function getByUserId($user_id) {
        $query = "SELECT p.*, j.nama_jurusan, pt.nama_petugas 
                 FROM pendaftar p 
                 LEFT JOIN jurusan j ON p.kode_jur = j.kode_jur 
                 LEFT JOIN petugas pt ON p.kd_petugas = pt.kd_petugas
                 WHERE p.user_id = ?";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $user_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getAll() {
        $query = "SELECT p.*, j.nama_jurusan, pt.nama_petugas 
                 FROM pendaftar p 
                 LEFT JOIN jurusan j ON p.kode_jur = j.kode_jur 
                 LEFT JOIN petugas pt ON p.kd_petugas = pt.kd_petugas 
                 ORDER BY p.tanggal_daftar DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function updateStatus($no_daftar, $status) {
        $query = "UPDATE pendaftar SET status_pendaftaran = ? WHERE no_daftar = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $status);
        $stmt->bindParam(2, $no_daftar);
        return $stmt->execute();
    }
}
?>
