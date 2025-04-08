<?php
// Add these variables at the top of your file
$pending_count = 0;
$completed_count = 0;
$total_bookings = 0;
$total_revenue = 0;

// Query to get the counts and revenue
$today = date('Y-m-d');
$sql = "SELECT 
    COUNT(*) as total,
    SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status = 'Completed' THEN 1 ELSE 0 END) as completed,
    SUM(price) as revenue
FROM booking 
WHERE DATE(booking_date) = ?";

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

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("s", $today);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $total_bookings = $row['total'];
        $pending_count = $row['pending'];
        $completed_count = $row['completed'];
        $total_revenue = $row['revenue'] ?? 0;
    }
    $stmt->close();
}

// Handle form submission for updating prices
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_id = $_POST['service_id'];
    $vehicle_type = strtolower($_POST['vehicle_type']);
    $new_price = $_POST['price'];
    
    $column_name = "price_" . $vehicle_type;
    $update_sql = "UPDATE services SET `$column_name` = ? WHERE id = ?";
    
    if ($stmt = $conn->prepare($update_sql)) {
        $stmt->bind_param("di", $new_price, $service_id);
        
        if ($stmt->execute()) {
            $success_message = "Price updated successfully!";
        } else {
            $error_message = "Error updating price: " . $conn->error;
        }
        $stmt->close();
    } else {
        $error_message = "Error preparing statement: " . $conn->error;
    }
}

