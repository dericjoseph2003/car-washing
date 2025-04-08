<?php
// Database configuration
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

// Get payment statistics with proper NULL handling and formatting
$total_payments = 0;
$total_amount = 0;
$sql = "SELECT 
    COUNT(*) as total_payments,
    SUM(CASE 
        WHEN payment_amount IS NOT NULL AND payment_amount > 0 THEN payment_amount 
        ELSE CASE service_type
            WHEN 'basic_wash' THEN (CASE vehicle_type 
                WHEN 'Sedan' THEN 400 
                WHEN 'SUV' THEN 500 
                WHEN 'Hatchback' THEN 350 END)
            WHEN 'basic_interior' THEN (CASE vehicle_type 
                WHEN 'Sedan' THEN 600 
                WHEN 'SUV' THEN 700 
                WHEN 'Hatchback' THEN 500 END)
            WHEN 'exterior_washing' THEN (CASE vehicle_type 
                WHEN 'Sedan' THEN 800 
                WHEN 'SUV' THEN 1000 
                WHEN 'Hatchback' THEN 700 END)
            WHEN 'interior_washing' THEN (CASE vehicle_type 
                WHEN 'Sedan' THEN 1000 
                WHEN 'SUV' THEN 1200 
                WHEN 'Hatchback' THEN 900 END)
        END END) as total_amount 
    FROM booking 
    WHERE payment_status = 'completed'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_payments = $row['total_payments'];
    $total_amount = $row['total_amount'];
}

// Get today's payments with proper NULL handling
$today_payments = 0;
$today_amount = 0;
$sql = "SELECT 
    COUNT(*) as today_payments,
    SUM(CASE 
        WHEN payment_amount IS NOT NULL AND payment_amount > 0 THEN payment_amount 
        ELSE CASE service_type
            WHEN 'basic_wash' THEN (CASE vehicle_type 
                WHEN 'Sedan' THEN 400 
                WHEN 'SUV' THEN 500 
                WHEN 'Hatchback' THEN 350 END)
            WHEN 'basic_interior' THEN (CASE vehicle_type 
                WHEN 'Sedan' THEN 600 
                WHEN 'SUV' THEN 700 
                WHEN 'Hatchback' THEN 500 END)
            WHEN 'exterior_washing' THEN (CASE vehicle_type 
                WHEN 'Sedan' THEN 800 
                WHEN 'SUV' THEN 1000 
                WHEN 'Hatchback' THEN 700 END)
            WHEN 'interior_washing' THEN (CASE vehicle_type 
                WHEN 'Sedan' THEN 1000 
                WHEN 'SUV' THEN 1200 
                WHEN 'Hatchback' THEN 900 END)
        END END) as today_amount 
    FROM booking 
    WHERE payment_status = 'completed' 
    AND DATE(payment_date) = CURDATE()";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $today_payments = $row['today_payments'];
    $today_amount = $row['today_amount'];
}

