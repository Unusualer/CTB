<?php
// Include the database configuration
require_once 'includes/config.php';

echo "<h1>Setting up Properties Table</h1>";

try {
    // Make sure property_types table exists first
    $checkTableQuery = "SHOW TABLES LIKE 'property_types'";
    $result = $conn->query($checkTableQuery);
    
    if ($result->rowCount() == 0) {
        echo "<div style='color: red; margin-bottom: 15px;'>Warning: property_types table does not exist. Please run create-property-types-table.php first.</div>";
    }

    // Drop existing table if it exists
    $conn->exec("DROP TABLE IF EXISTS properties");
    echo "Dropped existing properties table.<br>";

    // Create properties table
    $sql = "CREATE TABLE properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        property_type_id INT,
        address TEXT NOT NULL,
        units INT NOT NULL,
        description TEXT,
        amenities TEXT,
        status ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (property_type_id) REFERENCES property_types(id) ON DELETE SET NULL
    )";
    
    $conn->exec($sql);
    echo "Properties table created successfully.<br>";

    // Get available property type IDs
    $propertyTypeIds = [];
    $typeQuery = "SELECT id FROM property_types";
    $typeResult = $conn->query($typeQuery);
    
    if ($typeResult->rowCount() > 0) {
        while ($row = $typeResult->fetch(PDO::FETCH_ASSOC)) {
            $propertyTypeIds[] = $row['id'];
        }
    } else {
        // Default property type IDs if table exists but is empty
        $propertyTypeIds = [1, 2, 3, 4, 5];
    }

    // Prepare statement for inserting properties
    $stmt = $conn->prepare("INSERT INTO properties (name, property_type_id, address, units, description, amenities, status) 
                          VALUES (:name, :property_type_id, :address, :units, :description, :amenities, :status)");
    
    // Sample properties data
    $properties = [
        [
            'name' => 'Sunset Apartments',
            'property_type_id' => $propertyTypeIds[0] ?? 1, // Apartment
            'address' => '123 Sunset Blvd, Los Angeles, CA 90001',
            'units' => 24,
            'description' => 'Modern apartment complex with spacious units and beautiful sunset views.',
            'amenities' => 'Swimming pool, Fitness center, Covered parking, Pet-friendly, Laundry facilities',
            'status' => 'active'
        ],
        [
            'name' => 'Riverdale Heights',
            'property_type_id' => $propertyTypeIds[3] ?? 4, // Condominium
            'address' => '456 River Road, Chicago, IL 60601',
            'units' => 36,
            'description' => 'Luxury apartments in downtown with river views and modern finishes.',
            'amenities' => 'Rooftop terrace, 24/7 security, High-speed internet, Gym, Business center',
            'status' => 'active'
        ],
        [
            'name' => 'Park View Residences',
            'property_type_id' => $propertyTypeIds[6] ?? 7, // Penthouse
            'address' => '789 Park Avenue, New York, NY 10001',
            'units' => 48,
            'description' => 'Elegant residences overlooking Central Park with premium amenities.',
            'amenities' => 'Doorman, Concierge service, Gym, Underground parking, Pet spa',
            'status' => 'active'
        ],
        [
            'name' => 'Beachside Condos',
            'property_type_id' => $propertyTypeIds[3] ?? 4, // Condominium
            'address' => '101 Ocean Drive, Miami, FL 33109',
            'units' => 18,
            'description' => 'Beachfront condominiums with direct access to the beach and ocean views.',
            'amenities' => 'Private beach access, Infinity pool, BBQ area, Tennis court, Valet parking',
            'status' => 'active'
        ],
        [
            'name' => 'Mountain View Apartments',
            'property_type_id' => $propertyTypeIds[2] ?? 3, // Townhouse
            'address' => '222 Highland Drive, Denver, CO 80201',
            'units' => 30,
            'description' => 'Cozy apartments with spectacular mountain views and outdoor activities nearby.',
            'amenities' => 'Hiking trails, Ski storage, Fireplace, Balconies, Community garden',
            'status' => 'active'
        ]
    ];

    // Insert sample properties
    $count = 0;
    foreach ($properties as $property) {
        $stmt->execute($property);
        $count++;
    }
    
    echo "$count sample properties inserted successfully.<br>";
    
    echo "<hr>";
    echo "<h2>Success! Properties table has been set up with sample data.</h2>";
    echo "<h3>Sample Properties:</h3>";
    
    // Select properties with their property types
    $query = "SELECT p.id, p.identifier as name, pt.name as property_type, p.address, p.units 
              FROM properties p 
              LEFT JOIN property_types pt ON p.property_type_id = pt.id";
    $result = $conn->query($query);
    
    echo "<table border='1' cellpadding='5'>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Property Type</th>
            <th>Address</th>
            <th>Units</th>
        </tr>";
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['name']}</td>
            <td>{$row['property_type']}</td>
            <td>{$row['address']}</td>
            <td>{$row['units']}</td>
        </tr>";
    }
    
    echo "</table>";
    
} catch(PDOException $e) {
    echo "<div style='color: red;'>";
    echo "Error: " . $e->getMessage();
    echo "</div>";
}
?> 