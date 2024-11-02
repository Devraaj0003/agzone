<?php
session_start(); // Start the session
include_once('dbconnection.php');

if (isset($_POST['submit'])) {
    // Fetch from HTML
    $UserName = $_POST["UserName"];
    $Password = $_POST['Password'];

    // Use prepared statements to avoid SQL injection
    $stmt = $con->prepare("SELECT `Password` FROM `membership` WHERE UserName = ?");
    $stmt->bind_param("s", $UserName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // Verify the password
        if (password_verify($Password, $row['Password'])) {
            // Password is correct, set session variables
            $_SESSION['login'] = true;
            $_SESSION['name'] = $UserName; // It's better to use UserName directly
            header("location:index.php");
            exit(); // Stop further execution
        } else {
            echo "<script>alert('Wrong password');</script>";
        }
    } else {
        echo "<script>alert('User not registered');</script>";
    }

    // Close the statement
    $stmt->close();
}
?>
