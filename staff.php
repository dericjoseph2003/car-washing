<?php
session_start();



// Database connection
require_once 'conn.php';

// Initialize messages
$success_message = $error_message = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role = $_POST['role'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check_sql = "SELECT id FROM staff WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        $error_message = "Email already exists in the system.";
    } else {
        $sql = "INSERT INTO staff (name, email, phone, role, password) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $name, $email, $phone, $role, $password);
        
        if ($stmt->execute()) {
            // Store success message in session
            $_SESSION['success_message'] = "Staff member added successfully!";
            // Redirect to prevent form resubmission
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $error_message = "Error adding staff member: " . $stmt->error;
        }
    }
}

// Check for session messages at the top of the file
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']); // Clear the message
}

// Fetch existing staff members
$staff_members = [];
$sql = "SELECT id, name, email, phone, role, active FROM staff";
$result = $conn->query($sql);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $staff_members[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Management - Car Wash Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Add CSS variables at the top of your styles */
        :root {
            --white: #ffffff;
            --primary-color: #4CAF50;
            --secondary-color: #45a049;
            --text-dark: #333333;
            --border-color: #dddddd;
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --accent-color: #3949ab;
            --light-bg: #f5f6fa;
            --text-light: #636e72;
        }

        /* Reuse existing styles from admindash.php */
        /* Add additional styles for the form */
        .staff-container {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            margin: 20px;
            align-items: start;
        }

        .add-staff-form {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 20px;
        }

        .staff-list {
            background: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .bookings-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .bookings-table th,
        .bookings-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }

        .edit-btn {
            background-color: #ffc107;
            color: #000;
        }

        .delete-btn {
            background-color: #dc3545;
            color: #fff;
        }

        /* Added responsive styles */
        @media (max-width: 1024px) {
            .staff-container {
                grid-template-columns: 1fr;
            }
            
            .add-staff-form {
                position: static;
            }
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: var(--text-dark);
        }

        .form-group input, .form-group select {
            width: 100%;
            padding: 8px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .submit-btn {
            background-color: var(--primary-color);
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
            margin-top: 10px;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background-color: var(--secondary-color);
        }

        .message {
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .success {
            background-color: #d4edda;
            color: #155724;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Add these new styles */
        .status-btn.active {
            background-color: var(--primary-color);
            color: white;
        }
        
        .status-btn.inactive {
            background-color: #6c757d;
            color: white;
        }

        /* Update these validation styles */
        .form-group input:invalid,
        .form-group select:invalid {
            border-color: var(--border-color); /* Reset to default border color */
        }

        .form-group input.error,
        .form-group select.error {
            border-color: #dc3545; /* Red border only for fields with errors */
        }

        .validation-message {
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 4px;
            display: none;
        }

        .validation-message.show {
            display: block;
        }

        /* Add these if not already present */
        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background-color: var(--primary-color);
            color: var(--white);
            padding: 20px;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            padding: 20px 0;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-menu {
            margin-top: 30px;
        }

        .nav-section {
            margin-bottom: 20px;
        }

        .nav-section-title {
            font-size: 12px;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 10px;
            padding-left: 12px;
        }

        .nav-item {
            padding: 12px 15px;
            margin: 8px 0;
            cursor: pointer;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .nav-item:hover {
            background-color: var(--secondary-color);
        }

        .nav-item.active {
            background-color: var(--accent-color);
        }

        .nav-item i {
            width: 20px;
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            margin-left: 260px;
            padding: 30px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .page-title {
            font-size: 24px;
            color: var(--text-dark);
        }

        /* Remove underline from all navigation links */
        .nav-menu a,
        .nav-item a,
        a.nav-item {
            text-decoration: none;
            color: white;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .sidebar {
                width: 200px;
            }
            .main-content {
                margin-left: 200px;
            }
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }
            .main-content {
                margin-left: 70px;
            }
            .logo span, .nav-item span, .nav-section-title {
                display: none;
            }
            .nav-item {
                justify-content: center;
                padding: 12px;
            }
            .nav-item i {
                margin: 0;
            }
            .staff-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Sidebar -->
         <!-- Sidebar -->
         <div class="sidebar">
            <div class="logo">
                <i class="fas fa-car-wash"></i>
                <span>CarWash Admin</span>
            </div>
            <div class="nav-menu">
                <div class="nav-section">
                    <div class="nav-section-title">Main Menu</div>
                    <a href="index.php" class="nav-item">
                        <i class="fas fa-home"></i>
                        <span>Home</span>
                    </a>
                    <div class="nav-item active">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </div>
                    <a href="bookings.php" class="nav-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Bookings</span>
                    </a>
                    <a href="feedback.php" class="nav-item">
                        <i class="fas fa-comment"></i>
                        <span>Feedback</span>
                    </a>

                    <a href="payment_details.php" class="nav-item">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Payment Details</span>
                    </a>
                    <a href="service_prices.php" class="nav-item">
                        <i class="fas fa-dollar-sign"></i>
                        <span>Service Prices</span>
                    </a>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Management</div>
                    <!-- <a href="customers.php"><div class="nav-item">
                        <i class="fas fa-users"></i>
                        <span>Customers</span>
                    </div></a> -->
                    <a href="staff.php" class="nav-item">
                        <i class="fas fa-user-tie"></i>
                        <span>Staff Management</span>
                    </a>
                    <!-- <a href="enquiries.php" class="nav-item">
                        <i class="fas fa-message"></i>
                        <span>Enquiries</span>
                    </a> -->
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Settings</div>
                    <a href="adminprofile.php" class="nav-item">
                        <i class="fas fa-user-circle"></i>
                        <span>Profile</span>
                    </a>
                </div>
            </div>
        </div>  


        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1 class="page-title">Staff Management</h1>
            </div>

            <div class="staff-container">
                <div class="add-staff-form">
                    <h2>Add New Staff Member</h2>
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($success_message)): ?>
                        <div class="message success"><?php echo $success_message; ?></div>
                    <?php endif; ?>
                    <?php if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($error_message)): ?>
                        <div class="message error"><?php echo $error_message; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="staffForm" novalidate>
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" required pattern="^[a-zA-Z\s]{2,50}$">
                            <div class="validation-message" id="nameError">Please enter a valid name (2-50 characters, letters only)</div>
                        </div>
                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" id="email" name="email" required>
                            <div class="validation-message" id="emailError">Please enter a valid email address</div>
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone</label>
                            <input type="tel" id="phone" name="phone" required pattern="^[0-9]{10}$">
                            <div class="validation-message" id="phoneError">Please enter a valid 10-digit phone number</div>
                        </div>
                        <div class="form-group">
                            <label for="role">Role</label>
                            <select id="role" name="role" required>
                                <option value="">Select a role</option>
                                <option value="washer">Car Washer</option>
                                <option value="detailer">Car Detailer</option>
                                <option value="polisher">Polisher</option>
                            </select>
                            <div class="validation-message" id="roleError">Please select a role</div>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" name="password" required pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$">
                            <div class="validation-message" id="passwordError">Password must be at least 8 characters long and contain both letters and numbers</div>
                        </div>
                        <button type="submit" class="submit-btn">Add Staff Member</button>
                    </form>
                </div>

                <div class="staff-list">
                    <div class="bookings-section">
                        <div class="table-header">
                            <h2 class="table-title">Current Staff Members</h2>
                        </div>
                        <table class="bookings-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($staff_members as $staff): ?>
                                <tr>
                                    <td>#<?php echo $staff['id']; ?></td>
                                    <td><?php echo $staff['name']; ?></td>
                                    <td><?php echo $staff['email']; ?></td>
                                    <td><?php echo $staff['phone']; ?></td>
                                    <td><?php echo ucfirst($staff['role']); ?></td>
                                    <td><?php echo $staff['active'] ? 'Active' : 'Inactive'; ?></td>
                                    <td>
                                        <div class="actions">
                                            <button class="action-btn edit-btn" data-id="<?php echo $staff['id']; ?>">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <button class="action-btn status-btn <?php echo $staff['active'] ? 'active' : 'inactive'; ?>" 
                                                    data-id="<?php echo $staff['id']; ?>"
                                                    data-status="<?php echo $staff['active']; ?>">
                                                <i class="fas <?php echo $staff['active'] ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
                                                <?php echo $staff['active'] ? 'Active' : 'Inactive'; ?>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add form validation
        const form = document.getElementById('staffForm');
        const inputs = form.querySelectorAll('input, select');

        // Validation functions
        const validators = {
            name: (value) => {
                const regex = /^[a-zA-Z\s]{2,50}$/;
                return regex.test(value);
            },
            email: (value) => {
                const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                return regex.test(value);
            },
            phone: (value) => {
                const regex = /^[0-9]{10}$/;
                return regex.test(value);
            },
            role: (value) => value !== '',
            password: (value) => {
                const regex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/;
                return regex.test(value);
            }
        };

        // Add validation listeners to all inputs
        inputs.forEach(input => {
            ['input', 'blur'].forEach(eventType => {
                input.addEventListener(eventType, function() {
                    validateField(input);
                });
            });
        });

        // Validate individual field
        function validateField(field) {
            const errorElement = document.getElementById(`${field.id}Error`);
            const isValid = validators[field.id](field.value);
            
            if (!isValid) {
                errorElement.classList.add('show');
                field.classList.add('error');
                field.setCustomValidity('invalid');
            } else {
                errorElement.classList.remove('show');
                field.classList.remove('error');
                field.setCustomValidity('');
            }
        }

        // Form submission handler
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            inputs.forEach(input => {
                validateField(input);
                if (!validators[input.id](input.value)) {
                    isValid = false;
                }
            });

            if (!isValid) {
                e.preventDefault();
            }
        });

        // Replace delete button handler with status toggle
        document.querySelectorAll('.status-btn').forEach(button => {
            button.addEventListener('click', function() {
                const staffId = this.dataset.id;
                const currentStatus = this.dataset.status === '1';
                const newStatus = !currentStatus;
                
                fetch('update_staff_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ 
                        id: staffId,
                        active: newStatus
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update button appearance
                        this.classList.toggle('active');
                        this.classList.toggle('inactive');
                        this.dataset.status = newStatus ? '1' : '0';
                        this.innerHTML = `<i class="fas fa-toggle-${newStatus ? 'on' : 'off'}"></i> ${newStatus ? 'Active' : 'Inactive'}`;
                    } else {
                        alert('Error updating staff status');
                    }
                });
            });
        });

        // Handle edit button clicks
        document.querySelectorAll('.edit-btn').forEach(button => {
            button.addEventListener('click', function() {
                const staffId = this.dataset.id;
                window.location.href = `edit_staff.php?id=${staffId}`;
            });
        });
    });
    </script>
</body>
</html> 