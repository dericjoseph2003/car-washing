<?php
session_start();
require_once "conn.php";

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $service_type = trim($_POST['service_type']);
    $vehicle_type = trim($_POST['vehicle_type']);
    $booking_date = trim($_POST['booking_date']);
    $booking_time = trim($_POST['booking_time']);
    $vehicle_number = strtoupper(trim($_POST['vehicle_number']));
    $special_requests = trim($_POST['special_requests']);
    $username = $_SESSION['username'];

    // Basic validation
    $errors = [];
    if (empty($service_type)) $errors[] = "Service type is required";
    if (empty($vehicle_type)) $errors[] = "Vehicle type is required";
    if (empty($booking_date)) $errors[] = "Booking date is required";
    if (empty($booking_time)) $errors[] = "Booking time is required";
    if (empty($vehicle_number)) {
        $errors[] = "Vehicle number is required";
    } else {
        // Vehicle number validation (format: KL-01-AB-1234)
        if (!preg_match('/^[A-Z]{2}-\d{2}-[A-Z]{2}-\d{4}$/', $vehicle_number)) {
            $errors[] = "Vehicle number must be in format KL-01-AB-1234";
        }
    }

    if (empty($errors)) {
        $sql = "INSERT INTO bookings (username, service_type, vehicle_type, booking_date, booking_time, vehicle_number, special_requests, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("sssssss", $username, $service_type, $vehicle_type, $booking_date, $booking_time, $vehicle_number, $special_requests);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "Booking submitted successfully!";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $_SESSION['error'] = "Something went wrong. Please try again.";
            }
            $stmt->close();
        }
    } else {
        $_SESSION['error'] = implode("<br>", $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book a Service - Car Care</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <style>
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

        /* Container adjustments */
        .container {
            margin: 60px auto 60px auto;
            min-height: calc(100vh - 120px);
            padding: 20px;
        }

        .booking-form {
            max-width: 800px;
            margin: 0 auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
        }

        .form-group label {
            font-weight: 600;
        }

        .btn-custom {
            background: #1e3c72;
            color: white;
            padding: 10px 30px;
        }

        .btn-custom:hover {
            background: #152b52;
            color: white;
        }

        /* Time slot styling */
        .time-slots {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .time-slot {
            padding: 5px;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
        }

        .time-slot.selected {
            background-color: #1e3c72;
            color: white;
            border-color: #1e3c72;
        }

        .time-slot:hover {
            background-color: #f8f9fa;
        }

        .time-slot.selected:hover {
            background-color: #152b52;
        }

        /* Success Message Styling */
        .success-message {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.2);
            text-align: center;
            z-index: 1000;
            min-width: 300px;
        }

        .success-message i {
            font-size: 50px;
            color: #28a745;
            margin-bottom: 20px;
        }

        .success-message h2 {
            color: #28a745;
            margin-bottom: 15px;
            font-size: 24px;
        }

        .success-message p {
            color: #666;
            margin-bottom: 20px;
        }

        .success-message .btn {
            margin: 5px;
        }

        /* Overlay for success message */
        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Book a Service</h1>
    </div>

    <div class="container">
        <?php if(isset($_SESSION['success'])): ?>
        <div class="overlay"></div>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <h2>Success!</h2>
            <p><?php echo $_SESSION['success']; ?></p>
            <a href="View My Bookings.php" class="btn btn-custom">View My Bookings</a>
            <a href="index.php" class="btn btn-secondary">Back to Home</a>
        </div>
        <?php 
            unset($_SESSION['success']);
        endif; ?>

        <div class="booking-form">
            <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <div class="form-group">
                    <label for="service_category">Service Category</label>
                    <select class="form-control" id="service_category" name="service_category" required>
                        <option value="">Select a category</option>
                        <option value="basic">Basic Services</option>
                        <option value="premium">Premium Services </option>
                        <option value="detailing">Detailing Services </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="service_type">Service Type</label>
                    <select class="form-control" id="service_type" name="service_type" required>
                        <option value="">Select a service</option>
                        <!-- Options will be populated by JavaScript -->
                    </select>
                </div>

                <div class="form-group">
                    <label for="vehicle_type">Vehicle Type</label>
                    <select class="form-control" id="vehicle_type" name="vehicle_type" required>
                        <option value="">Select vehicle type</option>
                        <option value="Sedan">Sedan</option>
                        <option value="SUV">SUV</option>
                        <option value="Truck">Truck</option>
                        <option value="Van">Van</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="vehicle_number">Vehicle Number</label>
                    <input type="text" class="form-control" id="vehicle_number" name="vehicle_number" 
                           pattern="^[A-Z]{2}\d{2}[A-Z]{0,2}\d{1,4}$" 
                           title="Please enter a valid Indian vehicle number (e.g., KL07AB1234)" 
                           placeholder="e.g., KL07AB1234" 
                           required>
                    <small class="form-text text-muted">Format: KL07AB1234 (State Code + District Number + Optional Letters + Numbers)</small>
                </div>

                <div class="form-group">
                    <label for="booking_date">Preferred Date</label>
                    <input type="date" class="form-control" id="booking_date" name="booking_date" required>
                </div>

                <div class="form-group">
                    <label>Preferred Time</label>
                    <input type="hidden" id="booking_time" name="booking_time" required>
                    <div class="time-slots">
                        <div class="time-slot" data-time="09:00">9:00 AM</div>
                        <div class="time-slot" data-time="10:00">10:00 AM</div>
                        <div class="time-slot" data-time="11:00">11:00 AM</div>
                        <div class="time-slot" data-time="13:00">1:00 PM</div>
                        <div class="time-slot" data-time="14:00">2:00 PM</div>
                        <div class="time-slot" data-time="15:00">3:00 PM</div>
                        <div class="time-slot" data-time="16:00">4:00 PM</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="special_requests">Special Requests (Optional)</label>
                    <textarea class="form-control" id="special_requests" name="special_requests" rows="3"></textarea>
                </div>

                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-custom">Book Now</button>
                    <a href="index.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <div class="footer">
        <p>&copy; <?php echo date("Y"); ?> Car Care Services. All Rights Reserved.</p>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Set minimum date to today
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('booking_date').min = today;

            // Time slot selection
            const timeSlots = document.querySelectorAll('.time-slot');
            const timeInput = document.getElementById('booking_time');

            timeSlots.forEach(slot => {
                slot.addEventListener('click', function() {
                    // Remove selected class from all slots
                    timeSlots.forEach(s => s.classList.remove('selected'));
                    // Add selected class to clicked slot
                    this.classList.add('selected');
                    // Update hidden input
                    timeInput.value = this.dataset.time;
                });
            });

            // Form validation
            document.querySelector('form').addEventListener('submit', function(e) {
                if (!timeInput.value) {
                    e.preventDefault();
                    alert('Please select a time slot');
                }
            });

            // Add auto-dismiss for success message after 5 seconds
            const successMessage = document.querySelector('.success-message');
            const overlay = document.querySelector('.overlay');
            
            if (successMessage && overlay) {
                setTimeout(() => {
                    successMessage.style.display = 'none';
                    overlay.style.display = 'none';
                }, 5000);
            }
        });

        const serviceOptions = {
            basic: [
                { value: "basic_wash", label: "Basic Wash" },
                { value: "basic_interior", label: "Basic Interior Cleaning" },
                { value: "basic_exterior", label: "Basic Exterior Cleaning" }
            ],
            premium: [
                { value: "exterior_washing", label: "Exterior Washing" },
                { value: "interior_washing", label: "Interior Washing" },
                { value: "vacuum_cleaning", label: "Vacuum Cleaning" },
                { value: "seats_washing", label: "Seats Washing" },
                { value: "window_wiping", label: "Window Wiping" },
                { value: "wet_cleaning", label: "Wet Cleaning" },
                { value: "oil_changing", label: "Oil Changing" },
                { value: "brake_repairing", label: "Brake Repairing" }
            ],
            detailing: [
                { value: "exterior_detailing", label: "Exterior Detailing - Intensive cleaning, clay bar treatment, and polishing" },
                { value: "interior_detailing", label: "Interior Detailing - Deep vacuuming, steam cleaning, and dashboard polish" },
                { value: "full_detailing", label: "Full Car Detailing Package - Interior + Exterior detailing combo" },
                { value: "leather_conditioning", label: "Leather Seat Conditioning - Cleaning and conditioning leather seats" },
                { value: "fabric_shampooing", label: "Fabric Seat Shampooing - Deep cleaning of fabric upholstery" },
                { value: "engine_detailing", label: "Engine Bay Detailing - Degreasing and cleaning of the engine area" },
                { value: "tire_alloy_detailing", label: "Tire & Alloy Detailing - Polishing and restoring shine to wheels" },
                { value: "glass_polishing", label: "Glass Polishing & Treatment - Removing scratches and water stains" },
                { value: "chrome_polishing", label: "Chrome & Metal Polishing - Enhancing the shine of metallic parts" },
                { value: "dashboard_restoration", label: "Dashboard & Trim Restoration - Restoring faded or worn-out trims" }
            ]
        };

        document.getElementById('service_category').addEventListener('change', function() {
            const serviceType = document.getElementById('service_type');
            serviceType.innerHTML = '<option value="">Select a service</option>';
            
            if (this.value) {
                serviceOptions[this.value].forEach(service => {
                    const option = document.createElement('option');
                    option.value = service.value;
                    option.textContent = service.label;
                    serviceType.appendChild(option);
                });
            }
        });

        document.getElementById('vehicle_number').addEventListener('input', function(e) {
            // Convert to uppercase
            this.value = this.value.toUpperCase();
            
            // Remove any characters that aren't letters or numbers
            this.value = this.value.replace(/[^A-Z0-9]/g, '');
            
            // Limit to maximum length (10 characters)
            if (this.value.length > 10) {
                this.value = this.value.substring(0, 10);
            }
        });
    </script>
</body>
</html>