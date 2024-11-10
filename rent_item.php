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

// Initialize variables
$item_name = '';
$price_per_day = 0;
$total_cost = 0;
$days = 0;
$rental_start_date = '';
$delivery_address = '';
$alert_message = '';
$alert_type = '';

// Check if the form was submitted using POST method
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and retrieve POST data
    $item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
    $days = isset($_POST['days']) ? intval($_POST['days']) : 0;
    $rental_start_date = isset($_POST['rental_start_date']) ? $_POST['rental_start_date'] : '';
    $delivery_address = isset($_POST['delivery_address']) ? htmlspecialchars($_POST['delivery_address']) : '';

    // Validate the inputs
    if ($item_id > 0 && $days > 0) {
        // Fetch item details from the database
        $sql = "SELECT item_name, price_per_day FROM rentals WHERE id = ?";
        $stmt = $con->prepare($sql);
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Check if the item exists
        if ($result->num_rows > 0) {
            $item = $result->fetch_assoc();
            $item_name = htmlspecialchars($item['item_name']);
            $price_per_day = $item['price_per_day'];
            $total_cost = $price_per_day * $days;
        } else {
            echo "<p>Item not found.</p>";
        }

        $stmt->close();
    } else {
        echo "<p>Invalid input. Please try again.</p>";
    }

    // Check if payment was submitted
    if (isset($_POST['submit_payment'])) {
        // Process payment (this is where you'd integrate with a payment processor)
        // Simulate successful payment
        $payment_status = 'Paid';
        $payment_method = 'Credit Card';

        // Insert booking into the database
        $insert_sql = "INSERT INTO bookings (user_id, item_id, item_name, price_per_day, days_rented, total_cost, payment_status, payment_method, rental_start_date, delivery_address)
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $con->prepare($insert_sql);
        $stmt->bind_param("sississsss", $username, $item_id, $item_name, $price_per_day, $days, $total_cost, $payment_status, $payment_method, $rental_start_date, $delivery_address);

        if ($stmt->execute()) {
            $alert_message = "Payment of ₹" . number_format($total_cost, 2) . " for " . $item_name . " has been successfully processed!";
            $alert_type = "success";
            echo "<script>setTimeout(function(){ window.location.href = 'my_booking.php'; }, 2000);</script>";
        } else {
            $alert_message = "There was an error processing your booking. Please try again later.";
            $alert_type = "danger";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rental Confirmation</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
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

        /* Form container styling */
        .form-container {
            width: 90%;
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #c8e0d5;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .form-container h2 {
            margin: 0 0 20px;
            color: #355e3b;
        }

        .form-container p {
            margin: 10px 0;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #496c59;
        }

        .form-group input {
            width: 100%;
            padding: 8px;
            border: 1px solid #c8e0d5;
            border-radius: 4px;
        }

        .form-group button {
            padding: 10px;
            background-color: #3b8b4b;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s;
        }

        .form-group button:hover {
            background-color: #355e3b;
        }
    </style>
</head>
<body>

    <div class="form-container">
        <h2>Rental Confirmation</h2>
        <?php if ($alert_message): ?>
            <div class="alert alert-<?php echo $alert_type; ?>" role="alert">
                <?php echo $alert_message; ?>
            </div>
        <?php endif; ?>
        <?php if ($total_cost > 0): ?>
            <p>You have rented: <strong><?php echo $item_name; ?></strong></p>
            <p>Duration: <strong><?php echo $days; ?> day(s)</strong></p>
            <p>Price per day: ₹<strong><?php echo number_format($price_per_day, 2); ?></strong></p>
            <p>Total cost: ₹<strong><?php echo number_format($total_cost, 2); ?></strong></p>

            <h3>Payment Information</h3>
            <form action="rent_item.php" method="POST">
                <div class="form-group">
                    <label for="rental_start_date">Rental Start Date</label>
                    <input type="date" name="rental_start_date" id="rental_start_date" required>
                </div>
                <div class="form-group">
                    <label for="delivery_address">Delivery Address</label>
                    <input type="text" name="delivery_address" id="delivery_address" required>
                </div>
                <div class="form-group">
                    <label for="card_number">Card Number</label>
                    <input type="text" name="card_number" id="card_number" required>
                </div>
                <div class="form-group">
                    <label for="card_name">Cardholder Name</label>
                    <input type="text" name="card_name" id="card_name" required>
                </div>
                <div class="form-group">
                    <label for="expiry_date">Expiry Date (MM/YY)</label>
                    <input type="text" name="expiry_date" id="expiry_date" required>
                </div>
                <div class="form-group">
                    <label for="cvv">CVV</label>
                    <input type="text" name="cvv" id="cvv" required>
                </div>
                <input type="hidden" name="item_id" value="<?php echo $item_id; ?>">
                <input type="hidden" name="days" value="<?php echo $days; ?>">
                <div class="form-group">
                    <button type="submit" name="submit_payment">Submit Payment</button>
                </div>
            </form>
        <?php else: ?>
            <p>No rental details available.</p>
        <?php endif; ?>
    </div>

</body>
</html>

<?php
// Close the database connection
$con->close();
?>
