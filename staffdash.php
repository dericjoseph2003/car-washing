<?php
session_start();

// Check if staff is logged in
if (!isset($_SESSION['staff_id'])) {
    header("Location: staff_login.php");
    exit();
}

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

// After database connection and before initializing variables, modify the staff query with error handling:
$staff_name = "Staff Member"; // Default value
if (isset($_SESSION['staff_id'])) {
    $staff_id = $_SESSION['staff_id'];
    $staff_query = "SELECT name FROM staff WHERE id = ?";
    $stmt = $conn->prepare($staff_query);
    
    // Add error checking for prepare
    if ($stmt === false) {
        die("Error preparing statement: " . $conn->error);
    }
    
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        $staff_row = $result->fetch_assoc();
        $staff_name = $staff_row['name'];
    }
    $stmt->close();
}

// Initialize all variables with default values
$total_bookings = 0;
$pending_services = 0;
$completed_today = 0;
$contact_messages = 0;

// Total bookings
$sql = "SELECT COUNT(*) as total_bookings FROM booking";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $total_bookings = $row['total_bookings'];
}

// Pending services
$sql = "SELECT COUNT(*) as pending_count FROM booking WHERE status = 'Pending'";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $pending_services = $row['pending_count'];
}

// Completed today
$sql = "SELECT COUNT(*) as completed_count FROM booking WHERE status = 'Confirmed' AND DATE(booking_date) = CURDATE()";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $completed_today = $row['completed_count'];
}

