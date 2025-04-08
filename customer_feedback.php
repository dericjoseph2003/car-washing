<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_care2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch feedback from database
$sql = "SELECT f.*, u.Username as username, b.service_type, b.vehicle_type 
        FROM feedback f 
        LEFT JOIN booking b ON f.booking_id = b.id
        LEFT JOIN users u ON f.user_id = u.UserID 
        ORDER BY f.id ASC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>CarCare - Customer Feedback</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="assets/css/staffdash.css" rel="stylesheet">
    <style>
        /* Sidebar Styles */
        .sidebar {
            height: 100vh;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background: linear-gradient(180deg, #2c3e50, #3498db);
            padding-top: 20px;
            color: white;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.1);
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px 25px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-header h3 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            color: #fff;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 25px 0;
        }

        .sidebar-menu li {
            margin: 5px 0;
        }

        .sidebar-menu li a {
            display: flex;
            align-items: center;
            padding: 12px 25px;
            color: rgba(255, 255, 255, 0.8);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .sidebar-menu li a:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: #fff;
            transform: translateX(5px);
        }

        .sidebar-menu li.active a {
            background-color: rgba(255, 255, 255, 0.2);
            color: #fff;
            border-left: 4px solid #fff;
        }

        .sidebar-menu li i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
            font-size: 18px;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }
    </style>
</head>
<body>
    <!-- Sidebar Start -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>CarCare Staff</h3>
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="staffdash.php">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="service_history.php">
                    <i class="fas fa-history"></i>
                    <span>Service History</span>
                </a>
            </li>
            <li class="active">
                <a href="customer_feedback.php">
                    <i class="fas fa-comments"></i>
                    <span>Customer Feedback</span>
                </a>
            </li>
            <li>
                <a href="staff_profile.php">
                    <i class="fas fa-user"></i>
                    <span>My Profile</span>
                </a>
            </li>
            <li>
                <a href="enquiries.php">
                    <i class="fas fa-envelope"></i>
                    <span>Enquiries</span>
                </a>
            </li>
            <li>
                <a href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>
    <!-- Sidebar End -->

    <!-- Main Content Start -->
    <div class="main-content">
        <!-- Page Header Start -->
        <div class="page-header">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <h2>Customer Feedback</h2>
                    </div>
                </div>
            </div>
        </div>
        <!-- Page Header End -->

        <!-- Feedback List Start -->
        <div class="container-fluid py-5">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <h4 class="mb-0">Customer Reviews</h4>
                                <a href="staffdash.php" class="btn btn-light">Back to Dashboard</a>
                            </div>
                            <div class="card-body">
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                        <div class="feedback-item border-bottom p-3">
                                            <div class="d-flex justify-content-between">
                                                <div>
                                                    <h5 class="mb-1"><?php echo htmlspecialchars($row['username'] ?? 'Anonymous'); ?></h5>
                                                    <div class="text-muted">
                                                        <small>Service: <?php echo htmlspecialchars($row['service_type']); ?></small> |
                                                        <small>Vehicle: <?php echo htmlspecialchars($row['vehicle_type']); ?></small>
                                                    </div>
                                                </div>
                                                <div class="rating">
                                                    <?php
                                                    $rating = $row['rating'];
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $rating) {
                                                            echo '<i class="fas fa-star text-warning"></i>';
                                                        } else {
                                                            echo '<i class="far fa-star text-warning"></i>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                            <p class="mt-2 mb-1"><?php echo htmlspecialchars($row['feedback_text'] ?? 'No comment provided'); ?></p>
                                            <small class="text-muted">
                                                <?php echo date('F d, Y h:i A', strtotime($row['created_at'])); ?>
                                            </small>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="text-center py-5">
                                        <i class="fas fa-comments fa-3x text-muted mb-3"></i>
                                        <p>No feedback available yet.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Feedback List End -->
    </div>
    <!-- Main Content End -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 