// Categories and vehicle types from your database
$categories = [
    'basic' => ['name' => 'Basic Services', 'icon' => 'fa-car-wash'],
    'premium' => ['name' => 'Premium Services', 'icon' => 'fa-star'],
    'detailing' => ['name' => 'Detailing Services', 'icon' => 'fa-tools']
];
$vehicleTypes = ['Sedan', 'SUV', 'Truck', 'Van'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Prices - Car Wash Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Copy all base styles from admindash.php */
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --accent-color: #3949ab;
            --light-bg: #f5f6fa;
            --white: #ffffff;
            --text-dark: #2d3436;
            --text-light: #636e72;
            --border-color: #e0e0e0;
            --success-color: #2ecc71;
            --hover-success: #27ae60;
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
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            cursor: default;
            color: var(--white);
            text-decoration: none;
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

        /* Enhanced price table styles */
        .price-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 12px;
            margin-top: 20px;
        }

        .price-table th {
            background-color: var(--light-bg);
            padding: 16px;
            text-align: left;
            font-weight: 600;
            color: var(--text-dark);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .price-table td {
            background-color: var(--white);
            padding: 16px;
            margin: 8px 0;
            border: none;
            font-size: 14px;
        }

        .price-table tr {
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
        }

        .price-table tr:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .price-input {
            width: 120px;
            padding: 8px 12px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
            color: var(--text-dark);
        }

        .price-input:focus {
            border-color: var(--primary-color);
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
        }

        .price-input:hover {
            border-color: var(--secondary-color);
        }

        .save-btn {
            background-color: var(--success-color);
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .save-btn:hover {
            background-color: var(--hover-success);
            transform: translateY(-1px);
        }

        .save-btn i {
            font-size: 14px;
        }

        .message {
            padding: 16px;
            margin: 20px 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            animation: slideIn 0.3s ease;
        }

        @keyframes slideIn {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .success {
            background-color: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }

        .service-name {
            font-weight: 500;
            color: var(--text-dark);
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .service-name i {
            color: var(--primary-color);
            font-size: 18px;
        }

        .price-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .currency-symbol {
            color: var(--text-light);
            font-size: 14px;
        }

        .add-service-btn {
            background-color: var(--primary-color);
            color: var(--white);
            padding: 10px 20px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            font-weight: 500;
        }

        .add-service-btn:hover {
            background-color: var(--secondary-color);
            transform: translateY(-1px);
        }

        .category-section {
            margin-bottom: 40px;
            background: var(--white);
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .category-title {
            color: var(--primary-color);
            font-size: 1.5rem;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--light-bg);
        }

        .category-title i {
            font-size: 1.2rem;
        }

        .price-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .price-input {
            width: 100px;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
            text-align: right;
        }

        .save-btn {
            padding: 8px;
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .save-btn i {
            margin: 0;
        }

        .price-wrapper {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .currency-symbol {
            color: var(--text-light);
        }

        .price-table {
            width: 100%;
            margin-top: 0;
        }

        .price-table th {
            background-color: var(--light-bg);
            padding: 12px;
            text-align: center;
        }

        .price-table td {
            padding: 12px;
            text-align: center;
        }

        .service-name {
            text-align: left;
        }

        /* Add responsive styles */
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

        .todays-schedule {
            background: var(--white);
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 30px;
        }

        .schedule-header {
            padding: 20px;
            border-bottom: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .header-left {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .header-left h2 {
            color: var(--primary-color);
            font-size: 1.5rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .header-left .date {
            color: var(--text-light);
            font-size: 0.9rem;
        }

        .header-right .stats {
            display: flex;
            gap: 20px;
        }

        .stat-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 8px 15px;
            background: var(--light-bg);
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .stat-item i {
            color: var(--primary-color);
        }

        .schedule-footer {
            padding: 20px;
            border-top: 1px solid var(--border-color);
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: var(--light-bg);
            border-radius: 0 0 10px 10px;
        }

        .footer-stats {
            display: flex;
            gap: 30px;
        }

        .total-bookings, .revenue {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .total-bookings span, .revenue span {
            color: var(--text-light);
        }

        .total-bookings strong, .revenue strong {
            color: var(--text-dark);
            font-size: 1.1rem;
        }

        .footer-actions {
            display: flex;
            gap: 10px;
        }

        .refresh-btn, .export-btn {
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .refresh-btn {
            background: var(--white);
            color: var(--primary-color);
            border: 1px solid var(--border-color);
        }

        .export-btn {
            background: var(--primary-color);
            color: var(--white);
        }

        .refresh-btn:hover, .export-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }

        /* Add responsive styles */
        @media (max-width: 768px) {
            .schedule-header {
                flex-direction: column;
                gap: 15px;
            }

            .header-right .stats {
                flex-direction: column;
                gap: 10px;
            }

            .schedule-footer {
                flex-direction: column;
                gap: 15px;
            }

            .footer-stats {
                flex-direction: column;
                gap: 10px;
            }

            .footer-actions {
                width: 100%;
                justify-content: space-between;
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


        <!-- Keep your existing main content -->
        <div class="main-content">
            <div class="header">
                <h1 class="page-title">Service Prices</h1>
            </div>

            <?php if (isset($success_message)): ?>
                <div class="message success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success_message; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="message error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <div class="bookings-section">
                <div class="table-header">
                    <h2 class="table-title">Manage Service Prices</h2>
                </div>

                <?php foreach ($categories as $category_key => $category_info): ?>
                    <div class="category-section">
                        <h3 class="category-title">
                            <i class="fas <?php echo $category_info['icon']; ?>"></i>
                            <?php echo $category_info['name']; ?>
                        </h3>
                        
                        <table class="price-table">
                            <thead>
                                <tr>
                                    <th>Service Type</th>
                                    <?php foreach ($vehicleTypes as $vehicle): ?>
                                        <th><?php echo $vehicle; ?></th>
                                    <?php endforeach; ?>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM services WHERE category = ?";
                                if ($stmt = $conn->prepare($sql)) {
                                    $stmt->bind_param("s", $category_key);
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    
                                    while ($service = $result->fetch_assoc()):
                                ?>
                                <tr>
                                    <td>
                                        <div class="service-name">
                                            <i class="fas fa-check-circle"></i>
                                            <?php echo $service['service_name']; ?>
                                        </div>
                                    </td>
                                    <?php foreach ($vehicleTypes as $vehicle): ?>
                                        <td>
                                            <form method="POST" class="price-form">
                                                <div class="price-wrapper">
                                                    <span class="currency-symbol">₹</span>
                                                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                                    <input type="hidden" name="vehicle_type" value="<?php echo $vehicle; ?>">
                                                    <input type="number" 
                                                           name="price" 
                                                           value="<?php echo $service['price_' . strtolower($vehicle)]; ?>" 
                                                           class="price-input" 
                                                           step="1" 
                                                           min="0">
                                                    <button type="submit" class="save-btn" title="Save price">
                                                        <i class="fas fa-save"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </td>
                                    <?php endforeach; ?>
                                </tr>
                                <?php 
                                    endwhile;
                                    $stmt->close();
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="todays-schedule">
                <div class="schedule-header">
                    <div class="header-left">
                        <h2><i class="fas fa-calendar-day"></i> Today's Schedule</h2>
                        <span class="date"><?php echo date('F d, Y'); ?></span>
                    </div>
                    <div class="header-right">
                        <div class="stats">
                            <div class="stat-item">
                                <i class="fas fa-clock"></i>
                                <span>Pending: <?php echo $pending_count; ?></span>
                            </div>
                            <div class="stat-item">
                                <i class="fas fa-check-circle"></i>
                                <span>Completed: <?php echo $completed_count; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Your existing booking details table -->
                <table class="price-table">
                    <!-- ... existing table content ... -->
                </table>

                <div class="schedule-footer">
                    <div class="footer-stats">
                        <div class="total-bookings">
                            <span>Total Bookings Today:</span>
                            <strong><?php echo $total_bookings; ?></strong>
                        </div>
                        <div class="revenue">
                            <span>Today's Revenue:</span>
                            <strong>₹<?php echo number_format($total_revenue, 2); ?></strong>
                        </div>
                    </div>
                    <div class="footer-actions">
                        <button class="refresh-btn">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                        <button class="export-btn">
                            <i class="fas fa-download"></i> Export
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', function() {
            document.querySelector('.nav-item.active')?.classList.remove('active');
            this.classList.add('active');
        });
    });

    document.addEventListener('DOMContentLoaded', function() {
        const forms = document.querySelectorAll('.price-form');
        forms.forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const formData = new FormData(this);
                const btn = this.querySelector('.save-btn');
                
                // Show loading state
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                btn.disabled = true;

                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(() => {
                    // Show success state
                    btn.innerHTML = '<i class="fas fa-check"></i>';
                    btn.style.backgroundColor = 'var(--success-color)';
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fas fa-save"></i>';
                        btn.style.backgroundColor = '';
                        btn.disabled = false;
                    }, 2000);
                })
                .catch(error => {
                    // Show error state
                    btn.innerHTML = '<i class="fas fa-exclamation-circle"></i>';
                    btn.style.backgroundColor = '#dc3545';
                    alert('Error updating price. Please try again.');
                    
                    // Reset button after 2 seconds
                    setTimeout(() => {
                        btn.innerHTML = '<i class="fas fa-save"></i>';
                        btn.style.backgroundColor = '';
                        btn.disabled = false;
                    }, 2000);
                });
            });
        });
    });

    document.querySelector('.refresh-btn').addEventListener('click', function() {
        // Show loading state
        this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
        this.disabled = true;

        // Reload the page
        location.reload();
    });

    // Add export functionality
    document.querySelector('.export-btn').addEventListener('click', function() {
        // Add your export logic here
        alert('Export functionality will be implemented here');
    });
    </script>
</body>
</html> 