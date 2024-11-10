<!DOCTYPE html>
<html lang="en">
<?php
$user_check = 0;
$pw_check = 0;

if (isset($_POST['submit'])) {
    // Start the session
    include_once('dbconnection.php');

    // Fetch from HTML
    $UserName = $_POST["UserName"];
    $Password = $_POST['Password'];

    // Use prepared statements to avoid SQL injection
    $stmt = $con->prepare("SELECT `Password` FROM `users` WHERE UserName = ?");
    $stmt->bind_param("s", $UserName);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        // Verify the password
        if (password_verify($Password, $row['Password'])) {
            session_start();
            // Password is correct, set session variables
            $_SESSION['login'] = true;
            $_SESSION['name'] = $UserName; // It's better to use UserName directly
            header("location:index.php");
            exit(); // Stop further execution
        } else {
            $pw_check = 1;
        }
    } else {
        // echo "<script>alert('User not registered');</script>";
        $user_check = 1;

    }

    // Close the statement
    $stmt->close();
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            font-family: sans-serif;
        }

        :root {
            --main: #036e3a;
        }

        body {
            background-image: url("./Images/dan-meyers-IQVFVH0ajag-unsplash.jpg");
            background-size: cover;
            background-repeat: no-repeat;
            display: flex;
            justify-content: center;
            align-items: center;
            width: 100vw;
            height: 100vh;
        }

        .container {
            max-width: 999px;
            height: 690px;
            border-radius: 20px;
            display: flex;
            overflow: hidden;
        }


        .image-side {
            background-image: url("./Images/roman-synkevych-fjj7lVpCxRE-unsplash.jpg");
            width: 50%;
            height: 100%;
            background-position: center;
            background-size: cover;
            backdrop-filter: blur(5px);
            border-radius: 20px 0px 0px 20px;
        }

        .content-side {
            width: 50%;
            background-color: rgba(255, 255, 255, 0.47);
            padding: 30px;
            backdrop-filter: blur(5px);
            border-radius: 0px 20px 20px 0px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-heading img {
            height: 80px;
        }

        #sub-heading-1 {
            font-weight: bold;
            font-size: 1.5rem;
            color: var(--main);
            margin-top: 20px;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.9rem;
        }

        .form-control {
            font-weight: bold;
            font-size: 1.1rem;
            border: 2px solid black;
            border-radius: 10px;
            margin: 10px 0;
            background-color: transparent;
        }

        .form-control::placeholder {
            color: #555;
            font-weight: bold;
            font-size: 0.8rem;
        }

        .btn-submit {
            font-weight: bold;
            font-size: 1.5rem;
            border: 2px solid black;
            padding: 5px;
            border-radius: 20px;
            transition: all .5s ease;
            cursor: pointer;
            background-color: rgba(0, 0, 0, 0.244);
            color: white;
            width: 100%;
        }

        .btn-submit:hover {
            background-color: var(--main);
            color: white;
        }

        #footer {
            text-align: center;
            padding-top: 15px;
            color: black;
            font-size: 0.9rem;
        }

        #footer a {
            color: black;
            font-size: 1rem;
            text-decoration: none;
            transition: all .2s ease;
        }

        #footer a:hover {
            color: var(--main);
        }

        @media (max-width: 992px) {
            .image-side {
                display: none;
            }

            .content-side {
                width: 100%;
                border-radius: 0px;
                backdrop-filter: blur(3px);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="image-side d-none d-md-block"></div>
        <div class="content-side">
            <div class="login-heading text-center">
                <img src="Images/agzone-logo.png" alt="AgZone" title="AgZone">
            </div>

            <p id="sub-heading-1" class="text-center">Login</p>
            <?php
            if ($user_check) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Invalid </strong>Username.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
            }

            if ($pw_check) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Invalid </strong>Password.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
            }
            ?>
            <div class="form">
                <form action="log.php" method="post">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="UserName" class="form-control" placeholder="Username"
                            required>
                    </div>

                    <div class="mb-3">
                        <label for="Password" class="form-label">Password</label>
                        <input type="password" id="Password" name="Password" class="form-control" placeholder="Password"
                            required pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}"
                            title="Password must be at least 8 characters, contain an uppercase letter, a lowercase letter, a number, and a special character (!@#$%^&*).">
                    </div>

                    <button type="submit" name="submit" class="btn btn-submit">Sign In</button>
                </form>
            </div>

            <p id="footer">Don't have an account yet? <a href="reg.php">Register here</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script type="text/javascript" src="Scripts/bootstrap.min.js"></script>
    <script type="text/javascript" src="Scripts/jquery-2.1.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-HwwvtgBNo3bZJJLYd8oVXjrBZt8cqVSpeBNS5n7C8IVInixGAoxmnlMuBnhbgrkm"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.min.js"
        integrity="sha384-Rx+T1VzGupg4BHQYs2gCW9It+akI2MM/mndMCy36UVfodzcJcF0GGLxZIzObiEfa"
        crossorigin="anonymous"></script>
</body>

</html>