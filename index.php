<?php
include_once('dbconnection.php');
session_start();

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$username = $_SESSION['name'];
if (!isset($username)) {
    header('location:log.php');
    exit();
}

// Check if there is an active session for this user
$check_active_session_sql = "SELECT id FROM user_logs WHERE user_id = ? AND logout_time IS NULL";
$check_stmt = $con->prepare($check_active_session_sql);
if ($check_stmt) {
    $check_stmt->bind_param("s", $username);
    $check_stmt->execute();
    $check_stmt->store_result();
    
    if ($check_stmt->num_rows > 0) {
        // User already has an active session, no need to insert a new one
        $check_stmt->bind_result($active_log_id);
        $check_stmt->fetch();
        $_SESSION['log_id'] = $active_log_id; // Store the existing log ID in session
    } else {
        // Insert a new login record since no active session exists
        $insert_sql = "INSERT INTO user_logs (user_id, login_time) VALUES (?, NOW())";
        $insert_stmt = $con->prepare($insert_sql);
        if ($insert_stmt) {
            $insert_stmt->bind_param("s", $username);
            $insert_stmt->execute();
            $_SESSION['log_id'] = $con->insert_id; // Store the new log ID in session for logout
            $insert_stmt->close();
        } else {
            die("Error preparing insert statement: " . $con->error);
        }
    }
    $check_stmt->close();
} else {
    die("Error preparing check statement: " . $con->error);
}

// Close the database connection
$con->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="index_style.css">
    <script defer src="script.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <title>AgZone-Home</title>
    
</head>

<body>


    <div class="home">
        <div class="nav-bar">
            <div class="left-side">
                <div class="logo">
                    <img src="Images/agzone-logo.png" alt="AgZone-Logo" title="AgZone">
                </div>
            </div>

            <div class="right-side">

                <ul id="nav-links">
                    <li><a href="index.php"><i class="fa fa-fw fa-home"></i>Home</a></li>
                    <li><a href="rent_machine.php"><i class="fa fa-bus" aria-hidden="true"></i>Rent machinery</a></li>
                    <li><i class="fa fa-user" aria-hidden="true"></i> <em><b><?php echo $username ?></b></em>  </li>
                    


                </ul>



            </div>


            <a href="logout.php"><button id="login"><i class="fa fa-fw fa-user"></i>Logout</button></a><br>



            <button class="right-bar">
                <span class="bar"></span>
            </button>

        </div>

        <div class="mobile_nav">

            <ul id="mobile_nav_links">
                <li><a href="index.php"><i class="fa fa-fw fa-home"></i>Home</a></li>
                <li><a href="rent_machine.php"><i class="fa fa-bus" aria-hidden="true"></i>Rent machinery</a></li>
            </ul>
            <a href="logout.php"><button id="mobile_login"><i class="fa fa-fw fa-user"></i>Logout</button></a>
            <div class="mobile_footer">
                <p>Copyright&copy; 2022 AgZone. All Rights Reserved</p>
            </div>
        </div>

        <div class="hero-image">
            <video autoplay muted loop>
                <source src="Video/Banner Video.mp4" type="video/mp4">
            </video>
        </div>
        <div class="hero-image">
            <video autoplay muted loop>
                <source src="Video/Banner Video.mp4" type="video/mp4">
            </video>
        </div>
    </div>
    <div class="sub-hero-image" id="sub-hero-image">

    </div>

    <div class="about-agzone">
        <div class="about">

            <div class="image">
                <p id="inc">About AgZone, Inc.</p>
                <img src="./Images/about-agzone.jpeg">

            </div>
            <div class="about-para">
                <p style="top:100%;">
                    At <span>AgZone</span>,
                    We provide expert advice alongside a diverse range of rental farming machinery. Our clients are
                    welcome to visit our farm to test drive the machines, ensuring you make an informed decision before
                    renting. With dedicated teams across the country, we’re committed to offering top-notch equipment to
                    meet your farming needs. We’re also constantly expanding our network, always searching for skilled
                    individuals passionate about agriculture.
                </p>
            </div>

        </div>
    </div>


    <section class="services">
        <div class="heading">
            <p>Our Services</p>
        </div>
        <div class="service-image">

        </div>


        <div class="cards">

            <div class="actual-card">
                <div class="card-image">
                    <img src="./Images/rent (2).png">
                </div>
                <div class="content">
                    <div class="head">

                    </div>

                    <p id="para">Explore our extensive range of cutting-edge farming machines available for rent—click
                        below to find the perfect equipment for your needs and book your rental today!</p>
                    <p id="more"><a href="rent_machine.php">Check Now</a></p>
                    <!-- <a href="login.html"><button id="login"><i class="fa fa-fw fa-user"></i>Login</button></a> -->
                </div>
            </div>
        </div>
    </section>




    <section class="footer">
        <div class="left-side-footer">
            <p>&copy;2022 All Rights Reserved.AgZone.</p>
        </div>
        <div class="right-side-footer">
            <p>Web Design and Development by<a href="index.html">AgZone Team</a></p>
        </div>
    </section>



</body>

</html>
