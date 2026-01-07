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
    payment_date DATE NOT NULL,
    year INT NOT NULL COMMENT 'Year this payment is for (e.g., 2025, 2026)',
    status ENUM('paid', 'pending', 'cancelled', 'failed', 'refunded') DEFAULT 'pending' COMMENT 'paid=Payé, pending=En Attente, cancelled=Annulé, failed=Échoué, refunded=Remboursé',
    type ENUM('transfer', 'cheque') DEFAULT 'transfer' COMMENT 'transfer=Virement, cheque=Chèque',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Create cotisations table for annual fees
-- This table stores the annual cotisation (contribution) amount due for each property per year
-- Example: Apartment A101 might have 12,000 MAD due in 2025, 12,000 MAD in 2026, etc.
-- To add a new year: INSERT INTO cotisations (property_id, year, amount_due) VALUES (1, 2027, 12000.00);
-- To copy last year's amounts to new year: 
--   INSERT INTO cotisations (property_id, year, amount_due)
--   SELECT property_id, 2027, amount_due FROM cotisations WHERE year = 2026;
CREATE TABLE cotisations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    year INT NOT NULL COMMENT 'Year this cotisation applies to (e.g., 2025, 2026)',
    amount_due DECIMAL(10,2) NOT NULL COMMENT 'Annual amount due for this property in this year',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_property_year (property_id, year) COMMENT 'One cotisation per property per year'
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
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Reset AUTO_INCREMENT to start from 1
ALTER TABLE users AUTO_INCREMENT = 1;
ALTER TABLE properties AUTO_INCREMENT = 1;
ALTER TABLE payments AUTO_INCREMENT = 1;
ALTER TABLE cotisations AUTO_INCREMENT = 1;
ALTER TABLE maintenance AUTO_INCREMENT = 1;
ALTER TABLE tickets AUTO_INCREMENT = 1;
ALTER TABLE activity_log AUTO_INCREMENT = 1;

-- Insert initial admin and manager users
-- Note: IDs are omitted to let AUTO_INCREMENT handle them
INSERT INTO users (email, password, name, phone, role, status, profile_image, created_at, updated_at) VALUES
('admin@ctb.ma', '$2y$10$uJo//p7CZfZ3h0xuUWbGPOESbWW6Z0QbOmdDLmj6YME.5fEpz9kPq', 'System Administrator', '', 'admin', 'active', NULL, '2025-11-26 19:02:06', '2026-01-01 17:55:17'),
('ahlam@ctb.ma', '$2y$10$r4FpJEmclRTCBe5Souc/TOHtFaEfM1Qh298mhIfOpYothyJxFkECG', 'Ahlam', '', 'manager', 'active', NULL, '2025-11-26 19:02:06', '2026-01-01 17:54:54'),
('aboubakr@ctb.ma', '$2y$10$OYc3QSoGKHGINIeNnsXPa.BFch8xjBtit.DizxmAt1DXq5O1J9L2.', 'Aboubakr', '', 'admin', 'active', NULL, '2026-01-01 17:53:05', '2026-01-01 17:54:32'),
('rachid@ctb.ma', '$2y$10$i5OvLfZLfRC/gZenW5/q4.vY/FfQLMt3GcO47EtOokHhIDzaGFLKW', 'Rachid', '', 'admin', 'active', NULL, '2026-01-01 17:54:03', '2026-01-01 17:54:03');

-- Create indexes for better performance
CREATE INDEX idx_properties_user ON properties(user_id);
CREATE INDEX idx_payments_property ON payments(property_id);
CREATE INDEX idx_payments_year ON payments(year);
CREATE INDEX idx_payments_property_year ON payments(property_id, year);
CREATE INDEX idx_cotisations_property ON cotisations(property_id);
CREATE INDEX idx_cotisations_year ON cotisations(year);
CREATE INDEX idx_cotisations_property_year ON cotisations(property_id, year);
CREATE INDEX idx_tickets_user ON tickets(user_id);
CREATE INDEX idx_activity_user ON activity_log(user_id);
CREATE INDEX idx_maintenance_created_by ON maintenance(created_by);
CREATE INDEX idx_maintenance_status ON maintenance(status);
CREATE INDEX idx_maintenance_dates ON maintenance(start_date, end_date);

-- ============================================================================
-- EXAMPLE QUERIES FOR COTISATIONS SYSTEM
-- ============================================================================

-- Query 1: View cotisation summary for a specific year (e.g., 2026)
-- Shows: Property, Amount Due, Amount Paid, Remaining Balance
/*
SELECT 
    p.identifier AS property_identifier,
    p.type AS property_type,
    c.amount_due,
    COALESCE(SUM(CASE WHEN pay.status = 'paid' THEN pay.amount ELSE 0 END), 0) AS amount_paid,
    (c.amount_due - COALESCE(SUM(CASE WHEN pay.status = 'paid' THEN pay.amount ELSE 0 END), 0)) AS remaining_balance
FROM cotisations c
JOIN properties p ON c.property_id = p.id
LEFT JOIN payments pay ON pay.property_id = c.property_id AND pay.year = c.year
WHERE c.year = 2026
GROUP BY c.id, p.identifier, p.type, c.amount_due
ORDER BY p.identifier;
*/

-- Query 2: Copy last year's cotisations to a new year (e.g., copy 2026 to 2027)
-- This automatically creates cotisations for all properties for the new year
/*
INSERT INTO cotisations (property_id, year, amount_due)
SELECT property_id, 2027, amount_due 
FROM cotisations 
WHERE year = 2026;
*/

-- Query 3: Get payment history for a specific property and year
/*
SELECT 
    payment_date,
    amount,
    status,
    type,
    year
FROM payments
WHERE property_id = 1 AND year = 2025
ORDER BY payment_date DESC;
*/

-- Query 4: View all cotisations for a specific property across all years
/*
SELECT 
    year,
    amount_due,
    COALESCE(SUM(CASE WHEN pay.status = 'paid' THEN pay.amount ELSE 0 END), 0) AS amount_paid,
    (amount_due - COALESCE(SUM(CASE WHEN pay.status = 'paid' THEN pay.amount ELSE 0 END), 0)) AS remaining
FROM cotisations c
LEFT JOIN payments pay ON pay.property_id = c.property_id AND pay.year = c.year
WHERE c.property_id = 1
GROUP BY c.id, c.year, c.amount_due
ORDER BY c.year DESC;
*/

-- Query 5: Find properties with outstanding balances for a specific year
/*
SELECT 
    p.identifier,
    p.type,
    c.amount_due,
    COALESCE(SUM(CASE WHEN pay.status = 'paid' THEN pay.amount ELSE 0 END), 0) AS amount_paid,
    (c.amount_due - COALESCE(SUM(CASE WHEN pay.status = 'paid' THEN pay.amount ELSE 0 END), 0)) AS remaining_balance
FROM cotisations c
JOIN properties p ON c.property_id = p.id
LEFT JOIN payments pay ON pay.property_id = c.property_id AND pay.year = c.year AND pay.status = 'paid'
WHERE c.year = 2026
GROUP BY c.id, p.identifier, p.type, c.amount_due
HAVING remaining_balance > 0
ORDER BY remaining_balance DESC;
*/ 