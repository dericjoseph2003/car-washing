<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require 'conn.php'; // Include database connection

    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));

    // Admin credentials (Hardcoded)
    $admin_email = "admin@example.com";
    $admin_password = "Admin@123"; // Store a hashed password for better security

    // Check if the login is for admin
    if ($email === $admin_email && $password === $admin_password) {
        $_SESSION['username'] = "Admin";
        $_SESSION['role'] = "admin";
        header("Location: admindash.php");
        exit;
    }

    // Check staff login first
    $stmt = $conn->prepare("SELECT * FROM staff WHERE email = ? AND active = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $staff = $result->fetch_assoc();

    if ($staff && password_verify($password, $staff['password'])) {
        $_SESSION['username'] = $staff['name'];
        $_SESSION['role'] = $staff['role'];
        $_SESSION['staff_id'] = $staff['id'];
        header("Location: staffdash.php");
        exit;
    }

    // If not staff, check regular user
    $stmt = $conn->prepare("SELECT * FROM users WHERE Email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['PasswordHash'])) {
        $_SESSION['username'] = $user['Username'];
        $_SESSION['role'] = "user";
        header("Location: index.php");
        exit;
    } else {
        header("Location: login.php?error=invalid_credentials");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Car Care Services</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        /* Global Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #1B2133 0%, #2a3552 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Login Container */
        .login-container {
            background: rgba(255, 255, 255, 0.98);
            padding: 3.5rem 2.5rem;
            border-radius: 24px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 420px;
            transform: translateY(0);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .login-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
        }

        /* Header Styles */
        .login-header {
            text-align: center;
            margin-bottom: 2.5rem;
        }

        .login-header h1 {
            color: #1e3c72;
            font-size: 2.2rem;
            margin-bottom: 0.8rem;
            font-weight: 600;
            letter-spacing: -0.5px;
        }

        .login-header p {
            color: #666;
            font-size: 1rem;
            line-height: 1.5;
        }

        /* Form Styles */
        .loginform {
            width: 100%;
            padding: 10px;
        }

        .loginform div {
            margin-bottom: 1.8rem;
            position: relative;
        }

        .loginform input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e1e1;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            letter-spacing: 0.5px;
        }

        .loginform input[type="password"] {
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
            font-size: 1.1rem;
            padding-right: 45px;]]
        }

        .loginform input:focus {
            border-color: #1e3c72;
            outline: none;
            box-shadow: 0 4px 15px rgba(30, 60, 114, 0.15);
            transform: translateY(-1px);
        }

        .loginform input::placeholder {
            color: #999;
            font-size: 0.95rem;
            font-weight: 300;
        }

        .loginform button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-size: 1.1rem;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: all 0.3s ease;
            margin-top: 1.5rem;
            box-shadow: 0 4px 15px rgba(30, 60, 114, 0.2);
        }

        .loginform button:hover {
            background: linear-gradient(135deg, #2a5298 0%, #1e3c72 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(27, 33, 51, 0.3);
        }

        .loginform button:active {
            transform: translateY(0);
        }

        /* Forgot Password Styles */
        .forgot-password {
            text-align: center;
            margin-top: 1.8rem;
        }

        .forgot-password a {
            color: #1e3c72;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .forgot-password a:hover {
            color: #2a5298;
            text-decoration: underline;
        }

        /* Social Login Styles */
        .social-login {
            margin-top: 2.5rem;
            text-align: center;
            padding-top: 1.5rem;
            border-top: 1px solid #eee;
        }

        .social-login p {
            color: #666;
            font-size: 0.95rem;
            margin-bottom: 1.2rem;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            gap: 1.2rem;
        }

        .social-icons a {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }

        .social-icons a:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            background: white;
        }

        .social-icons img {
            width: 22px;
            height: 22px;
            opacity: 0.8;
            transition: opacity 0.3s ease;
        }

        .social-icons a:hover img {
            opacity: 1;
        }

        /* User Info Styles */
        .user-info {
            position: fixed;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.95);
            padding: 10px 20px;
            border-radius: 12px;
            color: #1B2133;
            font-size: 0.95rem;
            font-weight: 500;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Error Message Styles */
        .error-message {
            background: #fff1f1;
            color: #ff3333;
            text-align: center;
            padding: 12px;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
            border: 1px solid #ffe0e0;
        }

        /* Add these new styles */
        .home-button {
            position: fixed;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.95);
            padding: 10px 20px;
            border-radius: 12px;
            color: #1B2133;
            font-size: 0.95rem;
            font-weight: 500;
            text-decoration: none;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .home-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15);
        }
    </style>
