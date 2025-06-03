<?php
class BiayaTahunan {
    private $conn;

    public $tahun_ajaran;
    public $b_pendaftaran;
    public $b_awal_tahun;
    public $b_seragam;
    public $b_spp;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function totalBiaya($tahun_ajaran) {
        $biaya = $this->getByTahunAjaran($tahun_ajaran);
        if ($biaya) {
            return (float)$biaya['b_pendaftaran'] + (float)$biaya['b_awal_tahun'] + (float)$biaya['b_seragam'] + (float)$biaya['b_spp'];
        }
        return 0;
    }

    public function getByTahunAjaran($tahun_ajaran) {
        $query = "SELECT * FROM biaya_tahunan WHERE tahun_ajaran = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(1, $tahun_ajaran, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        $query = "SELECT * FROM biaya_tahunan ORDER BY tahun_ajaran DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO biaya_tahunan (tahun_ajaran, b_pendaftaran, b_awal_tahun, b_seragam, b_spp, status) 
                 VALUES (?, ?, ?, ?, ?, 'aktif')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(1, $this->tahun_ajaran, PDO::PARAM_STR);
        $stmt->bindValue(2, $this->b_pendaftaran, PDO::PARAM_STR);
        $stmt->bindValue(3, $this->b_awal_tahun, PDO::PARAM_STR);
        $stmt->bindValue(4, $this->b_seragam, PDO::PARAM_STR);
        $stmt->bindValue(5, $this->b_spp, PDO::PARAM_STR);
        return $stmt->execute();
    }

    public function update() {
        $query = "UPDATE biaya_tahunan SET b_pendaftaran=?, b_awal_tahun=?, b_seragam=?, b_spp=?, status=? 
                 WHERE tahun_ajaran=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(1, $this->b_pendaftaran, PDO::PARAM_STR);
        $stmt->bindValue(2, $this->b_awal_tahun, PDO::PARAM_STR);
        $stmt->bindValue(3, $this->b_seragam, PDO::PARAM_STR);
        $stmt->bindValue(4, $this->b_spp, PDO::PARAM_STR);
        $stmt->bindValue(5, $this->status, PDO::PARAM_STR);
        $stmt->bindValue(6, $this->tahun_ajaran, PDO::PARAM_STR);
        return $stmt->execute();
    }
}
?>