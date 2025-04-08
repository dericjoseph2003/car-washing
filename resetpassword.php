<?php
session_start();
include 'conn.php'; // Include database connection

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header('Location: forgot_pasword.php');
    exit(); 
}

$conn = new mysqli('localhost', 'root', '', 'car_care2');
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    $error_message = "An error occurred during login. Please try again later.";
} else {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        if ($password == $confirm_password) {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            // Update using the correct column name "PasswordHash" instead of "password"
            $sql = "UPDATE users SET PasswordHash = '$hashed_password' WHERE Email = '" . $_SESSION['email'] . "'";
            
            if ($conn->query($sql) === TRUE) {
                $_SESSION['success_message'] = "Your password has been successfully updated!";
                header('Location: login.php');
                unset($_SESSION['email']);  
                exit();
            } else {
                $error_message = "Error updating password: " . $conn->error;
            }
        } else {
            $error_message = "Passwords do not match.";
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarCare- Reset Password</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .login-container {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            animation: slideUp 0.5s ease-out;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            font-weight: 700;
        }

        .task { color:  #1e3c72; }
        .mate { color:  #1e3c72; }

        h2 {
            text-align: center;
            color: #1e293b;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #64748b;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.9rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color:  #1e3c72;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .login-btn {
            width: 100%;
            padding: 1rem;
            background:  #1e3c72;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            background:  #1e3c72;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .error-message {
            color: #dc2626;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            font-weight: 400;
        }

        .error-message1 {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <span class="task">Car</span><span class="mate">Care</span>
        </div>
        <h2>Reset Password</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error-message1">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form id="resetForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required minlength="6">
                <div id="password-error" class="error-message"></div> <!-- Error message for password -->
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                <div id="cpassword-error" class="error-message"></div> <!-- Error message for confirm password -->
            </div>
            <button type="submit" class="login-btn">Reset Password</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordError = document.getElementById('password-error');
            const cpasswordError = document.getElementById('cpassword-error');

            const passwordInput = document.getElementById('new_password');
            const cpasswordInput = document.getElementById('confirm_password');

            function checkPassword() {
                const password = passwordInput.value.trim();
                if (password === '') {
                    passwordError.textContent = "Password is required";
                    passwordInput.style.border = "2px solid red";
                    return false;
                } else if (password.length < 8) {
                    passwordError.textContent = "Password must be at least 8 characters long.";
                    passwordInput.style.border = "2px solid red";
                    return false;
                } else {
                    passwordError.textContent = "";
                    passwordInput.style.border = "2px solid green";
                    return true;
                }
            }

            function checkConfirmPassword() {
                const password = passwordInput.value.trim();
                const confirmPassword = cpasswordInput.value.trim();
                if (confirmPassword === '') {
                    cpasswordError.textContent = "Please confirm your password";
                    cpasswordInput.style.border = "2px solid red";
                    return false;
                } else if (confirmPassword !== password) {
                    cpasswordError.textContent = "Passwords do not match";
                    cpasswordInput.style.border = "2px solid red";
                    return false;
                } else {
                    cpasswordError.textContent = "";
                    cpasswordInput.style.border = "2px solid green";
                    return true;
                }
            }

            passwordInput.addEventListener('input', function() {
                checkPassword();
                if (cpasswordInput.value !== '') {
                    checkConfirmPassword();
                }
            });
            cpasswordInput.addEventListener('input', checkConfirmPassword);

            document.getElementById('resetForm').addEventListener('submit', function(e) {
                e.preventDefault();
                let isValid = true;
                if (!checkPassword()) isValid = false;
                if (!checkConfirmPassword()) isValid = false;
                if (isValid) {
                    this.submit();  
                }
            });
        });
    </script>
</body>
</html>
