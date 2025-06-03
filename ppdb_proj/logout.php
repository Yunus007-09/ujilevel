<?php
session_start();
require_once 'config/database.php';
require_once 'classes/Login.php';

// Debug: Check if session exists
error_log("Logout attempt - Session user_id: " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 'not set'));

if (isset($_SESSION['user_id'])) {
    try {
        $database = new Database();
        $db = $database->getConnection();
        $login = new Login($db);
        
        // Update login status in database
        if (isset($_SESSION['username'])) {
            $query = "UPDATE login SET status_login = FALSE WHERE username = ?";
            $stmt = $db->prepare($query);
            $stmt->bindParam(1, $_SESSION['username']);
            $stmt->execute();
        }
        
        // Clear all session data
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
        
        // Redirect to login page
        header("Location: login.php?logout=success");
        exit();
        
    } catch (Exception $e) {
        error_log("Logout error: " . $e->getMessage());
        // Even if there's an error, still destroy session and redirect
        session_destroy();
        header("Location: login.php?error=logout_error");
        exit();
    }
} else {
    // No active session, just redirect to login
    session_destroy();
    header("Location: login.php");
    exit();
}
?>


