<?php
class Petugas {
    private $conn;
    
    public $kd_petugas;
    public $nama_petugas;
    public $role;
    
    public function __construct($db) {
        $this->conn = $db;
    }
    
    public function verifikasiData($no_daftar) {
        // Implementation for data verification
        $query = "SELECT * FROM pendaftar WHERE no_daftar = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $no_daftar);
        $stmt->execute();
        
        return $stmt->rowCount() > 0;
    }
    
    public function getAll() {
        $query = "SELECT * FROM petugas ORDER BY nama_petugas";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
}
?>
