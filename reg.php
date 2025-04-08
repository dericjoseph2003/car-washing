<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Car Care Services</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #1B2133;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .register-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 3rem 2rem;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            transform: translateY(0);
            transition: all 0.3s ease;
        }

        .register-container:hover {
            transform: translateY(-5px);
        }

        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .register-header h1 {
            color: #1e3c72;
            font-size: 2rem;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .register-header p {
            color: #666;
            font-size: 0.9rem;
        }

        .register-form div {
            margin-bottom: 2rem;
            position: relative;
        }

        .register-form input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .register-form input:focus {
            border-color: #1e3c72;
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
        }

        .register-button {
            width: 100%;
            padding: 12px;
            background: #1B2133;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .register-button:hover {
            background: #232b42;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(27, 33, 51, 0.3);
        }

        .register-button:active {
            transform: translateY(0);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
        }

        .login-link a {
            color: #1e3c72;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            color: #2a5298;
            text-decoration: underline;
        }

        .social-login {
            margin-top: 2rem;
            text-align: center;
        }

        .social-login p {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        .social-icons {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .social-icons a {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f5f5f5;
            transition: all 0.3s ease;
        }

        .social-icons a:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .social-icons img {
            width: 20px;
            height: 20px;
        }

        .error-message {
            color: #ff3860;
            font-size: 0.8rem;
            margin-top: 0.5rem;
            position: absolute;
        }

        /* Form Styles */
        .input-group {
            margin-bottom: 1.5rem;
            position: relative;
        }

        .input-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #1B2133;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .input-group input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e1e1e1;
            border-radius: 10px;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            background: white;
        }

        .input-group input:focus {
            border-color: #1e3c72;
            outline: none;
            box-shadow: 0 0 0 3px rgba(30, 60, 114, 0.1);
        }

        .error-text {
            color: #ff3860;
            font-size: 0.8rem;
            margin-top: 0.5rem;
            display: block;
        }

        .signup-btn {
            width: 100%;
            padding: 12px;
            background: #1B2133;
            color: white;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: all 0.3s ease;
            margin-top: 1rem;
        }

        .signup-btn:hover {
            background: #232b42;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(27, 33, 51, 0.3);
        }

        .signup-btn:active {
            transform: translateY(0);
        }

        .terms {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.85rem;
            color: #666;
        }

        .terms a {
            color: #1e3c72;
            text-decoration: none;
            font-weight: 500;
        }

        .terms a:hover {
            text-decoration: underline;
        }

        #signup-error-message {
            background-color: #fff3f3;
            color: #ff3860;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            display: none;
        }

        #signup-error-message.visible {
            display: block;
        }

        /* Form validation states */
        .input-group input.error {
            border-color: #ff3860;
        }

        .input-group input.success {
            border-color: #23d160;
        }
        #signup-error-message {
    background-color: #fff3f3;
    color: #ff3860;
    padding: 10px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-size: 0.9rem;
    text-align: center;
}

    </style>
</head>



<body>

<?php
require 'conn.php';

