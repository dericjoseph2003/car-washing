<?php
session_start();
require_once "conn.php";

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    $_SESSION['error'] = "Please login to view your bookings";
    header("Location: login.php");
    exit();
}

// Add debugging
echo "<!-- Logged in user: " . $_SESSION['username'] . " -->";

// Fetch user's bookings with error checking
$username = $_SESSION['username'];
$sql = "SELECT * FROM booking WHERE username = ? ORDER BY booking_date DESC, booking_time DESC";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    die("Prepare failed: " . $conn->error);
}

$stmt->bind_param("s", $username);
if (!$stmt->execute()) {
    die("Execute failed: " . $stmt->error);
}

$result = $stmt->get_result();

// Add debugging
echo "<!-- Number of bookings found: " . $result->num_rows . " -->";

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - Car Care</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
    <style>
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

        .container {
            margin: 80px auto;
            padding: 20px;
        }

        .booking-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            padding: 20px;
        }

        .booking-status {
            font-weight: bold;
            padding: 5px 10px;
            border-radius: 5px;
            display: inline-block;
        }

        .status-pending {
            background-color: #ffc107;
            color: #000;
        }

        .status-confirmed {
            background-color: #28a745;
            color: white;
        }

        .status-completed {
            background-color: #28a745;
            color: white;
        }

        .status-cancelled {
            background-color: #dc3545;
            color: white;
        }

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

        .payment-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 20px;
            border: 1px solid #e9ecef;
        }

        .payment-details h5 {
            color: #2c3e50;
            margin-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 10px;
        }

        .payment-details .fa-credit-card {
            color: #3498db;
            margin-right: 8px;
        }

        .payment-status {
            padding: 5px 12px;
            border-radius: 4px;
            font-size: 0.9em;
            font-weight: 500;
        }

        .text-success {
            font-weight: 600;
        }

        .text-muted {
            color: #6c757d;
        }

        .booking-card {
            transition: all 0.3s ease;
        }

        .booking-card:hover {
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .badge {
            padding: 8px 12px;
            font-size: 0.9em;
        }
        
        .badge-success {
            background-color: #28a745;
        }
        
        .badge-warning {
            background-color: #ffc107;
            color: #000;
        }
        
        .badge-danger {
            background-color: #dc3545;
        }
        
        .badge-secondary {
            background-color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <h1>My Bookings</h1>
            <div class="nav-links">
                <a href="booking.php" class="nav-link">Book a Service</a>
                <a href="index.php" class="nav-link">Home</a>
            </div>
        </div>
    </div>

    <div class="container">
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>

        <?php 
        // Add debugging
        if ($result) {
            echo "<!-- Result is valid -->";
            if ($result->num_rows > 0) {
                ?>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Booking ID</th>
                                <th>Service Type</th>
                                <th>Vehicle Type</th>
                                <th>Date & Time</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Payment Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = $result->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['id']; ?></td>
                                    <td><?php echo $row['service_type']; ?></td>
                                    <td><?php echo $row['vehicle_type']; ?></td>
                                    <td>
                                        <?php 
                                        echo date('d-m-Y', strtotime($row['booking_date'])) . '<br>';
                                        echo date('h:i A', strtotime($row['booking_time']));
                                        ?>
                                    </td>
                                    <td>₹<?php echo number_format($row['price'], 2); ?></td>
                                    <td><?php echo ucfirst($row['status']); ?></td>
                                    <td>
                                        <?php 
                                        if ($row['payment_status'] === 'completed') {
                                            echo '<span class="badge badge-success">Paid</span>';
                                        } elseif ($row['payment_status'] === 'pending') {
                                            echo '<span class="badge badge-warning">Pending</span>';
                                        } elseif ($row['payment_status'] === 'failed') {
                                            echo '<span class="badge badge-danger">Failed</span>';
                                        } else {
                                            echo '<span class="badge badge-secondary">Not Paid</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($row['payment_status'] !== 'completed'): ?>
                                            <button class="btn btn-primary btn-sm" 
                                                    onclick="proceedToPayment(<?php echo $row['id']; ?>)">
                                                Pay Now
                                            </button>
                                        <?php else: ?>
                                            <div class="d-flex flex-column gap-2">
                                                <span class="text-success">
                                                    <i class="fas fa-check-circle"></i> Payment Complete
                                                </span>
                                                <a href="invoice.php?booking_id=<?php echo $row['id']; ?>" 
                                                   class="btn btn-info btn-sm mt-2" 
                                                   target="_blank">
                                                    <i class="fas fa-file-invoice"></i> View Invoice
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <?php
            } else {
                ?>
                <div class="text-center">
                    <h3>No bookings found</h3>
                    <p>You haven't made any bookings yet.</p>
                    <a href="booking.php" class="btn btn-primary mt-3">Book a Service</a>
                </div>
                <?php
            }
        } else {
            echo "<!-- Result is invalid -->";
        }
        ?>
    </div>

    <div class="footer">
        <p>&copy; <?php echo date("Y"); ?> Car Care Services. All Rights Reserved.</p>
    </div>

    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1.min.js"></script>
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
    function proceedToPayment(bookingId) {
        const button = event.target;
        button.disabled = true;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        fetch('payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'booking_id=' + bookingId
        })
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON response:', text);
                throw new Error('Server returned invalid response');
            }
        })
        .then(data => {
            if (data.status === 'success') {
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
                        
                        try {
                            // Proceed with payment verification
                            verifyPayment(response, data.order_id);
                        } catch (error) {
                            console.error('Handler Error:', error);
                            alert('Payment processing error: ' + error.message);
                            button.disabled = false;
                            button.innerHTML = 'Pay Now';
                        }
                    },
                    "modal": {
                        "ondismiss": function() {
                            button.disabled = false;
                            button.innerHTML = 'Pay Now';
                        }
                    },
                    "prefill": {
                        "name": "<?php echo $_SESSION['username']; ?>",
                        "email": data.email || ""
                    },
                    "theme": {
                        "color": "#1e3c72"
                    }
                };
                var rzp1 = new Razorpay(options);
                rzp1.on('payment.failed', function (response) {
                    console.error('Payment failed:', response.error);
                    alert('Payment failed: ' + (response.error.description || 'Please try again.'));
                    button.disabled = false;
                    button.innerHTML = 'Pay Now';
                });
                rzp1.open();
            } else {
                throw new Error(data.message || 'Error creating payment');
            }
        })
        .catch(error => {
            console.error('Payment Error:', error);
            alert('Payment Error: ' + error.message);
            button.disabled = false;
            button.innerHTML = 'Pay Now';
        });
    }

    function verifyPayment(response, orderId) {
        console.log('Starting payment verification with:', response, 'Order ID:', orderId);
        
        // Show loading state
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'alert alert-info';
        loadingDiv.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying payment...';
        document.querySelector('.container').prepend(loadingDiv);

        // Create form data with all required parameters
        const formData = new URLSearchParams();
        formData.append('action', 'verify_payment');
        formData.append('razorpay_payment_id', response.razorpay_payment_id);
        formData.append('razorpay_order_id', response.razorpay_order_id || orderId);
        formData.append('razorpay_signature', response.razorpay_signature);

        // Log the data being sent
        console.log('Sending verification data:', Object.fromEntries(formData));

        fetch('payment.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: formData
        })
        .then(async response => {
            const text = await response.text();
            console.log('Server response:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Invalid JSON response:', text);
                throw new Error('Server returned invalid response');
            }
        })
        .then(data => {
            if (data.status === 'success') {
                // Show success message
                const successDiv = document.createElement('div');
                successDiv.className = 'alert alert-success';
                successDiv.innerHTML = '<i class="fas fa-check-circle"></i> Payment successful! Refreshing page...';
                document.querySelector('.container').prepend(successDiv);
                
                // Remove loading message
                loadingDiv.remove();
                
                // Refresh the page after a short delay
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                throw new Error(data.message || 'Payment verification failed');
            }
        })
        .catch(error => {
            console.error('Verification Error:', error);
            
            // Show error message
            const errorDiv = document.createElement('div');
            errorDiv.className = 'alert alert-danger';
            errorDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${error.message}`;
            document.querySelector('.container').prepend(errorDiv);
            
            // Remove loading message
            loadingDiv.remove();
        });
    }
    </script>
</body>
</html> 