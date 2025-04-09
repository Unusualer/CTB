<?php
namespace App\Models;

class Payment {
    private $db;
    
    // Properties
    public $id;
    public $payment_id;
    public $user_id;
    public $property_id;
    public $amount;
    public $payment_method;
    public $payment_date;
    public $status;
    public $transaction_id;
    public $description;
    public $admin_notes;
    public $created_at;
    public $updated_at;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Create a new payment record
     * 
     * @return boolean True if successful, false otherwise
     */
    public function create() {
        $query = "INSERT INTO payments (
                    payment_id, 
                    user_id, 
                    property_id, 
                    amount, 
                    payment_method, 
                    payment_date, 
                    status, 
                    transaction_id, 
                    description, 
                    admin_notes, 
                    created_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW()
                )";
                
        $stmt = $this->db->prepare($query);
        
        // Generate unique payment ID if not provided
        if (empty($this->payment_id)) {
            $this->payment_id = 'PAY-' . date('YmdHis') . '-' . substr(md5(uniqid()), 0, 6);
        }
        
        $result = $stmt->execute([
            $this->payment_id,
            $this->user_id,
            $this->property_id,
            $this->amount,
            $this->payment_method,
            $this->payment_date,
            $this->status,
            $this->transaction_id,
            $this->description,
            $this->admin_notes
        ]);
        
        if ($result) {
            $this->id = $this->db->lastInsertId();
            return true;
        }
        
        return false;
    }
    
    /**
     * Get a payment by ID
     * 
     * @param int $id Payment ID
     * @return boolean True if found, false otherwise
     */
    public function read($id) {
        $query = "SELECT * FROM payments WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$id]);
        
        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            
            $this->id = $row['id'];
            $this->payment_id = $row['payment_id'];
            $this->user_id = $row['user_id'];
            $this->property_id = $row['property_id'];
            $this->amount = $row['amount'];
            $this->payment_method = $row['payment_method'];
            $this->payment_date = $row['payment_date'];
            $this->status = $row['status'];
            $this->transaction_id = $row['transaction_id'];
            $this->description = $row['description'];
            $this->admin_notes = $row['admin_notes'];
            $this->created_at = $row['created_at'];
            $this->updated_at = $row['updated_at'];
            
            return true;
        }
        
        return false;
    }
    
    /**
     * Update payment information
     * 
     * @return boolean True if successful, false otherwise
     */
    public function update() {
        $query = "UPDATE payments SET 
                  user_id = ?,
                  property_id = ?,
                  amount = ?,
                  payment_method = ?,
                  payment_date = ?,
                  status = ?,
                  transaction_id = ?,
                  description = ?,
                  admin_notes = ?,
                  updated_at = NOW()
                  WHERE id = ?";
        
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute([
            $this->user_id,
            $this->property_id,
            $this->amount,
            $this->payment_method,
            $this->payment_date,
            $this->status,
            $this->transaction_id,
            $this->description,
            $this->admin_notes,
            $this->id
        ]);
    }
    
    /**
     * Delete a payment record
     * 
     * @return boolean True if successful, false otherwise
     */
    public function delete() {
        $query = "DELETE FROM payments WHERE id = ?";
        $stmt = $this->db->prepare($query);
        
        return $stmt->execute([$this->id]);
    }
    
    /**
     * Get payments by user ID
     * 
     * @param int $user_id User ID
     * @return array Array of payment records
     */
    public function getPaymentsByUser($user_id) {
        $query = "SELECT * FROM payments WHERE user_id = ? ORDER BY payment_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$user_id]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get payments by property ID
     * 
     * @param int $property_id Property ID
     * @return array Array of payment records
     */
    public function getPaymentsByProperty($property_id) {
        $query = "SELECT * FROM payments WHERE property_id = ? ORDER BY payment_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$property_id]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get payments by status
     * 
     * @param string $status Payment status
     * @return array Array of payment records
     */
    public function getPaymentsByStatus($status) {
        $query = "SELECT * FROM payments WHERE status = ? ORDER BY payment_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$status]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
    
    /**
     * Get payments within a date range
     * 
     * @param string $start_date Start date (YYYY-MM-DD)
     * @param string $end_date End date (YYYY-MM-DD)
     * @return array Array of payment records
     */
    public function getPaymentsByDateRange($start_date, $end_date) {
        $query = "SELECT * FROM payments WHERE payment_date BETWEEN ? AND ? ORDER BY payment_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$start_date, $end_date]);
        
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
} 