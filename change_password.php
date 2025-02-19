<?php
session_start();
require_once "conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $currentPassword = trim($_POST['currentPassword']);
    $newPassword = trim($_POST['newPassword']);
    $confirmPassword = trim($_POST['confirmPassword']);
    $username = $_SESSION['username'];
    
    // Server-side validation
    $errors = [];
    
    // Check if fields are empty
    if (empty($currentPassword)) {
        $errors[] = "Current password is required";
    }
    if (empty($newPassword)) {
        $errors[] = "New password is required";
    }
    if (empty($confirmPassword)) {
        $errors[] = "Confirm password is required";
    }

    // Password validation rules
    if (strlen($newPassword) < 8) {
        $errors[] = "Password must be at least 8 characters long";
    }
    if (!preg_match("/[A-Z]/", $newPassword)) {
        $errors[] = "Password must contain at least one uppercase letter";
    }
    if (!preg_match("/[a-z]/", $newPassword)) {
        $errors[] = "Password must contain at least one lowercase letter";
    }
    if (!preg_match("/[0-9]/", $newPassword)) {
        $errors[] = "Password must contain at least one number";
    }
    if (!preg_match("/[!@#$%^&*()\-_=+{};:,<.>]/", $newPassword)) {
        $errors[] = "Password must contain at least one special character";
    }
    
    // Verify passwords match
    if ($newPassword !== $confirmPassword) {
        $errors[] = "New passwords do not match!";
    }

    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header("Location: change_password.php");
        exit();
    }
    
    // Get current password from database
    $sql = "SELECT PasswordHash FROM users WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    
    // Check if prepare failed
    if ($stmt === false) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: profile.php");
        exit();
    }
    
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['error'] = "User not found!";
        header("Location: profile.php");
        exit();
    }
    
    $user = $result->fetch_assoc();
    
    // Verify current password
    if (!password_verify($currentPassword, $user['PasswordHash'])) {
        $_SESSION['error'] = "Current password is incorrect!";
        header("Location: profile.php");
        exit();
    }
    
    // Hash new password
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    
    // Update password in database
    $sql = "UPDATE users SET PasswordHash = ? WHERE Username = ?";
    $stmt = $conn->prepare($sql);
    
    // Check if prepare failed
    if ($stmt === false) {
        $_SESSION['error'] = "Database error: " . $conn->error;
        header("Location: profile.php");
        exit();
    }
    
    $stmt->bind_param("ss", $hashedPassword, $username);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Password successfully changed!";
    } else {
        $_SESSION['error'] = "Error changing password: " . $stmt->error;
    }
    
    $stmt->close();
    header("Location: profile.php");
    exit();
}
?> 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        /* General Styles */
body {
    background: #f4f7f9;
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 500px;
    margin: 60px auto 60px auto;
    padding: 20px 30px;
    background: #ffffff;
    border-radius: 10px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
    min-height: calc(100vh - 120px);
    position: relative;
}

/* Heading */
h2 {
    text-align: center;
    margin-bottom: 30px;
    color: #333;
    font-size: 1.8rem;
    font-weight: 600;
}

/* Form Group */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
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