// Function to get service price
function getServicePrice($service_type, $vehicle_type) {
    // Basic pricing structure
    $prices = [
        'basic_wash' => [
            'Sedan' => 400,
            'SUV' => 500,
            'Hatchback' => 350
        ],
        'basic_interior' => [
            'Sedan' => 600,
            'SUV' => 700,
            'Hatchback' => 500
        ],
        'exterior_washing' => [
            'Sedan' => 800,
            'SUV' => 1000,
            'Hatchback' => 700
        ],
        'interior_washing' => [
            'Sedan' => 1000,
            'SUV' => 1200,
            'Hatchback' => 900
        ]
    ];

    return $prices[$service_type][$vehicle_type] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Details - Car Wash Admin</title>
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

        /* Table Styles */
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

        /* Payment Status Styles */
        .payment-status {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-success {
            background-color: #d4edda;
            color: #155724;
        }

        .status-pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status-failed {
            background-color: #f8d7da;
            color: #721c24;
        }

        .amount {
            font-weight: bold;
            color: #2d3436;
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
    </style>
</head>
<body>
    <div class="container">
        <!-- Copy the sidebar from admindash.php -->
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


        <div class="main-content">
            <div class="header">
                <h1 class="page-title">Payment Details</h1>
                <div class="date"><?php echo date('F d, Y'); ?></div>
            </div>

            <!-- Payment Stats -->
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Payments</h3>
                    <div class="value"><?php echo $total_payments; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Total Amount</h3>
                    <div class="value">₹<?php echo number_format($total_amount, 2); ?></div>
                </div>
                <div class="stat-card">
                    <h3>Today's Payments</h3>
                    <div class="value"><?php echo $today_payments; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Today's Amount</h3>
                    <div class="value">₹<?php echo number_format($today_amount, 2); ?></div>
                </div>
            </div>

            <!-- Payments Table -->
            <div class="bookings-section">
                <div class="table-header">
                    <h2 class="table-title">All Payments</h2>
                </div>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Customer</th>
                            <th>Service Type</th>
                            <th>Vehicle Type</th>
                            <th>Date & Time</th>
                            <th>Amount</th>
                            <th>Payment Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT b.*, 
                        CASE 
                            WHEN b.payment_amount IS NOT NULL AND b.payment_amount > 0 THEN b.payment_amount 
                            ELSE CASE b.service_type
                                WHEN 'basic_wash' THEN (CASE b.vehicle_type 
                                    WHEN 'Sedan' THEN 400 
                                    WHEN 'SUV' THEN 500 
                                    WHEN 'Hatchback' THEN 350 END)
                                WHEN 'basic_interior' THEN (CASE b.vehicle_type 
                                    WHEN 'Sedan' THEN 600 
                                    WHEN 'SUV' THEN 700 
                                    WHEN 'Hatchback' THEN 500 END)
                                WHEN 'exterior_washing' THEN (CASE b.vehicle_type 
                                    WHEN 'Sedan' THEN 800 
                                    WHEN 'SUV' THEN 1000 
                                    WHEN 'Hatchback' THEN 700 END)
                                WHEN 'interior_washing' THEN (CASE b.vehicle_type 
                                    WHEN 'Sedan' THEN 1000 
                                    WHEN 'SUV' THEN 1200 
                                    WHEN 'Hatchback' THEN 900 END)
                            END END as calculated_amount,
                        COALESCE(b.payment_status, 'pending') as payment_status
                        FROM booking b 
                        WHERE b.payment_status IS NOT NULL 
                        ORDER BY b.payment_date DESC, b.created_at DESC";
                        $result = $conn->query($query);

                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $status_class = match(strtolower($row['payment_status'])) {
                                    'completed' => 'status-success',
                                    'pending' => 'status-pending',
                                    'failed' => 'status-failed',
                                    default => 'status-pending'
                                };
                                
                                $amount = $row['calculated_amount'];
                                
                                echo "<tr>
                                    <td>#{$row['id']}</td>
                                    <td>{$row['username']}</td>
                                    <td>{$row['service_type']}</td>
                                    <td>{$row['vehicle_type']}</td>
                                    <td>" . date('M d, Y h:i A', strtotime($row['booking_date'] . ' ' . $row['booking_time'])) . "</td>
                                    <td class='amount'>₹" . number_format($amount, 2) . "</td>
                                    <td><span class='payment-status {$status_class}'>" . ucfirst($row['payment_status']) . "</span></td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' style='text-align: center;'>No payments found</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Add active class to current nav item
        document.addEventListener('DOMContentLoaded', function() {
            const currentPage = 'payment_details.php';
            document.querySelectorAll('.nav-item').forEach(item => {
                if (item.getAttribute('href') === currentPage) {
                    document.querySelector('.nav-item.active')?.classList.remove('active');
                    item.classList.add('active');
                }
            });
        });
    </script>
</body>
</html> 