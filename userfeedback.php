<?php
session_start();
require_once 'conn.php';

// Debug - check what's in the session (you can remove this later)
// echo "<pre>Session: "; print_r($_SESSION); echo "</pre>";

// Check different possible session variables
if (isset($_SESSION['Username'])) {
    $username = $_SESSION['Username'];
} elseif (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
} elseif (isset($_SESSION['user'])) {
    $username = $_SESSION['user'];
} else {
    // No username found in session, redirect to login
    header("Location: login.php");
    exit();
}

// Get user ID from username
$user_query = "SELECT UserID FROM users WHERE Username = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("s", $username);
$stmt->execute();
$user_result = $stmt->get_result();

// Check if user exists in the database
if ($user_result->num_rows === 0) {
    // Redirect to login if user not found in database
    header("Location: login.php");
    exit();
}

$user_data = $user_result->fetch_assoc();
$user_id = $user_data['UserID'];

// Fetch user's completed bookings that haven't been reviewed yet
$query = "SELECT b.* FROM booking b 
          LEFT JOIN feedback f ON b.id = f.booking_id 
          WHERE b.username = ? AND b.status = 'completed' AND f.id IS NULL";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $username);
$stmt->execute();
$bookings = $stmt->get_result();

// Handle feedback submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_feedback'])) {
    $booking_id = $_POST['booking_id'];
    $rating = $_POST['rating'];
    $feedback_text = $_POST['feedback_text'];

    $insert_query = "INSERT INTO feedback (user_id, booking_id, rating, feedback_text) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("iiis", $user_id, $booking_id, $rating, $feedback_text);
    
    if ($stmt->execute()) {
        $success_message = "Thank you for your feedback!";
    } else {
        $error_message = "Error submitting feedback. Please try again.";
    }
}

// Fetch user's previous feedbacks
$feedback_query = "SELECT f.*, b.service_type, b.vehicle_type, b.booking_date 
                  FROM feedback f 
                  JOIN booking b ON f.booking_id = b.id 
                  JOIN users u ON f.user_id = u.UserID 
                  WHERE u.Username = ? 
                  ORDER BY f.created_at DESC";
$stmt = $conn->prepare($feedback_query);
$stmt->bind_param("s", $username);
$stmt->execute();
$previous_feedbacks = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Feedback - CarCare</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .feedback-container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
        }

        .rating {
            display: flex;
            flex-direction: row-reverse;
            justify-content: center;
            gap: 10px;
        }

        .rating input {
            display: none;
        }

        .rating label {
            cursor: pointer;
            font-size: 30px;
            color: #ddd;
            transition: color 0.3s ease;
        }

        .rating label:hover,
        .rating label:hover ~ label,
        .rating input:checked ~ label {
            color: #ffc107;
        }

        .feedback-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
            padding: 20px;
        }

        .service-tag {
            background: #e3f2fd;
            color: #1976d2;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.85rem;
        }

        .previous-rating {
            color: #ffc107;
            font-size: 20px;
        }

        .header-section {
            background: linear-gradient(135deg, #1e3c72, #2a5298);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
    <!-- Header Section -->
    <div class="header-section">
        <div class="container">
            <h1 class="text-center">Service Feedback</h1>
            <p class="text-center">We value your opinion! Please share your experience with our services.</p>
        </div>
    </div>

    <div class="container feedback-container">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?php echo $error_message; ?></div>
        <?php endif; ?>

        <!-- Submit New Feedback -->
        <?php if ($bookings && $bookings->num_rows > 0): ?>
            <div class="feedback-card">
                <h3 class="mb-4">Submit Feedback</h3>
                <form method="POST" action="">
                    <div class="mb-3">
                        <label class="form-label">Select Service</label>
                        <select name="booking_id" class="form-select" required>
                            <option value="">Choose a service...</option>
                            <?php while ($booking = $bookings->fetch_assoc()): ?>
                                <option value="<?php echo $booking['id']; ?>">
                                    <?php echo htmlspecialchars($booking['service_type'] . ' - ' . 
                                          $booking['vehicle_type'] . ' (' . 
                                          date('M d, Y', strtotime($booking['booking_date'])) . ')'); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Rating</label>
                        <div class="rating mb-3">
                            <?php for($i = 5; $i >= 1; $i--): ?>
                                <input type="radio" name="rating" value="<?php echo $i; ?>" id="star<?php echo $i; ?>" required>
                                <label for="star<?php echo $i; ?>"><i class="fas fa-star"></i></label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Your Feedback</label>
                        <textarea name="feedback_text" class="form-control" rows="4" required 
                                placeholder="Please share your experience with our service..."></textarea>
                    </div>

                    <button type="submit" name="submit_feedback" class="btn btn-primary">
                        Submit Feedback
                    </button>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-info">No completed services available for feedback.</div>
        <?php endif; ?>

        <!-- Previous Feedbacks -->
        <div class="mt-5">
            <h3 class="mb-4">Your Previous Feedbacks</h3>
            <?php if ($previous_feedbacks && $previous_feedbacks->num_rows > 0): ?>
                <?php while ($feedback = $previous_feedbacks->fetch_assoc()): ?>
                    <div class="feedback-card">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="service-tag">
                                <?php echo htmlspecialchars($feedback['service_type'] . ' - ' . $feedback['vehicle_type']); ?>
                            </span>
                            <small class="text-muted">
                                <?php echo date('M d, Y', strtotime($feedback['created_at'])); ?>
                            </small>
                        </div>
                        <div class="previous-rating mb-2">
                            <?php
                            $rating = intval($feedback['rating']);
                            echo str_repeat('<i class="fas fa-star"></i>', $rating);
                            echo str_repeat('<i class="far fa-star"></i>', 5 - $rating);
                            ?>
                        </div>
                        <p class="mb-0"><?php echo htmlspecialchars($feedback['feedback_text']); ?></p>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">You haven't submitted any feedback yet.</div>
            <?php endif; ?>
        </div>

        <!-- Back to Home Button -->
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-outline-primary">
                <i class="fas fa-arrow-left me-2"></i>Back to Home
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 