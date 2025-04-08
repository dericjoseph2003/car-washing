<?php
session_start();
require_once 'conn.php';

// Simply remove the admin check temporarily to see if the rest of the page works
// We'll uncomment and fix it after testing

/*
// Check if admin is logged in
if (!isset($_SESSION['admin'])) {
    header("Location: admin_login.php");
    exit();
}
*/

// Fetch feedbacks with user and booking details
$query = "SELECT f.*, u.Username, b.service_type, b.vehicle_type, b.booking_date 
          FROM feedback f 
          LEFT JOIN users u ON f.user_id = u.UserID 
          LEFT JOIN booking b ON f.booking_id = b.id 
          ORDER BY f.created_at DESC";
$result = $conn->query($query);

// Calculate average rating
$avg_query = "SELECT AVG(rating) as avg_rating, COUNT(*) as total_feedbacks FROM feedback";
$avg_result = $conn->query($avg_query);
$rating_stats = $avg_result->fetch_assoc();
$average_rating = number_format($rating_stats['avg_rating'], 1);
$total_feedbacks = $rating_stats['total_feedbacks'];

// Get rating distribution
$rating_dist_query = "SELECT rating, COUNT(*) as count 
                     FROM feedback 
                     GROUP BY rating 
                     ORDER BY rating DESC";
