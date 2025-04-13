<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';

// Check if user is logged in and has appropriate role
requireRole('admin');


// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error'] = "L'ID de l'utilisateur est requis.";
    header("Location: users.php");
    exit();
}

$user_id = (int)$_GET['id'];
$user = null;
$assigned_properties = [];

// Get user data
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch user details
    $stmt = $db->prepare("SELECT * FROM users WHERE id = :id");
    $stmt->bindParam(':id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    
    if ($stmt->rowCount() === 0) {
        $_SESSION['error'] = "Utilisateur non trouvé.";
        header("Location: users.php");
        exit();
    }
    
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // If user is a resident, get assigned properties
    if ($user['role'] === 'resident') {
        $prop_stmt = $db->prepare("SELECT * FROM properties WHERE user_id = :user_id");
        $prop_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $prop_stmt->execute();
        $assigned_properties = $prop_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Get activity log for this user
    $log_stmt = $db->prepare("SELECT * FROM activity_log WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 10");
    $log_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $log_stmt->execute();
    $activity_logs = $log_stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    header("Location: users.php");
    exit();
}

// Page title
$page_title = "Voir l'Utilisateur";
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
                <div class="breadcrumb">
                    <a href="users.php">Utilisateurs</a>
                    <span>Voir l'Utilisateur</span>
                </div>
                <div class="actions">
                    <a href="edit-user.php?id=<?php echo $user['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Modifier l'Utilisateur
                    </a>
                    <a href="javascript:void(0);" class="btn btn-danger delete-user" data-id="<?php echo $user['id']; ?>">
                        <i class="fas fa-trash-alt"></i> Supprimer l'Utilisateur
                    </a>
                </div>
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

            <div class="content-wrapper">
                <div class="profile-header card">
                    <div class="profile-header-content">
                        <div class="profile-avatar">
                            <?php if (!empty($user['profile_image'])): ?>
                                <img src="../assets/img/avatars/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="<?php echo htmlspecialchars($user['name']); ?>">
                            <?php else: ?>
                                <div class="avatar-placeholder">
                                    <i class="fas fa-user-circle"></i>
                                    <span class="avatar-initial"><?php echo substr(htmlspecialchars($user['name']), 0, 1); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="profile-details">
                            <div class="profile-name-wrapper">
                                <h2><?php echo htmlspecialchars($user['name']); ?></h2>
                                <div class="user-id-badge">ID: <?php echo $user['id']; ?></div>
                            </div>
                            <div class="profile-meta">
                                <span class="user-role"><i class="fas fa-user-tag"></i> <?php echo ucfirst(htmlspecialchars($user['role'])); ?></span>
                                <span class="user-status <?php echo $user['status']; ?>">
                                    <i class="fas fa-circle"></i> <?php echo ucfirst(htmlspecialchars($user['status'])); ?>
                                </span>
                                <span class="user-joined"><i class="far fa-calendar-alt"></i> Joined <?php echo date('M d, Y', strtotime($user['created_at'])); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-grid">
                    <!-- User Information Card -->
                    <div class="card user-info-card">
                        <div class="card-header">
                            <h3><i class="fas fa-info-circle"></i> Informations Utilisateur</h3>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-group">
                                    <label><i class="fas fa-envelope"></i> Email:</label>
                                    <span class="info-value"><?php echo htmlspecialchars($user['email']); ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-phone"></i> Téléphone:</label>
                                    <span class="info-value"><?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : 'Non renseigné'; ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-user-shield"></i> Rôle:</label>
                                    <span class="info-value"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-check-circle"></i> Statut:</label>
                                    <span class="status-badge <?php echo $user['status']; ?>"><?php echo ucfirst(htmlspecialchars($user['status'])); ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-calendar-plus"></i> Créé le:</label>
                                    <span class="info-value"><?php echo date('d F Y', strtotime($user['created_at'])); ?></span>
                                </div>
                                <div class="info-group">
                                    <label><i class="fas fa-edit"></i> Dernière mise à jour:</label>
                                    <span class="info-value"><?php echo date('d F Y', strtotime($user['updated_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Resident Properties Card (if user is a resident) -->
                    <?php if ($user['role'] === 'resident'): ?>
                        <div class="card property-assignments-card">
                            <div class="card-header">
                                <h3><i class="fas fa-building"></i> Propriétés Assignées</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($assigned_properties)): ?>
                                    <div class="empty-state">
                                        <i class="fas fa-building"></i>
                                        <p>Aucune propriété assignée à ce résident.</p>
                                    </div>
                                <?php else: ?>
                                    <div class="property-list">
                                        <?php foreach ($assigned_properties as $property): ?>
                                            <div class="property-item">
                                                <div class="property-icon">
                                                    <i class="<?php echo ($property['type'] === 'apartment') ? 'fas fa-home' : 'fas fa-car'; ?>"></i>
                                                </div>
                                                <div class="property-details">
                                                    <h4><?php echo ucfirst(htmlspecialchars($property['type'])) . ' ' . htmlspecialchars($property['identifier']); ?></h4>
                                                    <div class="property-meta">
                                                        <span class="property-id">ID: <?php echo $property['id']; ?></span>
                                                        <a href="view-property.php?id=<?php echo $property['id']; ?>" class="view-link">
                                                            Voir la Propriété <i class="fas fa-arrow-right"></i>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Activity Log Card -->
                    <div class="card activity-card">
                        <div class="card-header">
                            <h3><i class="fas fa-history"></i> Activité Récente</h3>
                        </div>
                        <div class="card-body">
                            <?php if (empty($activity_logs)): ?>
                                <div class="no-data">
                                    <i class="far fa-clock"></i>
                                    <p>Aucune activité récente trouvée.</p>
                                </div>
                            <?php else: ?>
                                <div class="activity-timeline">
                                    <?php foreach ($activity_logs as $log): ?>
                                        <div class="activity-item">
                                            <div class="activity-icon">
                                                <i class="fas fa-circle"></i>
                                            </div>
                                            <div class="activity-content">
                                                <p class="activity-text"><?php echo isset($log['description']) ? htmlspecialchars($log['description']) : 'Aucune description disponible'; ?></p>
                                                <p class="activity-time"><?php echo isset($log['created_at']) ? date('d M Y H:i', strtotime($log['created_at'])) : 'Date inconnue'; ?></p>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
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
    </script>

    <style>
        /* Enhanced Property List Styling */
        .property-list {
            display: flex;
            flex-direction: column;
            gap: 16px;
        }
        
        .property-item {
            display: flex;
            align-items: center;
            background-color: var(--light-color);
            border-radius: 8px;
            padding: 16px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
        }
        
        .property-item:hover {
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }
        
        .property-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--primary-color-light);
            border-radius: 50%;
            margin-right: 16px;
            color: var(--primary-color);
        }
        
        .property-icon i {
            font-size: 1.25rem;
        }
        
        .property-details {
            flex: 1;
        }
        
        .property-details h4 {
            margin: 0 0 8px 0;
            font-size: 1.1rem;
            color: var(--text-primary);
        }
        
        .property-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .property-id {
            font-size: 0.85rem;
            color: var(--text-secondary);
            background-color: var(--secondary-bg);
            padding: 3px 8px;
            border-radius: 4px;
        }
        
        .view-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 5px 12px;
            border-radius: 6px;
            transition: all 0.2s ease;
            background-color: rgba(var(--primary-rgb), 0.1);
        }
        
        .view-link i {
            font-size: 0.8rem;
            transition: transform 0.2s ease;
        }
        
        .view-link:hover {
            background-color: rgba(var(--primary-rgb), 0.15);
            color: var(--primary-color-dark);
        }
        
        .view-link:hover i {
            transform: translateX(3px);
        }
        
        /* Dark mode specific styles */
        [data-theme="dark"] .property-item {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
        }
        
        [data-theme="dark"] .property-details h4 {
            color: #ffffff;
        }
        
        [data-theme="dark"] .property-id {
            color: #b0b0b0;
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        [data-theme="dark"] .view-link {
            color: var(--primary-color-light);
            background-color: rgba(var(--primary-rgb), 0.2);
        }
        
        [data-theme="dark"] .view-link:hover {
            background-color: rgba(var(--primary-rgb), 0.3);
            color: #ffffff;
        }
        
        [data-theme="dark"] .property-icon {
            background-color: rgba(var(--primary-rgb), 0.3);
            color: var(--primary-color-light);
        }
        
        /* Breadcrumb styling from edit-user.php */
        [data-theme="dark"] .breadcrumb {
            color: #b0b0b0;
        }
        
        [data-theme="dark"] .breadcrumb a {
            color: #ffffff;
        }
    </style>
</body>
</html> 