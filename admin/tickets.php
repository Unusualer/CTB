<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error'] = "Vous devez être connecté en tant qu'administrateur pour accéder à cette page.";
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';
$priority_filter = $_GET['priority'] ?? '';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$items_per_page = 10;
$offset = ($page - 1) * $items_per_page;

// Get total count and tickets list
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Build the query - select tickets with user and property info
    $query = "SELECT t.* 
              FROM maintenance t 
              WHERE 1=1";
    $count_query = "SELECT COUNT(*) as total FROM maintenance t WHERE 1=1";
    $params = [];
    
    // Apply filters
    if (!empty($search)) {
        $query .= " AND (t.title LIKE :search OR t.description LIKE :search)";
        $count_query .= " AND (t.title LIKE :search OR t.description LIKE :search)";
        $params[':search'] = "%$search%";
    }
    
    if (!empty($status_filter)) {
        $query .= " AND t.status = :status";
        $count_query .= " AND t.status = :status";
        $params[':status'] = $status_filter;
    }
    
    // Add ordering
    $query .= " ORDER BY t.created_at DESC LIMIT :offset, :limit";
    
    // Get total count
    $count_stmt = $db->prepare($count_query);
    foreach ($params as $key => $value) {
        $count_stmt->bindValue($key, $value);
    }
    $count_stmt->execute();
    $total = $count_stmt->fetch(PDO::FETCH_ASSOC)['total'];
    $total_pages = ceil($total / $items_per_page);
    
    // Get tickets
    $stmt = $db->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $items_per_page, PDO::PARAM_INT);
    $stmt->execute();
    $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get counts by status for stats
    $status_counts = [];
    $status_stmt = $db->prepare("SELECT status, COUNT(*) as count FROM maintenance GROUP BY status");
    $status_stmt->execute();
    $status_results = $status_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($status_results as $status_data) {
        $status_counts[$status_data['status']] = $status_data['count'];
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    $tickets = [];
    $total = 0;
    $total_pages = 0;
    $status_counts = ['reported' => 0, 'in_progress' => 0, 'completed' => 0, 'cancelled' => 0];
}

