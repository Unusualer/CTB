<?php
// Start session and include configuration
session_start();
require_once '../includes/config.php';

// Check if user is logged in and has manager role
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'manager') {
    $_SESSION['error_message'] = "You don't have permission to access this page.";
    header("Location: ../login.php");
    exit();
}

// Initialize variables
$logs = [];
$filters = [
    'user_id' => isset($_GET['user_id']) ? intval($_GET['user_id']) : 0,
    'action' => isset($_GET['action']) ? $_GET['action'] : '',
    'entity_type' => isset($_GET['entity_type']) ? $_GET['entity_type'] : '',
    'date_from' => isset($_GET['date_from']) ? $_GET['date_from'] : '',
    'date_to' => isset($_GET['date_to']) ? $_GET['date_to'] : '',
];

// Pagination variables
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

try {
    // Get manager's properties
    $propertyStmt = $conn->prepare("SELECT id FROM properties WHERE manager_id = :manager_id");
    $propertyStmt->bindValue(':manager_id', $_SESSION['user_id'], PDO::PARAM_INT);
    $propertyStmt->execute();
    $propertyIds = $propertyStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get residents associated with manager's properties
    $residentIds = [];
    if (!empty($propertyIds)) {
        $placeholders = str_repeat('?,', count($propertyIds) - 1) . '?';
        $residentStmt = $conn->prepare("SELECT DISTINCT user_id FROM resident_properties WHERE property_id IN ($placeholders)");
        $residentStmt->execute($propertyIds);
        $residentIds = $residentStmt->fetchAll(PDO::FETCH_COLUMN);
    }
    
    // Build query based on filters
    // Managers can see:
    // 1. All actions they personally performed
    // 2. Activities related to their properties
    // 3. Activities performed by residents of their properties
    $query = "SELECT a.*, u.name, u.email
              FROM activity_log a
              LEFT JOIN users u ON a.user_id = u.id
              WHERE (a.user_id = :manager_id";
    $params = [':manager_id' => $_SESSION['user_id']];
    
    // Add property-related activities
    if (!empty($propertyIds)) {
        $query .= " OR (a.entity_type = 'property' AND a.entity_id IN (";
        $propCount = 0;
        foreach ($propertyIds as $propId) {
            $propParam = ":prop_id_" . $propCount;
            $params[$propParam] = $propId;
            $query .= $propParam . ", ";
            $propCount++;
        }
        $query = rtrim($query, ", ") . "))";
    }
    
    // Add resident-related activities
    if (!empty($residentIds)) {
        $query .= " OR a.user_id IN (";
        $resCount = 0;
        foreach ($residentIds as $resId) {
            $resParam = ":res_id_" . $resCount;
            $params[$resParam] = $resId;
            $query .= $resParam . ", ";
            $resCount++;
        }
        $query = rtrim($query, ", ") . ")";
    }
    
    $query .= ")";
    
    // Apply additional filters
    if ($filters['user_id'] > 0) {
        $query .= " AND a.user_id = :filter_user_id";
        $params[':filter_user_id'] = $filters['user_id'];
    }
    
    if (!empty($filters['action'])) {
        $query .= " AND a.action = :action";
        $params[':action'] = $filters['action'];
    }
    
    if (!empty($filters['entity_type'])) {
        $query .= " AND a.entity_type = :entity_type";
        $params[':entity_type'] = $filters['entity_type'];
    }
    
    if (!empty($filters['date_from'])) {
        $query .= " AND DATE(a.created_at) >= :date_from";
        $params[':date_from'] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $query .= " AND DATE(a.created_at) <= :date_to";
        $params[':date_to'] = $filters['date_to'];
    }
    
    // Get total count for pagination
    $countQuery = str_replace("SELECT a.*, u.name, u.email", "SELECT COUNT(*) as total", $query);
    $countStmt = $conn->prepare($countQuery);
    foreach ($params as $key => $value) {
        $countStmt->bindValue($key, $value);
    }
    $countStmt->execute();
    $totalRecords = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
    $totalPages = ceil($totalRecords / $limit);
    
    // Get paginated results
    $query .= " ORDER BY a.created_at DESC LIMIT :offset, :limit";
    $stmt = $conn->prepare($query);
    foreach ($params as $key => $value) {
        $stmt->bindValue($key, $value);
    }
    $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
    $stmt->execute();
    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get unique actions for filter
    $actionQuery = "SELECT DISTINCT action FROM activity_log WHERE user_id = :manager_id";
    $actionParams = [':manager_id' => $_SESSION['user_id']];
    
    if (!empty($propertyIds)) {
        $actionQuery .= " OR (entity_type = 'property' AND entity_id IN (";
        $propCount = 0;
        foreach ($propertyIds as $propId) {
            $propParam = ":a_prop_id_" . $propCount;
            $actionParams[$propParam] = $propId;
            $actionQuery .= $propParam . ", ";
            $propCount++;
        }
        $actionQuery = rtrim($actionQuery, ", ") . "))";
    }
    
    if (!empty($residentIds)) {
        $actionQuery .= " OR user_id IN (";
        $resCount = 0;
        foreach ($residentIds as $resId) {
            $resParam = ":a_res_id_" . $resCount;
            $actionParams[$resParam] = $resId;
            $actionQuery .= $resParam . ", ";
            $resCount++;
        }
        $actionQuery = rtrim($actionQuery, ", ") . ")";
    }
    
    $actionStmt = $conn->prepare($actionQuery);
    foreach ($actionParams as $key => $value) {
        $actionStmt->bindValue($key, $value);
    }
    $actionStmt->execute();
    $actions = $actionStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get unique entity types for filter
    $entityQuery = "SELECT DISTINCT entity_type FROM activity_log WHERE user_id = :manager_id";
    $entityParams = [':manager_id' => $_SESSION['user_id']];
    
    if (!empty($propertyIds)) {
        $entityQuery .= " OR (entity_type = 'property' AND entity_id IN (";
        $propCount = 0;
        foreach ($propertyIds as $propId) {
            $propParam = ":e_prop_id_" . $propCount;
            $entityParams[$propParam] = $propId;
            $entityQuery .= $propParam . ", ";
            $propCount++;
        }
        $entityQuery = rtrim($entityQuery, ", ") . "))";
    }
    
    if (!empty($residentIds)) {
        $entityQuery .= " OR user_id IN (";
        $resCount = 0;
        foreach ($residentIds as $resId) {
            $resParam = ":e_res_id_" . $resCount;
            $entityParams[$resParam] = $resId;
            $entityQuery .= $resParam . ", ";
            $resCount++;
        }
        $entityQuery = rtrim($entityQuery, ", ") . ")";
    }
    
    $entityStmt = $conn->prepare($entityQuery);
    foreach ($entityParams as $key => $value) {
        $entityStmt->bindValue($key, $value);
    }
    $entityStmt->execute();
    $entityTypes = $entityStmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Get users for filter (manager + residents)
    $userIds = array_merge([$_SESSION['user_id']], $residentIds);
    if (!empty($userIds)) {
        $userPlaceholders = str_repeat('?,', count($userIds) - 1) . '?';
        $userStmt = $conn->prepare("SELECT id, CONCAT(first_name, ' ', last_name) as name 
                                   FROM users 
                                   WHERE id IN ($userPlaceholders)
                                   ORDER BY name");
        $userStmt->execute($userIds);
        $users = $userStmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $users = [];
    }
    
} catch (PDOException $e) {
    $_SESSION['error_message'] = "Database error: " . $e->getMessage();
}

// Page title
$page_title = "Activity Log";

// Include header
include 'manager-header.php';
?>

<div class="container-fluid">
    <div class="page-header">
        <h1><?php echo $page_title; ?></h1>
        <p>Track activities related to your properties and residents</p>
    </div>
    
    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success">
            <?php 
                echo $_SESSION['success_message']; 
                unset($_SESSION['success_message']);
            ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger">
            <?php 
                echo $_SESSION['error_message']; 
                unset($_SESSION['error_message']);
            ?>
        </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-header">
            <h5>Filter Activity Log</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="" class="row">
                <div class="col-md-2 mb-3">
                    <label for="user_id">User</label>
                    <select name="user_id" id="user_id" class="form-select">
                        <option value="0">All Users</option>
                        <?php foreach($users as $user): ?>
                            <option value="<?php echo $user['id']; ?>" <?php echo ($filters['user_id'] == $user['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($user['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="action">Action</label>
                    <select name="action" id="action" class="form-select">
                        <option value="">All Actions</option>
                        <?php foreach($actions as $action): ?>
                            <option value="<?php echo $action; ?>" <?php echo ($filters['action'] == $action) ? 'selected' : ''; ?>>
                                <?php echo ucfirst(htmlspecialchars($action)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="entity_type">Entity Type</label>
                    <select name="entity_type" id="entity_type" class="form-select">
                        <option value="">All Types</option>
                        <?php foreach($entityTypes as $type): ?>
                            <option value="<?php echo $type; ?>" <?php echo ($filters['entity_type'] == $type) ? 'selected' : ''; ?>>
                                <?php echo ucfirst(htmlspecialchars($type)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="date_from">Date From</label>
                    <input type="date" name="date_from" id="date_from" class="form-control" value="<?php echo $filters['date_from']; ?>">
                </div>
                
                <div class="col-md-2 mb-3">
                    <label for="date_to">Date To</label>
                    <input type="date" name="date_to" id="date_to" class="form-control" value="<?php echo $filters['date_to']; ?>">
                </div>
                
                <div class="col-md-2 d-flex align-items-end mb-3">
                    <button type="submit" class="btn btn-primary me-2">Filter</button>
                    <a href="activity-log.php" class="btn btn-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Entity Type</th>
                            <th>Entity ID</th>
                            <th>Details</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No activity logs found</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($logs as $log): ?>
                                <tr>
                                    <td><?php echo $log['id']; ?></td>
                                    <td>
                                        <?php if (!empty($log['name'])): ?>
                                            <?php echo htmlspecialchars($log['name']); ?>
                                            <br><small class="text-muted"><?php echo htmlspecialchars($log['email']); ?></small>
                                        <?php else: ?>
                                            <span class="text-muted">User ID: <?php echo $log['user_id']; ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill bg-<?php echo getActionBadgeColor($log['action']); ?>">
                                            <?php echo ucfirst(htmlspecialchars($log['action'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo ucfirst(htmlspecialchars($log['entity_type'])); ?></td>
                                    <td><?php echo $log['entity_id']; ?></td>
                                    <td class="text-wrap"><?php echo htmlspecialchars($log['details']); ?></td>
                                    <td><?php echo date('M j, Y, g:i a', strtotime($log['created_at'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($totalPages > 1): ?>
                <nav aria-label="Activity log pagination">
                    <ul class="pagination justify-content-center">
                        <?php if ($page > 1): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page - 1; ?>&user_id=<?php echo $filters['user_id']; ?>&action=<?php echo urlencode($filters['action']); ?>&entity_type=<?php echo urlencode($filters['entity_type']); ?>&date_from=<?php echo urlencode($filters['date_from']); ?>&date_to=<?php echo urlencode($filters['date_to']); ?>" aria-label="Previous">
                                    <span aria-hidden="true">&laquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                        
                        <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                            <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                <a class="page-link" href="?page=<?php echo $i; ?>&user_id=<?php echo $filters['user_id']; ?>&action=<?php echo urlencode($filters['action']); ?>&entity_type=<?php echo urlencode($filters['entity_type']); ?>&date_from=<?php echo urlencode($filters['date_from']); ?>&date_to=<?php echo urlencode($filters['date_to']); ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $totalPages): ?>
                            <li class="page-item">
                                <a class="page-link" href="?page=<?php echo $page + 1; ?>&user_id=<?php echo $filters['user_id']; ?>&action=<?php echo urlencode($filters['action']); ?>&entity_type=<?php echo urlencode($filters['entity_type']); ?>&date_from=<?php echo urlencode($filters['date_from']); ?>&date_to=<?php echo urlencode($filters['date_to']); ?>" aria-label="Next">
                                    <span aria-hidden="true">&raquo;</span>
                                </a>
                            </li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
// Helper function to get badge color based on action
function getActionBadgeColor($action) {
    switch ($action) {
        case 'created':
        case 'added':
            return 'success';
        case 'updated':
        case 'modified':
            return 'primary';
        case 'deleted':
        case 'removed':
            return 'danger';
        case 'activated':
            return 'info';
        case 'deactivated':
            return 'warning';
        case 'logged in':
            return 'info';
        case 'logged out':
            return 'secondary';
        default:
            return 'secondary';
    }
}

// Include footer
include 'manager-footer.php';
?> 