$rating_dist_result = $conn->query($rating_dist_query);
$rating_distribution = [];
while ($row = $rating_dist_result->fetch_assoc()) {
    $rating_distribution[$row['rating']] = $row['count'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Feedback - Car Wash Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Sidebar Styles */
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

        /* Main Content Styles */
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        .feedback-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }

        .feedback-card:hover {
            transform: translateY(-5px);
        }

        .feedback-header {
            padding: 15px 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .feedback-body {
            padding: 20px;
        }

        .rating {
            color: #ffc107;
            font-size: 20px;
        }

        .service-tag {
            background: #e3f2fd;
            color: #1976d2;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
        }

        .feedback-date {
            color: #666;
            font-size: 0.9rem;
        }

        .feedback-text {
            margin-top: 15px;
            color: #333;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .sidebar {
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

        /* Add to your existing styles */
        .stats-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .rating-bar {
            height: 10px;
            background: #e9ecef;
            border-radius: 5px;
            margin: 5px 0;
        }

        .rating-bar-fill {
            height: 100%;
            background: #ffc107;
            border-radius: 5px;
            transition: width 0.3s ease;
        }

        .average-rating {
            font-size: 48px;
            font-weight: bold;
            color: #1e3c72;
        }

        .filter-btn {
            padding: 8px 15px;
            border: 1px solid #dee2e6;
            border-radius: 20px;
            background: white;
            color: #666;
            margin: 0 5px;
            transition: all 0.3s ease;
        }

        .filter-btn.active {
            background: #1e3c72;
            color: white;
            border-color: #1e3c72;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="logo">
            <h3>CarCare Admin</h3>
        </div>
        <ul class="nav-menu">
            <li class="nav-section">
                <span class="nav-section-title">Main</span>
            </li>
            <li>
                <a href="admindash.php" class="nav-item">
                    <i class="fas fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>
            <li>
                <a href="bookings.php" class="nav-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Bookings</span>
                </a>
            </li>
            <li class="active">
                <a href="feedback.php" class="nav-item">
                    <i class="fas fa-comments"></i>
                    <span>Feedback</span>
                </a>
            </li>
            <li>
                <a href="service_prices.php" class="nav-item">
                    <i class="fas fa-tags"></i>
                    <span>Service Prices</span>
                </a>
            </li>
            <li>
                <a href="staff.php" class="nav-item">
                    <i class="fas fa-user-tie"></i>
                    <span>Staff Management</span>
                </a>
            </li>
            <li>
                <a href="adminprofile.php" class="nav-item">
                    <i class="fas fa-user-circle"></i>
                    <span>Profile</span>
                </a>
            </li>
            <li>
                <a href="logout.php" class="nav-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </li>
        </ul>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid">
            <!-- Statistics Section -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="stats-card text-center">
                        <div class="average-rating mb-2"><?php echo $average_rating; ?></div>
                        <div class="rating mb-2">
                            <?php
                            $full_stars = floor($average_rating);
                            $half_star = $average_rating - $full_stars >= 0.5;
                            
                            for ($i = 1; $i <= 5; $i++) {
                                if ($i <= $full_stars) {
                                    echo '<i class="fas fa-star"></i>';
                                } elseif ($i == $full_stars + 1 && $half_star) {
                                    echo '<i class="fas fa-star-half-alt"></i>';
                                } else {
                                    echo '<i class="far fa-star"></i>';
                                }
                            }
                            ?>
                        </div>
                        <div class="text-muted">Based on <?php echo $total_feedbacks; ?> reviews</div>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="stats-card">
                        <h5 class="mb-3">Rating Distribution</h5>
                        <?php for ($i = 5; $i >= 1; $i--): ?>
                            <div class="d-flex align-items-center mb-2">
                                <div style="width: 60px">
                                    <?php echo $i; ?> stars
                                </div>
                                <div class="flex-grow-1 mx-2">
                                    <div class="rating-bar">
                                        <div class="rating-bar-fill" style="width: <?php 
                                            echo isset($rating_distribution[$i]) ? 
                                                ($rating_distribution[$i] / $total_feedbacks * 100) : 0;
                                        ?>%"></div>
                                    </div>
                                </div>
                                <div style="width: 40px">
                                    <?php echo isset($rating_distribution[$i]) ? $rating_distribution[$i] : 0; ?>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="row mb-4">
                <div class="col">
                    <div class="d-flex align-items-center">
                        <h2 class="me-4"><i class="fas fa-comments me-2"></i>Customer Feedback</h2>
                        <div class="d-flex">
                            <button class="filter-btn active" data-rating="all">All</button>
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                <button class="filter-btn" data-rating="<?php echo $i; ?>">
                                    <?php echo $i; ?> Stars
                                </button>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Feedback Cards -->
            <div class="row" id="feedback-container">
                <?php
                if ($result && $result->num_rows > 0) {
                    while($row = $result->fetch_assoc()) {
                        $rating = intval($row['rating']);
                        ?>
                        <div class="col-lg-6 mb-4 feedback-item" data-rating="<?php echo $rating; ?>">
                            <div class="feedback-card">
                                <div class="feedback-header">
                                    <div>
                                        <h5 class="mb-0"><?php echo htmlspecialchars($row['Username']); ?></h5>
                                        <small class="feedback-date">
                                            <i class="far fa-clock me-1"></i>
                                            <?php echo date('M d, Y', strtotime($row['created_at'])); ?>
                                        </small>
                                    </div>
                                    <span class="service-tag">
                                        <?php echo htmlspecialchars($row['service_type'] . ' - ' . $row['vehicle_type']); ?>
                                    </span>
                                </div>
                                <div class="feedback-body">
                                    <div class="rating mb-2">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            echo $i <= $rating ? 
                                                '<i class="fas fa-star"></i>' : 
                                                '<i class="far fa-star"></i>';
                                        }
                                        ?>
                                    </div>
                                    <p class="feedback-text"><?php echo htmlspecialchars($row['feedback_text']); ?></p>
                                </div>
                            </div>
                        </div>
                        <?php
                    }
                } else {
                    echo '<div class="col-12"><div class="alert alert-info">No feedback available yet.</div></div>';
                }
                ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Sidebar active class handling
        document.querySelectorAll('.nav-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelector('.nav-item.active')?.classList.remove('active');
                this.classList.add('active');
            });
        });

        // Feedback filtering
        document.querySelectorAll('.filter-btn').forEach(button => {
            button.addEventListener('click', function() {
                // Update active button
                document.querySelector('.filter-btn.active').classList.remove('active');
                this.classList.add('active');

                // Filter feedback items
                const rating = this.dataset.rating;
                document.querySelectorAll('.feedback-item').forEach(item => {
                    if (rating === 'all' || item.dataset.rating === rating) {
                        item.style.display = '';
                    } else {
                        item.style.display = 'none';
                    }
                });
            });
        });
    </script>
</body>
</html> 