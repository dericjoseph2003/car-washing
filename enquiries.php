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

// Handle message status updates
if (isset($_POST['message_id']) && isset($_POST['status'])) {
    $message_id = $_POST['message_id'];
    $status = $_POST['status'];
    
    $update_sql = "UPDATE contact_messages SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($update_sql);
    
    if ($stmt === false) {
        $_SESSION['error'] = "Error preparing statement: " . $conn->error;
    } else {
        $stmt->bind_param("si", $status, $message_id);
        
        if (!$stmt->execute()) {
            $_SESSION['error'] = "Error updating status: " . $stmt->error;
        } else {
            $_SESSION['success'] = "Status updated successfully!";
        }
        
        $stmt->close();
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch messages with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$records_per_page = 10;
$offset = ($page - 1) * $records_per_page;

$sql = "SELECT * FROM contact_messages ORDER BY created_at DESC LIMIT ?, ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $offset, $records_per_page);
$stmt->execute();
$result = $stmt->get_result();

// Get total number of records for pagination
$total_records_sql = "SELECT COUNT(*) as count FROM contact_messages";
$total_records_result = $conn->query($total_records_sql);
$total_records = $total_records_result->fetch_assoc()['count'];
$total_pages = ceil($total_records / $records_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>CarCare - Enquiries</title>
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
                <a href="enquiries.php">
                    <i class="fas fa-envelope"></i>
                    <span>Enquiries</span>
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
        <div class="container-fluid">
            <h2 class="mb-4">Customer Enquiries</h2>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Subject</th>
                                    <th>Message</th>
                                    <th>Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $row['id']; ?></td>
                                    <td><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><?php echo htmlspecialchars($row['email']); ?></td>
                                    <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($row['message'], 0, 50)) . '...'; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['created_at'])); ?></td>
                                    <td class="<?php echo $row['status'] ?? 'pending'; ?>">
                                        <?php echo ucfirst($row['status'] ?? 'pending'); ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary view-message" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#messageModal"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-name="<?php echo htmlspecialchars($row['name']); ?>"
                                                data-email="<?php echo htmlspecialchars($row['email']); ?>"
                                                data-subject="<?php echo htmlspecialchars($row['subject']); ?>"
                                                data-message="<?php echo htmlspecialchars($row['message']); ?>"
                                                data-date="<?php echo date('M d, Y', strtotime($row['created_at'])); ?>">
                                            View
                                        </button>
                                        <?php if (($row['status'] ?? 'pending') == 'pending'): ?>
                                        <form method="post" class="d-inline" onsubmit="return confirm('Are you sure you want to mark this message as resolved?');">
                                            <input type="hidden" name="message_id" value="<?php echo $row['id']; ?>">
                                            <input type="hidden" name="status" value="resolved">
                                            <button type="submit" class="btn btn-sm btn-success">Mark Resolved</button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <nav aria-label="Page navigation">
                        <ul class="pagination justify-content-center">
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <li class="page-item <?php echo $page == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                            <?php endfor; ?>
                        </ul>
                    </nav>
                </div>
            </div>
        </div>
    </div>
    <!-- Main Content End -->

    <!-- Message Modal -->
    <div class="modal fade" id="messageModal" tabindex="-1" aria-labelledby="messageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="messageModalLabel">Message Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <strong>From:</strong> <span id="modalName"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Email:</strong> <span id="modalEmail"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Subject:</strong> <span id="modalSubject"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Date:</strong> <span id="modalDate"></span>
                    </div>
                    <div class="mb-3">
                        <strong>Message:</strong>
                        <p id="modalMessage" class="mt-2"></p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="replyEmail" class="btn btn-primary">Reply via Email</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.view-message').forEach(button => {
            button.addEventListener('click', function() {
                const data = this.dataset;
                document.getElementById('modalName').textContent = data.name;
                document.getElementById('modalEmail').textContent = data.email;
                document.getElementById('modalSubject').textContent = data.subject;
                document.getElementById('modalMessage').textContent = data.message;
                document.getElementById('modalDate').textContent = data.date;
                document.getElementById('replyEmail').href = `mailto:${data.email}?subject=Re: ${data.subject}`;
            });
        });
    </script>
</body>
</html> 