</head>
<body>
    <a href="index.php" class="home-button">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M8.707 1.5a1 1 0 0 0-1.414 0L.646 8.146a.5.5 0 0 0 .708.708L2 8.207V13.5A1.5 1.5 0 0 0 3.5 15h9a1.5 1.5 0 0 0 1.5-1.5V8.207l.646.647a.5.5 0 0 0 .708-.708L13 5.793V2.5a.5.5 0 0 0-.5-.5h-1a.5.5 0 0 0-.5.5v1.293L8.707 1.5ZM13 7.207V13.5a.5.5 0 0 1-.5.5h-9a.5.5 0 0 1-.5-.5V7.207l5-5 5 5Z"/>
        </svg>
    </a>
    
    <?php if(isset($_SESSION['username'])): ?>
        <div class="user-info">
            Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>
        </div>
    <?php endif; ?>

    <div class="login-container">
        <div class="login-header">
            <h1>Car Care Services</h1>
            <p>Welcome back! Please login to continue.</p>
        </div>

        <?php if (isset($_GET['error']) && $_GET['error'] == 'invalid_credentials'): ?>
            <div class="error-message" style="color: #ff3333; text-align: center; margin-bottom: 1rem;">
                Invalid credentials. Please check your email and password and try again.
            </div>
        <?php endif; ?>

        <form class="loginform" action="login.php" method="POST">
            <div>
                <input type="text" name="email" id="emailInput" placeholder="Email" required 
                    oninput="validateEmail(this)" pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$">
                <span id="emailError" style="color: #ff3333; font-size: 0.8rem; display: none; margin-top: 5px;"></span>
            </div>
            <div>
                <input type="password" name="password" id="passwordInput" placeholder="Password" required 
                    oninput="validatePassword(this)" minlength="6">
                <span id="passwordError" style="color: #ff3333; font-size: 0.8rem; display: none; margin-top: 5px;"></span>
            </div>
            <button type="submit" class="login-button">Sign In</button>
        </form>
        <div class="forgot-password">
            <a href="forgot_pasword.php">Forgot Password?</a>
        </div>
        <!-- <div class="social-login">
            <p>Or continue with</p>
            <div class="social-icons">
                <a href="#" title="Login with Google">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google">
                </a>
                <a href="#" title="Login with Facebook">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/51/Facebook_f_logo_%282019%29.svg" alt="Facebook">
                </a>
                <a href="#" title="Login with Apple">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg" alt="Apple">
                </a>
            </div>
        </div> -->
    </div>
    <script>
        function validateEmail(input) {
            const emailRegex = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            const errorElement = document.getElementById('emailError');
            
            if (input.value.length === 0) {
                errorElement.style.display = 'none';
                input.style.borderColor = '#e1e1e1';
            } else if (!emailRegex.test(input.value)) {
                errorElement.textContent = 'Please enter a valid email address';
                errorElement.style.display = 'block';
                input.style.borderColor = '#ff3333';
            } else {
                errorElement.style.display = 'none';
                input.style.borderColor = '#4CAF50';
            }
        }

        function validatePassword(input) {
            const errorElement = document.getElementById('passwordError');
            
            if (input.value.length === 0) {
                errorElement.style.display = 'none';
                input.style.borderColor = '#e1e1e1';
            } else if (input.value.length < 6) {
                errorElement.textContent = 'Password must be at least 6 characters long';
                errorElement.style.display = 'block';
                input.style.borderColor = '#ff3333';
            } else {
                errorElement.style.display = 'none';
                input.style.borderColor = '#4CAF50';
            }
        }
    </script>
</body>
</html>
