-- CTB Residential Management System Database Schema
-- Combined SQL file

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

-- Create property_types table
CREATE TABLE property_types (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
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

-- Add property_type_id column to properties table
ALTER TABLE properties ADD COLUMN property_type_id INT(11) NULL AFTER type;
ALTER TABLE properties ADD CONSTRAINT fk_property_type FOREIGN KEY (property_type_id) REFERENCES property_types(id) ON DELETE SET NULL;

-- Create resident_properties junction table
CREATE TABLE resident_properties (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    property_id INT(11) NOT NULL,
    unit_number VARCHAR(20) NOT NULL,
    move_in_date DATE NOT NULL,
    lease_end_date DATE,
    monthly_rent DECIMAL(10,2) NOT NULL,
    status ENUM('active', 'past', 'pending') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_resident_unit (user_id, property_id, unit_number)
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
    payment_method ENUM('cash', 'check', 'bank_transfer', 'online', 'credit_card', 'other') DEFAULT 'cash',
    reference_number VARCHAR(255),
    receipt_number VARCHAR(50),
    description VARCHAR(255),
    status ENUM('paid', 'pending', 'cancelled', 'completed', 'failed', 'refunded') DEFAULT 'paid',
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

-- Create maintenance_updates table
CREATE TABLE maintenance_updates (
    id INT(11) NOT NULL AUTO_INCREMENT,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('scheduled', 'in_progress', 'completed', 'delayed', 'cancelled') NOT NULL DEFAULT 'scheduled',
    priority ENUM('low', 'medium', 'high', 'emergency') NOT NULL DEFAULT 'medium',
    created_by INT(11) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY created_by (created_by),
    CONSTRAINT maintenance_created_by_fk FOREIGN KEY (created_by) REFERENCES users (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Create tickets table
CREATE TABLE tickets (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    property_id INT(11) NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    category ENUM('maintenance', 'billing', 'noise_complaint', 'other') NOT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
    status ENUM('open', 'in_progress', 'closed', 'reopened') NOT NULL DEFAULT 'open',
    assigned_to INT(11) NULL,
    attachment VARCHAR(255) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    FOREIGN KEY (assigned_to) REFERENCES users(id) ON DELETE SET NULL
);

-- Create activity_log table
CREATE TABLE activity_log (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) NOT NULL,
    entity_id INT(11) NOT NULL,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample data

-- Insert sample admin user (password: "password")
INSERT INTO users (email, password, name, role, status) VALUES 
('admin@ctb.com', '$2y$10$HcvUc.6RkX9Q1uIz1Zcj/uvPYN1D7EGd0YKntr9yKJjlOHgJ2s3ue', 'System Administrator', 'admin', 'active');

-- Insert sample manager user (password: "password")
INSERT INTO users (email, password, name, role, status) VALUES 
('manager@ctb.com', '$2y$10$HcvUc.6RkX9Q1uIz1Zcj/uvPYN1D7EGd0YKntr9yKJjlOHgJ2s3ue', 'Building Manager', 'manager', 'active');

-- Insert sample resident user (password: "password")
INSERT INTO users (email, password, name, phone, role, status) VALUES 
('resident@ctb.com', '$2y$10$HcvUc.6RkX9Q1uIz1Zcj/uvPYN1D7EGd0YKntr9yKJjlOHgJ2s3ue', 'John Doe', '555-123-4567', 'resident', 'active');

-- Insert sample property types
INSERT INTO property_types (name, description) VALUES
('Apartment', 'Multi-unit residential building where units are stacked vertically'),
('Single Family House', 'Standalone residential building designed for one family'),
('Townhouse', 'Multi-floor home that shares one or two walls with adjacent properties'),
('Condominium', 'Privately owned unit within a building of other units'),
('Duplex', 'Building with two separate dwelling units, either side by side or one above the other'),
('Studio', 'Single room unit that combines living room, bedroom, and kitchen'),
('Penthouse', 'Luxury apartment on the top floor of a high-rise building'),
('Loft', 'Large, open space with minimal interior walls, often in converted industrial buildings');

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