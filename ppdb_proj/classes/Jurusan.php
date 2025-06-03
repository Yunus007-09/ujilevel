<?php
class Jurusan {
    private $conn;
    
    public $kode_jur;
    public $nama_jurusan;
    public $kuota;
    public $status;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function getJurusan() {
        $query = "SELECT * FROM jurusan WHERE status = 'aktif' ORDER BY nama_jurusan";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function getAll() {
        $query = "SELECT * FROM jurusan ORDER BY nama_jurusan";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function create() {
        $query = "INSERT INTO jurusan (kode_jur, nama_jurusan, kuota, status) VALUES (?, ?, ?, 'aktif')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->kode_jur);
        $stmt->bindParam(2, $this->nama_jurusan);
        $stmt->bindParam(3, $this->kuota);
        return $stmt->execute();
    }
    
    public function update() {
        $query = "UPDATE jurusan SET nama_jurusan=?, kuota=?, status=? WHERE kode_jur=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->nama_jurusan);
        $stmt->bindParam(2, $this->kuota);
        $stmt->bindParam(3, $this->status);
        $stmt->bindParam(4, $this->kode_jur);
        return $stmt->execute();
    }
}
?>
