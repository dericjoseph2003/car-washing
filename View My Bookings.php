<?php
session_start();
require_once "conn.php";

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Fetch user's bookings
$username = $_SESSION['username'];
$sql = "SELECT * FROM bookings WHERE username = ? ORDER BY booking_date DESC, booking_time DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Car Care</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <style>
        .header {
            background: #1e3c72;
            color: white;
            padding: 0.5rem;
            text-align: center;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 100;
        }

        .header h1 {
            font-size: 1.5rem;
            margin: 0;
        }

        .footer {
            background: #1e3c72;
            color: white;
            padding: 0.5rem;
            text-align: center;
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
        }

        .container {
            margin: 60px auto 60px auto;
            min-height: calc(100vh - 120px);
            padding: 20px;
        }

        .booking-card {
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
        }

        .status-pending {
            color: #ffc107;
        }

        .status-confirmed {
            color: #28a745;
        }

        .status-completed {
            color: #17a2b8;
        }

        .status-cancelled {
            color: #dc3545;
        }

        .btn-custom {
            background: #1e3c72;
            color: white;
        }

        .btn-custom:hover {
            background: #152b52;
            color: white;
        }

        .no-bookings {
            text-align: center;
            padding: 50px;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>My Bookings</h1>
    </div>

    <div class="container">
        <div class="text-right mb-4">
            <a href="booking.php" class="btn btn-custom">
                <i class="fas fa-plus"></i> New Booking
            </a>
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Back to Home
            </a>
        </div>

        <?php if (empty($bookings)): ?>
            <div class="no-bookings">
                <i class="fas fa-calendar-times fa-3x mb-3 text-muted"></i>
                <h3>No Bookings Found</h3>
                <p class="text-muted">You haven't made any bookings yet.</p>
                <a href="booking.php" class="btn btn-custom">Book a Service Now</a>
            </div>
        <?php else: ?>
            <?php foreach ($bookings as $booking): ?>
                <div class="booking-card">
                    <div class="row">
                        <div class="col-md-8">
                            <h4><?php echo htmlspecialchars($booking['service_type']); ?></h4>
                            <p>
                                <strong>Vehicle:</strong> <?php echo htmlspecialchars($booking['vehicle_type']); ?> 
                                (<?php echo htmlspecialchars($booking['vehicle_number']); ?>)
                            </p>
                            <p>
                                <strong>Date & Time:</strong> 
                                <?php 
                                    $date = new DateTime($booking['booking_date']);
                                    $time = new DateTime($booking['booking_time']);
                                    echo $date->format('F j, Y') . ' at ' . $time->format('g:i A');
                                ?>
                            </p>
                            <?php if (!empty($booking['special_requests'])): ?>
                                <p><strong>Special Requests:</strong> <?php echo htmlspecialchars($booking['special_requests']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-4 text-md-right">
                            <p>
                                <strong>Status: </strong>
                                <span class="status-<?php echo strtolower($booking['status']); ?>">
                                    <i class="fas fa-circle"></i>
                                    <?php echo htmlspecialchars($booking['status']); ?>
                                </span>
                            </p>
                            <p><small class="text-muted">Booking ID: <?php echo $booking['id']; ?></small></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="footer">
        <p>&copy; <?php echo date("Y"); ?> Car Care Services. All Rights Reserved.</p>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
</body>
</html> 