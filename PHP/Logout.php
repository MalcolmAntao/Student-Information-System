<?php
session_start();

// Check if the session is set for the user
if (isset($_SESSION['email'])) {
    // Unset all session variables
    $_SESSION = array();
    
    // If the session uses cookies, remove the session cookie
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, 
            $params["path"], 
            $params["domain"], 
            $params["secure"], 
            $params["httponly"]
        );
    }
    
    // Destroy the session
    session_destroy();
}

// Redirect to the login page or homepage after logging out
header("Location: /SIS/HTML/index.html");
exit();
?>
