<?php
// Start session
session_start();

// Include database configuration
require_once '../includes/config.php';

// Check if user is logged in and has manager role
requireRole('manager');

// Process status filter
$statusFilter = isset($_GET['status']) ? $_GET['status'] : 'all';
$statusClause = ($statusFilter != 'all') ? "AND u.status = :status" : "";

// Process search query
$searchQuery = isset($_GET['search']) ? trim($_GET['search']) : '';
$searchClause = '';
if (!empty($searchQuery)) {
    $searchClause = "AND (u.name LIKE :search OR u.email LIKE :search OR u.phone LIKE :search)";
}

// Get residents with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

try {
    // Count total residents
    $countQuery = "
        SELECT COUNT(*) as total 
        FROM users u 
        WHERE u.role = 'resident' 
        $statusClause 
        $searchClause
    ";
    
    $countStmt = $conn->prepare($countQuery);
    
    if ($statusFilter != 'all') {
        $countStmt->bindParam(':status', $statusFilter);
    }
    
    if (!empty($searchQuery)) {
        $searchParam = "%$searchQuery%";
        $countStmt->bindParam(':search', $searchParam);
    }
    
    $countStmt->execute();
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRecords / $recordsPerPage);
    
    // Fetch residents
    $query = "
        SELECT u.*, 
            (SELECT COUNT(*) FROM properties p WHERE p.user_id = u.id) as property_count
        FROM users u 
        WHERE u.role = 'resident' 
        $statusClause 
        $searchClause
        ORDER BY u.name ASC
        LIMIT :offset, :limit
    ";
    
    $stmt = $conn->prepare($query);
    
    if ($statusFilter != 'all') {
        $stmt->bindParam(':status', $statusFilter);
    }
    
    if (!empty($searchQuery)) {
        $searchParam = "%$searchQuery%";
        $stmt->bindParam(':search', $searchParam);
    }
    
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $recordsPerPage, PDO::PARAM_INT);
    $stmt->execute();
    
    $residents = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}

// Check for action messages
$successMessage = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
$errorMessage = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';

