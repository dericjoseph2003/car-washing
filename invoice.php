<?php
session_start();
require_once "conn.php";

// Check if user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

// Check if booking ID is provided
if (!isset($_GET['booking_id'])) {
    die("Booking ID not provided");
}

$booking_id = $_GET['booking_id'];
$username = $_SESSION['username'];

// Fetch booking details with payment information
$sql = "SELECT b.*, p.payment_date, p.payment_method, p.amount as paid_amount, u.Email, u.Phone 
        FROM booking b 
        LEFT JOIN payments p ON b.id = p.booking_id 
        LEFT JOIN users u ON b.username = u.Username
        WHERE b.id = ? AND b.username = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $booking_id, $username);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Booking not found or unauthorized access");
}

$booking = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?php echo $booking_id; ?> - CarCare</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
            }
        }

        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }

        .invoice-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #1e3c72;
        }

        .invoice-title {
            color: #1e3c72;
            font-size: 28px;
            margin-bottom: 10px;
        }

        .company-details {
            margin-bottom: 20px;
        }

        .invoice-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
        }

        .customer-details, .invoice-details {
            flex: 1;
        }

        .service-details {
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }

        .total-amount {
            text-align: right;
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            padding-top: 10px;
            border-top: 2px solid #1e3c72;
        }

        .payment-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: bold;
            background-color: #28a745;
            color: white;
        }

        .print-button {
            background-color: #1e3c72;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-bottom: 20px;
        }

        .print-button:hover {
            background-color: #15294f;
        }

        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 14px;
        }

        .qr-code {
            text-align: right;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: right;">
        <button onclick="window.print()" class="print-button">
            <i class="fas fa-print"></i> Print Invoice
        </button>
    </div>

    <div class="invoice-header">
        <h1 class="invoice-title">CarCare Services</h1>
        <div class="company-details">
            <p>123 Service Street, Auto District</p>
            <p>Phone: +012 345 67890 | Email: info@carcare.com</p>
        </div>
    </div>

    <div class="invoice-meta">
        <div class="customer-details">
            <h3>Bill To:</h3>
            <p><strong><?php echo htmlspecialchars($booking['username']); ?></strong></p>
            <p>Email: <?php echo htmlspecialchars($booking['Email']); ?></p>
            <p>Phone: <?php echo htmlspecialchars($booking['Phone']); ?></p>
        </div>
        <div class="invoice-details">
            <h3>Invoice Details:</h3>
            <p><strong>Invoice #:</strong> INV-<?php echo str_pad($booking_id, 6, '0', STR_PAD_LEFT); ?></p>
            <p><strong>Date:</strong> <?php echo date('d M Y', strtotime($booking['payment_date'])); ?></p>
            <p><strong>Booking ID:</strong> <?php echo $booking_id; ?></p>
        </div>
    </div>

    <div class="service-details">
        <table>
            <thead>
                <tr>
                    <th>Service Description</th>
                    <th>Vehicle Type</th>
                    <th>Service Date</th>
                    <th>Amount</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><?php echo htmlspecialchars($booking['service_type']); ?></td>
                    <td><?php echo htmlspecialchars($booking['vehicle_type']); ?></td>
                    <td><?php echo date('d M Y', strtotime($booking['booking_date'])) . ' ' . 
                               date('h:i A', strtotime($booking['booking_time'])); ?></td>
                    <td>₹<?php echo number_format($booking['price'], 2); ?></td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="total-amount">
        <p>Total Amount: ₹<?php echo number_format($booking['price'], 2); ?></p>
        <p style="margin-top: 10px;">
            Payment Status: 
            <span class="payment-status">
                <?php echo ucfirst($booking['payment_status']); ?>
            </span>
        </p>
        <p style="margin-top: 10px;">
            Payment Method: <?php echo ucfirst($booking['payment_method']); ?>
        </p>
    </div>

    <div class="footer">
        <p>Thank you for choosing CarCare Services!</p>
        <p>This is a computer-generated invoice, no signature required.</p>
    </div>
</body>
</html> 