/* Button Styles */
.btn-primary {
    display: block;
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

/* Alert Styles */
.alert {
    padding: 12px 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    font-size: 0.95rem;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Responsive */
@media (max-width: 576px) {
    .container {
        margin: 20px;
        padding: 15px;
    }
}

/* Header Styles */
.header {
    background: #1e3c72;
    color: white;
    padding: 0.5rem;
    text-align: center;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 100;
}

.header h1 {
    font-size: 1.5rem;
    margin: 0;
}

/* Footer Styles */
.footer {
    background: #1e3c72;
    color: white;
    padding: 0.5rem;
    text-align: center;
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
}

.footer p {
    margin: 0;
    font-size: 0.9rem;
}
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const newPassword = document.getElementById('newPassword');
            const confirmPassword = document.getElementById('confirmPassword');
            
            function validatePassword(password) {
                const minLength = password.length >= 8;
                const hasUpperCase = /[A-Z]/.test(password);
                const hasLowerCase = /[a-z]/.test(password);
                const hasNumbers = /[0-9]/.test(password);
                const hasSpecialChar = /[!@#$%^&*()\-_=+{};:,<.>]/.test(password);
                
                return {
                    isValid: minLength && hasUpperCase && hasLowerCase && hasNumbers && hasSpecialChar,
                    errors: {
                        minLength: !minLength ? 'Password must be at least 8 characters long' : '',
                        hasUpperCase: !hasUpperCase ? 'Password must contain at least one uppercase letter' : '',
                        hasLowerCase: !hasLowerCase ? 'Password must contain at least one lowercase letter' : '',
                        hasNumbers: !hasNumbers ? 'Password must contain at least one number' : '',
                        hasSpecialChar: !hasSpecialChar ? 'Password must contain at least one special character' : ''
                    }
                };
            }

            function showError(input, message) {
                const formGroup = input.closest('.form-group');
                let errorDiv = formGroup.querySelector('.error-message');
                
                if (!errorDiv) {
                    errorDiv = document.createElement('div');
                    errorDiv.className = 'error-message';
                    errorDiv.style.color = '#dc3545';
                    errorDiv.style.fontSize = '0.875rem';
                    errorDiv.style.marginTop = '5px';
                    formGroup.appendChild(errorDiv);
                }
                
                errorDiv.textContent = message;
                input.style.borderColor = '#dc3545';
            }

            function clearError(input) {
                const formGroup = input.closest('.form-group');
                const errorDiv = formGroup.querySelector('.error-message');
                if (errorDiv) {
                    errorDiv.remove();
                }
                input.style.borderColor = '#e1e1e1';
            }

            newPassword.addEventListener('input', function() {
                const validation = validatePassword(this.value);
                clearError(this);
                
                if (!validation.isValid) {
                    const errors = Object.values(validation.errors).filter(error => error !== '');
                    if (errors.length > 0) {
                        showError(this, errors[0]);
                    }
                }
            });

            confirmPassword.addEventListener('input', function() {
                clearError(this);
                if (this.value !== newPassword.value) {
                    showError(this, 'Passwords do not match');
                }
            });

            form.addEventListener('submit', function(e) {
                let hasErrors = false;
                
                // Validate current password
                if (!document.getElementById('currentPassword').value.trim()) {
                    showError(document.getElementById('currentPassword'), 'Current password is required');
                    hasErrors = true;
                }

                // Validate new password
                const validation = validatePassword(newPassword.value);
                if (!validation.isValid) {
                    const errors = Object.values(validation.errors).filter(error => error !== '');
                    if (errors.length > 0) {
                        showError(newPassword, errors[0]);
                        hasErrors = true;
                    }
                }

                // Validate confirm password
                if (confirmPassword.value !== newPassword.value) {
                    showError(confirmPassword, 'Passwords do not match');
                    hasErrors = true;
                }

                if (hasErrors) {
                    e.preventDefault();
                }
            });
        });
    </script>
</head>
<body>
    <div class="header">
        <h1>Change Password</h1>
    </div>

    <div class="container">
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <form action="change_password.php" method="POST">
            <div class="form-group">
                <label for="currentPassword">Current Password</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="currentPassword" 
                    name="currentPassword" 
                    placeholder="Enter your current password" 
                    required>
            </div>
            <div class="form-group">
                <label for="newPassword">New Password</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="newPassword" 
                    name="newPassword" 
                    placeholder="Enter your new password" 
                    required>
            </div>
            <div class="form-group">
                <label for="confirmPassword">Confirm New Password</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="confirmPassword" 
                    name="confirmPassword" 
                    placeholder="Confirm your new password" 
                    required>
            </div>
            <button type="submit" class="btn btn-primary">Change Password</button>
        </form>
    </div>

    <div class="footer">
        <p>&copy; <?php echo date("Y"); ?> Your Website Name. All rights reserved.</p>
    </div>

    <!-- Bootstrap JS and dependencies (optional, for functionality such as alerts) -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.bundle.min.js"></script>
</body>
</html>