// Clear session messages
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residents Management - Complexe Tanger Boulevard</title>
    
    <!-- CSS -->
    <link rel="stylesheet" href="../css/vendor.css">
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    
    <style>
        .dashboard-container {
            padding: 40px 0;
        }
        
        .dashboard-card {
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
            padding: 20px;
        }
        
        .dashboard-card-header {
            border-bottom: 1px solid #eee;
            margin-bottom: 20px;
            padding-bottom: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .dashboard-card-title {
            font-size: 1.2rem;
            font-weight: 700;
            margin: 0;
        }
        
        .filter-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
        }
        
        .search-container {
            flex-grow: 1;
        }
        
        .search-form {
            display: flex;
        }
        
        .search-input {
            border: 1px solid #ddd;
            border-radius: 4px 0 0 4px;
            flex-grow: 1;
            padding: 10px 15px;
        }
        
        .search-btn {
            background-color: #151515;
            border: none;
            border-radius: 0 4px 4px 0;
            color: #fff;
            cursor: pointer;
            padding: 10px 15px;
        }
        
        .filter-dropdown {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 10px 15px;
            min-width: 150px;
        }
        
        .resident-table {
            width: 100%;
        }
        
        .resident-table th,
        .resident-table td {
            padding: 12px 15px;
            text-align: left;
        }
        
        .resident-table thead {
            background-color: #f8f9fa;
        }
        
        .resident-table tbody tr {
            border-bottom: 1px solid #eee;
        }
        
        .resident-table tbody tr:last-child {
            border-bottom: none;
        }
        
        .action-btns {
            display: flex;
            gap: 5px;
        }
        
        .pagination {
            display: flex;
            justify-content: center;
            list-style: none;
            margin: 20px 0;
            padding: 0;
        }
        
        .pagination li {
            margin: 0 5px;
        }
        
        .pagination a {
            background-color: #f8f9fa;
            border: 1px solid #ddd;
            border-radius: 4px;
            color: #151515;
            display: block;
            padding: 5px 10px;
            text-decoration: none;
        }
        
        .pagination a.active {
            background-color: #151515;
            color: #fff;
        }
        
        .status-badge {
            border-radius: 20px;
            display: inline-block;
            font-size: 0.75rem;
            padding: 5px 10px;
            text-align: center;
            text-transform: uppercase;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-inactive {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .message {
            border-radius: 4px;
            margin-bottom: 20px;
            padding: 15px;
        }
        
        .message-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .message-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        
        .property-count {
            background-color: #e9ecef;
            border-radius: 50%;
            display: inline-block;
            font-size: 0.75rem;
            height: 24px;
            line-height: 24px;
            text-align: center;
            width: 24px;
        }
        
        @media (max-width: 768px) {
            .filter-container {
                flex-direction: column;
            }
            
            .search-container {
                width: 100%;
            }
            
            .resident-table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body id="top">

    <!-- preloader -->
    <div id="preloader">
        <div id="loader"></div>
    </div>

    <!-- page wrap -->
    <div id="page" class="s-pagewrap">

        <!-- site header -->
        <header class="s-header">
            <div class="s-header__logo">
                <a class="logo" href="../index.php">
                    <img src="../images/logo.svg" alt="Homepage">
                </a>
            </div>

            <a class="s-header__menu-toggle" href="#0">
                <span class="s-header__menu-text">Menu</span>
                <span class="s-header__menu-icon"></span>
            </a>

            <nav class="s-header__nav">
                <a href="#0" class="s-header__nav-close-btn" title="close"><span>Close</span></a>
                <h3>CTB Manager</h3>

                <ul class="s-header__nav-list">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li class="current"><a href="residents.php">Residents</a></li>
                    <li><a href="properties.php">Properties</a></li>
                    <li><a href="payments.php">Payments</a></li>
                    <li><a href="maintenance.php">Maintenance</a></li>
                    <li><a href="reports.php">Reports</a></li>
                    <li><a href="profile.php">My Profile</a></li>
                    <li><a href="../logout.php">Logout</a></li>
                </ul>
            </nav>
        </header> <!-- end s-header -->

        <!-- site content -->
        <section id="content" class="s-content">
            <div class="row" style="max-width: 1200px; margin: 0 auto; padding-top: 120px;">
                <div class="column">
                    <h1>Residents Management</h1>
                    <p>View and manage all resident accounts in Complexe Tanger Boulevard.</p>

                    <!-- Action Button -->
                    <div style="margin-bottom: 20px;">
                        <a href="residents-add.php" class="btn btn--stroke">
                            <i class="fas fa-user-plus"></i> Add New Resident
                        </a>
                    </div>

                    <!-- Messages -->
                    <?php if (!empty($successMessage)): ?>
                        <div class="message message-success">
                            <?php echo htmlspecialchars($successMessage); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($errorMessage)): ?>
                        <div class="message message-error">
                            <?php echo htmlspecialchars($errorMessage); ?>
                        </div>
                    <?php endif; ?>

                    <div class="dashboard-container">
                        <div class="dashboard-card">
                            <div class="dashboard-card-header">
                                <h2 class="dashboard-card-title">Resident Accounts</h2>
                            </div>

                            <!-- Filters and Search -->
                            <div class="filter-container">
                                <div class="search-container">
                                    <form action="residents.php" method="get" class="search-form">
                                        <input type="text" name="search" class="search-input" placeholder="Search by name, email or phone..." value="<?php echo htmlspecialchars($searchQuery); ?>">
                                        <button type="submit" class="search-btn">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </form>
                                </div>
                                
                                <div>
                                    <select name="status" class="filter-dropdown" onchange="window.location = 'residents.php?status=' + this.value + '<?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?>'">
                                        <option value="all" <?php echo $statusFilter == 'all' ? 'selected' : ''; ?>>All Statuses</option>
                                        <option value="active" <?php echo $statusFilter == 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="inactive" <?php echo $statusFilter == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Residents Table -->
                            <?php if (count($residents) > 0): ?>
                                <div style="overflow-x: auto;">
                                    <table class="resident-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Properties</th>
                                                <th>Status</th>
                                                <th>Created</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($residents as $resident): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($resident['name']); ?></td>
                                                    <td><?php echo htmlspecialchars($resident['email']); ?></td>
                                                    <td><?php echo !empty($resident['phone']) ? htmlspecialchars($resident['phone']) : 'N/A'; ?></td>
                                                    <td>
                                                        <span class="property-count" title="<?php echo $resident['property_count']; ?> properties assigned"><?php echo $resident['property_count']; ?></span>
                                                        <?php if ($resident['property_count'] > 0): ?>
                                                            <a href="properties.php?resident_id=<?php echo $resident['id']; ?>" class="dashboard-action-btn" title="View Assigned Properties">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td>
                                                        <span class="status-badge status-<?php echo strtolower($resident['status']); ?>">
                                                            <?php echo ucfirst(htmlspecialchars($resident['status'])); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo date('M d, Y', strtotime($resident['created_at'])); ?></td>
                                                    <td>
                                                        <div class="action-btns">
                                                            <a href="residents-view.php?id=<?php echo $resident['id']; ?>" class="dashboard-action-btn" title="View Details">
                                                                <i class="fas fa-eye"></i>
                                                            </a>
                                                            <a href="residents-edit.php?id=<?php echo $resident['id']; ?>" class="dashboard-action-btn" title="Edit Resident">
                                                                <i class="fas fa-edit"></i>
                                                            </a>
                                                            <a href="payments.php?resident_id=<?php echo $resident['id']; ?>" class="dashboard-action-btn" title="View Payments">
                                                                <i class="fas fa-money-bill"></i>
                                                            </a>
                                                            <a href="properties-assign.php?resident_id=<?php echo $resident['id']; ?>" class="dashboard-action-btn" title="Assign Property">
                                                                <i class="fas fa-home"></i>
                                                            </a>
                                                            <?php if ($resident['status'] == 'active'): ?>
                                                                <a href="residents-status.php?id=<?php echo $resident['id']; ?>&action=deactivate" class="dashboard-action-btn" title="Deactivate Account" onclick="return confirm('Are you sure you want to deactivate this resident account?');">
                                                                    <i class="fas fa-user-times"></i>
                                                                </a>
                                                            <?php else: ?>
                                                                <a href="residents-status.php?id=<?php echo $resident['id']; ?>&action=activate" class="dashboard-action-btn" title="Activate Account" onclick="return confirm('Are you sure you want to activate this resident account?');">
                                                                    <i class="fas fa-user-check"></i>
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- Pagination -->
                                <?php if ($totalPages > 1): ?>
                                    <ul class="pagination">
                                        <?php if ($page > 1): ?>
                                            <li><a href="?page=1<?php echo $statusFilter != 'all' ? '&status=' . $statusFilter : ''; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?>">First</a></li>
                                            <li><a href="?page=<?php echo $page - 1; ?><?php echo $statusFilter != 'all' ? '&status=' . $statusFilter : ''; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?>">Prev</a></li>
                                        <?php endif; ?>

                                        <?php
                                        $startPage = max(1, $page - 2);
                                        $endPage = min($totalPages, $page + 2);
                                        
                                        for ($i = $startPage; $i <= $endPage; $i++):
                                        ?>
                                            <li><a href="?page=<?php echo $i; ?><?php echo $statusFilter != 'all' ? '&status=' . $statusFilter : ''; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?>" <?php echo $i == $page ? 'class="active"' : ''; ?>><?php echo $i; ?></a></li>
                                        <?php endfor; ?>

                                        <?php if ($page < $totalPages): ?>
                                            <li><a href="?page=<?php echo $page + 1; ?><?php echo $statusFilter != 'all' ? '&status=' . $statusFilter : ''; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?>">Next</a></li>
                                            <li><a href="?page=<?php echo $totalPages; ?><?php echo $statusFilter != 'all' ? '&status=' . $statusFilter : ''; ?><?php echo !empty($searchQuery) ? '&search=' . urlencode($searchQuery) : ''; ?>">Last</a></li>
                                        <?php endif; ?>
                                    </ul>
                                <?php endif; ?>
                            <?php else: ?>
                                <p>No residents found matching your search criteria.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </section> <!-- end s-content -->

        <!-- footer -->
        <footer id="colophon" class="s-footer">
            <div class="row">
                <div class="column lg-12 ss-copyright">
                    <span>© Copyright Complexe Tanger Boulevard 2023</span>
                </div>
            </div>

            <div class="ss-go-top">
                <a class="smoothscroll" title="Back to Top" href="#top">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;"><path d="M6 4h12v2H6zm5 10v6h2v-6h5l-6-6-6 6z"></path></svg>
                </a>
            </div>
        </footer> <!-- end footer -->
    </div> <!-- end page wrap -->

    <!-- Java Script -->
    <script src="../js/plugins.js"></script>
    <script src="../js/main.js"></script>
</body>
</html> 