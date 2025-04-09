<?php
// Include database configuration
require_once 'includes/config.php';

try {
    // Drop the existing resident_properties table if it exists
    $dropTableQuery = "DROP TABLE IF EXISTS resident_properties";
    $conn->exec($dropTableQuery);
    
    echo "Existing resident_properties table dropped successfully (if it existed).<br>";
    
    // Create the resident_properties junction table
    $createTableQuery = "CREATE TABLE resident_properties (
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
    )";
    
    $conn->exec($createTableQuery);
    echo "Resident Properties table created successfully.<br>";
    
    // Get resident user IDs (roles = 'resident')
    $residentIds = [];
    $residentQuery = "SELECT id FROM users WHERE role = 'resident' LIMIT 10";
    $residentResult = $conn->query($residentQuery);
    
    if ($residentResult->rowCount() > 0) {
        while ($row = $residentResult->fetch(PDO::FETCH_ASSOC)) {
            $residentIds[] = $row['id'];
        }
    } else {
        // Default resident IDs if no residents found
        $residentIds = [2, 3, 4, 5, 6];
        echo "No residents found in users table. Using default IDs.<br>";
    }
    
    // Get property IDs
    $propertyIds = [];
    $propertyQuery = "SELECT id FROM properties LIMIT 5";
    $propertyResult = $conn->query($propertyQuery);
    
    if ($propertyResult->rowCount() > 0) {
        while ($row = $propertyResult->fetch(PDO::FETCH_ASSOC)) {
            $propertyIds[] = $row['id'];
        }
    } else {
        // Default property IDs if no properties found
        $propertyIds = [1, 2, 3, 4, 5];
        echo "No properties found in properties table. Using default IDs.<br>";
    }
    
    // Sample unit numbers for each property
    $unitsByProperty = [
        1 => ["101", "102", "103", "201", "202", "203", "301", "302", "303"],
        2 => ["A1", "A2", "B1", "B2", "C1", "C2", "D1", "D2", "PH1"],
        3 => ["1A", "1B", "2A", "2B", "3A", "3B", "4A", "4B", "5A"],
        4 => ["101", "102", "201", "202", "301", "302", "PH1", "PH2"],
        5 => ["Ground1", "Ground2", "First1", "First2", "Second1", "Second2"]
    ];
    
    // Generate random dates within a reasonable range
    function randomDate($start_date, $end_date) {
        $min = strtotime($start_date);
        $max = strtotime($end_date);
        $random_date = rand($min, $max);
        return date('Y-m-d', $random_date);
    }
    
    // Prepare insert statement
    $insertQuery = "INSERT INTO resident_properties 
                    (user_id, property_id, unit_number, move_in_date, lease_end_date, monthly_rent, status) 
                    VALUES 
                    (:user_id, :property_id, :unit_number, :move_in_date, :lease_end_date, :monthly_rent, :status)";
    $stmt = $conn->prepare($insertQuery);
    
    // Sample resident_properties data
    $residentProperties = [];
    $insertCount = 0;
    
    // Create at least one property for each resident
    foreach ($residentIds as $index => $userId) {
        // Select a property for this resident
        $propertyId = $propertyIds[$index % count($propertyIds)];
        
        // Select a unit from the property
        $availableUnits = $unitsByProperty[$propertyId] ?? ["101", "102", "103"];
        $unitNumber = $availableUnits[array_rand($availableUnits)];
        
        // Generate random dates
        $move_in_date = randomDate('2021-01-01', '2023-06-01');
        $lease_end_date = randomDate(date('Y-m-d', strtotime($move_in_date . ' + 1 year')), '2025-12-31');
        
        // Generate random monthly rent between $800 and $3000
        $monthly_rent = rand(800, 3000);
        
        // Create record
        $residentProperty = [
            'user_id' => $userId,
            'property_id' => $propertyId,
            'unit_number' => $unitNumber,
            'move_in_date' => $move_in_date,
            'lease_end_date' => $lease_end_date,
            'monthly_rent' => $monthly_rent,
            'status' => 'active'
        ];
        
        $residentProperties[] = $residentProperty;
        
        // Execute insert
        $stmt->bindParam(':user_id', $residentProperty['user_id']);
        $stmt->bindParam(':property_id', $residentProperty['property_id']);
        $stmt->bindParam(':unit_number', $residentProperty['unit_number']);
        $stmt->bindParam(':move_in_date', $residentProperty['move_in_date']);
        $stmt->bindParam(':lease_end_date', $residentProperty['lease_end_date']);
        $stmt->bindParam(':monthly_rent', $residentProperty['monthly_rent']);
        $stmt->bindParam(':status', $residentProperty['status']);
        
        $stmt->execute();
        $insertCount++;
    }
    
    // Add some past residences for variety (for the first two residents)
    for ($i = 0; $i < 2; $i++) {
        if (isset($residentIds[$i])) {
            // Select a different property
            $propertyId = $propertyIds[($i + 2) % count($propertyIds)];
            
            // Select a unit
            $availableUnits = $unitsByProperty[$propertyId] ?? ["201", "202", "203"];
            $unitNumber = $availableUnits[array_rand($availableUnits)];
            
            // Generate past dates
            $move_in_date = randomDate('2019-01-01', '2020-01-01');
            $lease_end_date = randomDate('2020-02-01', '2020-12-31');
            
            $monthly_rent = rand(800, 2500);
            
            $residentProperty = [
                'user_id' => $residentIds[$i],
                'property_id' => $propertyId,
                'unit_number' => $unitNumber,
                'move_in_date' => $move_in_date,
                'lease_end_date' => $lease_end_date,
                'monthly_rent' => $monthly_rent,
                'status' => 'past'
            ];
            
            $residentProperties[] = $residentProperty;
            
            // Execute insert
            $stmt->bindParam(':user_id', $residentProperty['user_id']);
            $stmt->bindParam(':property_id', $residentProperty['property_id']);
            $stmt->bindParam(':unit_number', $residentProperty['unit_number']);
            $stmt->bindParam(':move_in_date', $residentProperty['move_in_date']);
            $stmt->bindParam(':lease_end_date', $residentProperty['lease_end_date']);
            $stmt->bindParam(':monthly_rent', $residentProperty['monthly_rent']);
            $stmt->bindParam(':status', $residentProperty['status']);
            
            $stmt->execute();
            $insertCount++;
        }
    }
    
    echo "$insertCount sample resident-property relationships inserted successfully.<br>";
    
    // Display inserted resident-property relationships
    echo "<hr>";
    echo "<h2>Resident-Property Relationships Created:</h2>";
    
    $selectQuery = "SELECT rp.id, u.name as resident_name, p.identifier as property_name, 
                    rp.unit_number, rp.move_in_date, rp.lease_end_date, 
                    rp.monthly_rent, rp.status
                    FROM resident_properties rp
                    JOIN users u ON rp.user_id = u.id
                    JOIN properties p ON rp.property_id = p.id
                    ORDER BY rp.status, rp.move_in_date DESC";
    
    $result = $conn->query($selectQuery);
    
    echo "<table border='1' cellpadding='5'>
        <tr>
            <th>ID</th>
            <th>Resident</th>
            <th>Property</th>
            <th>Unit</th>
            <th>Move-in Date</th>
            <th>Lease End Date</th>
            <th>Monthly Rent</th>
            <th>Status</th>
        </tr>";
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['resident_name']}</td>
            <td>{$row['property_name']}</td>
            <td>{$row['unit_number']}</td>
            <td>{$row['move_in_date']}</td>
            <td>{$row['lease_end_date']}</td>
            <td>\${$row['monthly_rent']}</td>
            <td>{$row['status']}</td>
        </tr>";
    }
    
    echo "</table>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
?> 