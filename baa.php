<?php
$password = 'admin@123';
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Display the hashed password
echo "Hashed Password: " . $hashed_password;
?>
<?php
include_once('dbconnection.php');
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('location:admin_login.php');
    exit();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch all data for management
$users = $con->query("SELECT * FROM users");
$rentals = $con->query("SELECT * FROM rentals ORDER BY id");
$bookings = $con->query("SELECT * FROM bookings ORDER BY booking_date DESC");

$alert_message = '';
$alert_type = "success";

// Handle the edit action
$rental_to_edit = null;
if (isset($_GET['edit_id'])) {
    $rental_id = $_GET['edit_id'];
    $stmt = $con->prepare("SELECT * FROM rentals WHERE id = ?");
    $stmt->bind_param("i", $rental_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rental_to_edit = $result->fetch_assoc();
    $stmt->close();
}


// Handle the add/edit rental form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_name = $_POST['item_name'];
    $price_per_day = $_POST['price_per_day'];
    $description = $_POST['description'];

    // Check if a new image has been uploaded
    if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] == UPLOAD_ERR_OK) {
        $imageData = file_get_contents($_FILES['item_image']['tmp_name']);
    } else {
        // No image uploaded, retain the old image
        $imageData = null;
    }

    if (isset($_POST['rental_id'])) {
        // Edit existing rental
        $rental_id = $_POST['rental_id'];

        if ($imageData !== null) {
            // Update rental with new image
            $stmt = $con->prepare("UPDATE rentals SET item_name = ?, price_per_day = ?, description = ?, item_image = ? WHERE id = ?");
            $stmt->bind_param("sdssi", $item_name, $price_per_day, $description, $imageData, $rental_id);
        } else {
            // Update rental without changing the image
            $stmt = $con->prepare("UPDATE rentals SET item_name = ?, price_per_day = ?, description = ? WHERE id = ?");
            $stmt->bind_param("sdsi", $item_name, $price_per_day, $description, $rental_id);
        }
    } else {
        // Insert new rental
        if ($imageData !== null) {
            $stmt = $con->prepare("INSERT INTO rentals (item_name, price_per_day, description, item_image) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdss", $item_name, $price_per_day, $description, $imageData);
        } else {
            $stmt = $con->prepare("INSERT INTO rentals (item_name, price_per_day, description) VALUES (?, ?, ?)");
            $stmt->bind_param("sdss", $item_name, $price_per_day, $description);
        }
    }

    if ($stmt->execute()) {
        $alert_message = "Rental " . (isset($_POST['rental_id']) ? "updated" : "added") . " successfully!";
        $alert_type = "success";
        echo "<script>setTimeout(function(){ window.location.href = 'admin_dashboard.php'; }, 2000);</script>";
    } else {
        $alert_message = "Failed to store data: " . $stmt->error;
        $alert_type = "danger";
        echo "<script>setTimeout(function(){ window.location.href = 'admin_dashboard.php'; }, 2000);</script>";
    }

    $stmt->close();
}




