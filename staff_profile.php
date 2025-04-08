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

// Fetch staff information
$staff_id = $_SESSION['staff_id'];
$sql = "SELECT * FROM staff WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();
$staff = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    
    // Update profile information
    $update_sql = "UPDATE staff SET name = ?, email = ?, phone = ? WHERE id = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("sssi", $name, $email, $phone, $staff_id);
    
    if ($update_stmt->execute()) {
        $success_message = "Profile updated successfully!";
        // Refresh staff data
        $result = $stmt->execute();
        $staff = $result->fetch_assoc();
    } else {
        $error_message = "Error updating profile.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>CarCare - Staff Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="assets/css/staffdash.css" rel="stylesheet">
    <style>
        /* Sidebar styles */
        .sidebar {
            height: 100%;
            width: 250px;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #343a40;
            padding-top: 20px;
            color: white;
        }

        .sidebar .nav-link {
            color: #fff;
            padding: 15px 25px;
            transition: all 0.3s;
        }

        .sidebar .nav-link:hover {
            background-color: #495057;
        }

        .sidebar .nav-link i {
            margin-right: 10px;
        }

        .main-content {
            margin-left: 250px;
            padding: 20px;
        }

        .sidebar-brand {
            padding: 15px 25px;
            font-size: 1.5rem;
            font-weight: bold;
            border-bottom: 1px solid #495057;
            margin-bottom: 20px;
        }
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

.sidebar-menu li span {
    font-size: 15px;
}

        .profile-section {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .profile-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .profile-avatar {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: #f8f9fa;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .profile-avatar i {
            font-size: 80px;
            color: #1a2a6c;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .alert {
            margin-bottom: 20px;
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
            <li>
                <a href="customer_feedback.php">
                    <i class="fas fa-comments"></i>
                    <span>Customer Feedback</span>
                </a>
            </li>
            <li class="active">
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
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="profile-section">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <h2>My Profile</h2>
                        </div>

                        <?php if (isset($success_message)): ?>
                            <div class="alert alert-success"><?php echo $success_message; ?></div>
                        <?php endif; ?>

                        <?php if (isset($error_message)): ?>
                            <div class="alert alert-danger"><?php echo $error_message; ?></div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($staff['name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($staff['email']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($staff['phone']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="role">Role</label>
                                <input type="text" class="form-control" id="role" 
                                       value="<?php echo htmlspecialchars($staff['role']); ?>" readonly>
                            </div>

                            <div class="text-center mt-4">
                                <button type="submit" class="btn btn-custom">Update Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Main Content End -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 