-- Create property_types table
CREATE TABLE IF NOT EXISTS property_types (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

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

-- Create tickets table
CREATE TABLE IF NOT EXISTS tickets (
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

-- Create resident_properties junction table if it doesn't exist
CREATE TABLE IF NOT EXISTS resident_properties (
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

-- Create payments table if it doesn't exist
CREATE TABLE IF NOT EXISTS payments (
    id INT(11) PRIMARY KEY AUTO_INCREMENT,
    user_id INT(11) NOT NULL,
    property_id INT(11) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    payment_date DATE NOT NULL,
    payment_method ENUM('credit_card', 'bank_transfer', 'cash', 'check', 'other') NOT NULL,
    description VARCHAR(255) NOT NULL,
    status ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Create activity_log table if it doesn't exist
CREATE TABLE IF NOT EXISTS activity_log (
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

-- Add property_type_id column to properties table if it doesn't exist
ALTER TABLE properties ADD COLUMN IF NOT EXISTS property_type_id INT(11) NULL AFTER name;
ALTER TABLE properties ADD CONSTRAINT IF NOT EXISTS fk_property_type FOREIGN KEY (property_type_id) REFERENCES property_types(id) ON DELETE SET NULL; 