<?php
// Include the database configuration
require_once 'includes/config.php';

echo "<h1>Setting up Tickets Table</h1>";

try {
    // Check if the tickets table already exists
    $checkTableQuery = "SHOW TABLES LIKE 'tickets'";
    $result = $conn->query($checkTableQuery);
    
    if ($result->rowCount() > 0) {
        echo "The tickets table already exists. Skipping creation.<br>";
    } else {
        // Create the tickets table
        $createTableQuery = "CREATE TABLE tickets (
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
        )";
        
        $conn->exec($createTableQuery);
        echo "Tickets table created successfully.<br>";
        
        // Get resident and manager user IDs
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
        
        $managerIds = [];
        $managerQuery = "SELECT id FROM users WHERE role = 'manager' LIMIT 5";
        $managerResult = $conn->query($managerQuery);
        
        if ($managerResult->rowCount() > 0) {
            while ($row = $managerResult->fetch(PDO::FETCH_ASSOC)) {
                $managerIds[] = $row['id'];
            }
        } else {
            // Default manager IDs if no managers found
            $managerIds = [7, 8];
            echo "No managers found in users table. Using default IDs.<br>";
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
        
        // Generate random dates within a reasonable range
        function randomDate($start_date, $end_date) {
            $min = strtotime($start_date);
            $max = strtotime($end_date);
            $random_date = rand($min, $max);
            return date('Y-m-d H:i:s', $random_date);
        }
        
        // Sample ticket data
        $ticketSubjects = [
            'maintenance' => [
                'Leaking faucet in kitchen',
                'Broken air conditioner',
                'Toilet not flushing properly',
                'Lights flickering in bedroom',
                'Water heater not working',
                'Strange smell from ventilation',
                'Mold in bathroom ceiling',
                'Window won\'t close properly'
            ],
            'billing' => [
                'Question about last month\'s bill',
                'Double charge on maintenance fee',
                'Missing payment credit',
                'Incorrect late fee applied',
                'Need payment extension'
            ],
            'noise_complaint' => [
                'Loud music from unit above',
                'Dog barking at night',
                'Construction noise during quiet hours',
                'Loud party on weekend'
            ],
            'other' => [
                'Package delivery issue',
                'Request for amenity access',
                'Question about lease renewal',
                'Parking space dispute',
                'Request for additional key'
            ]
        ];
        
        $ticketDescriptions = [
            'maintenance' => [
                'The faucet has been leaking continuously for the past three days, causing water to accumulate in the sink.',
                'The air conditioner is making a loud noise and not cooling properly. It\'s very hot in the apartment.',
                'The toilet gets clogged easily and doesn\'t flush properly even with little use.',
                'The lights in the bedroom flicker continuously and sometimes turn off completely for a few minutes.',
                'No hot water coming from any taps. The water heater might be broken.',
                'There\'s a strange smell coming from the ventilation system, especially when the heat is on.',
                'I can see mold forming on the bathroom ceiling, it seems to be spreading.',
                'The window in the living room won\'t close completely, letting in cold air and noise.'
            ],
            'billing' => [
                'I have a question about the charges on last month\'s bill. Some items are unclear to me.',
                'I think I was charged twice for the maintenance fee in this month\'s bill.',
                'I made a payment last week but it\'s not reflected in my account yet.',
                'A late fee was applied to my account but I paid before the due date.',
                'Due to unforeseen circumstances, I need an extension on this month\'s payment.'
            ],
            'noise_complaint' => [
                'The tenant above my unit plays loud music almost every night until late hours.',
                'A dog in a nearby unit barks continuously throughout the night, disturbing my sleep.',
                'There\'s construction noise coming from a nearby unit outside of permitted hours.',
                'There was an extremely loud party last night that continued until 3 AM.'
            ],
            'other' => [
                'My packages have been delivered to the wrong unit twice this month.',
                'I need access to the fitness center but my key card doesn\'t work.',
                'I have questions about the lease renewal process as my lease expires next month.',
                'Another resident is repeatedly parking in my assigned parking space.',
                'I need an additional key for a family member who will be staying with me.'
            ]
        ];
        
        // Prepare insert statement for tickets
        $insertQuery = "INSERT INTO tickets 
                        (user_id, property_id, subject, description, category, priority, status, assigned_to, created_at) 
                        VALUES 
                        (:user_id, :property_id, :subject, :description, :category, :priority, :status, :assigned_to, :created_at)";
        $stmt = $conn->prepare($insertQuery);
        
        // Insert sample tickets (at least 15 tickets)
        $ticketsCount = 0;
        $tickets = [];
        
        // Define categories and priorities
        $categories = ['maintenance', 'billing', 'noise_complaint', 'other'];
        $priorities = ['low', 'medium', 'high', 'urgent'];
        $statuses = ['open', 'in_progress', 'closed', 'reopened'];
        
        for ($i = 0; $i < 20; $i++) {
            // Select a random resident
            $userId = $residentIds[array_rand($residentIds)];
            
            // Select a random property
            $propertyId = $propertyIds[array_rand($propertyIds)];
            
            // Select a random category
            $category = $categories[array_rand($categories)];
            
            // Select a random subject and description based on category
            $subject = $ticketSubjects[$category][array_rand($ticketSubjects[$category])];
            $description = $ticketDescriptions[$category][array_rand($ticketDescriptions[$category])];
            
            // Select a random priority
            $priority = $priorities[array_rand($priorities)];
            
            // Decide on a status and potentially assign to a manager
            $assignedTo = null;
            $status = $statuses[array_rand($statuses)];
            
            if ($status !== 'open') {
                // If ticket is not open, assign it to a manager
                $assignedTo = count($managerIds) > 0 ? $managerIds[array_rand($managerIds)] : null;
            }
            
            // Generate a random creation date in the last 3 months
            $createdAt = randomDate(date('Y-m-d', strtotime('-3 months')), date('Y-m-d'));
            
            // Create ticket data
            $ticket = [
                'user_id' => $userId,
                'property_id' => $propertyId,
                'subject' => $subject,
                'description' => $description,
                'category' => $category,
                'priority' => $priority,
                'status' => $status,
                'assigned_to' => $assignedTo,
                'created_at' => $createdAt
            ];
            
            $tickets[] = $ticket;
            
            // Bind parameters and execute
            $stmt->bindParam(':user_id', $ticket['user_id']);
            $stmt->bindParam(':property_id', $ticket['property_id']);
            $stmt->bindParam(':subject', $ticket['subject']);
            $stmt->bindParam(':description', $ticket['description']);
            $stmt->bindParam(':category', $ticket['category']);
            $stmt->bindParam(':priority', $ticket['priority']);
            $stmt->bindParam(':status', $ticket['status']);
            $stmt->bindParam(':assigned_to', $ticket['assigned_to']);
            $stmt->bindParam(':created_at', $ticket['created_at']);
            
            $stmt->execute();
            $ticketsCount++;
        }
        
        echo "$ticketsCount sample tickets inserted successfully.<br>";
        
        // Display inserted tickets summary
        echo "<hr>";
        echo "<h2>Tickets Table Created with Sample Data</h2>";
        
        // Display sample of tickets
        $selectQuery = "SELECT t.id, u.name as resident_name, p.identifier as property_name, 
                        t.subject, t.category, t.priority, t.status, 
                        m.name as assigned_to, t.created_at
                        FROM tickets t
                        JOIN users u ON t.user_id = u.id
                        LEFT JOIN properties p ON t.property_id = p.id
                        LEFT JOIN users m ON t.assigned_to = m.id
                        ORDER BY t.created_at DESC
                        LIMIT 10";
        
        $result = $conn->query($selectQuery);
        
        echo "<table border='1' cellpadding='5'>
            <tr>
                <th>ID</th>
                <th>Resident</th>
                <th>Property</th>
                <th>Subject</th>
                <th>Category</th>
                <th>Priority</th>
                <th>Status</th>
                <th>Assigned To</th>
                <th>Created At</th>
            </tr>";
        
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['resident_name']}</td>
                <td>{$row['property_name']}</td>
                <td>{$row['subject']}</td>
                <td>{$row['category']}</td>
                <td>{$row['priority']}</td>
                <td>{$row['status']}</td>
                <td>{$row['assigned_to']}</td>
                <td>{$row['created_at']}</td>
            </tr>";
        }
        
        echo "</table>";
    }
    
} catch(PDOException $e) {
    echo "<div style='color: red;'>";
    echo "Error: " . $e->getMessage();
    echo "</div>";
}

// Close the connection
$conn = null;
?> 