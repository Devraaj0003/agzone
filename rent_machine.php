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

// Fetch rental items from the database
$sql = "SELECT * FROM rentals";
$result = $con->query($sql);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="rent_machine.css">
    <script defer src="add_post.js"></script>
    <title>Rent Machine</title>
    <style>
        /* Page styling */
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f7f4;
            color: #2f4f2f;
            margin: 0;
            padding: 0;
        }

        h1 {
            text-align: center;
            margin-top: 20px;
            color: #355e3b;
            font-size: 2.5em;
        }

        /* Grid container for rental items */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            padding: 20px;
        }

        /* Individual item styling */
        .item {
            background-color: #ffffff;
            border: 1px solid #c8e0d5;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .item:hover {
            transform: scale(1.03);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .item img {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-bottom: 2px solid #c8e0d5;
        }

        /* Item content styling */
        .item-content {
            padding: 16px;
        }

        .item-content h3 {
            margin: 0;
            color: #355e3b;
            font-size: 1.3em;
        }

        .item-content p {
            margin: 8px 0;
            color: #496c59;
        }

        .item-content .price {
            font-weight: bold;
            color: #1d5a3a;
        }

        /* Rental form styling */
        .rent-form {
            margin-top: 10px;
        }

        .rent-form label {
            color: #496c59;
            font-size: 0.9em;
        }

        .rent-form input[type="number"] {
            width: 50px;
            padding: 4px;
            margin: 0 10px;
            border: 1px solid #c8e0d5;
            border-radius: 4px;
        }

        .rent-form button {
            padding: 8px 12px;
            background-color: #3b8b4b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .rent-form button:hover {
            background-color: #355e3b;
        }
    </style>

</head>

<body>

    <section class="header">
        <div class="nav-bar">
            <div class="left-side">
                <div class="logo">
                    <img src="Images/agzone-logo.png" alt="AgZone-Logo" title="AgZone">
                </div>
            </div>

            <div class="right-side">

                <ul id="nav-links">
                    <li><a href="index.php"><i class="fa fa-fw fa-home"></i>Home</a></li>
                    <!-- <li><a href="fertilizers.php"><i class="fa fa-leaf" aria-hidden="true"></i>Crops</a> -->
                    <li><a href="rent_machine.php"><i class="fa fa-bus" aria-hidden="true"></i>Rent machinery</a></li>
                    <li><a href="my_booking.php"><i class="fa fa-shopping-cart" aria-hidden="true"></i>Bookings</a></li>
                    <li><i class="fa fa-user" aria-hidden="true"></i> <em><b><?php echo $username ?></b></em> </li>


                </ul>



            </div>


            <a href="logout.php"><button id="login"><i class="fa fa-fw fa-user"></i>Logout</button></a>


            <button class="right-bar">
                <span class="bar"></span>
            </button>


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



        </div>

    </section>


    <div class="hero-image">

        <div class="rent-a-machine-text">
            <h1>Want to Rent a Machine?</h1>
        </div>
    </div>


    <!--Products-->
    <h1>Agricultural Rentals</h1>

    <div class="grid-container">
        <?php if ($result->num_rows > 0): ?>
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="item">
                    <!-- Dummy image for each item -->
                    <?php if ($row['item_image']): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($row['item_image']); ?>" alt="Item Image" width="20" height="50">
                <?php else: ?>
                    No Image
                <?php endif; ?>

                    <div class="item-content">
                        <h3><?php echo htmlspecialchars($row['item_name']); ?></h3>
                        <p><?php echo htmlspecialchars($row['description']); ?></p>
                        <p class="price">Price per day: ₹<?php echo number_format($row['price_per_day'], 2); ?></p>
                           
                
                        <!-- Rental form -->
                        <form action="rent_item.php" method="POST" class="rent-form">
                            <input type="hidden" name="item_id" value="<?php echo $row['id']; ?>">
                            <label for="days">Days to rent:</label>
                            <input type="number" name="days" min="1" required>
                            <button type="submit">Rent</button>
                        </form>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No items available for rent at the moment.</p>
        <?php endif; ?>
    </div>





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