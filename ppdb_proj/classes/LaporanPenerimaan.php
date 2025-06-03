<?php
class LaporanPenerimaan {
    
    public $id_laporan;
    public $tanggal_cetak;
    public $tahun_ajaran;
    public $jumlah_pendaftar;
    public $total_biaya;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function generateLaporan() {
        // Calculate data for report
        $count_query = "SELECT COUNT(*) as total FROM pendaftar WHERE tahun_ajaran = ?";
        $count_stmt = $this->conn->prepare($count_query);
        $count_stmt->bindParam(1, $this->tahun_ajaran);
        $count_stmt->execute();
        $count_result = $count_stmt->fetch(PDO::FETCH_ASSOC);
        
        $this->jumlah_pendaftar = $count_result['total'];
        $this->tanggal_cetak = date('Y-m-d');
        
        // Get biaya and calculate total
        $biaya_query = "SELECT * FROM biaya_tahunan WHERE tahun_ajaran = ?";
        $biaya_stmt = $this->conn->prepare($biaya_query);
        $biaya_stmt->bindParam(1, $this->tahun_ajaran);
        $biaya_stmt->execute();
        $biaya = $biaya_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($biaya) {
            $total_per_siswa = $biaya['b_pendaftaran'] + $biaya['b_awal_kuliah'] + $biaya['b_seragam'] + $biaya['b_spp'];
            $this->total_biaya = $this->jumlah_pendaftar * $total_per_siswa;
        }
        
        $query = "INSERT INTO laporan_penerimaan (tanggal_cetak, tahun_ajaran, jumlah_pendaftar, total_biaya) 
                 VALUES (?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->tanggal_cetak);
        $stmt->bindParam(2, $this->tahun_ajaran);
        $stmt->bindParam(3, $this->jumlah_pendaftar);
        $stmt->bindParam(4, $this->total_biaya);
        
        return $stmt->execute();
    }
    
    public function hitungJumlahPendaftar($tahun_ajaran) {
        $query = "SELECT COUNT(*) as total FROM pendaftar WHERE tahun_ajaran = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $tahun_ajaran);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
    
    public function hitungTotalBiaya($tahun_ajaran) {
        $jumlah = $this->hitungJumlahPendaftar($tahun_ajaran);
        
        $biaya_query = "SELECT * FROM biaya_tahunan WHERE tahun_ajaran = ?";
        $stmt = $this->conn->prepare($biaya_query);
        $stmt->bindParam(1, $tahun_ajaran);
        $stmt->execute();
        $biaya = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($biaya) {
            $total_per_siswa = $biaya['b_pendaftaran'] + $biaya['b_awal_kuliah'] + $biaya['b_seragam'] + $biaya['b_spp'];
            return $jumlah * $total_per_siswa;
        }
        
        return 0;
    }
}
?>