$errorMessage = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = htmlspecialchars(trim($_POST['username']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = htmlspecialchars(trim($_POST['password']));
    $confirm_password = htmlspecialchars(trim($_POST['confirm_password']));

    // Validate input
    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $errorMessage = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $errorMessage = "Passwords do not match.";
    } else {
        // Hash the password
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        // Insert into database
        $stmt = $conn->prepare("INSERT INTO users (Username, Email, PasswordHash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $email, $passwordHash);

        if ($stmt->execute()) {
            $successMessage = "Registration successful! You can now <a href='login.php'>log in</a>.";
        } else {
            $errorMessage = "Failed to register. Email might already exist.";
        }

        $stmt->close();
        $conn->close();
    }
}
?>





<?php if (isset($_GET['error'])): ?>
    <div class="error-message">
        <?php echo htmlspecialchars($_GET['error']); ?>
    </div>
<?php endif; ?>
    <div class="register-container">
        <div class="register-header">
        <?php if (!empty($errorMessage)): ?>
    <div id="signup-error-message">
        <?php echo $errorMessage; ?>
    </div>
<?php endif; ?>

<?php if (!empty($successMessage)): ?>
    <div id="signup-success-message" style="background-color: #e3fcef; color: #2d6a4f; padding: 10px; border-radius: 8px; text-align: center;">
        <?php echo $successMessage; ?>
    </div>
<?php endif; ?>

            <h1>Car Care Services</h1>
            <p>Create your account to get started.</p>
        </div>
        <form id="signupForm" action="" method="POST">
            <div class="input-group">
                <label for="signup-username">Name</label>
                <input type="text" id="signup-username" name="username" required>
                <span id="username-error" class="error-text"></span>
            </div>

            <div class="input-group">
                <label for="signup-email">Email</label>
                <input type="email" id="signup-email" name="email" required>
                <span id="email-error" class="error-text"></span>
            </div>

            <div class="input-group">
                <label for="signup-password">Password</label>
                <input type="password" id="signup-password" name="password" required>
                <span id="password-error" class="error-text"></span>
            </div>

            <div class="input-group">
                <label for="confirm-password">Confirm Password</label>
                <input type="password" id="confirm-password" name="confirm_password" required>
                <span id="confirm-password-error" class="error-text"></span>
            </div>

            <button type="submit" class="signup-btn">Create Account</button>
        </form>

        <div class="login-link">
            <a href="login.php">Already have an account? Sign In</a>
        </div>
        <!-- <div class="social-login">
            <p>Or continue with</p>
            <div class="social-icons">
                <a href="#" title="Register with Google">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/53/Google_%22G%22_Logo.svg" alt="Google">
                </a>
                <a href="#" title="Register with Facebook">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/51/Facebook_f_logo_%282019%29.svg" alt="Facebook">
                </a>
                <a href="#" title="Register with Apple">
                    <img src="https://upload.wikimedia.org/wikipedia/commons/f/fa/Apple_logo_black.svg" alt="Apple">
                </a>
            </div>
        </div> -->
    </div>
     <script>
        const username = document.getElementById('signup-username');
        const email = document.getElementById('signup-email');
        const password = document.getElementById('signup-password');
        const confirmPassword = document.getElementById('confirm-password');

        const usernameError = document.getElementById('username-error');
        const emailError = document.getElementById('email-error');
        const passwordError = document.getElementById('password-error');
        const confirmPasswordError = document.getElementById('confirm-password-error');

        username.addEventListener('input', () => {
            if (username.value.length < 3) {
                usernameError.textContent = 'Username must be at least 3 characters long';
                usernameError.style.display = 'block';
                username.classList.add('error');
            } else {
                usernameError.textContent = '';
                usernameError.style.display = 'none';
                username.classList.remove('error');
                username.classList.add('success');
            }
        });

        email.addEventListener('input', () => {
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email.value)) {
                emailError.textContent = 'Please enter a valid email address';
                emailError.style.display = 'block';
                email.classList.add('error');
                email.classList.remove('success');
            } else {
                // AJAX request to check if email exists
                fetch('check_email.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: 'email=' + encodeURIComponent(email.value)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        emailError.textContent = 'This email is already registered';
                        emailError.style.display = 'block';
                        email.classList.add('error');
                        email.classList.remove('success');
                    } else {
                        emailError.textContent = '';
                        emailError.style.display = 'none';
                        email.classList.remove('error');
                        email.classList.add('success');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    emailError.textContent = 'Error checking email availability';
                    emailError.style.display = 'block';
                });
            }
        });

        password.addEventListener('input', () => {
            if (password.value.length < 6) {
                passwordError.textContent = 'Password must be at least 6 characters long';
                passwordError.style.display = 'block';
                password.classList.add('error');
            } else {
                passwordError.textContent = '';
                passwordError.style.display = 'none';
                password.classList.remove('error');
                password.classList.add('success');
            }
        });

        confirmPassword.addEventListener('input', () => {
            if (confirmPassword.value !== password.value) {
                confirmPasswordError.textContent = 'Passwords do not match';
                confirmPasswordError.style.display = 'block';
                confirmPassword.classList.add('error');
            } else {
                confirmPasswordError.textContent = '';
                confirmPasswordError.style.display = 'none';
                confirmPassword.classList.remove('error');
                confirmPassword.classList.add('success');
            }
        });

        // const form = document.getElementById('signupForm');
        // form.addEventListener('submit', (e) => {
        //     if (
        //         username.value.length < 3 ||
        //         !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value) ||
        //         password.value.length < 6 ||
        //         confirmPassword.value !== password.value
        //     ) {
        //         e.preventDefault();
        //         alert('Please correct the errors in the form before submitting.');
        //     } else {
        //         // Form is valid, submit it
        //         e.preventDefault();
        //         const formData = new FormData(form);
                
        //         fetch('reg.php', {
        //             method: 'POST',
        //             body: formData
        //         })
        //         .then(response => response.json())
        //         .then(data => {
        //             if (data.success) {
        //                 // Redirect to index.php with welcome message
        //                 window.location.href = 'index.php?welcome=1&username=' + encodeURIComponent(username.value);
        //             } else {
        //                 // Show error message
        //                 const errorMessage = document.getElementById('signup-error-message');
        //                 errorMessage.textContent = data.message || 'Registration failed. Please try again.';
        //                 errorMessage.classList.add('visible');
        //             }
        //         })
        //         .catch(error => {
        //             console.log( error);
        //             // console.log(data)                    
        //             const errorMessage = document.getElementById('signup-error-message');
        //             errorMessage.textContent = 'An error occurred. Please try again.';
        //             errorMessage.classList.add('visible');
        //         });
        //     }
        // });
    </script> 
    
</body>
</html>