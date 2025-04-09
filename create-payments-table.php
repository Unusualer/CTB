<?php
// Include the database configuration
require_once 'includes/config.php';

echo "<h1>Setting up Payments Table</h1>";

try {
    // Drop existing table if it exists
    $conn->exec("DROP TABLE IF EXISTS payments");
    echo "Dropped existing payments table.<br>";

    // Create payments table
    $sql = "CREATE TABLE payments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        property_id INT NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_date DATE NOT NULL,
        payment_method ENUM('credit_card', 'bank_transfer', 'cash', 'check', 'other') NOT NULL,
        description VARCHAR(255) NOT NULL,
        status ENUM('pending', 'completed', 'failed', 'refunded') NOT NULL DEFAULT 'completed',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
    )";
    
    $conn->exec($sql);
    echo "Payments table created successfully.<br>";

    // Get user IDs (assuming we have users with role 'resident')
    $stmt = $conn->query("SELECT id FROM users WHERE role = 'resident' LIMIT 1");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $user_id = $user ? $user['id'] : 3; // Default to ID 3 if no resident found

    // Get property IDs
    $stmt = $conn->query("SELECT id FROM properties LIMIT 3");
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $property_ids = [];
    foreach ($properties as $property) {
        $property_ids[] = $property['id'];
    }
    
    // If no properties found, use default IDs
    if (empty($property_ids)) {
        $property_ids = [1, 2, 3];
    }

    // Prepare statement for inserting payments
    $stmt = $conn->prepare("INSERT INTO payments 
        (user_id, property_id, amount, payment_date, payment_method, description, status) 
        VALUES (:user_id, :property_id, :amount, :payment_date, :payment_method, :description, :status)");
    
    // Sample payments data - last 6 months of rent payments
    $payments = [];
    $payment_methods = ['credit_card', 'bank_transfer', 'check'];
    $currentMonth = date('m');
    $currentYear = date('Y');
    
    // Generate 6 months of payments
    for ($i = 0; $i < 6; $i++) {
        $month = $currentMonth - $i;
        $year = $currentYear;
        
        // Handle previous year if needed
        if ($month <= 0) {
            $month += 12;
            $year--;
        }
        
        $paymentDate = sprintf("%04d-%02d-01", $year, $month);
        
        // Create multiple payments for different properties
        foreach ($property_ids as $index => $property_id) {
            // Alternate between properties for the user
            if ($index % 3 == 0) {
                $payments[] = [
                    'user_id' => $user_id,
                    'property_id' => $property_id,
                    'amount' => 1200 + ($index * 100), // Different rent amounts
                    'payment_date' => $paymentDate,
                    'payment_method' => $payment_methods[array_rand($payment_methods)],
                    'description' => "Rent payment for " . date('F Y', strtotime($paymentDate)),
                    'status' => 'completed'
                ];
            }
        }
    }
    
    // Add some utility payments
    $utilities = ["Electricity bill", "Water bill", "Internet service", "Maintenance fee"];
    for ($i = 0; $i < 4; $i++) {
        $month = $currentMonth - rand(0, 2);
        $year = $currentYear;
        
        if ($month <= 0) {
            $month += 12;
            $year--;
        }
        
        $paymentDate = sprintf("%04d-%02d-%02d", $year, $month, rand(5, 28));
        
        $payments[] = [
            'user_id' => $user_id,
            'property_id' => $property_ids[array_rand($property_ids)],
            'amount' => rand(50, 200) + (rand(0, 99) / 100), // Random amount between 50 and 200 with cents
            'payment_date' => $paymentDate,
            'payment_method' => $payment_methods[array_rand($payment_methods)],
            'description' => $utilities[$i % count($utilities)],
            'status' => 'completed'
        ];
    }

    // Insert sample payments
    $count = 0;
    foreach ($payments as $payment) {
        $stmt->execute($payment);
        $count++;
    }
    
    echo "$count sample payments inserted successfully.<br>";
    
    echo "<hr>";
    echo "<h2>Success! Payments table has been set up with sample data.</h2>";
    echo "<h3>Sample Payments:</h3>";
    echo "<ul>";
    foreach ($payments as $payment) {
        echo "<li>$" . number_format($payment['amount'], 2) . " - " . $payment['description'] . 
             " (" . date('M d, Y', strtotime($payment['payment_date'])) . ")</li>";
    }
    echo "</ul>";
    
} catch(PDOException $e) {
    echo "<div style='color: red;'>";
    echo "Error: " . $e->getMessage();
    echo "</div>";
}
?> 