// Page title
$page_title = "Gestion des Tickets";
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
                <h1>Gestion des Tickets</h1>
                <a href="add-ticket.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i>
                    Créer un Nouveau Ticket
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
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon tickets">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Signalés</h3>
                        <div class="stat-number"><?php echo isset($status_counts['reported']) ? $status_counts['reported'] : 0; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon users">
                        <i class="fas fa-tools"></i>
                    </div>
                    <div class="stat-details">
                        <h3>En Cours</h3>
                        <div class="stat-number"><?php echo isset($status_counts['in_progress']) ? $status_counts['in_progress'] : 0; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon properties">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Terminés</h3>
                        <div class="stat-number"><?php echo isset($status_counts['completed']) ? $status_counts['completed'] : 0; ?></div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon payments">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="stat-details">
                        <h3>Statut de Priorité</h3>
                        <div class="stat-number"><?php echo $total; ?> Total</div>
                        <div class="stat-breakdown">
                            <span><i class="fas fa-circle" style="color: #28a745;"></i> Basse: <?php echo isset($priority_counts['low']) ? $priority_counts['low'] : 0; ?></span>
                            <span><i class="fas fa-circle" style="color: #ffc107;"></i> Moyenne: <?php echo isset($priority_counts['medium']) ? $priority_counts['medium'] : 0; ?></span>
                            <span><i class="fas fa-circle" style="color: #dc3545;"></i> Haute/Urgente: <?php echo isset($priority_counts['high']) + isset($priority_counts['urgent']) ? $priority_counts['high'] + $priority_counts['urgent'] : 0; ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="card user-filter-card">
                <div class="card-header user-filter-header">
                    <h3><i class="fas fa-filter"></i> Liste des Tickets</h3>
                    <form id="filter-form" action="tickets.php" method="GET" class="filter-form">
                        <div class="filter-wrapper">
                            <div class="search-filter">
                                <div class="search-bar">
                                    <i class="fas fa-search"></i>
                                    <input type="text" placeholder="Rechercher..." name="search" value="<?php echo htmlspecialchars($search); ?>" autocomplete="off" autofocus>
                                </div>
                            </div>
                            <div class="filter-group">
                                <label for="status">Statut:</label>
                                <select name="status" id="status" onchange="this.form.submit()">
                                    <option value="">Tous les Statuts</option>
                                    <option value="reported" <?php echo $status_filter === 'reported' ? 'selected' : ''; ?>>Signalé</option>
                                    <option value="in_progress" <?php echo $status_filter === 'in_progress' ? 'selected' : ''; ?>>En Cours</option>
                                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Terminé</option>
                                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Annulé</option>
                                </select>
                            </div>
                            <div class="filter-group">
                                <label for="priority">Priorité:</label>
                                <select name="priority" id="priority" onchange="this.form.submit()">
                                    <option value="">Toutes les Priorités</option>
                                    <option value="low" <?php echo $priority_filter === 'low' ? 'selected' : ''; ?>>Basse</option>
                                    <option value="medium" <?php echo $priority_filter === 'medium' ? 'selected' : ''; ?>>Moyenne</option>
                                    <option value="high" <?php echo $priority_filter === 'high' ? 'selected' : ''; ?>>Haute</option>
                                    <option value="urgent" <?php echo $priority_filter === 'urgent' ? 'selected' : ''; ?>>Urgente</option>
                                </select>
                            </div>
                            <a href="tickets.php" class="reset-link">Réinitialiser</a>
                        </div>
                    </form>
                </div>
                <div class="card-body">
                    <?php if (empty($tickets)): ?>
                        <div class="empty-state">
                            <i class="fas fa-ticket-alt"></i>
                            <p>Aucun ticket trouvé. Essayez d'ajuster vos filtres ou d'ajouter un nouveau ticket.</p>
                            <a href="add-ticket.php" class="btn btn-primary">Ajouter un Ticket</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Titre</th>
                                        <th>Propriété</th>
                                        <th>Signalé par</th>
                                        <th>Statut</th>
                                        <th>Priorité</th>
                                        <th>Créé le</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tickets as $ticket): ?>
                                        <tr>
                                            <td><?php echo $ticket['id']; ?></td>
                                            <td>
                                                <div class="ticket-cell">
                                                    <?php echo htmlspecialchars($ticket['title'] ?? $ticket['description']); ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-muted">N/A</span>
                                            </td>
                                            <td>
                                                <span class="text-muted">N/A</span>
                                            </td>
                                            <td>
                                                <?php 
                                                    $statusClass = '';
                                                    switch ($ticket['status']) {
                                                        case 'reported': $statusClass = 'primary'; break;
                                                        case 'in_progress': $statusClass = 'warning'; break;
                                                        case 'completed': $statusClass = 'success'; break;
                                                        case 'cancelled': $statusClass = 'danger'; break;
                                                    }
                                                ?>
                                                <span class="status-indicator status-<?php echo $statusClass; ?>">
                                                    <?php echo ucfirst(htmlspecialchars($ticket['status'])); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-muted">N/A</span>
                                            </td>
                                            <td><?php echo date('M d, Y', strtotime($ticket['created_at'])); ?></td>
                                            <td class="actions">
                                                <a href="view-ticket.php?id=<?php echo $ticket['id']; ?>" class="btn-icon" title="View Ticket">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="edit-ticket.php?id=<?php echo $ticket['id']; ?>" class="btn-icon" title="Edit Ticket">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="javascript:void(0);" class="btn-icon delete-ticket" data-id="<?php echo $ticket['id']; ?>" title="Delete Ticket">
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
                                    <a href="?page=<?php echo $page - 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&priority=<?php echo urlencode($priority_filter); ?>" class="pagination-link">
                                        <i class="fas fa-chevron-left"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&priority=<?php echo urlencode($priority_filter); ?>" 
                                       class="pagination-link <?php echo $i === $page ? 'active' : ''; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <a href="?page=<?php echo $page + 1; ?>&search=<?php echo urlencode($search); ?>&status=<?php echo urlencode($status_filter); ?>&priority=<?php echo urlencode($priority_filter); ?>" class="pagination-link">
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
                <p>Are you sure you want to delete this ticket? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <form id="deleteForm" action="view-ticket.php" method="POST">
                    <input type="hidden" name="delete_ticket" value="1">
                    <input type="hidden" name="ticket_id" id="deleteTicketId">
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete</button>
                </form>
            </div>
        </div>
    </div>

    <script src="js/dark-mode.js"></script>
    <script>
        // Delete ticket modal functionality
        const modal = document.getElementById('deleteModal');
        const deleteButtons = document.querySelectorAll('.delete-ticket');
        const closeButtons = document.querySelectorAll('.close, .close-modal');
        const deleteForm = document.getElementById('deleteForm');
        const deleteTicketIdInput = document.getElementById('deleteTicketId');
        
        deleteButtons.forEach(button => {
            button.addEventListener('click', function() {
                const ticketId = this.getAttribute('data-id');
                deleteTicketIdInput.value = ticketId;
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