<?php
session_start();
include 'conn.php'; // Include database connection

// If form is submitted, process the update.
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check if user is logged in
    if (!isset($_SESSION['username'])) {
        $_SESSION['error'] = "You must be logged in to update your profile.";
        header("Location: profile.php");
        exit();
    }

    // Get user input
    $newUsername = trim($_POST['newUsername']);
    $newPhone = trim($_POST['newPhone']);
    $currentUsername = $_SESSION['username']; // Current username from session

    // Validate input
    if (empty($newUsername)) {
        $_SESSION['error'] = "Username cannot be empty!";
        header("Location: profile.php");
        exit();
    }

    // Check if new username already exists for another user
    $sqlCheck = "SELECT UserID FROM users WHERE Username = ? AND Username != ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param("ss", $newUsername, $currentUsername);
    $stmtCheck->execute();
    $stmtCheck->store_result();
    if ($stmtCheck->num_rows > 0) {
        $_SESSION['error'] = "This username is already in use!";
        $stmtCheck->close();
        header("Location: profile.php");
        exit();
    }
    $stmtCheck->close();

    // Update database (updating username and phone only)
    $sql = "UPDATE users SET Username = ?, phone = ? WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $newUsername, $newPhone, $currentUsername);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Profile updated successfully!";
        // Update session with new username and phone
        $_SESSION['username'] = $newUsername;
        $_SESSION['phone'] = $newPhone;
    } else {
        $_SESSION['error'] = "Error updating profile: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();

    header("Location: profile.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Edit Profile - Car Care Services</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <!-- Bootstrap CSS for styling -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f4f7f9;
            font-family: 'Poppins', sans-serif;
        }
        .container {
            max-width: 500px;
            margin: 50px auto;
            background: #ffffff;
            padding: 20px 30px;
            border-radius: 10px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
            font-weight: 600;
        }
        .form-group label {
            font-size: 0.95rem;
            margin-bottom: 8px;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 12px 15px;
            font-size: 1rem;
            border: 2px solid #e1e1e1;
            border-radius: 6px;
            transition: border-color 0.3s ease;
        }
        .form-group input:focus {
            border-color: #1e3c72;
            outline: none;
            box-shadow: 0 0 5px rgba(30, 60, 114, 0.2);
        }
        .btn-primary {
            width: 100%;
            padding: 12px;
            background-color: #1e3c72;
            color: #fff;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s ease;
            margin-top: 10px;
        }
        .btn-primary:hover {
            background-color: #162b50;
            transform: translateY(-2px);
        }
        .btn-primary:active {
            transform: translateY(0);
        }
        /* Container for the header */
.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem;           /* Equivalent to Bootstrap's p-3 */
    background-color: #343a40; /* Dark background similar to Bootstrap's bg-dark */
    color: #ffffff;          /* White text */
}

/* Header Title */
.header-title {
    font-size: 1.75rem;
    margin: 0;
}

/* Navigation container */
.header-nav {
    display: flex;
    gap: 1.5rem;             /* Adds horizontal spacing between links */
}

/* Navigation links */
.header-link {
    color: #ffffff;
    text-decoration: none;
    font-size: 1rem;
    transition: color 0.3s ease;
}

.header-link:hover {
    text-decoration: underline;
    color: #dddddd;
}

/* Logout button styled as a link */
.header-logout {
    background-color: #dc3545;  /* Similar to Bootstrap's btn-danger */
    color: #ffffff;
    padding: 0.5rem 1rem;
    text-decoration: none;
    border-radius: 0.25rem;
    font-size: 1rem;
    transition: background-color 0.3s ease;
}

.header-logout:hover {
    background-color: #c82333;
}

    </style>

</head>
<body>
<header class="bg-dark text-white p-3">
   <div class="header-container">
    <h2 class="header-title">Car Care Services</h2>
    <nav class="header-nav">
        <a href="index.php" class="header-link">Home</a>
        <a href="services.php" class="header-link">Services</a>
        <a href="bookings.php" class="header-link">My Bookings</a>
        <a href="profile.php" class="header-link">Profile</a>
        <a href="logout.php" class="header-logout">Logout</a>
    </nav>
</div>

</header>
    <div class="container">
        <h2>Edit Profile</h2>
        
        <!-- Display any error or success messages -->
        <?php
        // Do not call session_start() again; it's already called above.
        if (isset($_SESSION['error'])) {
            echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error']) . '</div>';
            unset($_SESSION['error']);
        }
        if (isset($_SESSION['success'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success']) . '</div>';
            unset($_SESSION['success']);
        }
        ?>
        
        <form action="update_profile.php" method="POST">
            <div class="form-group">
                <label for="newUsername">New Username</label>
                <input type="text" id="newUsername" name="newUsername" class="form-control" required 
                    value="<?php echo htmlspecialchars($_SESSION['username'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label for="newPhone">New Phone Number</label>
                <input type="text" id="newPhone" name="newPhone" class="form-control" 
                    placeholder="Enter your new phone number" 
                    value="<?php echo htmlspecialchars($_SESSION['phone'] ?? ''); ?>">
            </div>
            <button type="submit" class="btn btn-primary">Save Changes</button>
        </form>
    </div>

    <!-- Bootstrap JS (optional) -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>
