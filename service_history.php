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

// Add pagination variables
$records_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $records_per_page;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>CarCare - Service History</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
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

/* Main Content adjustment */
.main-content {
    margin-left: 250px;
    padding: 20px;
    min-height: 100vh;
    background-color: #f8f9fa;
}

/* Responsive Design */
@media (max-width: 768px) {
    .sidebar {
        width: 200px;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
    }

    .sidebar.active {
        transform: translateX(0);
    }

    .main-content {
        margin-left: 0;
    }
}
    </style>
</head>

<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-brand">
            <i class="fas fa-car"></i> CarCare
        </div>
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="staffdash.php">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="service_history.php">
                    <i class="fas fa-history"></i> Service History
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="customer_feedback.php">
                    <i class="fas fa-comments"></i> Customer Feedback
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="staff_profile.php">
                    <i class="fas fa-user"></i> My Profile
                </a>
            </li>
            <li class="nav-item">
                    <a class="nav-link" href="enquiries.php">
                        <i class="fas fa-envelope"></i>
                        <span>Enquiries</span>
                    </a>
                </li>
            <li class="nav-item">
                <a class="nav-link" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <!-- Page Header -->
        <div class="container-fluid mb-4">
            <h2>Service History</h2>
        </div>

        <!-- Service History Table -->
        <div class="container-fluid">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h4>All Bookings</h4>
                </div>
                <div class="card-body">
                    <?php
                    // Get total number of records for pagination
                    $count_sql = "SELECT COUNT(*) as total FROM booking";
                    $count_result = $conn->query($count_sql);
                    $total_records = $count_result->fetch_assoc()['total'];
                    $total_pages = ceil($total_records / $records_per_page);

                    // Get all bookings with pagination
                    $sql = "SELECT * FROM booking 
                            ORDER BY id ASC 
                            LIMIT $offset, $records_per_page";
                    $result = $conn->query($sql);
                    ?>

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
                            if ($result && $result->num_rows > 0) {
                                while($row = $result->fetch_assoc()) {
                                    $status_class = $row['status'] === 'completed' ? 'text-success' : 'text-warning';
                                    ?>
                                    <tr>
                                        <td>#<?php echo $row['id']; ?></td>
                                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td><?php echo htmlspecialchars($row['service_type']); ?></td>
                                        <td><?php echo htmlspecialchars($row['vehicle_type']); ?></td>
                                        <td><?php 
                                            // Parse date and time separately
                                            $datetime = new DateTime($row['booking_date']);
                                            echo $datetime->format('M d, Y') . ' ' . 
                                                 date('h:i A', strtotime($row['booking_time'])); 
                                        ?></td>
                                        <td><?php echo htmlspecialchars($row['vehicle_number']); ?></td>
                                        <td><?php echo htmlspecialchars($row['special_requests'] ?? 'N/A'); ?></td>
                                        <td><span class="<?php echo $status_class; ?>"><?php echo ucfirst($row['status']); ?></span></td>
                                        <td>
                                            <?php if($row['status'] == 'pending'): ?>
                                                <button class="btn btn-sm btn-success complete-service" data-id="<?php echo $row['id']; ?>">
                                                    Complete
                                                </button>
                                            <?php endif; ?>
                                            <button class="btn btn-sm btn-primary view-details" data-id="<?php echo $row['id']; ?>">
                                                View Details
                                            </button>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo "<tr><td colspan='9' class='text-center'>No bookings found</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <nav aria-label="Service history pagination">
                        <ul class="pagination justify-content-center">
                            <?php for($i = 1; $i <= $total_pages; $i++): ?>
                                <li class="page-item <?php echo $i === $page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                                </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    <script>
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

    document.querySelectorAll('.view-details').forEach(button => {
        button.addEventListener('click', function() {
            const bookingId = this.dataset.id;
            window.location.href = `booking_details.php?id=${bookingId}`;
        });
    });
    </script>
</body>
</html> 