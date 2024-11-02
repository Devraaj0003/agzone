<!DOCTYPE html>
<html lang="en">
<?php
include_once('dbconnection.php');
$unamecheck = 0;
$pwcheck = 0;

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
            // echo "<script>alert('UserName has already been taken');</script>";
            $unamecheck= 1;
        } else {
            if ($Npassword == $Cpassword) {
                // Hash the password for security
                $hashedPassword = password_hash($Cpassword, PASSWORD_DEFAULT);

                // Insert into database
                $stmt = $con->prepare("INSERT INTO `membership`(`UserName`, `MobileNo`, `Email`, `Password`) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $UserName, $MobileNo, $Email, $hashedPassword);

                if ($stmt->execute()) {
                    echo "<script>alert('Registration Successful');</script>";
                    header("location:log.php");
                    exit();  // Stop further script execution
                }
            } else {
                // echo "<script>alert('Passwords do not match');</script>";
                $pwcheck= 1;
            }
        }
    }
}
?>
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Registration</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-4bw+/aepP/YC94hEpVNVgiZdgIC5+VKNBQNGCHeKRQN+PtmoHDEXuppvnDJzQIu9" crossorigin="anonymous">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * {
            box-sizing: border-box;
            font-family: sans-serif;
            margin: 0;
        }

        body {
            background-image: url("./Images/nadine-redlich-SEF34uHb1jE-unsplash.jpg");
            background-position: center;
            background-size: cover;
            background-repeat: no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .container {
            max-width: 90%;
            height: 760px;
            border-radius: 10px;
            overflow: hidden;
            display: flex;
            background-color: white;
        }

        .left-side {
            background-image: url("./Images/deepmind-s2TPKAD6fag-unsplash.jpg");
            background-size: cover;
            background-position: top;
            width: 50%;
            display: flex;
            align-content: flex-end;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            color: whitesmoke;
            font-size: 1.9rem;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }

        .content-side {
            padding: 30px;
            width: 50%;
            background-color: whitesmoke;
        }

        .login-heading img {
            height: 80px;
            padding-left: 10px;
        }

        .form-control {
            font-weight: bold;
            font-size: 1.1rem;
            background-color: transparent;
        }

        #submit {
            width: 100%;
            border-radius: 20px;
            cursor: pointer;
            font-size: 1.5rem;
            font-weight: bold;
            background-color: black;
            color: white;
            transition: background-color 0.5s ease;
        }

        #submit:hover {
            background-color: #036e3a;
            color: whitesmoke;
        }

        .sign-in-option a {
            color: #036e3a;
            font-weight: bold;
            font-size: 1.2rem;
            text-decoration: none;
        }

        @media (max-width: 992px) {
            .left-side {
                display: none;
            }
            .content-side {
                width: 100%;
                border-radius: 10px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- Left Side -->
        <div class="left-side d-none d-md-flex">
            <p>Welcome to our<br>website</p>
        </div>

        <!-- Content Side -->
        <div class="content-side">
            <div class="text-center mb-6">
                <img src="Images/agzone-logo.png" alt="AgZone" title="AgZone" />
            </div>

            <div class="sub-heading text-center mb-4">
                <p class="h4 text-success font-weight-bold">Create Your Free Account</p>
            </div>

            <div id="error">
            <?php
            if ($pwcheck) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Alert </strong>Passwords do not match.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
            }
            ?>
            <?php
            if ($unamecheck) {
                echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">
    <strong>Alert </strong>Username already taken.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>';
            }
            ?>
            </div>
            <div class="form">
                <form action="reg.php" method="post" id="form">
                    <div class="form-group">
                        <label for="UserName">Username</label>
                        <input type="text" id="UserName" name="UserName" class="form-control" placeholder="Sample852#" required />
                    </div>

                    <div class="form-group">
                        <label for="Email">Email</label>
                        <input type="email" id="Email" name="Email" class="form-control" placeholder="Example@email.com" required />
                    </div>

                    <div class="form-group">
                        <label for="MobileNo">Mobile No</label>
                        <input type="tel" id="MobileNo" name="MobileNo" class="form-control" placeholder="07########" pattern="^[0-9]{10}$" required />
                    </div>

                    <div class="form-group">
                        <label for="Npassword">New Password</label>
                        <input type="password" id="Npassword" name="Npassword" class="form-control" placeholder="Enter Password" pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}" 
            title="Password must be at least 8 characters, contain an uppercase letter, a lowercase letter, a number, and a special character (!@#$%^&*)."
        required />
                    </div>

                    <div class="form-group">
                        <label for="Cpassword">Confirm Password</label>
                        <input type="password" id="Cpassword" name="Cpassword" class="form-control" placeholder="Confirm Password" required pattern="(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}" 
            title="Password must be at least 8 characters, contain an uppercase letter, a lowercase letter, a number, and a special character (!@#$%^&*)."
        />
                    </div>

                    <button type="submit" id="submit" name="submit" class="btn btn-block">Create your account</button>
                </form>
            </div>

            <div class="sign-in-option mt-4 text-center">
                <p>Already have an account? <a href="log.php">Sign in</a></p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS (optional) -->
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
