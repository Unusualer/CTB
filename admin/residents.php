<?php
// Start session
session_start();

// Include database configuration
require_once '../includes/config.php';

// Check if user is logged in and has admin role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get admin information
$adminId = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM users WHERE id = :id");
$stmt->bindParam(':id', $adminId);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Get residents list
try {
    // Default sorting and filtering
    $sort = isset($_GET['sort']) ? $_GET['sort'] : 'name';
    $order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
    
    // Prepare the query base
    $query = "SELECT u.*, 
              COUNT(DISTINCT p.id) as property_count, 
              MAX(pay.payment_date) as last_payment_date
              FROM users u
              LEFT JOIN properties p ON u.id = p.resident_id
              LEFT JOIN payments pay ON u.id = pay.user_id
              WHERE u.role = 'resident'";
    
    // Add filters
    if ($filter === 'active') {
        $query .= " AND u.status = 'active'";
    } else if ($filter === 'inactive') {
        $query .= " AND u.status = 'inactive'";
    }
    
    // Group by and order
    $query .= " GROUP BY u.id";
    
    // Add sorting
    $validSortFields = ['name', 'email', 'status', 'created_at', 'property_count', 'last_payment_date'];
    $validOrders = ['ASC', 'DESC'];
    
    if (in_array($sort, $validSortFields) && in_array($order, $validOrders)) {
        $query .= " ORDER BY " . $sort . " " . $order;
    } else {
        $query .= " ORDER BY u.name ASC";
    }
    
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $residents = $stmt->fetchAll();
    
} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Residents - CTB Property Management</title>
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="dashboard-ui.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="sidebar-brand">
                <h3>CTB Admin</h3>
            </div>
            
            <div class="user-profile">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>&background=random" alt="Admin Profile">
                <div class="user-info">
                    <h5><?php echo htmlspecialchars($user['name']); ?></h5>
                    <p>System Administrator</p>
                </div>
            </div>
            
            <div class="sidebar-divider"></div>
            
            <div class="sidebar-heading">Core</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i>
                        <span>Dashboard</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link active" href="residents.php">
                        <i class="fas fa-users"></i>
                        <span>Residents</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="properties.php">
                        <i class="fas fa-building"></i>
                        <span>Properties</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-divider"></div>
            
            <div class="sidebar-heading">Financial</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="payments.php">
                        <i class="fas fa-money-bill-wave"></i>
                        <span>Payments</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="invoices.php">
                        <i class="fas fa-file-invoice-dollar"></i>
                        <span>Invoices</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="reports.php">
                        <i class="fas fa-chart-bar"></i>
                        <span>Reports</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-divider"></div>
            
            <div class="sidebar-heading">Management</div>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="maintenance.php">
                        <i class="fas fa-tools"></i>
                        <span>Maintenance</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="users.php">
                        <i class="fas fa-user-cog"></i>
                        <span>Users</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="settings.php">
                        <i class="fas fa-cog"></i>
                        <span>Settings</span>
                    </a>
                </li>
            </ul>
            
            <div class="sidebar-divider"></div>
            
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link" href="../logout.php">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </li>
            </ul>
        </aside>
        
        <!-- Main Content -->
        <main class="admin-content">
            <!-- Top Navigation Bar -->
            <div class="admin-topbar">
                <div class="page-title">
                    <h1>Residents Management</h1>
                </div>
                
                <div class="top-nav-items">
                    <div class="top-nav-item">
                        <a href="#" class="top-nav-link" title="Notifications">
                            <i class="fas fa-bell"></i>
                        </a>
                    </div>
                    
                    <div class="top-nav-item">
                        <a href="#" class="top-nav-link" title="Messages">
                            <i class="fas fa-envelope"></i>
                        </a>
                    </div>
                    
                    <div class="top-nav-item">
                        <div class="profile-dropdown">
                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['name']); ?>&background=random" alt="Profile">
                            <span><?php echo htmlspecialchars($user['name']); ?></span>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Content Container -->
            <div class="card mb-4">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6>Residents List</h6>
                        <div>
                            <a href="resident-add.php" class="btn btn-sm btn-primary">
                                <i class="fas fa-plus"></i> Add New Resident
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Filters -->
                    <div class="mb-3">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="input-group">
                                    <input type="text" class="form-control" placeholder="Search residents..." id="searchInput">
                                    <button class="btn btn-primary" type="button">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="col-md-6 d-flex justify-content-md-end">
                                <div class="btn-group">
                                    <a href="?filter=all" class="btn btn-outline-primary <?php echo $filter === 'all' ? 'active' : ''; ?>">All</a>
                                    <a href="?filter=active" class="btn btn-outline-primary <?php echo $filter === 'active' ? 'active' : ''; ?>">Active</a>
                                    <a href="?filter=inactive" class="btn btn-outline-primary <?php echo $filter === 'inactive' ? 'active' : ''; ?>">Inactive</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Residents Table -->
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="?sort=name&order=<?php echo $sort === 'name' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&filter=<?php echo $filter; ?>" class="text-decoration-none text-secondary">
                                            Name
                                            <?php if ($sort === 'name'): ?>
                                                <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?sort=email&order=<?php echo $sort === 'email' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&filter=<?php echo $filter; ?>" class="text-decoration-none text-secondary">
                                            Email
                                            <?php if ($sort === 'email'): ?>
                                                <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>Phone</th>
                                    <th>
                                        <a href="?sort=property_count&order=<?php echo $sort === 'property_count' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&filter=<?php echo $filter; ?>" class="text-decoration-none text-secondary">
                                            Properties
                                            <?php if ($sort === 'property_count'): ?>
                                                <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?sort=last_payment_date&order=<?php echo $sort === 'last_payment_date' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&filter=<?php echo $filter; ?>" class="text-decoration-none text-secondary">
                                            Last Payment
                                            <?php if ($sort === 'last_payment_date'): ?>
                                                <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>
                                        <a href="?sort=status&order=<?php echo $sort === 'status' && $order === 'ASC' ? 'DESC' : 'ASC'; ?>&filter=<?php echo $filter; ?>" class="text-decoration-none text-secondary">
                                            Status
                                            <?php if ($sort === 'status'): ?>
                                                <i class="fas fa-sort-<?php echo $order === 'ASC' ? 'up' : 'down'; ?>"></i>
                                            <?php endif; ?>
                                        </a>
                                    </th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (count($residents) > 0): ?>
                                    <?php foreach ($residents as $resident): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($resident['name']); ?>&background=random" alt="Profile" class="rounded-circle" width="32" height="32">
                                                    <span class="ms-2"><?php echo htmlspecialchars($resident['name']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($resident['email']); ?></td>
                                            <td><?php echo htmlspecialchars($resident['phone'] ?? 'N/A'); ?></td>
                                            <td>
                                                <span class="badge <?php echo $resident['property_count'] > 0 ? 'badge-info' : 'badge-secondary'; ?>">
                                                    <?php echo $resident['property_count']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $resident['last_payment_date'] ? date('M d, Y', strtotime($resident['last_payment_date'])) : 'No payments'; ?>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $resident['status'] === 'active' ? 'badge-success' : 'badge-danger'; ?>">
                                                    <?php echo ucfirst($resident['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="resident-view.php?id=<?php echo $resident['id']; ?>" class="action-btn" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="resident-edit.php?id=<?php echo $resident['id']; ?>" class="action-btn" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="resident-properties.php?id=<?php echo $resident['id']; ?>" class="action-btn" title="Manage Properties">
                                                    <i class="fas fa-home"></i>
                                                </a>
                                                <a href="resident-payments.php?id=<?php echo $resident['id']; ?>" class="action-btn" title="Payment History">
                                                    <i class="fas fa-money-bill"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">No residents found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <footer class="mt-4 text-center text-muted">
                <p>© 2023 CTB Property Management. All rights reserved.</p>
            </footer>
        </main>
    </div>

    <!-- Bootstrap Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom Scripts -->
    <script>
        // Toggle sidebar on mobile
        document.addEventListener('DOMContentLoaded', function() {
            const body = document.querySelector('body');
            const topbarToggle = document.createElement('button');
            topbarToggle.classList.add('d-lg-none', 'btn', 'btn-sm', 'btn-primary');
            topbarToggle.innerHTML = '<i class="fas fa-bars"></i>';
            topbarToggle.style.position = 'fixed';
            topbarToggle.style.top = '1rem';
            topbarToggle.style.left = '1rem';
            topbarToggle.style.zIndex = '1000';
            
            topbarToggle.addEventListener('click', function() {
                body.classList.toggle('sidebar-open');
            });
            
            document.body.appendChild(topbarToggle);
            
            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', function(event) {
                if (body.classList.contains('sidebar-open') && 
                    !event.target.closest('.admin-sidebar') && 
                    event.target !== topbarToggle) {
                    body.classList.remove('sidebar-open');
                }
            });
            
            // Search functionality
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keyup', function(e) {
                    if (e.key === 'Enter') {
                        const searchTerm = this.value.toLowerCase().trim();
                        const rows = document.querySelectorAll('.data-table tbody tr');
                        
                        rows.forEach(row => {
                            const text = row.textContent.toLowerCase();
                            if (text.includes(searchTerm)) {
                                row.style.display = '';
                            } else {
                                row.style.display = 'none';
                            }
                        });
                    }
                });
            }
        });
    </script>
</body>
</html> 