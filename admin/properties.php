<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';

// Check if user is logged in and has appropriate role
requireRole('admin');


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
    $query = "SELECT p.*, u.name as user_name 
              FROM properties p 
              LEFT JOIN users u ON p.user_id = u.id
              WHERE 1=1";
    $count_query = "SELECT COUNT(*) as total FROM properties WHERE 1=1";
    $params = [];
    
    // Apply filters
    if (!empty($search)) {
        $query .= " AND (p.identifier LIKE :search)";
        $count_query .= " AND (identifier LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($type_filter)) {
        $query .= " AND p.type = :type";
        $count_query .= " AND type = :type";
        $params[':type'] = $type_filter;
    }
    
    if (!empty($status_filter)) {
        if ($status_filter === 'assigned') {
            $query .= " AND p.user_id IS NOT NULL";
            $count_query .= " AND user_id IS NOT NULL";
        } else if ($status_filter === 'unassigned') {
            $query .= " AND p.user_id IS NULL";
            $count_query .= " AND user_id IS NULL";
        }
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
    
    // Get counts for assigned vs unassigned
    $assigned_stmt = $db->prepare("SELECT COUNT(*) as count FROM properties WHERE user_id IS NOT NULL");
    $assigned_stmt->execute();
    $assigned_count = $assigned_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    $unassigned_stmt = $db->prepare("SELECT COUNT(*) as count FROM properties WHERE user_id IS NULL");
    $unassigned_stmt->execute();
    $unassigned_count = $unassigned_stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    $properties = [];
    $total = 0;
    $total_pages = 0;
    $type_counts = ['apartment' => 0, 'parking' => 0];
    $assigned_count = 0;
    $unassigned_count = 0;
}

// Page title
$page_title = "Gestion des Propriétés";
?>

