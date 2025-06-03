<?php
class MonitoringPenerimaan {
    private $conn;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function getJumlahPendaftar($tahun_ajaran) {
        $query = "SELECT COUNT(*) as total FROM pendaftar WHERE tahun_ajaran = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $tahun_ajaran);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    public function getJumlahPerJurusan($tahun_ajaran) {
        $query = "SELECT j.kode_jur, j.nama_jurusan, j.kuota,
                 COUNT(p.no_daftar) as jumlah_pendaftar,
                 COUNT(CASE WHEN p.status_pendaftaran = 'diterima' THEN 1 END) as diterima,
                 COUNT(CASE WHEN p.status_pendaftaran = 'pending' THEN 1 END) as pending
                 FROM jurusan j 
                 LEFT JOIN pendaftar p ON j.kode_jur = p.kode_jur AND p.tahun_ajaran = ?
                 WHERE j.status = 'aktif'
                 GROUP BY j.kode_jur, j.nama_jurusan, j.kuota
                 ORDER BY j.kode_jur";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $tahun_ajaran);
        $stmt->execute();
        return $stmt;
    }
    
    public function getTotalBiaya($tahun_ajaran) {
        $query = "SELECT 
                 COUNT(p.no_daftar) as total_pendaftar,
                 (COUNT(p.no_daftar) * b.b_pendaftaran) as total_biaya_pendaftaran,
                 (COUNT(CASE WHEN p.status_pendaftaran = 'diterima' THEN 1 END) * 
                  (b.b_awal_tahun + b.b_seragam + b.b_spp)) as total_biaya_diterima
                 FROM pendaftar p
                 JOIN biaya_tahunan b ON p.tahun_ajaran = b.tahun_ajaran
                 WHERE p.tahun_ajaran = ? AND b.status = 'aktif'";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $tahun_ajaran);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function tampilkanStatistik($tahun_ajaran) {
        $stats = [
            'total_pendaftar' => $this->getJumlahPendaftar($tahun_ajaran),
            'per_jurusan' => $this->getJumlahPerJurusan($tahun_ajaran),
            'biaya' => $this->getTotalBiaya($tahun_ajaran)
        ];
        
        return $stats;
    }
}
?>
