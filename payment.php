<?php
session_start();
require_once "conn.php";
require 'vendor/autoload.php'; // Include Razorpay SDK

use Razorpay\Api\Api;
use Razorpay\Api\Errors\SignatureVerificationError;

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
            
            // Update booking status and payment status
            $sql = "UPDATE booking SET status = 'Confirmed', payment_status = 'completed' WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("i", $booking_id);
            
            if ($stmt->execute()) {
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
                'amount'          => 50000,
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
