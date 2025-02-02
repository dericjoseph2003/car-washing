<?php
session_start();
include "conn.php";

// Redirect if not logged in
if(!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit();
}
// Get the logged-in username
$currentUsername = $_SESSION['username'];

// Fetch user details from the database
$sql = "SELECT Username, Email,CreatedAt, phone FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $currentUsername);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user) {
    $_SESSION['username'] = $user['Username']; // Store in session
    $_SESSION['email'] = $user['Email'];
    $_SESSION['phone'] = $user['phone'] ?? 'Not specified';
    $_SESSION['createdat'] = $user['CreatedAt'];
} else {
    $_SESSION['error'] = "User details not found!";
    header("Location: login.php");
    exit();
}

$stmt->close();
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <title>CarCare - My Profile</title>
        <meta content="width=device-width, initial-scale=1.0" name="viewport">

        <!-- Favicon -->
        <link href="img/favicon.ico" rel="icon">

        <!-- Google Font -->
        <link href="https://fonts.googleapis.com/css2?family=Barlow:wght@400;500;600;700;800;900&display=swap" rel="stylesheet"> 
        
        <!-- CSS Libraries -->
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
        <link href="lib/flaticon/font/flaticon.css" rel="stylesheet">
        <link href="lib/animate/animate.min.css" rel="stylesheet">
        <link href="lib/owlcarousel/assets/owl.carousel.min.css" rel="stylesheet">

        <!-- Template Stylesheet -->
        <link href="css/style.css" rel="stylesheet">
    </head>

    <body>
        <!-- Add this right after the body tag, before your navigation -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> <?php echo htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> <?php echo htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
        <!-- Top Bar & Navigation (copy from index.php) -->
        <!-- ... existing header and navigation code ... -->

        <!-- Profile Content Start -->
        <div class="container mt-5 mb-5">
            <div class="row">
                <div class="col-lg-8 mx-auto">
                    <div class="card">
                        <div class="card-header bg-dark text-white">
                            <h4 class="mb-0">My Profile</h4>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <!-- <img src="img/default-avatar.jpg" alt="Profile Picture" class="rounded-circle" style="width: 150px; height: 150px;"> -->
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

                            <div class="mt-4">
                                <h5>Recent Services</h5>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Service</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td colspan="3" class="text-center">No services yet</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="text-center mt-4">
                                <button type="button" class="btn btn-custom" data-toggle="modal" data-target="#editProfileModal">Edit Profile</button>
                                <a href="#" class="btn btn-custom">Book New Service</a>
                                <button type="button" class="btn btn-custom" data-toggle="modal" data-target="#changePasswordModal">Change Password</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Profile Content End -->

        <!-- Change Password Modal -->
        <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-labelledby="changePasswordModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="changePasswordModalLabel">Change Password</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="changePasswordForm" action="change_password.php" method="POST">
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="currentPassword">Current Password</label>
                                <input type="password" class="form-control" id="currentPassword" name="currentPassword" required>
                            </div>
                            <div class="form-group">
                                <label for="newPassword">New Password</label>
                                <input type="password" class="form-control" id="newPassword" name="newPassword" required>
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirm New Password</label>
                                <input type="password" class="form-control" id="confirmPassword" name="confirmPassword" required>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-custom">Change Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer Start -->
        <!-- ... existing footer code ... -->

        <!-- Edit Profile Modal -->
<div class="modal fade" id="editProfileModal" tabindex="-1" role="dialog" aria-labelledby="editProfileModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editProfileModalLabel">Edit Profile</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="update_profile.php" method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="newUsername">Username</label>
                        <input type="text" class="form-control" id="newUsername" name="newUsername" value="<?php echo htmlspecialchars($_SESSION['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="newEmail">Email</label>
                        <input type="email" class="form-control" id="newEmail" name="newEmail" value="user@example.com" required>
                    </div>
                    <div class="form-group">
                        <label for="newPhone">Phone Number</label>
                        <input type="text" class="form-control" id="newPhone" name="newPhone" value="Not specified">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-custom">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>



        <!-- JavaScript Libraries -->
        <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
        <script src="lib/easing/easing.min.js"></script>
        <script src="lib/owlcarousel/owl.carousel.min.js"></script>
        <script src="lib/waypoints/waypoints.min.js"></script>
        <script src="lib/counterup/counterup.min.js"></script>
        
        <!-- Template Javascript -->
        <script src="js/main.js"></script>

        <!-- Add this before the closing body tag -->
        <script>
            // Auto close alerts after 5 seconds
            window.setTimeout(function() {
                $(".alert").fadeTo(500, 0).slideUp(500, function(){
                    $(this).remove(); 
                });
            }, 5000);
        </script>
    </body>
</html> 