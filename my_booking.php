<?php
include_once('dbconnection.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION['name'])) {
    header('location:log.php');
    exit();
}

// Fetch user's bookings
$user_id = $_SESSION['name'];
$sql = "SELECT * FROM bookings WHERE user_id = ? ORDER BY booking_date DESC";
$stmt = $con->prepare($sql);
$stmt->bind_param("s", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch user's current bookings
$user_id = $_SESSION['name'];
$sql_current = "SELECT * FROM bookings WHERE user_id = ? ORDER BY booking_date DESC";
$stmt_current = $con->prepare($sql_current);
$stmt_current->bind_param("s", $user_id);
$stmt_current->execute();
$result_current = $stmt_current->get_result();

// Fetch user's history
$sql_history = "SELECT * FROM history WHERE user_id = ? ORDER BY booking_date DESC";
$stmt_history = $con->prepare($sql_history);
$stmt_history->bind_param("s", $user_id);
$stmt_history->execute();
$result_history = $stmt_history->get_result();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Bookings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f7f4;
            color: #2f4f2f;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin-top: 100px;
            color: #355e3b;
            font-size: 2.5em;
        }

        .container {
            width: 90%;
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #c8e0d5;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            
        }

        .booking-card {
            padding: 15px;
            margin-bottom: 15px;
            border: 1px solid #c8e0d5;
            border-radius: 5px;
            background-color: #e9f3eb;
            transition: transform 0.2s;
        }

        .booking-card:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.15);
        }

        .booking-card p {
            margin: 5px 0;
            color: #496c59;
        }

        .booking-card p strong {
            color: #355e3b;
        }

        .nav-bar {
            display: flex;
            align-items: center;
            width: 100vw;
            height: 85px;
            background-color: transparent;
            justify-content: space-evenly;
            z-index: 99;
            background-color: white;
            position: fixed;
            top: 0px;


        }

        .nav-bar .logo {

            width: 70%;
            height: 100%;


        }

        .nav-bar .logo img {
            width: 100%;
            margin-top: 10px;
            padding-left: 10px;
        }

        .nav-bar #nav-links {
            display: flex;
            gap: 20px;
            list-style: none;
        }

        .nav-bar #nav-links li a {
            color: #444;
            font-weight: bold;
            text-decoration: none;
            letter-spacing: 2px;

        }


        .nav-bar #login {
            width: 10rem;
            border-radius: 10px;
            outline: none;
            border: 2px solid green;
            background: none;
            padding: 10px;
            font-weight: bold;
            font-size: 1.1rem;
            margin-right: 10px;
            transition: .5s;
        }

        .nav-bar #login:hover {
            background-color: #036e3a;
            color: white;
            border: none;
            cursor: pointer;
        }

        .hero-image {
            width: 100vw;
            height: 100%;
            position: absolute;
            top: 0px;
            z-index: -3;


        }

        .hero-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;


        }

        .hero-image video {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: top;

        }

        .mobile_nav {
            position: fixed;
            height: 100%;
            width: 100%;
            background-color: whitesmoke;
            right: 100%;
            top: 85px;
            transition: .8s;
            display: none;
            z-index: 999;

        }

        #mobile_nav_links {
            display: flex;
            flex-direction: column;
            gap: 30px;
            margin-top: 35px;
            border-bottom: 1px solid #036e3a;
            list-style: none;
            padding-bottom: 50px;

        }

        #mobile_nav_links li a {
            color: black;
            font-weight: bold;
            text-decoration: none;
            padding-left: 35px;
            letter-spacing: 1px;
        }

        .right-bar {
            width: 35px;
            height: 35px;
            background: none;
            border: none;
            display: none;

        }

        .right-bar::before,
        .right-bar .bar,
        .right-bar::after {
            content: '';
            width: 100%;
            background-color: #036e3a;
            display: block;
            height: 3px;
            margin: 6px 0px;
            transition: .5s;
            border-radius: 15%;
        }

        #mobile_nav_links li,
        a {
            transition: .5s;
            padding: 10px;
        }

        #mobile_nav_links li {
            position: relative;
        }

        #mobile_nav_links li a::before {
            content: '';
            width: 0%;
            height: 3px;
            background-color: #036e3a;
            position: absolute;
            bottom: -5px;
            transition: all .5s ease;
            border-radius: 15px;
        }

        #mobile_nav_links li a:hover::before {
            width: 30%;
        }



        #mobile_login {

            width: 10rem;
            border-radius: 10px;
            outline: none;
            border: 2px solid #036e3a;
            background: none;
            padding: 10px;
            font-weight: bold;
            font-size: 1.1rem;
            margin-top: 35px;
            margin-left: 35px;
            transition: all .5s ease;

        }

        .right-bar.is-active .bar {
            opacity: 0;
        }

        .right-bar.is-active::before {
            transform: rotate(-45deg) translate(-8px, 7px);
        }

        .right-bar.is-active::after {
            transform: rotate(45deg) translate(-6px, -6px);
        }


        .mobile_footer {

            background-color: rgba(0, 0, 0, 0.162);
            display: flex;
            height: 15%;
            align-items: center;
            position: absolute;
            width: 100%;
            justify-content: center;
            bottom: 10%;
        }

        .mobile_footer p {
            color: black;
            font-weight: bold;
            text-decoration: none;
            letter-spacing: 1px;
        }

        #mobile_login:hover {
            background-color: #036e3a;
            color: white;

        }



        @media (max-width:992px) {
            .right-side {
                display: none;
            }

            #login {
                display: none;
            }

            .right-bar {
                display: block;
            }

            .mobile_nav {
                display: block;
            }
        }

        .mobile_nav.is-active {
            right: 0%;
        }


        #nav-links li {

            position: relative;

        }

        #nav-links li a::before {
            content: '';
            width: 0%;
            height: 3px;
            background-color: #036e3a;
            position: absolute;
            bottom: -10px;
            transition: all .5s ease;
            border-radius: 15px;
            left: 0.4%;
        }

        #nav-links .activate a::before {
            width: 100%;

        }


        #nav-links li a:hover::before {
            width: 100%;
        }

        ::placeholder {
            color: rgb(188, 188, 188);
        }
        
    </style>
