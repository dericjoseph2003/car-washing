-- Create the car_care2 database if it doesn't exist
CREATE DATABASE IF NOT EXISTS car_care2;
USE car_care2;

-- Create the booking table
CREATE TABLE IF NOT EXISTS booking (
    id INT PRIMARY KEY AUTO_INCREMENT,
    service_type VARCHAR(100) NOT NULL,
    vehicle_type VARCHAR(100) NOT NULL,
    customer_id INT,
    booking_date DATETIME,
    status VARCHAR(50) DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create the feedback table
CREATE TABLE IF NOT EXISTS feedback (
    id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT,
    username VARCHAR(100) NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (booking_id) REFERENCES booking(id) ON DELETE SET NULL
);

-- Add some sample data for testing
INSERT INTO booking (service_type, vehicle_type, customer_id, booking_date) VALUES
('Car Wash', 'Sedan', 1, '2024-03-20 10:00:00'),
('Oil Change', 'SUV', 2, '2024-03-21 14:30:00'),
('Tire Rotation', 'Truck', 3, '2024-03-22 11:15:00');

INSERT INTO feedback (booking_id, username, rating, comment) VALUES
(1, 'John Doe', 5, 'Excellent service! My car looks brand new.'),
(2, 'Jane Smith', 4, 'Good service but had to wait a bit longer than expected.'),
(3, 'Mike Johnson', 5, 'Very professional staff and great attention to detail.'); 