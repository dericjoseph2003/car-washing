<?php
session_start();
require_once "conn.php";
require 'vendor/autoload.php'; // Include Razorpay SDK

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

// Initialize Razorpay API with your key and secret
$api = new Api('rzp_test_NPqdzqGri9yJVc', 'ISG4iVXQG6eJGn6HxVL5AgbT');

// Ensure proper content type for JSON responses
header('Content-Type: application/json');

// Disable error reporting for notices
error_reporting(E_ALL & ~E_NOTICE);

// Buffer output to prevent any unwanted output before JSON
ob_start();

// Add initial logging
error_log("Payment script started - POST data: " . print_r($_POST, true));

function sendBookingConfirmationEmail($booking_id, $userEmail, $bookingDetails) {
    $mail = new PHPMailer(true);
    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'ccare8703@gmail.com';
        $mail->Password   = 'uvui zlfu mfce jcru';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Recipients
        $mail->setFrom('ccare8703@gmail.com', 'CarCare');
        $mail->addAddress($userEmail);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Booking Confirmation - CarCare Services';

        // Email template
        $emailBody = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <div style='background-color: #1e3c72; color: white; padding: 20px; text-align: center;'>
                <h2>Thank You for Your Booking!</h2>
            </div>
            
            <div style='padding: 20px; background-color: #f8f9fa;'>
                <h3>Booking Details:</h3>
                <p><strong>Booking ID:</strong> #{$booking_id}</p>
                <p><strong>Service:</strong> {$bookingDetails['service_type']}</p>
                <p><strong>Category:</strong> {$bookingDetails['service_category']}</p>
                <p><strong>Vehicle:</strong> {$bookingDetails['vehicle_type']} ({$bookingDetails['vehicle_number']})</p>
                <p><strong>Date:</strong> " . date('d M Y', strtotime($bookingDetails['booking_date'])) . "</p>
                <p><strong>Time:</strong> " . date('h:i A', strtotime($bookingDetails['booking_time'])) . "</p>
                <p><strong>Amount Paid:</strong> ₹{$bookingDetails['price']}</p>
            </div>

            <div style='padding: 20px; background-color: #ffffff;'>
                <h3>What's Next?</h3>
                <p>1. Our team will review your booking</p>
                <p>2. You'll receive a confirmation message</p>
                <p>3. Please arrive 10 minutes before your scheduled time</p>
            </div>

            <div style='background-color: #f8f9fa; padding: 20px; text-align: center;'>
                <p>If you have any questions, please contact us:</p>
                <p>Phone: +012 345 67890</p>
                <p>Email: info@carcare.com</p>
            </div>

            <div style='background-color: #1e3c72; color: white; padding: 15px; text-align: center;'>
                <small>&copy; " . date('Y') . " CarCare Services. All rights reserved.</small>
            </div>
        </div>";

        $mail->Body = $emailBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '</p>'], ["\n", "\n\n"], $emailBody));

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'verify_payment') {
        try {
            // Verify the payment signature
            $attributes = array(
                'razorpay_order_id' => $_POST['razorpay_order_id'],
                'razorpay_payment_id' => $_POST['razorpay_payment_id'],
                'razorpay_signature' => $_POST['razorpay_signature']
            );
            
            $api->utility->verifyPaymentSignature($attributes);
            
            // Get the booking ID from the order
            $order = $api->order->fetch($_POST['razorpay_order_id']);
            $booking_id = substr($order->receipt, 8); // Remove 'booking_' prefix
            
            // Get payment details
            $payment = $api->payment->fetch($_POST['razorpay_payment_id']);
            
            // Insert payment record into payments table
            $insert_payment_sql = "INSERT INTO payments (booking_id, amount, payment_date, payment_method, status) 
                                 VALUES (?, ?, NOW(), 'Razorpay', 'completed')";
            $stmt = $conn->prepare($insert_payment_sql);
            $stmt->bind_param("id", $booking_id, $payment->amount);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to insert payment record: " . $stmt->error);
            }
            
            // Update booking status and payment status
            $sql = "UPDATE booking SET status = 'Confirmed', payment_status = 'completed' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $booking_id);
            
            if ($stmt->execute()) {
                // Fetch booking details
                $sql = "SELECT b.*, u.Email 
                        FROM booking b 
                        LEFT JOIN users u ON b.username = u.Username 
                        WHERE b.id = ?";
                
                if ($stmt = $conn->prepare($sql)) {
                    $stmt->bind_param("i", $booking_id);
                    $stmt->execute();
                    $result = $stmt->get_result();
                    $bookingDetails = $result->fetch_assoc();
                    
                    if ($bookingDetails && $bookingDetails['Email']) {
                        // Send confirmation email
                        $emailSent = sendBookingConfirmationEmail($booking_id, $bookingDetails['Email'], $bookingDetails);
                        
                        if ($emailSent) {
                            error_log("Booking confirmation email sent successfully for booking ID: " . $booking_id);
                        } else {
                            error_log("Failed to send booking confirmation email for booking ID: " . $booking_id);
                        }
                    }
                    
                    $stmt->close();
                }

                echo json_encode([
                    'status' => 'success',
                    'message' => 'Payment verified and booking confirmed',
                    'booking_id' => $booking_id
                ]);
            } else {
                throw new Exception("Failed to update booking status");
            }
            
        } catch (SignatureVerificationError $e) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid payment signature'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
        exit();
    }

    try {
        // Log all POST data for debugging
        error_log("POST data received: " . print_r($_POST, true));

        // Handle initial payment creation
        if (isset($_POST['booking_id'])) {
            $booking_id = $_POST['booking_id'];
            
            // Fetch booking details
            $sql = "SELECT b.*, u.Email as customer_email 
                   FROM booking b 
                   LEFT JOIN users u ON b.username = u.Username 
                   WHERE b.id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("i", $booking_id);
            if (!$stmt->execute()) {
                throw new Exception("Execute failed: " . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $booking = $result->fetch_assoc();
            
            if (!$booking) {
                throw new Exception("Booking not found for ID: " . $booking_id);
            }

            // Create Razorpay order
            $orderData = [
                'receipt'         => 'booking_' . $booking_id,
                'amount'          => $booking['price'] * 100, // Convert to paise
                'currency'        => 'INR',
                'payment_capture' => 1,
                'notes'           => [
                    'booking_id' => $booking_id
                ]
            ];
            
            $order = $api->order->create($orderData);
            
            // Update booking with order ID
            $sql = "UPDATE booking SET payment_id = ?, payment_status = 'pending' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            if (!$stmt) {
                throw new Exception("Prepare failed: " . $conn->error);
            }
            
            $stmt->bind_param("si", $order['id'], $booking_id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update booking with order ID: " . $stmt->error);
            }
            
            ob_clean();
            echo json_encode([
                'status' => 'success',
                'order_id' => $order['id'],
                'amount' => $orderData['amount'],
                'currency' => $orderData['currency'],
                'email' => $booking['customer_email'] ?? ''
            ]);
            
        } else {
            throw new Exception("Missing required parameters: booking_id or payment details");
        }
    } catch (Exception $e) {
        error_log("Payment Error: " . $e->getMessage());
        ob_clean();
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
    }
    exit();
}

// If we get here, it wasn't a POST request
ob_clean();
echo json_encode(['status' => 'error', 'message' => 'Invalid request method']);
exit();
?>
