<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Car Wash Admin Dashboard</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        :root {
            --primary-color: #1e3c72;
            --white: #ffffff;
            --gray-light: #f5f5f5;
            --gray: #e0e0e0;
        }

        body {
            background-color: var(--white);
        }

        .container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 250px;
            background-color: var(--primary-color);
            color: var(--white);
            padding: 20px;
            transition: width 0.3s ease;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sidebar.collapsed {
            width: 70px;
        }

        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 30px;
            text-align: center;
        }

        .nav-section {
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding-bottom: 15px;
        }

        .nav-section:last-child {
            border-bottom: none;
        }

        .nav-section-title {
            font-size: 0.8rem;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 10px;
            padding-left: 12px;
        }

        .nav-item {
            padding: 12px;
            margin: 8px 0;
            cursor: pointer;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .nav-item:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }

        .nav-item.active {
            background-color: rgba(255, 255, 255, 0.2);
        }

        /* Main Content Styles */
        .main-content {
            flex: 1;
            padding: 20px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background-color: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card h3 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 1.1rem;
        }

        .stat-card .value {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
        }

        .charts-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .chart-card {
            background-color: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .recent-bookings {
            display: none; /* Initially hidden */
            background-color: var(--white);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-top: 30px;
        }

        .booking-table {
            width: 100%;
            border-collapse: collapse;
        }

        .booking-table th,
        .booking-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid var(--gray);
        }

        .status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.9rem;
        }

        .status.pending {
            background-color: #fff3cd;
            color: #856404;
        }

        .status.completed {
            background-color: #d4edda;
            color: #155724;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 70px;
            }

            .nav-section-title,
            .logo span {
                display: none;
            }

            .nav-item span {
                display: none;
            }

            .charts-container {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="sidebar" id="sidebar">
            <div class="logo">CW</div>
            <nav>
                <div class="nav-section">
                    <div class="nav-section-title">Overview</div>
                    <div class="nav-item active">Dashboard</div>
                    <div class="nav-item" id="bookingsBtn">Bookings</div>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Analytics</div>
                    <div class="nav-item">Total Users</div>
                    <div class="nav-item">Active Bookings</div>
                    <div class="nav-item">Today's Revenue</div>
                    <div class="nav-item">Active Technicians</div>
                </div>
                <div class="nav-section">
                    <div class="nav-section-title">Management</div>
                    <div class="nav-item">Users</div>
                    <div class="nav-item">Technicians</div>
                    <div class="nav-item">Settings</div>
                </div>
            </nav>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Dashboard Overview</h1>
                <div class="date">February 9, 2025</div>
            </div>

            <!-- The recent bookings section -->
            <div class="recent-bookings" id="recentBookings">
                <h3>Recent Bookings</h3>
                <table class="booking-table">
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Service Type</th>
                            <th>Vehicle Type</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Vehicle Number</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        include 'conn.php';
                        $query = "SELECT id, username, service_type, vehicle_type, booking_date, booking_time, vehicle_number, status FROM bookings ORDER BY booking_date ASC, booking_time ASC";
                        $result = $conn->query($query);
                        if ($result && $result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>{$row['id']}</td>";
                                echo "<td>{$row['username']}</td>";
                                echo "<td>{$row['service_type']}</td>";
                                echo "<td>{$row['vehicle_type']}</td>";
                                echo "<td>" . date("M d, Y", strtotime($row['booking_date'])) . "</td>";
                                echo "<td>" . date("h:i A", strtotime($row['booking_time'])) . "</td>";
                                echo "<td>{$row['vehicle_number']}</td>";
                                echo "<td class='status {$row['status']}'>{$row['status']}</td>";
                                echo "<td><button>Update</button> <button>Delete</button></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='9'>No bookings available.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        // Get the "Bookings" nav item and the "Recent Bookings" div
        const bookingsBtn = document.getElementById('bookingsBtn');
        const recentBookingsDiv = document.getElementById('recentBookings');

        // Add event listener for "Bookings" click
        bookingsBtn.addEventListener('click', function() {
            // Toggle the visibility of the recent bookings div
            if (recentBookingsDiv.style.display === 'none' || recentBookingsDiv.style.display === '') {
                recentBookingsDiv.style.display = 'block';
            } else {
                recentBookingsDiv.style.display = 'none';
            }
        });
    </script>
</body>
</html>
