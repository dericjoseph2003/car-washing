<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require 'conn.php'; // Include database connection

    $username = htmlspecialchars(trim($_POST['username']));
    $password = htmlspecialchars(trim($_POST['password']));

    // Fetch user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['PasswordHash'])) {
        $_SESSION['username'] = $user['Username'];
        header("Location: index.php"); // Redirect to dashboard
        exit;
    } else {
        // Redirect back to login with error
        header("Location: login.php?error=user_not_found");
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
    </style>
</head>
<body>
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

        <?php if (isset($_GET['error']) && $_GET['error'] == 'user_not_found'): ?>
            <div class="error-message" style="color: #ff3333; text-align: center; margin-bottom: 1rem;">
                User not found. Please check your credentials and try again.
            </div>
        <?php endif; ?>

        <form class="loginform" action="login.php" method="POST">
            <div>
                <input type="text" name="username" placeholder="Username" required>
            </div>
            <div>
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <button type="submit" class="login-button">Sign In</button>
        </form>
        <div class="forgot-password">
            <a href="/forgot-password">Forgot Password?</a>
        </div>
        <div class="social-login">
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
        </div>
    </div>
</body>
</html>
