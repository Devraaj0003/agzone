<?php
include_once('dbconnection.php');

if (isset($_POST['submit'])) {
    // Fetch from html
    $UserName = $_POST['UserName'];
    $Email = $_POST['Email'];
    $MobileNo = $_POST['MobileNo'];
    $Npassword = $_POST['Npassword'];
    $Cpassword = $_POST['Cpassword'];

    // Validation for mobile number
    if (!preg_match("/^[0-9]{10}$/", $MobileNo)) {
        echo "<script>alert('Mobile Number is invalid');</script>";
    } else {
        // Check for duplicate username
        $stmt = $con->prepare("SELECT * FROM `membership` WHERE UserName = ?");
        $stmt->bind_param("s", $UserName);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<script>alert('UserName has already been taken');</script>";
        } else {
            if ($Npassword == $Cpassword) {
                // Hash the password for security
                $hashedPassword = password_hash($Cpassword, PASSWORD_DEFAULT);

                // Insert into database
                $stmt = $con->prepare("INSERT INTO `membership`(`UserName`, `MobileNo`, `Email`, `Password`) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $UserName, $MobileNo, $Email, $hashedPassword);

                if ($stmt->execute()) {
                    echo "<script>alert('Registration Successful');</script>";
                    header("location:login.html");
                    exit();  // Stop further script execution
                }
            } else {
                echo "<script>alert('Passwords do not match');</script>";
            }
        }
    }
}
?>