<!DOCTYPE html>
<html lang="fr">
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
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <h1>Gestion des Propriétés</h1>
            </div>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                        echo $_SESSION['success']; 
                        unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                        echo $_SESSION['error']; 
                        unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Stats Cards -->
            <div class="content-wrapper">
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon properties">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Appartements</h3>
                            <div class="stat-number"><?php echo isset($type_counts['apartment']) ? $type_counts['apartment'] : 0; ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon tickets">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Places de Parking</h3>
                            <div class="stat-number"><?php echo isset($type_counts['parking']) ? $type_counts['parking'] : 0; ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Assignées</h3>
                            <div class="stat-number"><?php echo $assigned_count; ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon payments">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Total Propriétés</h3>
                            <div class="stat-number"><?php echo $total; ?></div>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-circle" style="color: #28a745;"></i> Assignées: <?php echo $assigned_count; ?></span>
                                <span><i class="fas fa-circle" style="color: #dc3545;"></i> Non Assignées: <?php echo $unassigned_count; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card user-filter-card">
                    <div class="card-header user-filter-header">
                        <h3><i class="fas fa-filter"></i> Liste des Propriétés</h3>
                        <form id="filter-form" action="properties.php" method="GET" class="filter-form">
                            <div class="filter-wrapper">
                                <div class="search-filter">
                                    <div class="search-bar">
                                        <i class="fas fa-search"></i>
                                        <input type="text" placeholder="Rechercher..." name="search" value="<?php echo htmlspecialchars($search); ?>" autocomplete="off" autofocus>
                                    </div>
                                </div>
                                <div class="filter-group">
                                    <label for="type">Type:</label>
                                    <select name="type" id="type" onchange="this.form.submit()">
                                        <option value="">Tous les Types</option>
                                        <option value="apartment" <?php echo $type_filter === 'apartment' ? 'selected' : ''; ?>>Appartement</option>
                                        <option value="parking" <?php echo $type_filter === 'parking' ? 'selected' : ''; ?>>Parking</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="status">Statut:</label>
                                    <select name="status" id="status" onchange="this.form.submit()">
                                        <option value="">Tous les Statuts</option>
                                        <option value="assigned" <?php echo $status_filter === 'assigned' ? 'selected' : ''; ?>>Assignée</option>
                                        <option value="unassigned" <?php echo $status_filter === 'unassigned' ? 'selected' : ''; ?>>Non Assignée</option>
                                    </select>
                                </div>
                                <a href="properties.php" class="reset-link">Réinitialiser</a>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <?php if (empty($properties)): ?>
                            <div class="no-data">
                                <i class="fas fa-building"></i>
                                <p>Aucune propriété trouvée. Essayez d'ajuster vos filtres.</p>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Identifiant</th>
                                            <th>Type</th>
                                            <th>Assignée à</th>
                                            <th>Créée le</th>
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
                                                        <span class="property-identifier"><?php echo htmlspecialchars($property['identifier']); ?></span>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="property-type-badge">
                                                        <?php echo ucfirst(htmlspecialchars($property['type'])); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($property['user_name'])): ?>
                                                        <span class="resident-tag">
                                                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($property['user_name']); ?>
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="unassigned-tag">
                                                            <i class="fas fa-times-circle"></i> Unassigned
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($property['created_at'])); ?></td>
                                                <td class="actions">
                                                    <a href="view-property.php?id=<?php echo $property['id']; ?>" class="btn-icon" title="View Property">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit-property.php?id=<?php echo $property['id']; ?>" class="btn-icon" title="Edit Property">
                                                        <i class="fas fa-edit"></i>
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
            </div>
        </main>
    </div>

    <script src="js/dark-mode.js"></script>
    <script>
        // Real-time search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-filter input[name="search"]');
            const propertyRows = document.querySelectorAll('.table tbody tr');
            
            function performSearch(searchTerm) {
                searchTerm = searchTerm.toLowerCase().trim();
                
                let visibleCount = 0;
                propertyRows.forEach(row => {
                    const identifier = row.querySelector('.property-identifier')?.textContent.toLowerCase() || '';
                    const type = row.querySelector('.property-type-badge')?.textContent.toLowerCase() || '';
                    const resident = row.querySelector('.resident-tag')?.textContent.toLowerCase() || '';
                    const unassigned = row.querySelector('.unassigned-tag')?.textContent.toLowerCase() || '';
                    
                    if (identifier.includes(searchTerm) || type.includes(searchTerm) || 
                        resident.includes(searchTerm) || unassigned.includes(searchTerm)) {
                        row.style.display = '';
                        visibleCount++;
                    } else {
                        row.style.display = 'none';
                    }
                });
                
                // Show/hide "no results" message if needed
                const existingNoResults = document.querySelector('.search-no-results');
                const tableContainer = document.querySelector('.table-responsive');
                
                if (visibleCount === 0 && !existingNoResults && tableContainer) {
                    const noResults = document.createElement('div');
                    noResults.className = 'no-data search-no-results';
                    noResults.innerHTML = '<i class="fas fa-search"></i><p>No properties match your search criteria.</p>';
                    
                    tableContainer.style.display = 'none';
                    tableContainer.parentNode.insertBefore(noResults, tableContainer.nextSibling);
                } else if (visibleCount > 0 && existingNoResults) {
                    existingNoResults.remove();
                    if (tableContainer) tableContainer.style.display = '';
                }
            }
            
            if (searchInput) {
                // Set focus on search input if it's empty
                if (!searchInput.value) {
                    setTimeout(() => {
                        searchInput.focus();
                    }, 100);
                }
                
                searchInput.addEventListener('input', function() {
                    performSearch(this.value);
                });
                
                // Add a clear button to the search input
                const searchContainer = searchInput.parentElement;
                const clearButton = document.createElement('i');
                clearButton.className = 'fas fa-times search-clear';
                clearButton.style.display = searchInput.value ? 'block' : 'none';
                clearButton.style.cursor = 'pointer';
                clearButton.style.position = 'absolute';
                clearButton.style.right = '10px';
                clearButton.style.top = '50%';
                clearButton.style.transform = 'translateY(-50%)';
                searchContainer.style.position = 'relative';
                searchContainer.appendChild(clearButton);
                
                // Show/hide clear button based on input value
                searchInput.addEventListener('input', function() {
                    clearButton.style.display = this.value ? 'block' : 'none';
                });
                
                // Clear search when button is clicked
                clearButton.addEventListener('click', function() {
                    searchInput.value = '';
                    performSearch('');
                    this.style.display = 'none';
                    searchInput.focus();
                });
                
                // If there's an initial search value, perform the search
                if (searchInput.value) {
                    performSearch(searchInput.value);
                }
            }
        });
    </script>
</body>
</html> 