// Handle delete request
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $id = $_GET['id'];

    $stmt = null;
    $msg = "";

    if ($action == 'delete_user') {
        $stmt = $con->prepare("DELETE FROM users WHERE UserName = ?");
        $stmt->bind_param("s", $id);
        $msg = "User";
    } elseif ($action == 'delete_rental') {
        $stmt = $con->prepare("DELETE FROM rentals WHERE id = ?");
        $stmt->bind_param("i", $id);
        $msg = "Rental";
    } elseif ($action == 'delete_booking') {
        $stmt = $con->prepare("DELETE FROM bookings WHERE booking_id = ?");
        $stmt->bind_param("i", $id);
        $msg = "Booking";
    }

    if ($stmt && $stmt->execute()) {
        $alert_message = "$msg deleted successfully!";
    } else {
        $alert_message = "$msg deletion failed!";
        $alert_type = "danger";
    }
    $stmt->close();
    echo "<script>setTimeout(function(){ window.location.href = 'admin_dashboard.php'; }, 2000);</script>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            display: flex;
            min-height: 100vh;
            flex-direction: row;
        }

        .sidebar {
            width: 250px;
            background-color: #f8f9fa;
            padding-top: 20px;
        }

        .sidebar a {
            padding: 10px 15px;
            font-size: 18px;
            text-decoration: none;
            color: #333;
            display: block;
        }

        .sidebar a:hover {
            background-color: #ddd;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h4 class="text-center">Admin Menu</h4>
        <a href="#" id="usersLink">Users</a>
        <a href="#" id="rentalsLink">Rentals</a>
        <a href="#" id="bookingsLink">Bookings</a>
    </div>

    <div class="content">
        <h1 class="text-center mb-4">Admin Dashboard</h1>
        <?php if ($alert_message): ?>
            <div class="alert alert-<?php echo $alert_type; ?>" role="alert">
                <?php echo $alert_message; ?>
            </div>
        <?php endif; ?>

        <div id="usersSection" class="section" style="display: none;">
            <h2>Users</h2>
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>UserName</th>
                        <th>Mobile No</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['UserName']); ?></td>
                            <td><?php echo htmlspecialchars($row['MobileNo']); ?></td>
                            <td><?php echo htmlspecialchars($row['Email']); ?></td>
                            <td>
                                <a href="admin_dashboard.php?action=delete_user&id=<?php echo $row['UserName']; ?>"
                                    class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div id="rentalsSection" class="section" style="display: none;">
            <a href="#" id="addRental" class="btn btn-success">Add Rental</a>
            <div id="addNewRental" style="display: none;">
                <h3>Add New Rental</h3>
                <form action="admin_dashboard.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="item_name">Item Name</label>
                        <input type="text" class="form-control" id="item_name" name="item_name" required>
                    </div>
                    <div class="form-group">
                        <label for="price_per_day">Price per Day (₹)</label>
                        <input type="number" class="form-control" id="price_per_day" name="price_per_day" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="item_image">Product Image</label>
                        <input type="file" class="form-control" id="item_image" name="item_image" required>
                    </div>
                    <button type="submit" class="btn btn-success">Add Rental</button>
                </form>

            </div>

            <h2>Rentals</h2>
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Item Name</th>
                        <th>Price per Day</th>
                        <th>Description</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($rental = $rentals->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rental['id']); ?></td>
                            <td><?php echo htmlspecialchars($rental['item_name']); ?></td>
                            <td>₹<?php echo number_format($rental['price_per_day'], 2); ?></td>
                            <td><?php echo htmlspecialchars($rental['description']); ?></td>
                            <td>
                <?php if ($rental['item_image']): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($rental['item_image']); ?>" alt="Item Image" width="50" height="50">
                <?php else: ?>
                    No Image
                <?php endif; ?>
            </td>

                            <td>
                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editRentalModal"
                                    data-id="<?php echo $rental['id']; ?>"
                                    data-item_name="<?php echo $rental['item_name']; ?>"
                                    data-price_per_day="<?php echo $rental['price_per_day']; ?>"
                                    data-description="<?php echo $rental['description']; ?>">Edit</button>
                                <a href="admin_dashboard.php?action=delete_rental&id=<?php echo $rental['id']; ?>"
                                    class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
          

        </div>

        <!-- Bookings Management Section -->
        <div id="bookingsSection" class="section" style="display: none;">
            <h2>Bookings</h2>
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Booking ID</th>
                        <th>User ID</th>
                        <th>Item ID</th>
                        <th>Item Name</th>
                        <th>Price per Day</th>
                        <th>Days Rented</th>
                        <th>Total Cost</th>
                        <th>Payment Status</th>
                        <th>Payment Method</th>
                        <th>Booking Date</th>
                        <th>Rental Start Date</th>
                        <th>Delivery Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $bookings->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['item_id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['item_name']); ?></td>
                            <td>₹<?php echo number_format($booking['price_per_day'], 2); ?></td>
                            <td><?php echo htmlspecialchars($booking['days_rented']); ?></td>
                            <td>₹<?php echo number_format($booking['total_cost'], 2); ?></td>
                            <td><?php echo htmlspecialchars($booking['payment_status']); ?></td>
                            <td><?php echo htmlspecialchars($booking['payment_method']); ?></td>
                            <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['rental_start_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['delivery_address']); ?></td>
                            <td>
                                <a href="admin_dashboard.php?action=delete_booking&id=<?php echo $booking['booking_id']; ?>"
                                    class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            // Toggle sections
            $('#usersLink').click(function () {
                $('.section').hide();
                $('#usersSection').show();
            });
            $('#rentalsLink').click(function () {
                $('.section').hide();
                $('#rentalsSection').show();
            });
            $('#bookingsLink').click(function () {
                $('.section').hide();
                $('#bookingsSection').show();
            });

            // Toggle Add Rental form
            $('#addRental').click(function () {
                $('#addNewRental').toggle();
            });

            // Populate edit modal with rental data
            $('#editRentalModal').on('show.bs.modal', function (e) {
                const button = $(e.relatedTarget);
                const id = button.data('id');
                const itemName = button.data('item_name');
                const pricePerDay = button.data('price_per_day');
                const description = button.data('description');

                $('#editRentalId').val(id);
                $('#editItemName').val(itemName);
                $('#editPricePerDay').val(pricePerDay);
                $('#editDescription').val(description);
            });
        });
    </script>