// Contact messages count
$sql = "SELECT COUNT(*) as contact_count FROM contact_messages";
$result = $conn->query($sql);
if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $contact_messages = $row['contact_count'];
}

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>CarCare - Staff Dashboard</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
        <link href="assets/css/staffdash.css" rel="stylesheet">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <!-- ... existing head content ... -->
         <style>
            /* Sidebar Styles - Enhanced */
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


            /* Main Content Styles */
            .main-content {
                margin-left: 280px;
                padding: 30px;
                min-height: 100vh;
                background-color: #f8f9fa;
            }

            /* Dashboard Cards */
            .service-item {
                background: white;
                padding: 25px;
                border-radius: 15px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                text-align: center;
                height: 100%;
            }

            .service-item:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
            }

            .service-item i {
                font-size: 40px;
                margin-bottom: 15px;
                color: #1a2a6c;
            }

            .service-item h3 {
                font-size: 18px;
                margin-bottom: 10px;
                color: #333;
            }

            .service-item h4 {
                font-size: 28px;
                font-weight: 700;
                color: #0066cc;
            }

            /* Table Styling */
            .card {
                border: none;
                border-radius: 15px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
                overflow: hidden;
                margin-bottom: 30px;
            }

            .card-header {
                background: linear-gradient(135deg, #1a2a6c, #0066cc);
                padding: 20px;
                border: none;
            }

            .card-header h4 {
                margin: 0;
                color: white;
                font-weight: 600;
            }

            .table {
                margin: 0;
            }

            .table th {
                background-color: #f8f9fa;
                font-weight: 600;
                border-bottom: 2px solid #dee2e6;
            }

            .table td {
                vertical-align: middle;
            }

            /* Buttons */
            .btn-custom {
                background: linear-gradient(135deg, #1a2a6c, #0066cc);
                color: white;
                border: none;
                padding: 12px 20px;
                border-radius: 8px;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .btn-custom:hover {
                background: linear-gradient(135deg, #0066cc, #1a2a6c);
                transform: translateY(-2px);
                color: white;
            }

            .btn-sm {
                padding: 5px 10px;
                font-size: 14px;
                border-radius: 5px;
            }

            /* Status Colors */
            .text-warning { color: #ffc107 !important; }
            .text-primary { color: #0d6efd !important; }
            .text-success { color: #198754 !important; }
            .text-secondary { color: #6c757d !important; }

            /* Page Header */
            .page-header {
                background: linear-gradient(135deg, #1a2a6c, #0066cc);
                padding: 30px 0;
                margin-bottom: 30px;
                border-radius: 15px;
            }

            .page-header h2 {
                color: white;
                margin: 0;
                font-weight: 700;
                text-align: center;
            }

            /* Responsive Design */
            @media (max-width: 992px) {
                .sidebar {
                    width: 240px;
                    transform: translateX(-100%);
                }

                .main-content {
                    margin-left: 0;
                }

                .sidebar.active {
                    transform: translateX(0);
                }
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
                <li class="active">
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
            <!-- Top Bar Start -->
            <!-- ... existing top bar code ... -->
            <!-- Top Bar End -->

            <!-- Nav Bar Start -->
            <!-- ... existing nav bar code ... -->
            <!-- Nav Bar End -->

            <!-- Page Header Start -->
            <div class="page-header">
                <div class="container">
                    <div class="row">
                        <div class="col d-flex justify-content-end align-items-center">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle bg-white d-flex align-items-center justify-content-center me-3" 
                                     style="width: 50px; height: 50px; border: 2px solid white;">
                                    <i class="fas fa-user" style="font-size: 25px; color: #343a40;"></i>
                                </div>
                                <h2 class="mb-0"><?php echo htmlspecialchars($staff_name); ?></h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Page Header End -->

            <!-- Dashboard Start -->
            <div class="container-fluid py-5">
                <div class="container">
                    <div class="row">
                        <!-- Quick Stats -->
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="service-item">
                                <i class="fa fa-calendar-check"></i>
                                <h3>Total Bookings</h3>
                                <h4><?php echo $total_bookings; ?></h4>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="service-item">
                                <i class="fa fa-clock"></i>
                                <h3>Pending Services</h3>
                                <h4><?php echo $pending_services; ?></h4>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="service-item">
                                <i class="fa fa-check-circle"></i>
                                <h3>Completed Today</h3>
                                <h4><?php echo $completed_today; ?></h4>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 mb-4">
                            <div class="service-item">
                                <i class="fa fa-star"></i>
                                <h3>Enquiries</h3>
                                <h4><?php echo $contact_messages; ?></h4>
                            </div>
                        </div>

                        <!-- Today's Schedule -->
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header bg-dark text-white">
                                    <h4>Today's Schedule</h4>
                                </div>
                                <div class="card-body">
                                    <table class="table">
                                        <thead>
                                            <tr>
                                                <th>ID</th>
                                                <th>Customer</th>
                                                <th>Service Type</th>
                                                <th>Vehicle Type</th>
                                                <th>Date & Time</th>
                                                <th>Vehicle Number</th>
                                                <th>Special Requests</th>
                                                <th>Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            // Modified query to include both today's bookings and future bookings
                                            $sql = "SELECT b.*, 
                                                   b.service_category,
                                                   b.payment_status,
                                                   b.payment_amount 
                                            FROM booking b
                                            WHERE DATE(booking_date) >= CURDATE() 
                                            ORDER BY booking_date ASC, booking_time ASC 
                                            LIMIT 10";
                                            $result = $conn->query($sql);

                                            if ($result && $result->num_rows > 0) {
                                                while($row = $result->fetch_assoc()) {
                                                    $status_class = '';
                                                    switch($row['status']) {
                                                        case 'Pending':
                                                            $status_class = 'text-warning';
                                                            break;
                                                        case 'Confirmed':
                                                            $status_class = 'text-success';
                                                            break;
                                                        default:
                                                            $status_class = 'text-secondary';
                                                    }
                                                    ?>
                                                    <tr>
                                                        <td>#<?php echo $row['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['service_category'] . ' - ' . $row['service_type']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['vehicle_type']); ?></td>
                                                        <td><?php 
                                                            $datetime = new DateTime($row['booking_date']);
                                                            echo $datetime->format('M d, Y') . ' ' . 
                                                                 date('h:i A', strtotime($row['booking_time'])); 
                                                        ?></td>
                                                        <td><?php echo htmlspecialchars($row['vehicle_number']); ?></td>
                                                        <td><?php echo htmlspecialchars($row['special_requests'] ?? 'N/A'); ?></td>
                                                        <td>
                                                            <span class="<?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span>
                                                            <?php if($row['payment_status']): ?>
                                                                <br><small>(Payment: <?php echo ucfirst($row['payment_status']); ?>)</small>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php if($row['status'] == 'Pending'): ?>
                                                                <button class="btn btn-sm btn-success confirm-service" data-id="<?php echo $row['id']; ?>">Confirm</button>
                                                            <?php endif; ?>
                                                            <button class="btn btn-sm btn-primary view-details" data-id="<?php echo $row['id']; ?>">Details</button>
                                                        </td>
                                                    </tr>
                                                    <?php
                                                }
                                            } else {
                                                echo "<tr><td colspan='9' class='text-center'>No upcoming bookings found</td></tr>";
                                            }
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header bg-dark text-white">
                                    <h4>Quick Actions</h4>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                      
                                        <a href="service_history.php" class="btn btn-custom btn-block mb-2">
                                            <i class="fa fa-history"></i> Service History
                                        </a>
                                        <a href="customer_feedback.php" class="btn btn-custom btn-block mb-2">
                                            <i class="fa fa-comments"></i> Customer Feedback
                                        </a>
                                        <a href="staff_profile.php" class="btn btn-custom btn-block">
                                            <i class="fa fa-user"></i> My Profile
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Dashboard End -->
        </div>
        <!-- Main Content End -->

        <!-- Footer Start -->
        <!-- ... existing footer code ... -->
        <!-- Footer End -->

        <!-- JavaScript Libraries -->
        <!-- ... existing JavaScript includes ... -->
        <script src="assets/js/staffdash.js"></script>
        <script>
        // Handle service start
        document.querySelectorAll('.start-service').forEach(button => {
            button.addEventListener('click', function() {
                const bookingId = this.dataset.id;
                if(confirm('Start this service?')) {
                    fetch('update_service_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `booking_id=${bookingId}&status=in_progress`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            location.reload();
                        } else {
                            alert('Error updating service status');
                        }
                    });
                }
            });
        });

        // Handle service completion
        document.querySelectorAll('.complete-service').forEach(button => {
            button.addEventListener('click', function() {
                const bookingId = this.dataset.id;
                if(confirm('Mark this service as completed?')) {
                    fetch('update_service_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `booking_id=${bookingId}&status=completed`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            location.reload();
                        } else {
                            alert('Error updating service status');
                        }
                    });
                }
            });
        });

        // Handle view details
        document.querySelectorAll('.view-details').forEach(button => {
            button.addEventListener('click', function() {
                const bookingId = this.dataset.id;
                window.location.href = `booking_details.php?id=${bookingId}`;
            });
        });

        // Update the service status handling
        document.querySelectorAll('.confirm-service').forEach(button => {
            button.addEventListener('click', function() {
                const bookingId = this.dataset.id;
                if(confirm('Confirm this service?')) {
                    fetch('update_service_status.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: `booking_id=${bookingId}&status=Confirmed`
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            location.reload();
                        } else {
                            alert('Error updating service status');
                        }
                    });
                }
            });
        });
        </script>
    </body>
</html> 