</head>

<body>
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
                <li><a href="my_booking.php"><i class="fa fa-shopping-cart" aria-hidden="true"></i>Bookings</a></li>
                <li><i class="fa fa-user" aria-hidden="true"></i> <em><b><?php echo $user_id ?></b></em> </li>

                



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
            <li><a href="my_booking.php"><i class="fa fa-shopping-cart" aria-hidden="true"></i>Bookings</a></li>
            <li><i class="fa fa-user" aria-hidden="true"></i> <em><b><?php echo $username ?></b></em> </li>
        </ul>
        <a href="logout.php"><button id="mobile_login"><i class="fa fa-fw fa-user"></i>Logout</button></a>
        <div class="mobile_footer">
            <p>Copyright&copy; 2022 AgZone. All Rights Reserved</p>
        </div>
    </div>
    <div class="section">
        <div class="heading">
            <h1>Your Bookings</h1>
        </div>
        <div class="container">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="booking-card">
                        <p><strong>Item:</strong> <?php echo htmlspecialchars($row['item_name']); ?></p>
                        <p><strong>Rental Start Date:</strong> <?php echo htmlspecialchars($row['rental_start_date']); ?></p>
                        <p><strong>Days Rented:</strong> <?php echo htmlspecialchars($row['days_rented']); ?></p>
                        <p><strong>Total Cost:</strong> ₹<?php echo number_format($row['total_cost'], 2); ?></p>
                        <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($row['payment_status']); ?></p>
                        <p><strong>Booking Date:</strong> <?php echo htmlspecialchars($row['booking_date']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>You have no bookings.</p>
            <?php endif; ?>
        </div>
    </div>
    <!-- History Section -->
    <div class="section">
        <h1>Your Booking History</h1>
        <div class="container">
            <?php if ($result_history->num_rows > 0): ?>
                <?php while ($row = $result_history->fetch_assoc()): ?>
                    <div class="booking-card">
                        <p><strong>Item:</strong> <?php echo htmlspecialchars($row['item_name']); ?></p>
                        <p><strong>Rental Start Date:</strong> <?php echo htmlspecialchars($row['rental_start_date']); ?></p>
                        <p><strong>Days Rented:</strong> <?php echo htmlspecialchars($row['days_rented']); ?></p>
                        <p><strong>Total Cost:</strong> ₹<?php echo number_format($row['total_cost'], 2); ?></p>
                        <p><strong>Payment Status:</strong> <?php echo htmlspecialchars($row['payment_status']); ?></p>
                        <p><strong>Booking Date:</strong> <?php echo htmlspecialchars($row['booking_date']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No past bookings found.</p>
            <?php endif; ?>
        </div>
    </div>

    <?php
    $stmt->close();
    $con->close();
    ?>

</body>

</html>