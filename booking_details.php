<?php
session_start();

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_care2";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch booking details with user information
$sql = "SELECT b.*, u.Email, u.phone 
        FROM booking b 
        LEFT JOIN users u ON b.username = u.Username 
        WHERE b.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

if (!$booking) {
    header("Location: service_history.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>CarCare - Booking Details</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="assets/css/staffdash.css" rel="stylesheet">
    <style>
        /* Header Styles */
        .top-bar {
            background: #091E3E;
            padding: 15px 0;
            color: #ffffff;
        }

        .top-bar .contact-info {
            display: flex;
            gap: 20px;
        }

        .top-bar .contact-info i {
            color: #E81C2E;
            margin-right: 5px;
        }

        .main-header {
            background: linear-gradient(rgba(9, 30, 62, 0.9), rgba(9, 30, 62, 0.9)), url('img/carousel-1.jpg') center/cover;
            padding: 100px 0;
            text-align: center;
            color: #ffffff;
        }

        .main-header h1 {
            font-size: 2.5rem;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .breadcrumb {
            display: flex;
            justify-content: center;
            background: transparent;
            margin: 0;
        }

        .breadcrumb-item a {
            color: #E81C2E;
            text-decoration: none;
        }

        .breadcrumb-item.active {
            color: #ffffff;
        }

        /* Footer Styles */
        .footer {
            background: #091E3E;
            color: #ffffff;
            padding: 60px 0 0;
        }

        .footer h4 {
            color: #ffffff;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .footer .contact-info p {
            margin-bottom: 10px;
        }

        .footer .contact-info i {
            color: #E81C2E;
            margin-right: 10px;
            width: 20px;
        }

        .footer-links a {
            color: #ffffff;
            text-decoration: none;
            display: block;
            margin-bottom: 10px;
            transition: all 0.3s;
        }

        .footer-links a:hover {
            color: #E81C2E;
            padding-left: 5px;
        }

        .footer-links a i {
            margin-right: 10px;
            color: #E81C2E;
        }

        .footer-bottom {
            padding: 25px 0;
            margin-top: 30px;
            border-top: 1px solid rgba(256, 256, 256, .1);
        }

        .social-links a {
            display: inline-flex;
            width: 35px;
            height: 35px;
            background: #E81C2E;
            color: #ffffff;
            margin-right: 10px;
            text-align: center;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }

        .social-links a:hover {
            background: #ffffff;
            color: #E81C2E;
        }
    </style>
</head>
<body>
    <!-- Top Bar Start -->
    <div class="top-bar">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 contact-info">
                    <span><i class="fa fa-phone-alt"></i>+012 345 6789</span>
                    <span><i class="fa fa-envelope"></i>info@carcare.com</span>
                </div>
                <div class="col-lg-6 text-end">
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Top Bar End -->

    <!-- Header Start -->
    <div class="main-header">
        <div class="container">
            <h1>Booking Details</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb justify-content-center">
                    <li class="breadcrumb-item"><a href="index.php">Home</a></li>
                    <li class="breadcrumb-item"><a href="service_history.php">Service History</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Booking Details</li>
                </ol>
            </nav>
        </div>
    </div>
    <!-- Header End -->

    <!-- Booking Details Start -->
    <div class="container-fluid py-5">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                            <h4 class="mb-0">Booking #<?php echo $booking['id']; ?></h4>
                            <a href="service_history.php" class="btn btn-light">
                                <i class="fa fa-arrow-left"></i> Back to Service History
                            </a>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Customer Information</h5>
                                    <p><strong>Name:</strong> <?php echo htmlspecialchars($booking['username']); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['Email'] ?? 'N/A'); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($booking['phone'] ?? 'N/A'); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-3">Vehicle Information</h5>
                                    <p><strong>Vehicle Type:</strong> <?php echo htmlspecialchars($booking['vehicle_type']); ?></p>
                                    <p><strong>Vehicle Number:</strong> <?php echo htmlspecialchars($booking['vehicle_number']); ?></p>
                                    <p><strong>Service Type:</strong> <?php echo htmlspecialchars($booking['service_type']); ?></p>
                                </div>
                            </div>
                            <hr>
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="mb-3">Booking Information</h5>
                                    <p><strong>Date:</strong> <?php echo date('M d, Y', strtotime($booking['booking_date'])); ?></p>
                                    <p><strong>Time:</strong> <?php echo date('h:i A', strtotime($booking['booking_time'])); ?></p>
                                    <p><strong>Status:</strong> 
                                        <span class="badge <?php 
                                            echo match($booking['status']) {
                                                'Pending' => 'bg-warning',
                                                'Confirmed' => 'bg-success',
                                                'in_progress' => 'bg-primary',
                                                'completed' => 'bg-success',
                                                default => 'bg-secondary'
                                            };
                                        ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                    </p>
                                </div>
                                <div class="col-md-6">
                                    <h5 class="mb-3">Additional Notes</h5>
                                    <p><?php echo htmlspecialchars($booking['special_request'] ?? 'No additional notes'); ?></p>
                                </div>
                            </div>
                            <?php if ($booking['status'] !== 'completed'): ?>
                            <hr>
                            <div class="row">
                                <div class="col-12">
                                    <h5 class="mb-3">Update Status</h5>
                                    <form action="update_status.php" method="POST" class="d-flex gap-2">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <select name="status" class="form-select w-auto">
                                            <option value="Pending" <?php echo $booking['status'] == 'Pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="Confirmed" <?php echo $booking['status'] == 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="completed" <?php echo $booking['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                        </select>
                                        <button type="submit" class="btn btn-primary">Update Status</button>
                                    </form>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Booking Details End -->

    <!-- Footer Start -->
    <div class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <h4>Get In Touch</h4>
                    <div class="contact-info">
                        <p><i class="fa fa-map-marker-alt"></i>123 Street, New York, USA</p>
                        <p><i class="fa fa-phone-alt"></i>+012 345 67890</p>
                        <p><i class="fa fa-envelope"></i>info@carcare.com</p>
                        <div class="social-links mt-4">
                            <a href="#"><i class="fab fa-facebook-f"></i></a>
                            <a href="#"><i class="fab fa-twitter"></i></a>
                            <a href="#"><i class="fab fa-instagram"></i></a>
                            <a href="#"><i class="fab fa-linkedin-in"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h4>Quick Links</h4>
                    <div class="footer-links">
                        <a href="index.php"><i class="fa fa-angle-right"></i>Home</a>
                        <a href="about.php"><i class="fa fa-angle-right"></i>About Us</a>
                        <a href="services.php"><i class="fa fa-angle-right"></i>Our Services</a>
                        <a href="booking.php"><i class="fa fa-angle-right"></i>Book Service</a>
                        <a href="contact.php"><i class="fa fa-angle-right"></i>Contact Us</a>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <h4>Opening Hours</h4>
                    <div class="footer-links">
                        <a href="#"><i class="far fa-clock"></i>Monday: 9:00 AM - 9:00 PM</a>
                        <a href="#"><i class="far fa-clock"></i>Tuesday: 9:00 AM - 9:00 PM</a>
                        <a href="#"><i class="far fa-clock"></i>Wednesday: 9:00 AM - 9:00 PM</a>
                        <a href="#"><i class="far fa-clock"></i>Thursday: 9:00 AM - 9:00 PM</a>
                        <a href="#"><i class="far fa-clock"></i>Friday: 9:00 AM - 9:00 PM</a>
                        <a href="#"><i class="far fa-clock"></i>Saturday: 9:00 AM - 9:00 PM</a>
                        <a href="#"><i class="far fa-clock"></i>Sunday: 9:00 AM - 9:00 PM</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-bottom text-center">
            <div class="container">
                <p>&copy; <?php echo date('Y'); ?> <a href="#">CarCare</a>. All Rights Reserved.</p>
            </div>
        </div>
    </div>
    <!-- Footer End -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 