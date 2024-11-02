<?php
include_once('dbconnection.php');
session_start();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the log ID is set in the session
if (isset($_SESSION['log_id'])) {
    $log_id = $_SESSION['log_id'];

    // Update the logout time for the current session in the database
    $logout_sql = "UPDATE user_logs SET logout_time = NOW() WHERE id = ?";
    $logout_stmt = $con->prepare($logout_sql);
    
    if ($logout_stmt) {
        $logout_stmt->bind_param("i", $log_id); // Bind log_id as integer
        $logout_stmt->execute();
        $logout_stmt->close();
    } else {
        die("Error preparing logout statement: " . $con->error);
    }
}

// Destroy the session to log out the user
session_unset();  // Unset all session variables
session_destroy(); // Destroy the session itself

// Close the database connection
$con->close();

// Redirect to the login page
header("Location: log.php");
exit();
?>
