-- CTB Residential Management System Database Schema

-- Drop database if it exists
DROP DATABASE IF EXISTS ctb_db;

-- Create database
CREATE DATABASE ctb_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Use database
USE ctb_db;

-- Create users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    role ENUM('admin', 'manager', 'resident') NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create properties table
CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('apartment', 'parking') NOT NULL,
    identifier VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    area DECIMAL(10,2) DEFAULT NULL,
    floor INT DEFAULT NULL,
    user_id INT DEFAULT NULL,
    status ENUM('occupied', 'vacant', 'maintenance') DEFAULT 'vacant',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create payments table
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_month DATE NOT NULL,
    payment_type ENUM('rent', 'maintenance', 'other') DEFAULT 'rent',
    payment_method ENUM('cash', 'check', 'bank_transfer', 'online') DEFAULT 'cash',
    reference_number VARCHAR(255),
    receipt_number VARCHAR(50),
    status ENUM('paid', 'pending', 'cancelled') DEFAULT 'paid',
    notes TEXT,
    created_by INT, -- ID of the user (admin/manager) who recorded the payment
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

-- Create maintenance_logs table
CREATE TABLE maintenance_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    reported_by INT NOT NULL,
    assigned_to INT,
    start_date DATE,
    expected_completion_date DATE,
    actual_completion_date DATE,
    status ENUM('reported', 'in_progress', 'completed', 'cancelled') DEFAULT 'reported',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium',
    cost DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (reported_by) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (email, password, name, role, status) VALUES 
('admin@ctb.com', '$2y$10$ZMMgvVJbBJKndTKPJQQaZe.4UNO9PE4Zoj9dHrAYrXXUHt.qU8YnK', 'System Administrator', 'admin', 'active');

-- Insert sample manager user (password: manager123)
INSERT INTO users (email, password, name, role, status) VALUES 
('manager@ctb.com', '$2y$10$vTcO3Epf89WpYCiNpWd5WOneMQzS8YxvWwcr4MQdDy.AzXAGi4Svy', 'Building Manager', 'manager', 'active');

-- Insert sample resident user (password: resident123)
INSERT INTO users (email, password, name, phone, role, status) VALUES 
('resident@ctb.com', '$2y$10$t9bEbXYYfpEbTDR0zLF9Y.R6vFjpbMzWM/.9dYbq6DUmzpZ1NUxDK', 'John Doe', '555-123-4567', 'resident', 'active');

-- Insert sample properties
INSERT INTO properties (type, identifier, description, area, floor, status) VALUES 
('apartment', 'A101', 'One bedroom apartment with balcony', 75.50, 1, 'vacant'),
('apartment', 'A102', 'Two bedroom apartment with sea view', 95.75, 1, 'vacant'),
('apartment', 'A201', 'Luxury apartment with three bedrooms', 120.00, 2, 'vacant'),
('parking', 'P001', 'Covered parking spot', NULL, -1, 'vacant'),
('parking', 'P002', 'Covered parking spot', NULL, -1, 'vacant');

-- Assign property to resident
UPDATE properties SET user_id = 3, status = 'occupied' WHERE identifier = 'A101';
UPDATE properties SET user_id = 3, status = 'occupied' WHERE identifier = 'P001';

-- Insert sample payments
INSERT INTO payments (user_id, property_id, amount, payment_date, payment_month, payment_type, payment_method, receipt_number, status, created_by) VALUES 
(3, 1, 5000.00, '2023-01-05', '2023-01-01', 'rent', 'bank_transfer', 'RCP-001', 'paid', 2),
(3, 1, 5000.00, '2023-02-03', '2023-02-01', 'rent', 'cash', 'RCP-002', 'paid', 2),
(3, 1, 5000.00, '2023-03-10', '2023-03-01', 'rent', 'cash', 'RCP-003', 'paid', 2);

-- Insert sample maintenance logs
INSERT INTO maintenance_logs (property_id, title, description, reported_by, assigned_to, start_date, expected_completion_date, status, priority) VALUES 
(1, 'Leaking faucet', 'The bathroom sink faucet is leaking and needs repair', 3, 2, '2023-03-15', '2023-03-17', 'in_progress', 'medium'),
(1, 'Broken AC', 'The air conditioning unit is not cooling properly', 3, 2, '2023-02-20', '2023-02-22', 'completed', 'high');

-- Create indexes for better performance
CREATE INDEX idx_properties_user ON properties(user_id);
CREATE INDEX idx_payments_user ON payments(user_id);
CREATE INDEX idx_payments_property ON payments(property_id);
CREATE INDEX idx_maintenance_property ON maintenance_logs(property_id);
CREATE INDEX idx_maintenance_reported ON maintenance_logs(reported_by);
CREATE INDEX idx_maintenance_assigned ON maintenance_logs(assigned_to); 