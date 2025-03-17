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
        // Get the price based on selections
        $price = 0;
        $service_prices = [
            'basic' => [
                'Sedan' => [
                    'basic_wash' => 500,
                    'basic_interior' => 800,
                    'basic_exterior' => 700
                ],
                'SUV' => [
                    'basic_wash' => 700,
                    'basic_interior' => 1000,
                    'basic_exterior' => 900
                ],
                'Truck' => [
                    'basic_wash' => 1000,
                    'basic_interior' => 1300,
                    'basic_exterior' => 1200
                ],
                'Van' => [
                    'basic_wash' => 800,
                    'basic_interior' => 1100,
                    'basic_exterior' => 1000
                ]
            ],
            'premium' => [
                'Sedan' => [
                    'exterior_washing' => 1000,
                    'interior_washing' => 1200,
                    'vacuum_cleaning' => 800,
                    'seats_washing' => 1500,
                    'window_wiping' => 500,
                    'wet_cleaning' => 1000,
                    'oil_changing' => 2000,
                    'brake_repairing' => 2500
                ],
                'SUV' => [
                    'exterior_washing' => 1200,
                    'interior_washing' => 1400,
                    'vacuum_cleaning' => 1000,
                    'seats_washing' => 1800,
                    'window_wiping' => 700,
                    'wet_cleaning' => 1200,
                    'oil_changing' => 2500,
                    'brake_repairing' => 3000
                ],
                'Truck' => [
                    'exterior_washing' => 1500,
                    'interior_washing' => 1700,
                    'vacuum_cleaning' => 1300,
                    'seats_washing' => 2100,
                    'window_wiping' => 900,
                    'wet_cleaning' => 1500,
                    'oil_changing' => 3000,
                    'brake_repairing' => 3500
                ],
                'Van' => [
                    'exterior_washing' => 1300,
                    'interior_washing' => 1500,
                    'vacuum_cleaning' => 1100,
                    'seats_washing' => 1900,
                    'window_wiping' => 800,
                    'wet_cleaning' => 1300,
                    'oil_changing' => 2700,
                    'brake_repairing' => 3200
                ]
            ],
            'detailing' => [
                'Sedan' => [
                    'exterior_detailing' => 3000,
                    'interior_detailing' => 3500,
                    'full_detailing' => 6000,
                    'leather_conditioning' => 2000,
                    'fabric_shampooing' => 1800,
                    'engine_detailing' => 2500,
                    'tire_alloy_detailing' => 1500,
                    'glass_polishing' => 1200,
                    'chrome_polishing' => 1000,
                    'dashboard_restoration' => 1500
                ],
                'SUV' => [
                    'exterior_detailing' => 3500,
                    'interior_detailing' => 4000,
                    'full_detailing' => 7000,
                    'leather_conditioning' => 2500,
                    'fabric_shampooing' => 2300,
                    'engine_detailing' => 3000,
                    'tire_alloy_detailing' => 2000,
                    'glass_polishing' => 1500,
                    'chrome_polishing' => 1300,
                    'dashboard_restoration' => 1800
                ],
                'Truck' => [
                    'exterior_detailing' => 4000,
                    'interior_detailing' => 4500,
                    'full_detailing' => 8000,
                    'leather_conditioning' => 3000,
                    'fabric_shampooing' => 2800,
                    'engine_detailing' => 3500,
                    'tire_alloy_detailing' => 2500,
                    'glass_polishing' => 1800,
                    'chrome_polishing' => 1600,
                    'dashboard_restoration' => 2100
                ],
                'Van' => [
                    'exterior_detailing' => 3700,
                    'interior_detailing' => 4200,
                    'full_detailing' => 7500,
                    'leather_conditioning' => 2700,
                    'fabric_shampooing' => 2500,
                    'engine_detailing' => 3200,
                    'tire_alloy_detailing' => 2200,
                    'glass_polishing' => 1600,
                    'chrome_polishing' => 1400,
                    'dashboard_restoration' => 1900
                ]
            ]
        ];
        
        // Debug information
        error_log("Service Category: " . $service_category);
        error_log("Vehicle Type: " . $vehicle_type);
        error_log("Service Type: " . $service_type);
        
        if (isset($service_prices[$service_category][$vehicle_type][$service_type])) {
            $price = $service_prices[$service_category][$vehicle_type][$service_type];
            error_log("Price found: " . $price);
        } else {
            error_log("Price not found in array");
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
            // Add event listener to update service type options when service category changes
            document.getElementById('service_category').addEventListener('change', function() {
                const serviceTypeSelect = document.getElementById('service_type');
                const selectedCategory = this.value;
                
                // Clear existing options
                serviceTypeSelect.innerHTML = '<option value="">Select a service</option>';
                
                // Add new options based on selected category
                if (selectedCategory && serviceOptions[selectedCategory]) {
                    serviceOptions[selectedCategory].forEach(option => {
                        const optionElement = document.createElement('option');
                        optionElement.value = option.value;
                        optionElement.textContent = option.label;
                        serviceTypeSelect.appendChild(optionElement);
                    });
                }
                
                // Update price when service category changes
                updatePrice();
            });

            // Add event listeners to update price when selections change
            document.getElementById('service_type').addEventListener('change', updatePrice);
            document.getElementById('vehicle_type').addEventListener('change', updatePrice);

            // Function to update the price display
            function updatePrice() {
                const serviceCategory = document.getElementById('service_category').value;
                const serviceType = document.getElementById('service_type').value;
                const vehicleType = document.getElementById('vehicle_type').value;
                
                // Get the selected service option label
                let serviceLabel = '-';
                if (serviceCategory && serviceType) {
                    const serviceOption = serviceOptions[serviceCategory]?.find(opt => opt.value === serviceType);
                    serviceLabel = serviceOption ? serviceOption.label : '-';
                }
                
                // Update table cells
                document.getElementById('service_name').textContent = serviceLabel;
                document.getElementById('vehicle_name').textContent = vehicleType || '-';
                document.getElementById('category_name').textContent = serviceCategory ? 
                    (serviceCategory.charAt(0).toUpperCase() + serviceCategory.slice(1) + ' Category') : 
                    '-';
                
                if (serviceCategory && serviceType && vehicleType && 
                    servicePrices[serviceCategory]?.[vehicleType]?.[serviceType]) {
                    const price = servicePrices[serviceCategory][vehicleType][serviceType];
                    document.getElementById('service_price').value = price;
                    document.getElementById('price_display').textContent = `₹${price.toFixed(2)}`;
                } else {
                    document.getElementById('service_price').value = '';
                    document.getElementById('price_display').textContent = '₹0.00';
                }
            }

            // Initial call to set the price when the page loads
            updatePrice();
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
                { value: "exterior_detailing", label: "Exterior Detailing" },
                { value: "interior_detailing", label: "Interior Detailing" },
                { value: "full_detailing", label: "Full Car Detailing Package" },
                { value: "leather_conditioning", label: "Leather Seat Conditioning" },
                { value: "fabric_shampooing", label: "Fabric Seat Shampooing" },
                { value: "engine_detailing", label: "Engine Bay Detailing" },
                { value: "tire_alloy_detailing", label: "Tire & Alloy Detailing" },
                { value: "glass_polishing", label: "Glass Polishing & Treatment" },
                { value: "chrome_polishing", label: "Chrome & Metal Polishing" },
                { value: "dashboard_restoration", label: "Dashboard & Trim Restoration" }
            ]
        };

        const servicePrices = {
            'basic': {
                'Sedan': {
                    'basic_wash': 500,
                    'basic_interior': 800,
                    'basic_exterior': 700
                },
                'SUV': {
                    'basic_wash': 700,
                    'basic_interior': 1000,
                    'basic_exterior': 900
                },
                'Truck': {
                    'basic_wash': 1000,
                    'basic_interior': 1300,
                    'basic_exterior': 1200
                },
                'Van': {
                    'basic_wash': 800,
                    'basic_interior': 1100,
                    'basic_exterior': 1000
                }
            },
            'premium': {
                'Sedan': {
                    'exterior_washing': 1000,
                    'interior_washing': 1200,
                    'vacuum_cleaning': 800,
                    'seats_washing': 1500,
                    'window_wiping': 500,
                    'wet_cleaning': 1000,
                    'oil_changing': 2000,
                    'brake_repairing': 2500
                },
                'SUV': {
                    'exterior_washing': 1200,
                    'interior_washing': 1400,
                    'vacuum_cleaning': 1000,
                    'seats_washing': 1800,
                    'window_wiping': 700,
                    'wet_cleaning': 1200,
                    'oil_changing': 2500,
                    'brake_repairing': 3000
                },
                'Truck': {
                    'exterior_washing': 1500,
                    'interior_washing': 1700,
                    'vacuum_cleaning': 1300,
                    'seats_washing': 2100,
                    'window_wiping': 900,
                    'wet_cleaning': 1500,
                    'oil_changing': 3000,
                    'brake_repairing': 3500
                },
                'Van': {
                    'exterior_washing': 1300,
                    'interior_washing': 1500,
                    'vacuum_cleaning': 1100,
                    'seats_washing': 1900,
                    'window_wiping': 800,
                    'wet_cleaning': 1300,
                    'oil_changing': 2700,
                    'brake_repairing': 3200
                }
            },
            'detailing': {
                'Sedan': {
                    'exterior_detailing': 3000,
                    'interior_detailing': 3500,
                    'full_detailing': 6000,
                    'leather_conditioning': 2000,
                    'fabric_shampooing': 1800,
                    'engine_detailing': 2500,
                    'tire_alloy_detailing': 1500,
                    'glass_polishing': 1200,
                    'chrome_polishing': 1000,
                    'dashboard_restoration': 1500
                },
                'SUV': {
                    'exterior_detailing': 3500,
                    'interior_detailing': 4000,
                    'full_detailing': 7000,
                    'leather_conditioning': 2500,
                    'fabric_shampooing': 2300,
                    'engine_detailing': 3000,
                    'tire_alloy_detailing': 2000,
                    'glass_polishing': 1500,
                    'chrome_polishing': 1300,
                    'dashboard_restoration': 1800
                },
                'Truck': {
                    'exterior_detailing': 4000,
                    'interior_detailing': 4500,
                    'full_detailing': 8000,
                    'leather_conditioning': 3000,
                    'fabric_shampooing': 2800,
                    'engine_detailing': 3500,
                    'tire_alloy_detailing': 2500,
                    'glass_polishing': 1800,
                    'chrome_polishing': 1600,
                    'dashboard_restoration': 2100
                },
                'Van': {
                    'exterior_detailing': 3700,
                    'interior_detailing': 4200,
                    'full_detailing': 7500,
                    'leather_conditioning': 2700,
                    'fabric_shampooing': 2500,
                    'engine_detailing': 3200,
                    'tire_alloy_detailing': 2200,
                    'glass_polishing': 1600,
                    'chrome_polishing': 1400,
                    'dashboard_restoration': 1900
                }
            }
        };

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
                            // Redirect to view-my-booking.php after successful payment
                            window.location.href = 'view-my-booking.php';
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