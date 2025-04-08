<?php
// Database configuration and connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_care2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get customer statistics
$total_customers = 0;
$sql = "SELECT COUNT(*) as total_customers FROM users WHERE role='customer'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_customers = $row['total_customers'];
}

// Get active customers (those with at least one booking)
$active_customers = 0;
$sql = "SELECT COUNT(DISTINCT username) as active_customers FROM booking";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $active_customers = $row['active_customers'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management - Car Wash Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Use the same CSS as admindash.php -->
    <style>
        /* General Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: #f5f6fa;
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 250px;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .page-title {
            color: #2c3e50;
            font-size: 24px;
        }

        .date {
            color: #7f8c8d;
            font-size: 14px;
        }

        /* Stats Cards Styles */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .stat-card h3 {
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .stat-card .value {
            color: #2c3e50;
            font-size: 24px;
            font-weight: bold;
        }

        /* Table Styles */
        .bookings-section {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .table-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .table-title {
            color: #2c3e50;
            font-size: 18px;
        }

        .add-button {
            background: #3498db;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background-color 0.3s;
        }

        .add-button:hover {
            background: #2980b9;
        }

        .bookings-table {
            width: 100%;
            border-collapse: collapse;
        }

        .bookings-table th,
        .bookings-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ecf0f1;
        }

        .bookings-table th {
            background-color: #f8f9fa;
            color: #7f8c8d;
            font-weight: 600;
        }

        .bookings-table tr:hover {
            background-color: #f8f9fa;
        }

        /* Status Badge Styles */
        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .status-completed {
            background-color: #e8f5e9;
            color: #2e7d32;
        }

        .status-pending {
            background-color: #fff3e0;
            color: #ef6c00;
        }

        /* Action Buttons Styles */
        .actions {
            display: flex;
            gap: 8px;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 4px;
            font-size: 12px;
            transition: background-color 0.3s;
        }

        .edit-btn {
            background-color: #f1c40f;
            color: #fff;
        }

        .edit-btn:hover {
            background-color: #f39c12;
        }

        .delete-btn {
            background-color: #e74c3c;
            color: #fff;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 60px;
                padding: 10px;
            }
            
            .main-content {
                margin-left: 60px;
            }
            
            .stats-container {
                grid-template-columns: 1fr;
            }
            
            .bookings-table {
                display: block;
                overflow-x: auto;
            }
        }

        /* Link Styles */
        .nav-item a,
        a .nav-item {
            text-decoration: none;
            color: white;
        }

        .nav-menu a {
            text-decoration: none;
            color: inherit;
        }

        /* Ensure nav items are white */
        .nav-item {
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar (same as admindash.php but with Customers active) -->
        <div class="sidebar">
            <!-- ... existing sidebar code ... -->
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1 class="page-title">Customer Management</h1>
                <div class="date"><?php echo date('F d, Y'); ?></div>
            </div>

            <!-- Stats Cards -->
            <div class="stats-container">
                <div class="stat-card">
                    <h3>Total Customers</h3>
                    <div class="value"><?php echo $total_customers; ?></div>
                </div>
                <div class="stat-card">
                    <h3>Active Customers</h3>
                    <div class="value"><?php echo $active_customers; ?></div>
                </div>
            </div>

            <!-- Customers Table -->
            <div class="bookings-section">
                <div class="table-header">
                    <h2 class="table-title">Customer List</h2>
                    <button class="add-button">
                        <i class="fas fa-plus"></i>
                        <span>Add New Customer</span>
                    </button>
                </div>
                <table class="bookings-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Total Bookings</th>
                            <th>Last Booking</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $query = "SELECT u.*, 
                                 COUNT(b.id) as booking_count,
                                 MAX(b.booking_date) as last_booking
                                 FROM users u
                                 LEFT JOIN booking b ON u.username = b.username
                                 WHERE u.role = 'customer'
                                 GROUP BY u.id
                                 ORDER BY u.id DESC";
                        $result = $conn->query($query);

                        if ($result && $result->num_rows > 0) {
                            while($row = $result->fetch_assoc()) {
                                $status = $row['booking_count'] > 0 ? 'Active' : 'Inactive';
                                $status_class = $status == 'Active' ? 'status-completed' : 'status-pending';
                                $last_booking = $row['last_booking'] ? date('M d, Y', strtotime($row['last_booking'])) : 'Never';
                                
                                echo "<tr>
                                    <td>#{$row['id']}</td>
                                    <td>{$row['username']}</td>
                                    <td>{$row['email']}</td>
                                    <td>{$row['phone']}</td>
                                    <td>{$row['booking_count']}</td>
                                    <td>{$last_booking}</td>
                                    <td><span class='status-badge {$status_class}'>{$status}</span></td>
                                    <td class='actions'>
                                        <button class='action-btn edit-btn' data-id='{$row['id']}'>
                                            <i class='fas fa-edit'></i>
                                            Edit
                                        </button>
                                        <button class='action-btn delete-btn' data-id='{$row['id']}'>
                                            <i class='fas fa-trash'></i>
                                            Delete
                                        </button>
                                    </td>
                                </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='8' style='text-align: center;'>No customers found</td></tr>";
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
            document.querySelector('.nav-item.active')?.classList.remove('active');
            this.classList.add('active');
        });
    });

    // Delete customer confirmation
    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const customerId = this.dataset.id;
            if(confirm('Are you sure you want to delete this customer?')) {
                window.location.href = `delete_customer.php?id=${customerId}`;
            }
        });
    });

    // Edit customer redirect
    document.querySelectorAll('.edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const customerId = this.dataset.id;
            window.location.href = `edit_customer.php?id=${customerId}`;
        });
    });

    // Add new customer redirect
    document.querySelector('.add-button').addEventListener('click', function() {
        window.location.href = 'add_customer.php';
    });
    </script>
</body>
</html> 