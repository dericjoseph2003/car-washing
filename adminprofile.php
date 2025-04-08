<?php
session_start();

// Include database connection
include 'conn.php';

// Check if user is logged in as admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Fetch admin details
$admin_email = "admin@example.com";
$sql = "SELECT * FROM users WHERE Email = ?";
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("Error preparing statement: " . $conn->error);
}

$stmt->bind_param("s", $admin_email);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_email = $_POST['email'];
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    
    // Verify current password
    if (password_verify($current_password, $admin['PasswordHash'])) {
        // Update email if changed
        if ($new_email !== $admin_email) {
            $update_sql = "UPDATE users SET Email = ? WHERE Email = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $new_email, $admin_email);
            $update_stmt->execute();
        }
        
        // Update password if provided
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET PasswordHash = ? WHERE Email = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $hashed_password, $admin_email);
            $update_stmt->execute();
        }
        
        // Redirect to refresh the page
        header("Location: adminprofile.php?success=1");
        exit();
    } else {
        header("Location: adminprofile.php?error=wrong_password");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile - Car Wash Admin Dashboard</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Include your existing CSS here -->
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
            text-decoration: none;
            color: white;
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

        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
            }
        }

        /* Keep your existing profile-specific styles below */
        .profile-container {
            background: var(--white);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            margin-top: 20px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 40px;
            padding-bottom: 20px;
            border-bottom: 2px solid var(--border-color);
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 30px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .profile-avatar:hover {
            transform: scale(1.05);
        }

        .profile-avatar i {
            font-size: 50px;
            color: var(--white);
        }

        .profile-header h2 {
            color: var(--text-dark);
            margin: 0 0 10px 0;
            font-size: 28px;
        }

        .profile-header p {
            color: var(--text-light);
            margin: 0;
            font-size: 16px;
        }

        .profile-form {
            max-width: 600px;
            margin: 0 auto;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            color: var(--text-dark);
            font-weight: 500;
            font-size: 16px;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid var(--border-color);
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
            outline: none;
        }

        .save-button {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 14px 28px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            width: 100%;
            max-width: 200px;
        }

        .save-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(76, 175, 80, 0.2);
        }

        .save-button:active {
            transform: translateY(0);
        }

        .save-button i {
            font-size: 18px;
        }

        .header {
            margin-bottom: 30px;
        }

        .page-title {
            color: var(--text-dark);
            font-size: 32px;
            margin: 0 0 10px 0;
        }

        .date {
            color: var(--text-light);
            font-size: 16px;
        }

        /* Add success/error message styles */
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .message.success {
            background-color: rgba(40, 167, 69, 0.1);
            color: var(--success-color);
            border: 1px solid var(--success-color);
        }

        .message.error {
            background-color: rgba(220, 53, 69, 0.1);
            color: var(--danger-color);
            border: 1px solid var(--danger-color);
        }

        .site-details {
            padding: 20px 0;
        }

        .info-group {
            margin-bottom: 30px;
        }

        .info-group h3 {
            color: var(--primary-color);
            font-size: 20px;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--border-color);
        }

        .info-item {
            display: flex;
            margin-bottom: 15px;
            padding: 10px;
            background-color: var(--light-bg);
            border-radius: 8px;
        }

        .info-item .label {
            font-weight: 600;
            width: 150px;
            color: var(--text-dark);
        }

        .info-item .value {
            color: var(--text-light);
            flex: 1;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Include your sidebar here -->
        <div class="sidebar">
            <div class="logo">
                <i class="fas fa-car-wash"></i>
                <span>CarWash Admin</span>
            </div>
            <div class="nav-menu">
                <div class="nav-section">
                    <div class="nav-section-title">Main Menu</div>
                    <a href="admindash.php" class="nav-item">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="bookings.php" class="nav-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Bookings</span>
                    </a>
                
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Management</div>
                    <a href="enquiries.php" class="nav-item">
                        <i class="fas fa-message"></i>
                        <span>Enquiries</span>
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                    <a href="adminprofile.php" class="nav-item active">
                        <i class="fas fa-user-circle"></i>
                        <span>Profile</span>
                    </a>
                    <a href="logout.php" class="nav-item">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1 class="page-title">Profile Settings</h1>
                <div class="date"><?php echo date('F d, Y'); ?></div>
            </div>

            <div class="profile-container">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h2>Admin</h2>
                        <p>Overview of your car wash business details</p>
                    </div>
                </div>

                <div class="site-details">
                    <div class="info-group">
                        <h3>Business Details</h3>
                        <div class="info-item">
                            <span class="label">Business Name:</span>
                            <span class="value">Car Care Services</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Contact Email:</span>
                            <span class="value"><?php echo htmlspecialchars($admin['Email'] ?? ''); ?></span>
                        </div>
                        <div class="info-item">
                            <span class="label">Operating Hours:</span>
                            <span class="value">Monday - Sunday: 8:00 AM - 6:00 PM</span>
                        </div>
                    </div>

                    

                    <div class="info-group">
                        <h3>Contact Information</h3>
                        <div class="info-item">
                            <span class="label">Address:</span>
                            <span class="value">123 Car Wash Street, City, Country</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Phone:</span>
                            <span class="value">+1 234 567 8900</span>
                        </div>
                        <div class="info-item">
                            <span class="label">Support Email:</span>
                            <span class="value">support@carcare.com</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Add your existing JavaScript here
    </script>
</body>
</html>