<!-- Edit Rental Modal -->
<div class="modal fade" id="editRentalModal" tabindex="-1" aria-labelledby="editRentalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="admin_dashboard.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRentalModalLabel">Edit Rental</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="rental_id" id="editRentalId">
                    <div class="form-group">
                        <label for="editItemName">Item Name</label>
                        <input type="text" class="form-control" id="editItemName" name="item_name" required>
                    </div>
                    <div class="form-group">
                        <label for="editPricePerDay">Price per Day (₹)</label>
                        <input type="number" class="form-control" id="editPricePerDay" name="price_per_day" required>
                    </div>
                    <div class="form-group">
                        <label for="editDescription">Description</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3" required></textarea>
                    </div>
                    <!-- Optional Image upload (only if the image needs to be changed) -->
                    <div class="form-group">
                        <label for="editItemImage">Change Image (optional)</label>
                        <input type="file" class="form-control-file" id="editItemImage" name="item_image">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>


</body>

</html>















<?php
include_once('dbconnection.php');
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header('location:admin_login.php');
    exit();
}

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Fetch all data for management
$users = $con->query("SELECT * FROM users");
$rentals = $con->query("SELECT * FROM rentals ORDER BY id");
$bookings = $con->query("SELECT * FROM bookings ORDER BY booking_date DESC");

// Additional queries for stats
$totalRentals = $con->query("SELECT COUNT(*) as total FROM rentals")->fetch_assoc()['total'];
$totalBookings = $con->query("SELECT COUNT(*) as total FROM bookings")->fetch_assoc()['total'];
$totalRevenue = $con->query("SELECT SUM(total_cost) as revenue FROM bookings")->fetch_assoc()['revenue'];
$totalUsers = $con->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];

$alert_message = '';
$alert_type = "success";

