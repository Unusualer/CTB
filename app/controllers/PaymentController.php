<?php
namespace App\Controllers;

class PaymentController {
    private $db;
    
    public function __construct($db) {
        $this->db = $db;
    }
    
    /**
     * Get all payments with pagination and filtering
     */
    public function getAllPayments($page = 1, $limit = 10, $filters = []) {
        // Start building the query
        $query = "SELECT p.*, u.name as user_name, pr.identifier as property_identifier 
                FROM payments p 
                LEFT JOIN users u ON p.user_id = u.id 
                LEFT JOIN properties pr ON p.property_id = pr.id 
                WHERE 1=1";
        
        $countQuery = "SELECT COUNT(*) as total FROM payments p WHERE 1=1";
        $params = [];
        
        // Apply filters if provided
        if (!empty($filters['search'])) {
            $searchTerm = "%{$filters['search']}%";
            $query .= " AND (p.payment_id LIKE ? OR u.name LIKE ? OR pr.identifier LIKE ?)";
            $countQuery .= " AND (p.payment_id LIKE ? OR u.name LIKE ? OR pr.identifier LIKE ?)";
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        if (!empty($filters['status'])) {
            $query .= " AND p.status = ?";
            $countQuery .= " AND p.status = ?";
            $params[] = $filters['status'];
        }
        
        if (!empty($filters['payment_method'])) {
            $query .= " AND p.payment_method = ?";
            $countQuery .= " AND p.payment_method = ?";
            $params[] = $filters['payment_method'];
        }
        
        if (!empty($filters['start_date'])) {
            $query .= " AND p.payment_date >= ?";
            $countQuery .= " AND p.payment_date >= ?";
            $params[] = $filters['start_date'];
        }
        
        if (!empty($filters['end_date'])) {
            $query .= " AND p.payment_date <= ?";
            $countQuery .= " AND p.payment_date <= ?";
            $params[] = $filters['end_date'];
        }
        
        // Add ordering
        $query .= " ORDER BY p.payment_date DESC";
        
        // Add pagination
        $offset = ($page - 1) * $limit;
        $query .= " LIMIT ?, ?";
        $finalParams = array_merge($params, [$offset, $limit]);
        
        // Get total count
        $countStmt = $this->db->prepare($countQuery);
        if ($params) {
            $types = str_repeat('s', count($params));
            $countStmt->bind_param($types, ...$params);
        }
        $countStmt->execute();
        $totalResult = $countStmt->get_result()->fetch_assoc();
        $total = $totalResult['total'];
        
        // Get payments
        $stmt = $this->db->prepare($query);
        if ($finalParams) {
            $types = str_repeat('s', count($finalParams) - 2) . 'ii';
            $stmt->bind_param($types, ...$finalParams);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $payments = [];
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        
        return [
            'payments' => $payments,
            'total' => $total,
            'pages' => ceil($total / $limit)
        ];
    }
    
    /**
     * Get payment statistics
     */
    public function getPaymentStats() {
        // Total payments
        $totalQuery = "SELECT COUNT(*) as count, SUM(amount) as total FROM payments";
        $result = $this->db->query($totalQuery);
        $totals = $result->fetch_assoc();
        
        // Get counts by status
        $statusQuery = "SELECT status, COUNT(*) as count, SUM(amount) as total FROM payments GROUP BY status";
        $statusResult = $this->db->query($statusQuery);
        $statusStats = [];
        
        while ($row = $statusResult->fetch_assoc()) {
            $statusStats[$row['status']] = [
                'count' => $row['count'],
                'total' => $row['total']
            ];
        }
        
        // Get counts by payment method
        $methodQuery = "SELECT payment_method, COUNT(*) as count FROM payments GROUP BY payment_method";
        $methodResult = $this->db->query($methodQuery);
        $methodStats = [];
        
        while ($row = $methodResult->fetch_assoc()) {
            $methodStats[$row['payment_method']] = $row['count'];
        }
        
        return [
            'total_count' => $totals['count'] ?: 0,
            'total_amount' => $totals['total'] ?: 0,
            'status_stats' => $statusStats,
            'method_stats' => $methodStats
        ];
    }
    
    /**
     * Get a single payment by ID
     */
    public function getPaymentById($id) {
        $query = "SELECT p.*, u.name as user_name, u.email as user_email, 
                 pr.identifier as property_identifier, pr.title as property_title
                 FROM payments p 
                 LEFT JOIN users u ON p.user_id = u.id 
                 LEFT JOIN properties pr ON p.property_id = pr.id 
                 WHERE p.id = ?";
                 
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            return null;
        }
        
        return $result->fetch_assoc();
    }
    
    /**
     * Update payment status
     */
    public function updatePaymentStatus($id, $status, $notes = null) {
        $query = "UPDATE payments SET status = ?, admin_notes = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param("ssi", $status, $notes, $id);
        return $stmt->execute();
    }
} 