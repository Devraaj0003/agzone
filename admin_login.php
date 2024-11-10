<?php
include_once('dbconnection.php');
session_start();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the admin is already logged in
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true) {
    header('Location: admin_dashboard.php');
    exit();
}

// Initialize variables for form data and error messages
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($username && $password) {
        // Fetch admin data from the database
        $sql = "SELECT * FROM admin WHERE username = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            // Verify the password
            if (password_verify($password, $admin['password'])) {
                // Successful login
                $_SESSION['is_admin'] = true;
                $_SESSION['admin_username'] = $username;
                header('Location: admin_dashboard.php');
                exit();
            } else {
                $error = 'Invalid username or password.';
            }
        } else {
            $error = 'Invalid username or password.';
        }

        $stmt->close();
    } else {
        $error = 'Please enter both username and password.';
    }
}

$con->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f0f7f4;
        }
        .login-container {
            max-width: 400px;
            margin: 50px auto;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #c8e0d5;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }
        .login-container h2 {
            color: #355e3b;
            text-align: center;
        }
        .form-group button {
            background-color: #3b8b4b;
            color: white;
        }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Admin Login</h2>
    <?php if ($error): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    <form action="admin_login.php" method="POST">
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary">Login</button>
        </div>
    </form>
</div>

</body>
</html>
