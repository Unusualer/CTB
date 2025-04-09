<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "You must be logged in as an administrator to access this page.";
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$search = $_GET['search'] ?? '';
$type_filter = $_GET['type'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Get total count and properties list
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Build the query
    $query = "SELECT p.*, u.name as user_name FROM properties p LEFT JOIN users u ON p.user_id = u.id WHERE 1=1";
    $count_query = "SELECT COUNT(*) as total FROM properties WHERE 1=1";
    $params = [];
    
    // Apply filters
    if (!empty($search)) {
        $query .= " AND (p.identifier LIKE :search OR p.description LIKE :search)";
        $count_query .= " AND (identifier LIKE :search OR description LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($type_filter)) {
        $query .= " AND p.type = :type";
        $count_query .= " AND type = :type";
        $params[':type'] = $type_filter;
    }
    
    if (!empty($status_filter)) {
        $query .= " AND p.status = :status";
        $count_query .= " AND status = :status";
        $params[':status'] = $status_filter;
    }
    
    // Add ordering
    $query .= " ORDER BY p.created_at DESC LIMIT :offset, :limit";
    
    // Get total count
    $count_stmt = $db->prepare($count_query);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value);
    }
    $count_stmt->execute();
    $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total / $items_per_page);
    
    // Get properties
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get counts by type for stats
    $type_counts = [];
    $type_stmt = $db->prepare("SELECT type, COUNT(*) as count FROM properties GROUP BY type");
    $type_stmt->execute();
    $type_results = $type_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($type_results as $type_data) {
        $type_counts[$type_data['type']] = $type_data['count'];
    }
    
    // Get counts by status for stats
    $status_counts = [];
    $status_stmt = $db->prepare("SELECT status, COUNT(*) as count FROM properties GROUP BY status");
    $status_stmt->execute();
    $status_results = $status_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($status_results as $status_data) {
        $status_counts[$status_data['status']] = $status_data['count'];
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    $properties = [];
    $total = 0;
    $total_pages = 0;
    $type_counts = ['apartment' => 0, 'parking' => 0];
    $status_counts = ['occupied' => 0, 'vacant' => 0, 'maintenance' => 0];
}

