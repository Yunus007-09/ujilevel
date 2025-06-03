<?php
class Registrasi {
    private $conn;
    private $table_name = "registrasi";

    public $username;
    public $password;
    public $email;
    public $role;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function buatAkun() {
        try {
            // Validate input
            if (empty($this->username) || empty($this->password) || empty($this->email)) {
                return "Semua field harus diisi!";
            }
            
            if (strlen($this->username) < 3) {
                return "Username minimal 3 karakter!";
            }
            
            if (strlen($this->password) < 6) {
                return "Password minimal 6 karakter!";
            }
            
            if (!filter_var($this->email, FILTER_VALIDATE_EMAIL)) {
                return "Format email tidak valid!";
            }
            
            // Check if username or email already exists
            $check_query = "SELECT id_registrasi FROM " . $this->table_name . " WHERE username = ? OR email = ?";
            $check_stmt = $this->conn->prepare($check_query);
            $check_stmt->bindParam(1, $this->username);
            $check_stmt->bindParam(2, $this->email);
            $check_stmt->execute();
            
            if ($check_stmt->rowCount() > 0) {
                return "Username atau email sudah digunakan!";
            }
            
            // Validate role
            $valid_roles = ['pendaftar', 'petugas', 'admin', 'tu', 'kepala_sekolah'];
            if (!in_array($this->role, $valid_roles)) {
                $this->role = 'pendaftar'; // Default role
            }
            
            // Insert new user
            $query = "INSERT INTO " . $this->table_name . " (username, password, email, role, status_aktif) VALUES (?, MD5(?), ?, ?, TRUE)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $this->username);
            $stmt->bindParam(2, $this->password);
            $stmt->bindParam(3, $this->email);
            $stmt->bindParam(4, $this->role);
            
            if ($stmt->execute()) {
                return true;
            }
            
            return "Terjadi kesalahan saat membuat akun!";
            
        } catch (PDOException $e) {
            error_log("Registration error: " . $e->getMessage());
            return "Terjadi kesalahan sistem. Silakan coba lagi.";
        }
    }
    
    public function getAllUsers() {
        $query = "SELECT r.*, p.nama_petugas 
                 FROM " . $this->table_name . " r 
                 LEFT JOIN petugas p ON r.id_registrasi = p.user_id 
                 ORDER BY r.created_at DESC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    
    public function updateStatus($user_id, $status) {
        $query = "UPDATE " . $this->table_name . " SET status_aktif = ? WHERE id_registrasi = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $status);
        $stmt->bindParam(2, $user_id);
        return $stmt->execute();
    }
}
?>

