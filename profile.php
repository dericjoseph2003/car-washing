<?php
session_start();
include "conn.php";

// Redirect if not logged in
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}

// Get the logged-in username
$currentUsername = $_SESSION['username'];



// Fetch user details from the database
$sql = "SELECT Username, Email, CreatedAt, phone FROM users WHERE Username = '$currentUsername'";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Query failed: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($result);

if ($user) {
    $_SESSION['username'] = $user['Username']; 
    $_SESSION['email'] = $user['Email'];
    $_SESSION['phone'] = $user['phone'] ?? 'Not specified';
    $_SESSION['createdat'] = $user['CreatedAt'];
} else {
    $_SESSION['error'] = "User details not found!";
    header("Location: login.php");
    exit();
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>CarCare - My Profile</title>
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <link href="img/favicon.ico" rel="icon">
    <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"> 
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>

<body>

<!-- Header with Navigation -->
<header class="bg-dark text-white p-3">
    <div class="container d-flex justify-content-between align-items-center">
        <h2>Car Care Services</h2>
        <nav>
            <a href="index.php" class="text-white mx-3">Home</a>
            <a href="services.php" class="text-white mx-3">Services</a>
            <a href="bookings.php" class="text-white mx-3">My Bookings</a>
            <a href="profile.php" class="text-white mx-3">Profile</a>
            <a href="logout.php" class="btn btn-danger">Logout</a>
        </nav>
    </div>
</header>

<!-- Success and Error Messages -->
<div class="container mt-4">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>
</div>

<!-- Profile Content -->
<div class="container mt-5 mb-5">
    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-header bg-dark text-white">
                    <h4 class="mb-0">My Profile</h4>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <h3 class="mt-3"><?php echo htmlspecialchars($_SESSION['username']); ?></h3>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h5>Personal Information</h5>
                            <ul class="list-unstyled">
                                <li><strong>Username:</strong> <?php echo htmlspecialchars($_SESSION['username']); ?></li>
                                <li><strong>Email:</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></li>
                                <li><strong>Phone:</strong> <?php echo htmlspecialchars($_SESSION['phone']); ?></li>
                                <li><strong>Member Since:</strong> <?php echo htmlspecialchars($_SESSION['createdat']); ?></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5>Car Information</h5>
                            <ul class="list-unstyled">
                                <li><strong>Car Model:</strong> Not specified</li>
                                <li><strong>Year:</strong> Not specified</li>
                                <li><strong>Last Service:</strong> Not specified</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-4 text-center">
                        <a href="update_profile.php">
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editProfileModal">Edit Profile</button>
                        </a>
                        
                        <!-- <a href="book_service.php" class="btn btn-success">Book New Service</a> -->
                        <a href="change_password.php">
                        <button type="button" class="btn btn-warning" data-toggle="modal" data-target="#changePasswordModal">Change Password</button>

                        </a>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Footer -->
<footer class="bg-dark text-white text-center p-3 mt-5">
    <p>&copy; <?php echo date("Y"); ?> Car Care Services. All Rights Reserved.</p>
</footer>

<!-- JavaScript Libraries -->
<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>

<!-- Auto-close Alerts -->
<script>
    window.setTimeout(function() {
        $(".alert").fadeTo(500, 0).slideUp(500, function(){
            $(this).remove(); 
        });
    }, 5000);
</script>

</body>
</html>
