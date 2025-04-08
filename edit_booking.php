<?php
// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "car_care2";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get booking ID from URL
$booking_id = isset($_GET['id']) ? $_GET['id'] : null;

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $service_type = $conn->real_escape_string($_POST['service_type']);
    $vehicle_type = $conn->real_escape_string($_POST['vehicle_type']);
    $booking_date = $conn->real_escape_string($_POST['booking_date']);
    $booking_time = $conn->real_escape_string($_POST['booking_time']);
    $vehicle_number = $conn->real_escape_string($_POST['vehicle_number']);
    $special_requests = $conn->real_escape_string($_POST['special_requests']);
    $status = $conn->real_escape_string($_POST['status']);

    $sql = "UPDATE booking SET 
            username='$username',
            service_type='$service_type',
            vehicle_type='$vehicle_type',
            booking_date='$booking_date',
            booking_time='$booking_time',
            vehicle_number='$vehicle_number',
            special_requests='$special_requests',
            status='$status'
            WHERE id=$booking_id";

    if ($conn->query($sql) === TRUE) {
        header("Location: admindash.php");
        exit();
    } else {
        echo "Error updating record: " . $conn->error;
    }
}

// Fetch booking details
$sql = "SELECT * FROM booking WHERE id = $booking_id";
$result = $conn->query($sql);
$booking = $result->fetch_assoc();

if (!$booking) {
    die("Booking not found");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Booking</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #1e3c72;
            --secondary-color: #2a5298;
            --accent-color: #3949ab;
            --light-bg: #f5f6fa;
            --white: #ffffff;
            --text-dark: #2d3436;
            --text-light: #636e72;
            --border-color: #e0e0e0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--light-bg);
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            background: var(--white);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
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

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-weight: 500;
        }

        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--border-color);
            border-radius: 6px;
            font-size: 14px;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        .button-group {
            display: flex;
            gap: 10px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: opacity 0.3s ease;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: var(--white);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: var(--white);
        }

        .back-link {
            color: var(--text-light);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .back-link:hover {
            color: var(--text-dark);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="page-title">Edit Booking #<?php echo $booking_id; ?></h1>
            <a href="admindash.php" class="back-link">
                <i class="fas fa-arrow-left"></i>
                Back to Dashboard
            </a>
        </div>

        <form method="POST">
            <div class="form-group">
                <label for="username">Customer Name</label>
                <input type="text" id="username" name="username" value="<?php echo $booking['username']; ?>" required>
            </div>

            <div class="form-group">
                <label for="service_type">Service Type</label>
                <select id="service_type" name="service_type" required>
                    <option value="Basic Wash" <?php echo $booking['service_type'] == 'Basic Wash' ? 'selected' : ''; ?>>Basic Wash</option>
                    <option value="Premium Wash" <?php echo $booking['service_type'] == 'Premium Wash' ? 'selected' : ''; ?>>Premium Wash</option>
                    <option value="Full Service" <?php echo $booking['service_type'] == 'Full Service' ? 'selected' : ''; ?>>Full Service</option>
                </select>
            </div>

            <div class="form-group">
                <label for="vehicle_type">Vehicle Type</label>
                <select id="vehicle_type" name="vehicle_type" required>
                    <option value="Sedan" <?php echo $booking['vehicle_type'] == 'Sedan' ? 'selected' : ''; ?>>Sedan</option>
                    <option value="SUV" <?php echo $booking['vehicle_type'] == 'SUV' ? 'selected' : ''; ?>>SUV</option>
                    <option value="Truck" <?php echo $booking['vehicle_type'] == 'Truck' ? 'selected' : ''; ?>>Truck</option>
                </select>
            </div>

            <div class="form-group">
                <label for="booking_date">Booking Date</label>
                <input type="date" id="booking_date" name="booking_date" value="<?php echo $booking['booking_date']; ?>" required>
            </div>

            <div class="form-group">
                <label for="booking_time">Booking Time</label>
                <input type="time" id="booking_time" name="booking_time" value="<?php echo $booking['booking_time']; ?>" required>
            </div>

            <div class="form-group">
                <label for="vehicle_number">Vehicle Number</label>
                <input type="text" id="vehicle_number" name="vehicle_number" value="<?php echo $booking['vehicle_number']; ?>" 
                    required 
                    pattern="^[A-Z]{2}-\d{2}-[A-Z]{2}-\d{4}$"
                    oninput="this.value = this.value.toUpperCase()"
                    onkeyup="validateVehicleNumber(this)">
                <small id="vehicle_number_error" style="color: red; display: none;">Please enter a valid Indian vehicle number (Format: XX-99-XX-9999)</small>
            </div>

            <div class="form-group">
                <label for="special_requests">Special Requests</label>
                <textarea id="special_requests" name="special_requests"><?php echo $booking['special_requests']; ?></textarea>
            </div>

            <div class="form-group">
                <label for="status">Status</label>
                <select id="status" name="status" required>
                    <option value="pending" <?php echo $booking['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="completed" <?php echo $booking['status'] == 'completed' ? 'selected' : ''; ?>>Completed</option>
                </select>
            </div>

            <div class="button-group">
                <button type="submit" class="btn btn-primary">Update Booking</button>
                <a href="admindash.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <script>
        function validateVehicleNumber(input) {
            const value = input.value;
            const pattern = /^[A-Z]{2}-\d{2}-[A-Z]{2}-\d{4}$/;
            const errorElement = document.getElementById('vehicle_number_error');
            
            // Auto-format as user types
            if (value.length === 2 || value.length === 5 || value.length === 8) {
                if (value.charAt(value.length - 1) !== '-') {
                    input.value = value + '-';
                }
            }

            // Validate format
            if (!pattern.test(value) && value.length > 0) {
                errorElement.style.display = 'block';
                input.setCustomValidity('Invalid vehicle number format');
            } else {
                errorElement.style.display = 'none';
                input.setCustomValidity('');
            }
        }

        // Validate on page load if value exists
        window.onload = function() {
            const vehicleInput = document.getElementById('vehicle_number');
            if (vehicleInput.value) {
                validateVehicleNumber(vehicleInput);
            }
        }
    </script>
</body>
</html> 