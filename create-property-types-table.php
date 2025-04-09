<?php
// Include database configuration
require_once 'includes/config.php';

try {
    // Drop the existing property_types table if it exists
    $dropTableQuery = "DROP TABLE IF EXISTS property_types";
    $conn->exec($dropTableQuery);
    
    echo "Existing property_types table dropped successfully (if it existed).<br>";
    
    // Create the property_types table
    $createTableQuery = "CREATE TABLE property_types (
        id INT(11) PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )";
    
    $conn->exec($createTableQuery);
    echo "Property Types table created successfully.<br>";
    
    // Insert sample property types
    $propertyTypes = [
        [
            'name' => 'Apartment',
            'description' => 'Multi-unit residential building where units are stacked vertically'
        ],
        [
            'name' => 'Single Family House',
            'description' => 'Standalone residential building designed for one family'
        ],
        [
            'name' => 'Townhouse',
            'description' => 'Multi-floor home that shares one or two walls with adjacent properties'
        ],
        [
            'name' => 'Condominium',
            'description' => 'Privately owned unit within a building of other units'
        ],
        [
            'name' => 'Duplex',
            'description' => 'Building with two separate dwelling units, either side by side or one above the other'
        ],
        [
            'name' => 'Studio',
            'description' => 'Single room unit that combines living room, bedroom, and kitchen'
        ],
        [
            'name' => 'Penthouse',
            'description' => 'Luxury apartment on the top floor of a high-rise building'
        ],
        [
            'name' => 'Loft',
            'description' => 'Large, open space with minimal interior walls, often in converted industrial buildings'
        ]
    ];
    
    // Prepare insert statement
    $insertQuery = "INSERT INTO property_types (name, description) VALUES (:name, :description)";
    $stmt = $conn->prepare($insertQuery);
    
    // Insert sample property types
    foreach ($propertyTypes as $type) {
        $stmt->bindParam(':name', $type['name']);
        $stmt->bindParam(':description', $type['description']);
        $stmt->execute();
    }
    
    echo "Sample property types inserted successfully:<br>";
    
    // Display inserted property types
    $selectQuery = "SELECT id, name, description FROM property_types";
    $result = $conn->query($selectQuery);
    
    echo "<table border='1' cellpadding='5'>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Description</th>
        </tr>";
    
    while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['name']}</td>
            <td>{$row['description']}</td>
        </tr>";
    }
    
    echo "</table>";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Close the connection
$conn = null;
?> 