// Page title
$page_title = "Property Management";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> - Community Trust Bank</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/admin-style.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <img src="../assets/images/logo.png" alt="CTB Logo" class="logo">
                <h2>CTB Admin</h2>
            </div>
            
            <div class="user-info">
                <div class="user-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="user-details">
                    <h4><?php echo htmlspecialchars($_SESSION['name']); ?></h4>
                    <p>Administrator</p>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li>
                        <a href="dashboard.php">
                            <i class="fas fa-chart-line"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php">
                            <i class="fas fa-users"></i>
                            <span>Users</span>
                        </a>
                    </li>
                    <li class="active">
                        <a href="properties.php">
                            <i class="fas fa-building"></i>
                            <span>Properties</span>
                        </a>
                    </li>
                    <li>
                        <a href="tickets.php">
                            <i class="fas fa-ticket-alt"></i>
                            <span>Tickets</span>
                        </a>
                    </li>
                    <li>
                        <a href="payments.php">
                            <i class="fas fa-credit-card"></i>
                            <span>Payments</span>
                        </a>
                    </li>
                    <li>
                        <a href="activity-log.php">
                            <i class="fas fa-history"></i>
                            <span>Activity Log</span>
                        </a>
                    </li>
                    <li>
                        <a href="maintenance-new.php">
                            <i class="fas fa-tools"></i>
                            <span>Maintenance</span>
                        </a>
                    </li>
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <div class="theme-toggle">
                    <i class="fas fa-moon"></i>
                    <label class="switch">
                        <input type="checkbox" id="darkModeToggle">
                        <span class="slider round"></span>
                    </label>
                </div>
                <a href="../logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="topbar">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" placeholder="Search properties..." form="filter-form" name="search" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                
                <div class="topbar-right">
                    <div class="notifications">
                        <i class="fas fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                    <div class="messages">
                        <i class="fas fa-envelope"></i>
                        <span class="badge">5</span>
                    </div>
                </div>
            </header>

            <div class="page-header">
                <h1>Property Management</h1>
                <a href="add-property.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Add New Property
                </a>
            </div>

            <!-- Stats Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon properties">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Apartments</h3>
                        <div class="stat-number"><?php echo isset($type_counts['apartment']) ? $type_counts['apartment'] : 0; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon tickets">
                        <i class="fas fa-car"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Parking Spots</h3>
                        <div class="stat-number"><?php echo isset($type_counts['parking']) ? $type_counts['parking'] : 0; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Occupied</h3>
                        <div class="stat-number"><?php echo isset($status_counts['occupied']) ? $status_counts['occupied'] : 0; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon payments">
                        <i class="fas fa-building"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Property Status</h3>
                        <div class="stat-number"><?php echo $total; ?> Total</div>
                        <div class="stat-breakdown">
                            <span><i class="fas fa-circle" style="color: #28a745;"></i> Occupied: <?php echo isset($status_counts['occupied']) ? $status_counts['occupied'] : 0; ?></span>
                            <span><i class="fas fa-circle" style="color: #ffc107;"></i> Vacant: <?php echo isset($status_counts['vacant']) ? $status_counts['vacant'] : 0; ?></span>
                            <span><i class="fas fa-circle" style="color: #dc3545;"></i> Maintenance: <?php echo isset($status_counts['maintenance']) ? $status_counts['maintenance'] : 0; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card">
                <div class="card-header user-filter-header">
                    <h3>Properties List</h3>
                    <form id="filter-form" action="properties.php" method="GET" class="filter-form">
                        <div class="filter-group">
                            <label for="type">Type:</label>
                            <select name="type" id="type" onchange="this.form.submit()">
                                <option value="">All Types</option>
                                <option value="apartment" <?php echo $type_filter === 'apartment' ? 'selected' : ''; ?>>Apartment</option>
                                <option value="parking" <?php echo $type_filter === 'parking' ? 'selected' : ''; ?>>Parking</option>
                            </select>
                        </div>
                        <div class="filter-group">
                            <label for="status">Status:</label>
                            <select name="status" id="status" onchange="this.form.submit()">
                                <option value="">All Statuses</option>
                                <option value="occupied" <?php echo $status_filter === 'occupied' ? 'selected' : ''; ?>>Occupied</option>
                                <option value="vacant" <?php echo $status_filter === 'vacant' ? 'selected' : ''; ?>>Vacant</option>
                                <option value="maintenance" <?php echo $status_filter === 'maintenance' ? 'selected' : ''; ?>>Maintenance</option>
                            </select>
                        </div>
                        <a href="properties.php" class="reset-link">Reset</a>
                    </form>
                </div>
                <div class="card-body">
                    <?php if (empty($properties)): ?>
                        <div class="empty-state">
                            <i class="fas fa-building"></i>
                            <p>No properties found. Try adjusting your filters or add a new property.</p>
                            <a href="add-property.php" class="btn btn-primary">Add New Property</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Identifier</th>
                                        <th>Type</th>
                                        <th>Area (m²)</th>
                                        <th>Floor</th>
                                        <th>Resident</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($properties as $property): ?>
                                        <tr>
                                            <td><?php echo $property['id']; ?></td>
                                            <td>
                                                <div class="property-cell">
                                                    <div class="property-icon">
                                                        <i class="fas fa-<?php echo $property['type'] === 'apartment' ? 'home' : 'car'; ?>"></i>
                                                    </div>
                                                    <?php echo htmlspecialchars($property['identifier']); ?>
                                                </div>
                                            </td>
                                            <td><?php echo ucfirst(htmlspecialchars($property['type'])); ?></td>
                                            <td><?php echo $property['area'] ? htmlspecialchars($property['area']) : 'N/A'; ?></td>
                                            <td><?php echo $property['floor'] !== null ? htmlspecialchars($property['floor']) : 'N/A'; ?></td>
                                            <td>
                                                <?php if ($property['user_id']): ?>
                                                    <a href="view-user.php?id=<?php echo $property['user_id']; ?>" class="user-link">
                                                        <?php echo htmlspecialchars($property['user_name']); ?>
                                                    </a>
                                                <?php else: ?>
                                                    <span class="text-muted">Unassigned</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    $statusClass = '';
                                                    switch ($property['status']) {
                                                        case 'occupied': $statusClass = 'success'; break;
                                                        case 'vacant': $statusClass = 'warning'; break;
                                                        case 'maintenance': $statusClass = 'danger'; break;
                                                    }
                                                ?>
                                                <span class="status-indicator status-<?php echo $statusClass; ?>">
                                                    <?php echo ucfirst(htmlspecialchars($property['status'])); ?>
                                                </span>
                                            </td>
                                            <td class="actions">
                                                <a href="view-property.php?id=<?php echo $property['id']; ?>" class="btn-icon" title="View Property">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-property.php?id=<?php echo $property['id']; ?>" class="btn-icon" title="Edit Property">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0);" class="btn-icon delete-property" data-id="<?php echo $property['id']; ?>" title="Delete Property">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="pagination">
                                <?php if ($page > 1): ?>
                                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type_filter); ?>&status=<?php echo urlencode($status_filter); ?>" class="pagination-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type_filter); ?>&status=<?php echo urlencode($status_filter); ?>" 
                                       class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&type=<?php echo urlencode($type_filter); ?>&status=<?php echo urlencode($status_filter); ?>" class="pagination-link">
                                        <i class="fas fa-chevron-right"></i>
                                    </a>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirm Deletion</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this property? This action cannot be undone.</p>
                <p class="text-danger"><strong>Warning:</strong> Deleting a property will also remove all associated records (payments, maintenance logs, etc.).</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="delete-property.php" method="POST">
                    <input type="hidden" name="property_id" id="deletePropertyId">
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Dark mode toggle
        const darkModeToggle = document.getElementById('darkModeToggle');
        
        // Check for saved dark mode preference
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.body.classList.add('dark-mode');
            darkModeToggle.checked = true;
        }
        
        // Dark mode toggle event listener
        darkModeToggle.addEventListener('change', function() {
            if (this.checked) {
                document.body.classList.add('dark-mode');
                localStorage.setItem('darkMode', 'enabled');
            } else {
                document.body.classList.remove('dark-mode');
                localStorage.setItem('darkMode', null);
            }
        });
        
        // Delete property modal functionality
        const modal = document.getElementById('deleteModal');
        const deleteButtons = document.querySelectorAll('.delete-property');
        const closeButtons = document.querySelectorAll('.close, .close-modal');
        const deleteForm = document.getElementById('deleteForm');
        const deletePropertyIdInput = document.getElementById('deletePropertyId');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const propertyId = this.getAttribute('data-id');
                deletePropertyIdInput.value = propertyId;
                modal.style.display = 'block';
            });
        });
        
        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        });
        
        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    </script>
</body>
</html> 