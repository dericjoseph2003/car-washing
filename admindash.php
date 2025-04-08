<?php
// config.php - Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_care2";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Contact messages count
$contact_messages = 0;
$sql = "SELECT COUNT(*) as contact_count FROM contact_messages";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $contact_messages = $row['contact_count'];
}

// Initialize statistics with error handling
// Total bookings
$total_bookings = 0;
$sql = "SELECT COUNT(*) as total_bookings FROM booking";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_bookings = $row['total_bookings'];
}

// Pending bookings
$pending_bookings = 0;
$sql = "SELECT COUNT(*) as pending_bookings FROM booking WHERE status='pending'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $pending_bookings = $row['pending_bookings'];
}

// Completed bookings
$completed_bookings = 0;
$sql = "SELECT COUNT(*) as completed_bookings FROM booking WHERE status='completed'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $completed_bookings = $row['completed_bookings'];
}

// Today's bookings
$today_bookings = 0;
$sql = "SELECT COUNT(*) as today_bookings FROM booking WHERE DATE(booking_date) = CURDATE()";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $today_bookings = $row['today_bookings'];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Wash Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --accent-color: #3949ab;
            --light-bg: #f5f6fa;
            --white: #ffffff;
            --text-dark: #2d3436;
            --text-light: #636e72;
            --border-color: #e0e0e0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-bg);
            min-height: 100vh;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background-color: var(--primary-color);
            color: var(--white);
            padding: 20px;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            padding: 20px 0;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-menu {
            margin-top: 30px;
        }

        .nav-section {
            margin-bottom: 20px;
        }

        .nav-section-title {
            font-size: 12px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 10px;
            padding-left: 12px;
        }

        .nav-item {
            padding: 12px 15px;
            margin: 8px 0;
            cursor: pointer;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .nav-item:hover {
            background-color: var(--secondary-color);
        }

        .nav-item.active {
            background-color: var(--accent-color);
        }

        .nav-item i {
            width: 20px;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .page-title {
            font-size: 24px;
            color: var(--text-dark);
        }

        .date {
            color: var(--text-light);
            font-size: 14px;
        }

        /* Stats Grid */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            color: var(--text-light);
            font-size: 16px;
            margin-bottom: 10px;
        }

        .stat-card .value {
            font-size: 28px;
            font-weight: bold;
            color: var(--text-dark);
        }

        /* Bookings Table */
        .bookings-section {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .table-title {
            font-size: 18px;
            color: var(--text-dark);
        }

        .add-button {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s ease;
        }

        .add-button:hover {
            background-color: var(--secondary-color);
        }

        .bookings-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bookings-table th {
            background-color: var(--light-bg);
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 14px;
        }

        .bookings-table td {
            padding: 12px;
            border-bottom: 1px solid var(--border-color);
            color: var(--text-light);
            font-size: 14px;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
            text-transform: capitalize;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 4px;
            transition: opacity 0.3s ease;
        }

        .action-btn:hover {
            opacity: 0.8;
        }

        .edit-btn {
            background-color: #4CAF50;
            color: white;
        }

        .delete-btn {
            background-color: #f44336;
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
            }
        }

        @media (max-width: 768px) {
            .stats-container {
                grid-template-columns: repeat(2, 1fr);
            }
            .sidebar {
                width: 70px;
            }
            .main-content {
                margin-left: 70px;
            }
            .logo span, .nav-item span, .nav-section-title {
                display: none;
            }
            .nav-item {
                justify-content: center;
                padding: 12px;
            }
            .nav-item i {
                margin: 0;
            }
        }

        @media (max-width: 480px) {
            .stats-container {
                grid-template-columns: 1fr;
            }
            .main-content {
                padding: 15px;
            }
            .bookings-table {
                display: block;
                overflow-x: auto;
            }
        }

        /* Remove underline from all navigation links */
        .nav-menu a,
        .nav-item a,
        a.nav-item {
            text-decoration: none;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-car-wash"></i>
                <span>CarWash Admin</span>
            </div>
            <div class="nav-menu">
                <div class="nav-section">
                    <div class="nav-section-title">Main Menu</div>
                        <a href="index.php" class="nav-item">
                            <i class="fas fa-home"></i>
                            <span>Home</span>
                        </a>
                    <div class="nav-item active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </div>
                    <a href="report.php" class="nav-item">
                        <i class="fas fa-report"></i>
                        <span>Report</span>
                    </a>
                    <a href="bookings.php" class="nav-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Bookings</span>
                    </a>
                    <a href="feedback.php" class="nav-item">
                        <i class="fas fa-comment"></i>
                        <span>Feedback</span>
                    </a>

                    <a href="payment_details.php" class="nav-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Payment Details</span>
                    </a>
                    <a href="service_prices.php" class="nav-item">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Service Prices</span>
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Management</div>
                    <!-- <a href="customers.php"><div class="nav-item">
                        <i class="fas fa-users"></i>
                        <span>Customers</span>
                    </div></a> -->
                    <a href="staff.php" class="nav-item">
                        <i class="fas fa-user-tie"></i>
                        <span>Staff Management</span>
                    </a>
                    <!-- <a href="enquiries.php" class="nav-item">
                        <i class="fas fa-message"></i>
                        <span>Enquiries</span>
                    </a> -->
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                    <a href="adminprofile.php" class="nav-item">
                        <i class="fas fa-user-circle"></i>
                        <span>Profile</span>
                    </a>
                </div>
            </div>
        </div>  

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1 class="page-title">Dashboard Overview</h1>
                <div class="date"><?php echo date('F d, Y'); ?></div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Bookings</h3>
                    <div class="value"><?php echo $total_bookings; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Pending Bookings</h3>
                    <div class="value"><?php echo $pending_bookings; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Completed Bookings</h3>
                    <div class="value"><?php echo $completed_bookings; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Enquiries</h3>
                    <div class="value"><?php echo $contact_messages; ?></div>
                </div>
            </div>

            <!-- Bookings Table -->
            <div class="bookings-section">
                <div class="table-header">
                    <h2 class="table-title">Recent Bookings</h2>
                    <!-- <button class="add-button"  >
                        <i class="fas fa-plus"></i>
                        <span>Add New Booking</span>
                    </button> -->
                </div>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Service Type</th>
                            <th>Vehicle Type</th>
                            <th>Date & Time</th>
                            <th>Vehicle Number</th>
                            <th>Special Requests</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT * FROM booking ORDER BY id ASC LIMIT 10";
                        $result = $conn->query($query);

                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>
                                    <td>#{$row['id']}</td>
                                    <td>{$row['username']}</td>
                                    <td>{$row['service_type']}</td>
                                    <td>{$row['vehicle_type']}</td>
                                    <td>" . date('M d, Y h:i A', strtotime($row['booking_date'] . ' ' . $row['booking_time'])) . "</td>
                                    <td>{$row['vehicle_number']}</td>
                                    <td>{$row['special_requests']}</td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' style='text-align: center;'>No bookings found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    // Add active class to nav items
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from current active item
            document.querySelector('.nav-item.active').classList.remove('active');
            // Add active class to clicked item
            this.classList.add('active');
        });
    });

    // Delete booking confirmation
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if(confirm('Are you sure you want to delete this booking?')) {
                const bookingId = this.closest('tr').querySelector('td:first-child').textContent.replace('#', '');
                window.location.href = `delete_booking.php?id=${bookingId}`;
            }
        });
    });

    // Edit booking redirect
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const bookingId = this.closest('tr').querySelector('td:first-child').textContent.replace('#', '');
            window.location.href = `edit_booking.php?id=${bookingId}`;
        });
    });

    // Add new booking redirect
    document.querySelector('.add-button').addEventListener('click', function() {
        window.location.href = 'booking.php';
    });
</script>
</body>
</html>