<?php
session_start();
require_once "conn.php";
require_once 'vendor/autoload.php';
use Razorpay\Api\Api;

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "Please login to book a service";
    header("Location: login.php");
    exit();
}

// Initialize Razorpay API
$api = new Api('rzp_test_NPqdzqGri9yJVc', 'ISG4iVXQG6eJGn6HxVL5AgbT');

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Add error handling for AJAX requests
    header('Content-Type: application/json');
    
    $username = $_SESSION['username'];
    $service_category = trim($_POST['service_category']);
    $service_type = trim($_POST['service_type']);
    $vehicle_type = trim($_POST['vehicle_type']);
    $booking_date = trim($_POST['booking_date']);
    $booking_time = trim($_POST['booking_time']);
    $vehicle_number = strtoupper(trim($_POST['vehicle_number']));
    $special_requests = trim($_POST['special_requests']);

    // Basic validation
    $errors = [];
    if (empty($service_category)) $errors[] = "Service category is required";
    if (empty($service_type)) $errors[] = "Service type is required";
    if (empty($vehicle_type)) $errors[] = "Vehicle type is required";
    if (empty($booking_date)) {
        $errors[] = "Booking date is required";
    } else {
        $today = new DateTime();
        $today->setTime(0, 0, 0); // Reset time part to midnight
        $selected_date = new DateTime($booking_date);
        $max_date = (new DateTime())->modify('+30 days');
        
        if ($selected_date < $today) {
            $errors[] = "Booking date cannot be in the past";
        } elseif ($selected_date > $max_date) {
            $errors[] = "Booking date cannot be more than 30 days in the future";
        }
    }
    if (empty($booking_time)) $errors[] = "Booking time is required";
    if (empty($vehicle_number)) {
        $errors[] = "Vehicle number is required";
    } else {
        if (!preg_match("/^[A-Z]{2}\d{2}[A-Z]\d{4}$/", $vehicle_number)) {
            $errors[] = "Invalid vehicle number format (e.g., KL29H2525)";
        }
    }

    if (empty($errors)) {
        // Get the price based on selections from database
        $price = 0;
        
        // Prepare SQL to get price for specific service
        $sql = "SELECT * FROM services 
                WHERE category = ? 
                AND LOWER(REPLACE(service_name, ' ', '_')) = ?";
                
        if ($stmt = $conn->prepare($sql)) {
            $service_type_clean = strtolower(str_replace(' ', '_', $service_type));
            $stmt->bind_param("ss", $service_category, $service_type_clean);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                // Get price based on vehicle type
                $price_column = 'price_' . strtolower($vehicle_type);
                if (isset($row[$price_column])) {
                    $price = $row[$price_column];
                    error_log("Price found in database: " . $price);
                } else {
                    error_log("Price column not found: " . $price_column);
                }
            } else {
                error_log("Service not found in database");
            }
            
            $stmt->close();
        } else {
            error_log("Failed to prepare statement: " . $conn->error);
        }

        // Check if price is valid
        if ($price <= 0) {
            echo json_encode([
                'status' => 'error', 
                'message' => "Invalid order amount. Please check your selections.",
                'debug' => [
                    'category' => $service_category,
                    'vehicle' => $vehicle_type,
                    'service' => $service_type,
                    'price' => $price
                ]
            ]);
            exit();
        }

        $sql = "INSERT INTO booking (username, service_category, service_type, vehicle_type, booking_date, booking_time, vehicle_number, special_requests, status, payment_status, price) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Pending', 'pending', ?)";
        
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssssssssd", $username, $service_category, $service_type, $vehicle_type, $booking_date, $booking_time, $vehicle_number, $special_requests, $price);
            
            try {
                if ($stmt->execute()) {
                    $booking_id = $conn->insert_id;
                    
                    // Create Razorpay Order with dynamic price
                    $orderData = [
                        'receipt'         => 'booking_' . $booking_id,
                        'amount'          => $price * 100, // Convert to paise
                        'currency'        => 'INR',
                        'payment_capture' => 1
                    ];
                    
                    $razorpayOrder = $api->order->create($orderData);
                    
                    // Store order details in session
                    $_SESSION['razorpay_order_id'] = $razorpayOrder['id'];
                    $_SESSION['razorpay_amount'] = $orderData['amount'];
                    $_SESSION['razorpay_currency'] = $orderData['currency'];
                    $_SESSION['booking_id'] = $booking_id;
                    
                    echo json_encode([
                        'status' => 'success',
                        'order_id' => $razorpayOrder['id'],
                        'amount' => $orderData['amount'],
                        'currency' => $orderData['currency'],
                        'booking_id' => $booking_id
                    ]);
                    exit();
                } else {
                    echo json_encode(['status' => 'error', 'message' => "Database Error: " . $stmt->error]);
                    exit();
                }
            } catch (Exception $e) {
                echo json_encode(['status' => 'error', 'message' => "Database Error: " . $e->getMessage()]);
                exit();
            }
            $stmt->close();
        } else {
            echo json_encode(['status' => 'error', 'message' => "Prepare Error: " . $conn->error]);
            exit();
        }
        $conn->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => implode("<br>", $errors)]);
        exit();
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

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .nav-links {
            display: flex;
            gap: 20px;
        }

        .nav-link {
            color: white;
            text-decoration: none;
            padding: 5px 10px;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .nav-link:hover {
            background-color: rgba(255, 255, 255, 0.1);
            color: white;
            text-decoration: none;
        }

        .price-display {
            padding: 0;
            background-color: #fff;
            border-radius: 5px;
            border: 1px solid #dee2e6;
        }
        
        .price-display .table {
            margin-bottom: 0;
        }

        .price-display .table td,
        .price-display .table th {
            vertical-align: middle;
        }

        #price_display {
            color: #28a745;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .thead-light th {
            background-color: #f8f9fa;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>Book a Service</h1>
            <div class="nav-links">
                <a href="view-my-booking.php" class="nav-link">View My Bookings</a>
                <a href="index.php" class="nav-link">Home</a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if(isset($_SESSION['success'])): ?>
        <div class="overlay"></div>
        <div class="success-message">
            <i class="fas fa-check-circle"></i>
            <h2>Success!</h2>
            <p><?php echo $_SESSION['success']; ?></p>
            <a href="view-my-booking.php" class="btn btn-custom">View My Bookings</a>
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
                           pattern="^[A-Z]{2}\d{2}[A-Z]\d{4}$" 
                           title="Please enter a valid vehicle number (e.g., KL29H2525)" 
                           placeholder="e.g., KL29H2525" 
                           required>
                    <small class="form-text text-muted">Format: KL29H2525 (State Code + District Number + Letter + Numbers)</small>
                </div>

                <div class="form-group">
                    <label for="booking_date">Preferred Date</label>
                    <input type="date" 
                           class="form-control" 
                           id="booking_date" 
                           name="booking_date" 
                           min="<?php echo date('Y-m-d'); ?>"
                           max="<?php echo date('Y-m-d', strtotime('+30 days')); ?>"
                           required>
                    <small class="form-text text-muted">Please select a date within the next 30 days</small>
                </div>

                <div class="form-group">
                    <label for="booking_time">Preferred Time</label>
                    <input type="time" 
                           class="form-control" 
                           id="booking_time" 
                           name="booking_time" 
                           min="08:00" 
                           max="21:00" 
                           required>
                    <small class="form-text text-muted">Business hours: 8:00 AM to 8:00 PM</small>
                </div>

                <div class="form-group">
                    <label for="special_requests">Special Requests (Optional)</label>
                    <textarea class="form-control" id="special_requests" name="special_requests" rows="3"></textarea>
                </div>

                <div class="form-group">
                    <label>Service Price Details</label>
                    <div class="price-display">
                        <table class="table table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Service</th>
                                    <th>Details</th>
                                    <th>Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td id="service_name">-</td>
                                    <td>
                                        <span id="vehicle_name">-</span><br>
                                        <small id="category_name" class="text-muted">-</small>
                                    </td>
                                    <td>
                                        <h5 id="price_display" class="mb-0">₹0.00</h5>
                                        <input type="hidden" id="service_price" name="service_price" value="">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
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

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1.min.js"></script>
    <script src="lib/easing/easing.min.js"></script>
    <script src="lib/owlcarousel/owl.carousel.min.js"></script>
    <script src="lib/waypoints/waypoints.min.js"></script>
    <script src="lib/counterup/counterup.min.js"></script>
    
    <!-- Template Javascript -->
    <script src="js/main.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Function to fetch services from database
            async function fetchServices(category) {
                try {
                    const response = await fetch(`get_services.php?category=${category}`);
                    const data = await response.json();
                    if (data.status === 'success') {
                        return data.services;
                    }
                    return [];
                } catch (error) {
                    console.error('Error fetching services:', error);
                    return [];
                }
            }

            // Function to update service types dropdown
            async function updateServiceTypes() {
                const serviceCategory = document.getElementById('service_category').value;
                const serviceTypeSelect = document.getElementById('service_type');
                
                // Clear existing options
                serviceTypeSelect.innerHTML = '<option value="">Select a service</option>';
                
                if (serviceCategory) {
                    // Show loading state
                    serviceTypeSelect.disabled = true;
                    
                    // Fetch services from database
                    const services = await fetchServices(serviceCategory);
                    
                    // Add new options
                    services.forEach(service => {
                        const option = document.createElement('option');
                        option.value = service.value;
                        option.textContent = service.label;
                        serviceTypeSelect.appendChild(option);
                    });
                    
                    // Enable select
                    serviceTypeSelect.disabled = false;
                }
                
                // Update price after changing service types
                updatePrice();
            }

            // Function to fetch price from database
            async function fetchPrice(serviceCategory, serviceType, vehicleType) {
                try {
                    const response = await fetch('get_price.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            category: serviceCategory,
                            service: serviceType,
                            vehicle: vehicleType
                        })
                    });
                    const data = await response.json();
                    return data.price || 0;
                } catch (error) {
                    console.error('Error fetching price:', error);
                    return 0;
                }
            }

            // Function to update price display
            async function updatePrice() {
                const serviceCategory = document.getElementById('service_category').value;
                const serviceType = document.getElementById('service_type').value;
                const vehicleType = document.getElementById('vehicle_type').value;
                
                if (serviceCategory && serviceType && vehicleType) {
                    const price = await fetchPrice(serviceCategory, serviceType, vehicleType);
                    document.getElementById('price_display').textContent = `₹${price.toFixed(2)}`;
                    document.getElementById('service_price').value = price;
                    
                    // Update other display elements
                    document.getElementById('service_name').textContent = 
                        document.getElementById('service_type').options[
                            document.getElementById('service_type').selectedIndex
                        ].text;
                    document.getElementById('vehicle_name').textContent = vehicleType;
                    document.getElementById('category_name').textContent = 
                        serviceCategory.charAt(0).toUpperCase() + serviceCategory.slice(1) + ' Category';
                } else {
                    document.getElementById('price_display').textContent = '₹0.00';
                    document.getElementById('service_price').value = '';
                    document.getElementById('service_name').textContent = '-';
                    document.getElementById('vehicle_name').textContent = '-';
                    document.getElementById('category_name').textContent = '-';
                }
            }

            // Add event listeners
            document.getElementById('service_category').addEventListener('change', updateServiceTypes);
            document.getElementById('service_type').addEventListener('change', updatePrice);
            document.getElementById('vehicle_type').addEventListener('change', updatePrice);
        });

        // Vehicle number input handling
        document.getElementById('vehicle_number').addEventListener('input', function(e) {
            this.value = this.value.toUpperCase();
            this.value = this.value.replace(/[^A-Z0-9]/g, '');
            if (this.value.length > 10) {
                this.value = this.value.substring(0, 10);
            }
        });
    </script>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Show loading indicator
        const submitButton = this.querySelector('button[type="submit"]');
        submitButton.disabled = true;
        submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        fetch(window.location.href, {
            method: 'POST',
            body: new FormData(this)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'error') {
                alert(data.message);
                submitButton.disabled = false;
                submitButton.innerHTML = 'Book Now';
                return;
            }
            
            var options = {
                "key": "rzp_test_NPqdzqGri9yJVc",
                "amount": data.amount,
                "currency": data.currency,
                "name": "Car Care Services",
                "description": "Service Booking Payment",
                "order_id": data.order_id,
                "handler": function (response) {
                    // Log the complete response object
                    console.log('Complete Razorpay Response:', response);
                    
                    // Create form data for verification
                    const formData = new URLSearchParams();
                    formData.append('action', 'verify_payment');
                    formData.append('razorpay_payment_id', response.razorpay_payment_id);
                    formData.append('razorpay_order_id', response.razorpay_order_id);
                    formData.append('razorpay_signature', response.razorpay_signature);

                    // Verify the payment
                    fetch('payment.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            // Show success message
                            Swal.fire({
                                icon: 'success',
                                title: 'Booking Successful!',
                                text: 'A confirmation email has been sent to your registered email address.',
                                confirmButtonColor: '#1e3c72'
                            }).then(() => {
                            // Redirect to view-my-booking.php after successful payment
                            window.location.href = 'view-my-booking.php';
                            });
                        } else {
                            throw new Error(data.message || 'Payment verification failed');
                        }
                    })
                    .catch(error => {
                        console.error('Payment Error:', error);
                        alert('Payment Error: ' + error.message);
                        submitButton.disabled = false;
                        submitButton.innerHTML = 'Book Now';
                    });
                },
                "prefill": {
                    "name": "<?php echo $_SESSION['username']; ?>",
                    "email": data.email || "",
                    "contact": ""
                },
                "theme": {
                    "color": "#1e3c72"
                }
            };
            
            var rzp1 = new Razorpay(options);
            rzp1.on('payment.failed', function (response) {
                console.error('Payment failed:', response.error);
                alert('Payment failed: ' + (response.error.description || 'Please try again.'));
                submitButton.disabled = false;
                submitButton.innerHTML = 'Book Now';
            });
            rzp1.open();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            submitButton.disabled = false;
            submitButton.innerHTML = 'Book Now';
        });
    });
    </script>
</body>
</html>