// Handle the edit action
$rental_to_edit = null;
if (isset($_GET['edit_id'])) {
    $rental_id = $_GET['edit_id'];
    $stmt = $con->prepare("SELECT * FROM rentals WHERE id = ?");
    $stmt->bind_param("i", $rental_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $rental_to_edit = $result->fetch_assoc();
    $stmt->close();
}

// Handle form submissions and edits (same as your previous code)
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .sidebar {
            width: 250px;
            background-color: #4caf50;
            padding-top: 20px;
        }

        .sidebar a {
            padding: 10px 15px;
            font-size: 18px;
            text-decoration: none;
            color: white;
            display: block;
        }

        .sidebar a:hover {
            background-color: #3e8e41;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
        }

        .card {
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #4caf50;
            color: white;
        }

        .card-footer {
            background-color: #f1f1f1;
        }

        .stats-card {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .stats-card h3 {
            font-weight: bold;
        }
    </style>
</head>

<body>

    <div class="sidebar">
        <h4 class="text-center text-white">Admin Dashboard</h4>
        <a href="#" id="dashboardLink">Dashboard</a>
        <a href="#" id="usersLink">Users</a>
        <a href="#" id="rentalsLink">Rentals</a>
        <a href="#" id="bookingsLink">Bookings</a>
    </div>
    
    <?php if ($alert_message): ?>
        <div class="alert alert-<?php echo $alert_type; ?>" role="alert">
            <?php echo $alert_message; ?>
        </div>
    <?php endif; ?>
    
    <div class="content">
        <h1 class="text-center mb-4">Dashboard</h1>

        <!-- Stats Cards -->
        <div id="dashboardSection" class="section active">
        <div class="row">
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-header">Total Rentals</div>
                    <div class="card-body">
                        <h3><?php echo $totalRentals; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-header">Total Bookings</div>
                    <div class="card-body">
                        <h3><?php echo $totalBookings; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-header">Total Revenue</div>
                    <div class="card-body">
                        <h3>₹<?php echo number_format($totalRevenue, 2); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card stats-card">
                    <div class="card-header">Total Users</div>
                    <div class="card-body">
                        <h3><?php echo $totalUsers; ?></h3>
                    </div>
                </div>
            </div>
        </div>
        <!-- Chart.js - Rental Statistics -->
        <div class="card">
            <div class="card-header">Rental Statistics</div>
            <div class="card-body">
                <canvas id="rentalStatsChart"></canvas>
            </div>
        </div>
        </div>

        

        <div id="usersSection" class="section" style="display: none;">
            <h2>Users</h2>
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>UserName</th>
                        <th>Mobile No</th>
                        <th>Email</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $users->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['UserName']); ?></td>
                            <td><?php echo htmlspecialchars($row['MobileNo']); ?></td>
                            <td><?php echo htmlspecialchars($row['Email']); ?></td>
                            <td>
                                <a href="admin_dashboard.php?action=delete_user&id=<?php echo $row['UserName']; ?>"
                                    class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <div id="rentalsSection" class="section" style="display: none;">
            <a href="#" id="addRental" class="btn btn-success">Add Rental</a>
            <div id="addNewRental" style="display: none;">
                <h3>Add New Rental</h3>
                <!-- Add Rental Form -->
                <form action="admin_dashboard.php" method="POST" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="item_name">Item Name</label>
                        <input type="text" class="form-control" id="item_name" name="item_name" required>
                    </div>
                    <div class="form-group">
                        <label for="price_per_day">Price per Day (₹)</label>
                        <input type="number" class="form-control" id="price_per_day" name="price_per_day" required>
                    </div>
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="item_image">Product Image</label>
                        <input type="file" class="form-control" id="item_image" name="item_image" required>
                    </div>
                    <button type="submit" class="btn btn-success">Add Rental</button>
                </form>
            </div>

            <h2>Rentals</h2>
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Item Name</th>
                        <th>Price per Day</th>
                        <th>Description</th>
                        <th>Image</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($rental = $rentals->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($rental['id']); ?></td>
                            <td><?php echo htmlspecialchars($rental['item_name']); ?></td>
                            <td>₹<?php echo number_format($rental['price_per_day'], 2); ?></td>
                            <td><?php echo htmlspecialchars($rental['description']); ?></td>
                            <td>
                <?php if ($rental['item_image']): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($rental['item_image']); ?>" alt="Item Image" width="50" height="50">
                <?php else: ?>
                    No Image
                <?php endif; ?>
            </td>

                            <td>
                                <button class="btn btn-warning btn-sm" data-toggle="modal" data-target="#editRentalModal"
                                    data-id="<?php echo $rental['id']; ?>"
                                    data-item_name="<?php echo $rental['item_name']; ?>"
                                    data-price_per_day="<?php echo $rental['price_per_day']; ?>"
                                    data-description="<?php echo $rental['description']; ?>">Edit</button>
                                <a href="admin_dashboard.php?action=delete_rental&id=<?php echo $rental['id']; ?>"
                                    class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Bookings Management Section -->
        <div id="bookingsSection" class="section" style="display: none;">
            <h2>Bookings</h2>
            <table class="table table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>Booking ID</th>
                        <th>User ID</th>
                        <th>Item ID</th>
                        <th>Item Name</th>
                        <th>Price per Day</th>
                        <th>Days Rented</th>
                        <th>Total Cost</th>
                        <th>Payment Status</th>
                        <th>Payment Method</th>
                        <th>Booking Date</th>
                        <th>Rental Start Date</th>
                        <th>Delivery Address</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($booking = $bookings->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($booking['booking_id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['user_id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['item_id']); ?></td>
                            <td><?php echo htmlspecialchars($booking['item_name']); ?></td>
                            <td>₹<?php echo number_format($booking['price_per_day'], 2); ?></td>
                            <td><?php echo htmlspecialchars($booking['days_rented']); ?></td>
                            <td>₹<?php echo number_format($booking['total_cost'], 2); ?></td>
                            <td><?php echo htmlspecialchars($booking['payment_status']); ?></td>
                            <td><?php echo htmlspecialchars($booking['payment_method']); ?></td>
                            <td><?php echo htmlspecialchars($booking['booking_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['rental_start_date']); ?></td>
                            <td><?php echo htmlspecialchars($booking['delivery_address']); ?></td>
                            <td>
                                <a href="admin_dashboard.php?action=delete_booking&id=<?php echo $booking['booking_id']; ?>"
                                    class="btn btn-danger btn-sm">Delete</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        $(document).ready(function () {
            // Toggle sections
            $('#dashboardLink').click(function () {
                $('.section').hide();
                $('#dashboardSection').show();
            });
            $('#usersLink').click(function () {
                $('.section').hide();
                $('#usersSection').show();
            });
            $('#rentalsLink').click(function () {
                $('.section').hide();
                $('#rentalsSection').show();
            });
            $('#bookingsLink').click(function () {
                $('.section').hide();
                $('#bookingsSection').show();
            });

            // Chart.js rental statistics
            var ctx = document.getElementById('rentalStatsChart').getContext('2d');
            var rentalStatsChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Total Rentals', 'Total Bookings', 'Revenue'],
                    datasets: [{
                        label: 'Statistics',
                        data: [<?php echo $totalRentals; ?>, <?php echo $totalBookings; ?>, <?php echo $totalRevenue; ?>],
                        backgroundColor: ['#66bb6a', '#42a5f5', '#ff7043'],
                        borderColor: ['#388e3c', '#1e88e5', '#d32f2f'],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            // Toggle Add Rental form
            $('#addRental').click(function () {
                $('#addNewRental').toggle();
            });

            // Populate edit modal with rental data
            $('#editRentalModal').on('show.bs.modal', function (e) {
                const button = $(e.relatedTarget);
                const id = button.data('id');
                const itemName = button.data('item_name');
                const pricePerDay = button.data('price_per_day');
                const description = button.data('description');

                $('#editRentalId').val(id);
                $('#editItemName').val(itemName);
                $('#editPricePerDay').val(pricePerDay);
                $('#editDescription').val(description);
            });
        });
    </script>
    <!-- Edit Rental Modal -->
<div class="modal fade" id="editRentalModal" tabindex="-1" aria-labelledby="editRentalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="admin_dashboard.php" method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRentalModalLabel">Edit Rental</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="rental_id" id="editRentalId">
                    <div class="form-group">
                        <label for="editItemName">Item Name</label>
                        <input type="text" class="form-control" id="editItemName" name="item_name" required>
                    </div>
                    <div class="form-group">
                        <label for="editPricePerDay">Price per Day (₹)</label>
                        <input type="number" class="form-control" id="editPricePerDay" name="price_per_day" required>
                    </div>
                    <div class="form-group">
                        <label for="editDescription">Description</label>
                        <textarea class="form-control" id="editDescription" name="description" rows="3" required></textarea>
                    </div>
                    <!-- Optional Image upload (only if the image needs to be changed) -->
                    <div class="form-group">
                        <label for="editItemImage">Change Image (optional)</label>
                        <input type="file" class="form-control-file" id="editItemImage" name="item_image">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>

</html>
