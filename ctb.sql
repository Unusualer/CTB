-- CTB Residential Management System Database Schema
-- Simplified version

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
    role ENUM('admin', 'manager', 'resident') NOT NULL COMMENT 'admin=Administrateur, manager=Gestionnaire, resident=Résident',
    status ENUM('active', 'inactive') DEFAULT 'active' COMMENT 'active=Actif, inactive=Inactif',
    profile_image VARCHAR(255) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Create properties table with simplified structure
CREATE TABLE properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type ENUM('apartment', 'parking') NOT NULL COMMENT 'apartment=Appartement, parking=Place de Parking',
    identifier VARCHAR(50) NOT NULL UNIQUE,
    user_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- Create payments table with simplified structure
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    month DATE NOT NULL,
    status ENUM('paid', 'pending', 'cancelled', 'failed', 'refunded') DEFAULT 'pending' COMMENT 'paid=Payé, pending=En Attente, cancelled=Annulé, failed=Échoué, refunded=Remboursé',
    type ENUM('transfer', 'cheque') DEFAULT 'transfer' COMMENT 'transfer=Virement, cheque=Chèque',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Create maintenance table
CREATE TABLE maintenance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    location VARCHAR(255) NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status ENUM('scheduled', 'in_progress', 'completed', 'delayed', 'cancelled') DEFAULT 'scheduled' COMMENT 'scheduled=Programmé, in_progress=En Cours, completed=Terminé, delayed=Retardé, cancelled=Annulé',
    priority ENUM('low', 'medium', 'high', 'emergency') DEFAULT 'medium' COMMENT 'low=Basse, medium=Moyenne, high=Haute, emergency=Urgente',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);

-- Create tickets table with simplified structure and response column
CREATE TABLE tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    response TEXT,
    status ENUM('open', 'in_progress', 'closed', 'reopened') DEFAULT 'open' COMMENT 'open=Ouvert, in_progress=En Cours, closed=Fermé, reopened=Réouvert',
    priority ENUM('low', 'medium', 'high', 'urgent') DEFAULT 'medium' COMMENT 'low=Basse, medium=Moyenne, high=Haute, urgent=Urgente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Create activity_log table with simplified structure
CREATE TABLE activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    entity_type VARCHAR(50) DEFAULT NULL,
    entity_id INT DEFAULT NULL,
    details TEXT,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert sample data

-- Insert sample admin user (password: "password")
INSERT INTO users (email, password, name, role, status) VALUES 
('admin@ctb.com', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'System Administrator', 'admin', 'active');

-- Insert sample manager user (password: "password")
INSERT INTO users (email, password, name, role, status) VALUES 
('manager@ctb.com', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'Building Manager', 'manager', 'active');

-- Insert sample resident user (password: "password")
INSERT INTO users (email, password, name, phone, role, status) VALUES 
('resident@ctb.com', '$2y$10$Km.56tx92n1R9o4MZuwJleiCDikkBjyDRwNzpmE44hCpfBzQtu2k2', 'John Doe', '555-123-4567', 'resident', 'active');

-- Insert sample properties
INSERT INTO properties (type, identifier, user_id) VALUES 
('apartment', 'A101', 3),
('apartment', 'A102', NULL),
('parking', 'P456', 3);

-- Insert sample payments
INSERT INTO payments (property_id, amount, month, status, type) VALUES 
(1, 1000.00, '2023-01-01', 'paid', 'transfer'),
(1, 1000.00, '2023-02-01', 'paid', 'transfer'),
(3, 250.00, '2023-01-01', 'paid', 'cheque');

-- Insert sample tickets
INSERT INTO tickets (user_id, subject, description, status) VALUES 
(3, 'Noisy neighbors', 'The neighbors in A102 are making too much noise at night', 'open'),
(3, 'Parking issues', 'Someone keeps parking in my spot P456', 'closed');

-- Insert sample activity logs
INSERT INTO activity_log (user_id, action, entity_type, entity_id, details, description) VALUES 
(1, 'login', 'user', 1, 'Admin login', 'Admin logged into the system'),
(3, 'payment', 'property', 1, 'Rent payment for January', 'Resident made a rent payment for January 2023');

-- Insert sample maintenance updates
INSERT INTO maintenance (title, description, location, start_date, end_date, status, priority, created_by) VALUES
('Elevator Maintenance', 'Annual inspection and maintenance of elevators in Building A', 'Building A', DATE_ADD(CURDATE(), INTERVAL 5 DAY), DATE_ADD(CURDATE(), INTERVAL 6 DAY), 'scheduled', 'medium', 1),
('Roof Repair', 'Fixing leak in roof above apartment A101', 'Building A - A101', DATE_ADD(CURDATE(), INTERVAL -2 DAY), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'in_progress', 'high', 1),
('HVAC System Service', 'Regular maintenance of HVAC systems in all apartments', 'All Buildings', DATE_ADD(CURDATE(), INTERVAL 10 DAY), DATE_ADD(CURDATE(), INTERVAL 15 DAY), 'scheduled', 'low', 2),
('Parking Lot Repainting', 'Repainting parking lot lines and signs', 'Parking Area', DATE_ADD(CURDATE(), INTERVAL -10 DAY), DATE_ADD(CURDATE(), INTERVAL -8 DAY), 'completed', 'medium', 1);

-- Create indexes for better performance
CREATE INDEX idx_properties_user ON properties(user_id);
CREATE INDEX idx_payments_property ON payments(property_id);
CREATE INDEX idx_tickets_user ON tickets(user_id);
CREATE INDEX idx_activity_user ON activity_log(user_id);
CREATE INDEX idx_maintenance_created_by ON maintenance(created_by);
CREATE INDEX idx_maintenance_status ON maintenance(status);
CREATE INDEX idx_maintenance_dates ON maintenance(start_date, end_date); 