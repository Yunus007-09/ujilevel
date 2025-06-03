<?php
class Login {
    private $conn;
    private $table_name = "registrasi";

    public $username;
    public $password;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function autentikasi() {
        // Check if user exists and is active
        $query = "SELECT r.*, p.nama_petugas 
                 FROM " . $this->table_name . " r 
                 LEFT JOIN petugas p ON r.id_registrasi = p.user_id 
                 WHERE r.username = ? AND r.password = MD5(?) AND r.status_aktif = TRUE";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(1, $this->username);
        $stmt->bindValue(2, $this->password);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Update login status
            $update_query = "INSERT INTO login (username, password, status_login, last_login) 
                           VALUES (?, MD5(?), TRUE, NOW()) 
                           ON DUPLICATE KEY UPDATE status_login = TRUE, last_login = NOW()";
            $update_stmt = $this->conn->prepare($update_query);
            $update_stmt->bindParam(1, $this->username);
            $update_stmt->bindParam(2, $this->password);
            $update_stmt->execute();
            
            // Set session variables
            $_SESSION['user_id'] = $row['id_registrasi'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['role'] = $row['role'];
            $_SESSION['nama_user'] = $row['nama_petugas'] ?? $row['username'];
            $_SESSION['login_time'] = date('Y-m-d H:i:s');
            
            return true;
        }
        return false;
    }
    
    public function logout() {
        try {
            if (isset($_SESSION['username'])) {
                $query = "UPDATE login SET status_login = FALSE WHERE username = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bindParam(1, $_SESSION['username']);
                $stmt->execute();
            }
            
            // Clear session data
            $_SESSION = array();
            
            // Destroy session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            // Destroy session
            session_destroy();
            
            return true;
        } catch (Exception $e) {
            error_log("Logout method error: " . $e->getMessage());
            return false;
        }
    }
}
?>

