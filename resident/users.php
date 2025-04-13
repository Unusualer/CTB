<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';

// Check if user is logged in and has appropriate role
requireRole('resident');


// Initialize variables
$search = $_GET['search'] ?? '';
$role_filter = $_GET['role'] ?? '';
$status_filter = $_GET['status'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Get total count and users list
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Build the query
    $query = "SELECT * FROM users WHERE 1=1";
    $count_query = "SELECT COUNT(*) as total FROM users WHERE 1=1";
    $params = [];
    
    // Apply filters
    if (!empty($search)) {
        $query .= " AND (name LIKE :search OR email LIKE :search)";
        $count_query .= " AND (name LIKE :search OR email LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($role_filter)) {
        $query .= " AND role = :role";
        $count_query .= " AND role = :role";
        $params[':role'] = $role_filter;
    }
    
    if (!empty($status_filter)) {
        $query .= " AND status = :status";
        $count_query .= " AND status = :status";
        $params[':status'] = $status_filter;
    }
    
    // Add ordering
    $query .= " ORDER BY created_at DESC LIMIT :offset, :limit";
    
    // Get total count
    $count_stmt = $db->prepare($count_query);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value);
    }
    $count_stmt->execute();
    $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total / $items_per_page);
    
    // Get users
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get counts by role for stats
    $role_counts = [];
    $role_stmt = $db->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $role_stmt->execute();
    $role_results = $role_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($role_results as $role_data) {
        $role_counts[$role_data['role']] = $role_data['count'];
    }
    
    // Get counts by status for stats
    $status_counts = [];
    $status_stmt = $db->prepare("SELECT status, COUNT(*) as count FROM users GROUP BY status");
    $status_stmt->execute();
    $status_results = $status_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($status_results as $status_data) {
        $status_counts[$status_data['status']] = $status_data['count'];
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    $users = [];
    $total = 0;
    $total_pages = 0;
    $role_counts = ['admin' => 0, 'manager' => 0, 'resident' => 0];
    $status_counts = ['active' => 0, 'inactive' => 0];
}

