<?php
session_start();


require_once 'conn.php';

$staff_id = $_GET['id'] ?? null;
$staff = null;
$success_message = null;
$error_message = null;

if ($staff_id) {
    // First check if staff member exists
    $check_staff = $conn->prepare("SELECT id FROM staff WHERE id = ?");
    $check_staff->bind_param("i", $staff_id);
    $check_staff->execute();
    $check_result = $check_staff->get_result();
    
    if ($check_result->num_rows === 0) {
        $error_message = "Staff member not found.";
        $staff = null;
    } else {
        // Handle form submission for updates
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $name = $_POST['name'];
            $email = $_POST['email'];
            $phone = $_POST['phone'];
            $role = $_POST['role'];
            
            // Check if email exists for other staff members
            $check_email = $conn->prepare("SELECT id FROM staff WHERE email = ? AND id != ?");
            $check_email->bind_param("si", $email, $staff_id);
            $check_email->execute();
            $result = $check_email->get_result();
            
            if ($result->num_rows > 0) {
                $error_message = "This email is already in use by another staff member.";
            } else {
                $sql = "UPDATE staff SET name = ?, email = ?, phone = ?, role = ?";
                $params = [$name, $email, $phone, $role];
                $types = "ssss";
                
                // Only update password if a new one is provided
                if (!empty($_POST['password'])) {
                    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
                    $sql .= ", password = ?";
                    $params[] = $password;
                    $types .= "s";
                }
                
                $sql .= " WHERE id = ?";
                $params[] = $staff_id;
                $types .= "i";
                
                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$params);
                
                if ($stmt->execute()) {
                    // Redirect with success message
                    header("Location: edit_staff.php?id=" . $staff_id . "&success=1");
                    exit();
                } else {
                    $error_message = "Error updating staff member: " . $conn->error;
                }
            }
        }
        
        // Check for success message in URL
        if (isset($_GET['success']) && $_GET['success'] == 1) {
            $success_message = "Staff member updated successfully!";
        }
        
        // Fetch staff member data
        $stmt = $conn->prepare("SELECT name, email, phone, role FROM staff WHERE id = ?");
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $staff = $result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Staff Member - Car Wash Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Copy the same CSS from staff.php -->
</head>
<body>
    <div class="container">
        <div class="main-content">
            <div class="header">
                <h1 class="page-title">Edit Staff Member</h1>
            </div>

            <?php if ($staff): ?>
                <div class="staff-container">
                    <div class="add-staff-form">
                        <?php if ($success_message): ?>
                            <div class="message success"><?php echo $success_message; ?></div>
                        <?php endif; ?>
                        <?php if ($error_message): ?>
                            <div class="message error"><?php echo $error_message; ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="form-group">
                                <label for="name">Full Name</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($staff['name']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($staff['email']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($staff['phone']); ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="role">Role</label>
                                <select id="role" name="role" required>
                                    <option value="washer" <?php echo $staff['role'] == 'washer' ? 'selected' : ''; ?>>Car Washer</option>
                                    <option value="manager" <?php echo $staff['role'] == 'manager' ? 'selected' : ''; ?>>Car Repair</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="password">New Password (leave blank to keep current)</label>
                                <input type="password" id="password" name="password">
                            </div>
                            <button type="submit" class="submit-btn">Update Staff Member</button>
                        </form>
                    </div>
                </div>
            <?php else: ?>
                <div class="message error">Staff member not found.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 