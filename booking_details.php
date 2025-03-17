<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_care2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch booking details
$sql = "SELECT * FROM booking WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    header("Location: service_history.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>CarCare - Booking Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="assets/css/staffdash.css" rel="stylesheet">
</head>
<body>
    <!-- Page Header Start -->
    <div class="page-header">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <h2>Booking Details</h2>
                </div>
            </div>
        </div>
    </div>
    <!-- Page Header End -->

    <!-- Booking Details Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Booking #<?php echo $booking['id']; ?></h4>
                            <a href="service_history.php" class="btn btn-light">
                                <i class="fa fa-arrow-left"></i> Back to Service History
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Customer Information</h5>
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['username']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['user_email'] ?? $booking['email'] ?? 'N/A'); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['user_phone'] ?? $booking['phone'] ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-3">Vehicle Information</h5>
                                    <p><strong>Vehicle Type:</strong> <?php echo htmlspecialchars($booking['vehicle_type']); ?></p>
                                    <p><strong>Vehicle Number:</strong> <?php echo htmlspecialchars($booking['vehicle_number']); ?></p>
                                    <p><strong>Service Type:</strong> <?php echo htmlspecialchars($booking['service_type']); ?></p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Booking Information</h5>
                                    <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></p>
                                    <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($booking['booking_time'])); ?></p>
                                    <p><strong>Status:</strong> 
                                        <span class="badge <?php 
                                            echo match($booking['status']) {
                                                'pending' => 'bg-warning',
                                                'in_progress' => 'bg-primary',
                                                'completed' => 'bg-success',
                                                default => 'bg-secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-3">Additional Notes</h5>
                                    <p><?php echo htmlspecialchars($booking['special_request'] ?? 'No additional notes'); ?></p>
                                </div>
                            </div>
                            <?php if ($booking['status'] !== 'completed'): ?>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3">Update Status</h5>
                                    <form action="update_status.php" method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <select name="status" class="form-select w-auto">
                                            <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="in_progress" <?php echo $booking['status'] == 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                                            <option value="completed" <?php echo $booking['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary">Update Status</button>
                                    </form>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Booking Details End -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 