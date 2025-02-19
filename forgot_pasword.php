<?php
session_start();
$error_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
   
    $conn = new mysqli('localhost', 'root', '', 'car_care2');
        
    if ($conn->connect_error) {
        error_log("Connection failed: " . $conn->connect_error);
        $error_message = "An error occurred during login. Please try again later.";
    } else {
        
        $email = $conn->real_escape_string(filter_var($_POST['email'], FILTER_SANITIZE_EMAIL)); 
        $sql = "SELECT * FROM users WHERE email='$email'";
        
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            echo'<form id="form" method="POST" action="verify_otp.php">';
            echo '<input type="hidden" name="email" value="' . $email . '">';
            echo'</form>';
            echo'<script>document.getElementById("form").submit();</script>';

        } else {
            $error_message = "Invalid email";
        }
        
               $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CarCare - Forgot Password</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #f5f5f5;
        }

        .login-container {
            width: 400px;
            padding: 40px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            font-weight: 700;
        }

        .task { color: #1e3c72; }
        .mate { color: #2a5298; }

        h2 {
            color: #333;
            font-weight: 600;
            text-align: center;
            letter-spacing: 0.1em;
            margin-bottom: 1rem;
        }

        p.description {
            color: #666;
            text-align: center;
            margin-bottom: 1.5rem;
            font-size: 0.9em;
        }

        .form-group {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .form-group input {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e1e1e1;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
            letter-spacing: 0.5px;
        }

        .form-group input:focus {
            border-color: #1e3c72;
            outline: none;
            box-shadow: 0 4px 15px rgba(30, 60, 114, 0.15);
            transform: translateY(-1px);
        }

        .form-group label {
            position: absolute;
            left: 15px;
            top: -10px;
            background: white;
            padding: 0 5px;
            color: #666;
            font-size: 0.9em;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(to right, #1e3c72, #2a5298);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(30, 60, 114, 0.2);
        }

        .login-btn:active {
            transform: translateY(0);
        }

        .back-to-login {
            margin-top: 15px;
            color: #666;
            text-align: center;
        }

        .back-to-login a {
            color: #1e3c72;
            text-decoration: none;
            font-weight: 500;
        }

        .back-to-login a:hover {
            text-decoration: underline;
        }

        .error-message {
            background-color: #fff2f2;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            text-align: center;
            border: 2px solid #fecaca;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="logo">
                <span class="task">Car</span><span class="mate">Care</span>
            </div>
            <h2>Forgot Password</h2>
            <p class="description">Enter your email to receive an OTP.</p>
            <?php if (!empty($error_message)): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>
            <div class="form-group">
                <input type="email" id="email" name="email" required>
                <label for="email">Email Address</label>
            </div>
            <button type="submit" class="login-btn">Send An OTP</button>
            <p class="back-to-login">Remember your password? <a href="login.php">Login here</a></p>
        </form>
    </div>
</body>
</html>