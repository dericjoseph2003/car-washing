<?php
session_start();



// Replace the database configuration and connection code with:
require_once 'conn.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle status updates if provided
if (isset($_POST['booking_id']) && isset($_POST['new_status'])) {
    $booking_id = $_POST['booking_id'];
    $new_status = $_POST['new_status'];
    $update_sql = "UPDATE booking SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_status, $booking_id);
    $stmt->execute();
    $stmt->close();
    // Redirect to prevent form resubmission
    header("Location: booking.php");
    exit();
}

// Initialize date filter condition
$dateFilter = isset($_GET['dateFilter']) ? $_GET['dateFilter'] : '';
$statusFilter = isset($_GET['statusFilter']) ? $_GET['statusFilter'] : '';

// Base query
$query = "SELECT * FROM booking WHERE 1=1";

// Add date conditions based on filter
if ($dateFilter) {
    switch ($dateFilter) {
        case 'today':
            $query .= " AND DATE(booking_date) = CURDATE()";
            break;
        case 'week':
            $query .= " AND YEARWEEK(booking_date, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'month':
            $query .= " AND YEAR(booking_date) = YEAR(CURDATE()) AND MONTH(booking_date) = MONTH(CURDATE())";
            break;
    }
}

// Add status filter if selected
if ($statusFilter) {
    $query .= " AND status = '" . $conn->real_escape_string($statusFilter) . "'";
}

// Add ordering
$query .= " ORDER BY booking_date DESC, booking_time DESC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bookings Management - Car Wash Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Reuse the same CSS variables and base styles from admindash.php */
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

        /* Reuse sidebar styles from admindash.php */
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

        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }

        /* New styles for booking page */
        .bookings-container {
            background: var(--white);
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .bookings-table th,
        .bookings-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .bookings-table th {
            background-color: var(--light-bg);
            font-weight: 600;
            color: var(--text-dark);
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 0 2px;
            font-size: 14px;
        }

        .status-select {
            padding: 5px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
        }

        .filters {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter-select {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            min-width: 150px;
        }

        .search-box {
            padding: 8px;
            border-radius: 4px;
            border: 1px solid var(--border-color);
            width: 250px;
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

        /* Remove underline from all navigation links */
        .nav-menu a,
        .nav-item a,
        a.nav-item {
            text-decoration: none;
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
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
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
            <div class="bookings-container">
                <div class="header">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <button class="action-btn" style="background-color: var(--text-light); color: white;" onclick="history.back()">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                        <h1>Bookings Management</h1>
                    </div>
                    <!-- <button class="action-btn" style="background-color: var(--primary-color); color: white;" onclick="window.location.href='booking.php'">
                        <i class="fas fa-plus"></i> New Booking
                    </button> -->
                </div>

                <div class="filters">
                    <input type="text" class="search-box" placeholder="Search bookings..." id="searchInput">
                    <select class="filter-select" id="statusFilter" onchange="applyFilters()">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="completed" <?php echo $statusFilter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                    </select>
                    <select class="filter-select" id="dateFilter" onchange="applyFilters()">
                        <option value="">All Dates</option>
                        <option value="today" <?php echo $dateFilter === 'today' ? 'selected' : ''; ?>>Today</option>
                        <option value="week" <?php echo $dateFilter === 'week' ? 'selected' : ''; ?>>This Week</option>
                        <option value="month" <?php echo $dateFilter === 'month' ? 'selected' : ''; ?>>This Month</option>
                    </select>
                </div>

                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Service Type</th>
                            <th>Vehicle Info</th>
                            <th>Date & Time</th>
                            <th>Special Requests</th>
                            <th>Status</th>
                            <!-- <th>Actions</th> -->
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $status_class = $row['status'] == 'pending' ? 'status-pending' : 'status-completed';
                                echo "<tr>
                                    <td>#{$row['id']}</td>
                                    <td>{$row['username']}</td>
                                    <td>{$row['service_type']}</td>
                                    <td>{$row['vehicle_type']}<br>{$row['vehicle_number']}</td>
                                    <td>" . date('M d, Y h:i A', strtotime($row['booking_date'] . ' ' . $row['booking_time'])) . "</td>
                                    <td>{$row['special_requests']}</td>
                                    <td>
                                        <select class='status-select' onchange='updateStatus({$row['id']}, this.value)'>
                                            <option value='pending' " . ($row['status'] == 'pending' ? 'selected' : '') . ">Pending</option>
                                            <option value='completed' " . ($row['status'] == 'completed' ? 'selected' : '') . ">Completed</option>
                                        </select>
                                    </td>
                                    
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' style='text-align: center;'>No bookings found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        function editBooking(id) {
            window.location.href = `edit_booking.php?id=${id}`;
        }

        function deleteBooking(id) {
            if(confirm('Are you sure you want to delete this booking?')) {
                window.location.href = `delete_booking.php?id=${id}`;
            }
        }

        // Keep the search functionality client-side
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchText = this.value.toLowerCase();
            const rows = document.querySelectorAll('.bookings-table tbody tr');
            
            rows.forEach(row => {
                const rowText = row.textContent.toLowerCase();
                row.style.display = rowText.includes(searchText) ? '' : 'none';
            });
        });

        // Function to apply filters
        function applyFilters() {
            const dateFilter = document.getElementById('dateFilter').value;
            const statusFilter = document.getElementById('statusFilter').value;
            
            // Construct URL with filters
            let url = new URL(window.location.href);
            url.searchParams.set('dateFilter', dateFilter);
            url.searchParams.set('statusFilter', statusFilter);
            
            // Redirect with new filters
            window.location.href = url.toString();
        }

        function updateStatus(bookingId, newStatus) {
            fetch('bookings.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `booking_id=${bookingId}&new_status=${newStatus}`
            })
            .then(response => {
                if (response.ok) {
                    location.reload();
                } else {
                    throw new Error('Network response was not ok');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Error updating status. Please try again.');
            });
        }

        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function(e) {
                if (!this.classList.contains('active')) {
                    document.querySelector('.nav-item.active')?.classList.remove('active');
                    this.classList.add('active');
                }
            });
        });
    </script>
</body>
</html>