// Page title
$page_title = "Gestion des Utilisateurs";
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
                <h1>Gestion des Utilisateurs</h1>
                <a href="add-user.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Ajouter un Utilisateur
                </a>
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
                        <div class="stat-icon users">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Administrateurs</h3>
                            <div class="stat-number"><?php echo isset($role_counts['admin']) ? $role_counts['admin'] : 0; ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon properties">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Gestionnaires</h3>
                            <div class="stat-number"><?php echo isset($role_counts['manager']) ? $role_counts['manager'] : 0; ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon tickets">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Résidents</h3>
                            <div class="stat-number"><?php echo isset($role_counts['resident']) ? $role_counts['resident'] : 0; ?></div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon payments">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Total Utilisateurs</h3>
                            <div class="stat-number"><?php echo $total; ?></div>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-circle" style="color: #28a745;"></i> Actifs: <?php echo isset($status_counts['active']) ? $status_counts['active'] : 0; ?></span>
                                <span><i class="fas fa-circle" style="color: #dc3545;"></i> Inactifs: <?php echo isset($status_counts['inactive']) ? $status_counts['inactive'] : 0; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="card user-filter-card">
                    <div class="card-header user-filter-header">
                        <h3><i class="fas fa-filter"></i> Liste des Utilisateurs</h3>
                        <form id="filter-form" action="users.php" method="GET" class="filter-form">
                            <div class="filter-wrapper">
                                <div class="search-filter">
                                    <div class="search-bar">
                                        <i class="fas fa-search"></i>
                                        <input type="text" placeholder="Rechercher..." name="search" value="<?php echo htmlspecialchars($search); ?>" autocomplete="off" autofocus>
                                    </div>
                                </div>
                                <div class="filter-group">
                                    <label for="role">Rôle:</label>
                                    <select name="role" id="role" onchange="this.form.submit()">
                                        <option value="">Tous les Rôles</option>
                                        <option value="admin" <?php echo $role_filter === 'admin' ? 'selected' : ''; ?>>Administrateur</option>
                                        <option value="manager" <?php echo $role_filter === 'manager' ? 'selected' : ''; ?>>Gestionnaire</option>
                                        <option value="resident" <?php echo $role_filter === 'resident' ? 'selected' : ''; ?>>Résident</option>
                                    </select>
                                </div>
                                <div class="filter-group">
                                    <label for="status">Statut:</label>
                                    <select name="status" id="status" onchange="this.form.submit()">
                                        <option value="">Tous les Statuts</option>
                                        <option value="active" <?php echo $status_filter === 'active' ? 'selected' : ''; ?>>Actif</option>
                                        <option value="inactive" <?php echo $status_filter === 'inactive' ? 'selected' : ''; ?>>Inactif</option>
                                    </select>
                                </div>
                                <a href="users.php" class="reset-link">Réinitialiser</a>
                            </div>
                        </form>
                    </div>
                    <div class="card-body">
                        <?php if (empty($users)): ?>
                            <div class="no-data">
                                <i class="fas fa-users"></i>
                                <p>Aucun utilisateur trouvé. Essayez d'ajuster vos filtres ou d'ajouter un nouvel utilisateur.</p>
                                <a href="add-user.php" class="btn btn-primary">Ajouter un Utilisateur</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nom</th>
                                            <th>Email</th>
                                            <th>Rôle</th>
                                            <th>Statut</th>
                                            <th>Créé le</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user): ?>
                                            <tr>
                                                <td><?php echo $user['id']; ?></td>
                                                <td>
                                                    <div class="user-cell">
                                                        <div class="user-avatar-sm">
                                                            <?php if (!empty($user['profile_image'])): ?>
                                                                <img src="../assets/img/avatars/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                                                            <?php else: ?>
                                                                <div class="avatar-placeholder-sm">
                                                                    <span class="avatar-initial-sm"><?php echo substr(htmlspecialchars($user['name']), 0, 1); ?></span>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                        <span class="user-name"><?php echo htmlspecialchars($user['name']); ?></span>
                                                    </div>
                                                </td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td>
                                                    <span class="user-role-badge">
                                                        <?php
                                                        $roleIcon = '';
                                                        switch($user['role']) {
                                                            case 'admin':
                                                                $roleIcon = '<i class="fas fa-user-shield"></i>';
                                                                break;
                                                            case 'manager':
                                                                $roleIcon = '<i class="fas fa-user-cog"></i>';
                                                                break;
                                                            case 'resident':
                                                                $roleIcon = '<i class="fas fa-user"></i>';
                                                                break;
                                                        }
                                                        echo $roleIcon . ' ' . ucfirst(htmlspecialchars($user['role']));
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="status-badge <?php echo $user['status']; ?>">
                                                        <i class="fas fa-circle"></i>
                                                        <?php echo ucfirst(htmlspecialchars($user['status'])); ?>
                                                    </span>
                                                </td>
                                                <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                                                <td class="actions">
                                                    <a href="view-user.php?id=<?php echo $user['id']; ?>" class="btn-icon" title="View User">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn-icon" title="Edit User">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="javascript:void(0);" class="btn-icon delete-user" data-id="<?php echo $user['id']; ?>" title="Delete User">
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
                                        <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>" class="pagination-link">
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                        <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>" 
                                        class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    <?php endfor; ?>
                                    
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&role=<?php echo urlencode($role_filter); ?>&status=<?php echo urlencode($status_filter); ?>" class="pagination-link">
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

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirmer la Suppression</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <p>Êtes-vous sûr de vouloir supprimer cet utilisateur ? Cette action est irréversible.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="delete-user.php" method="POST">
                    <input type="hidden" name="user_id" id="deleteUserId">
                    <button type="button" class="btn btn-secondary close-modal">Annuler</button>
                    <button type="submit" class="btn btn-danger">Supprimer</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/dark-mode.js"></script>
    <script>
        // Delete user modal functionality
        const modal = document.getElementById('deleteModal');
        const deleteButtons = document.querySelectorAll('.delete-user');
        const closeButtons = document.querySelectorAll('.close, .close-modal');
        const deleteForm = document.getElementById('deleteForm');
        const deleteUserIdInput = document.getElementById('deleteUserId');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const userId = this.getAttribute('data-id');
                deleteUserIdInput.value = userId;
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

        // Real-time search functionality
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('.search-filter input[name="search"]');
            const userRows = document.querySelectorAll('.table tbody tr');
            
            function performSearch(searchTerm) {
                searchTerm = searchTerm.toLowerCase().trim();
                
                let visibleCount = 0;
                userRows.forEach(row => {
                    const name = row.querySelector('.user-name')?.textContent.toLowerCase() || '';
                    const email = row.querySelector('td:nth-child(3)')?.textContent.toLowerCase() || '';
                    const role = row.querySelector('.user-role-badge')?.textContent.toLowerCase() || '';
                    const status = row.querySelector('.status-badge')?.textContent.toLowerCase() || '';
                    
                    if (name.includes(searchTerm) || email.includes(searchTerm) || 
                        role.includes(searchTerm) || status.includes(searchTerm)) {
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
                    noResults.innerHTML = '<i class="fas fa-search"></i><p>No users match your search criteria.</p>';
                    
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