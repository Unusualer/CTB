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

// Check if maintenance ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = "L'ID de la maintenance est invalide.";
    header("Location: maintenance.php");
    exit();
}

$maintenance_id = intval($_GET['id']);
$error_message = '';
$maintenance = null;
$comments = [];

try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Fetch maintenance update details
    $query = "SELECT m.*, u.name as created_by_name 
              FROM maintenance m 
              LEFT JOIN users u ON m.created_by = u.id 
              WHERE m.id = :id";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id', $maintenance_id, PDO::PARAM_INT);
    $stmt->execute();
    
    $maintenance = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$maintenance) {
        $_SESSION['error'] = "Mise à jour de maintenance non trouvée.";
        header("Location: maintenance.php");
        exit();
    }
    
    // Fetch comments for this maintenance update (if we have a comments table)
    // This is a placeholder - you may need to adjust this based on your actual database schema
    $comment_query = "SELECT c.*, u.name as user_name 
                     FROM maintenance_comment c 
                     LEFT JOIN users u ON c.user_id = u.id 
                     WHERE c.maintenance_id = :maintenance_id 
                     ORDER BY c.created_at DESC";
    
    // Only try to fetch comments if the table exists
    try {
        $comment_stmt = $db->prepare($comment_query);
        $comment_stmt->bindParam(':maintenance_id', $maintenance_id, PDO::PARAM_INT);
        $comment_stmt->execute();
        $comments = $comment_stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Table might not exist yet, so we'll just leave comments empty
        $comments = [];
    }
    
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

// Helper function to format date
function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

// Helper function to get status badge class
function getStatusBadgeClass($status) {
    switch ($status) {
        case 'scheduled':
            return 'info';
        case 'in_progress':
            return 'primary';
        case 'completed':
            return 'success';
        case 'delayed':
            return 'warning';
        case 'cancelled':
            return 'danger';
        default:
            return 'secondary';
    }
}

// Helper function to get priority badge class
function getPriorityBadgeClass($priority) {
    switch ($priority) {
        case 'low':
            return 'success';
        case 'medium':
            return 'info';
        case 'high':
            return 'warning';
        case 'emergency':
            return 'danger';
        default:
            return 'secondary';
    }
}

$page_title = "Voir la Maintenance";
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
    <style>
        .maintenance-details {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        @media (min-width: 768px) {
            .maintenance-details {
                grid-template-columns: 2fr 1fr;
            }
        }
        
        .detail-group {
            margin-bottom: 1rem;
        }
        
        .detail-group .label {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--text-secondary);
        }
        
        .detail-group .value {
            font-size: 1rem;
        }
        
        .meta-info {
            color: var(--text-secondary);
            font-size: 0.875rem;
            border-top: 1px solid var(--border-color);
            padding-top: 1rem;
            margin-top: 1.5rem;
        }
        
        .comment-list {
            margin-top: 1.5rem;
        }
        
        .comment {
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: 0.5rem;
            margin-bottom: 1rem;
            background-color: var(--card-bg);
        }
        
        .comment-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
        }
        
        .comment-author {
            font-weight: 600;
        }
        
        .comment-time {
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        .comment-content {
            margin-top: 0.5rem;
            line-height: 1.5;
        }
        
        .description-box {
            background-color: var(--light-color);
            padding: 1.25rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
            margin: 1rem 0;
            white-space: pre-line;
            line-height: 1.6;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <a href="maintenance.php" class="back-button">
                    <i class="fas fa-arrow-left"></i> Retour aux Mises à Jour de Maintenance
                </a>
                <h1><?php echo $page_title; ?></h1>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($maintenance): ?>
                <div class="card">
                    <div class="card-header">
                        <h3><?php echo htmlspecialchars($maintenance['title']); ?></h3>
                        <div class="card-actions">
                            <a href="edit-maintenance.php?id=<?php echo $maintenance['id']; ?>" class="btn btn-secondary">
                                <i class="fas fa-edit"></i> Modifier
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="maintenance-details">
                            <div class="maintenance-main">
                                <div class="detail-group">
                                    <div class="label">Emplacement</div>
                                    <div class="value"><?php echo htmlspecialchars($maintenance['location']); ?></div>
                                </div>
                                
                                <div class="detail-group">
                                    <div class="label">Description</div>
                                    <div class="description-box">
                                        <?php echo nl2br(htmlspecialchars($maintenance['description'])); ?>
                                    </div>
                                </div>
                                
                                <div class="detail-group">
                                    <div class="label">Statut</div>
                                    <div class="value">
                                        <span class="status-indicator status-<?php echo getStatusBadgeClass($maintenance['status']); ?>">
                                            <?php 
                                                switch($maintenance['status']) {
                                                    case 'scheduled':
                                                        echo 'Planifié';
                                                        break;
                                                    case 'in_progress':
                                                        echo 'En Cours';
                                                        break;
                                                    case 'completed':
                                                        echo 'Terminé';
                                                        break;
                                                    case 'delayed':
                                                        echo 'Retardé';
                                                        break;
                                                    case 'cancelled':
                                                        echo 'Annulé';
                                                        break;
                                                    default:
                                                        echo ucfirst(str_replace('_', ' ', $maintenance['status']));
                                                }
                                            ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="meta-info">
                                    <p>Créé par <?php echo htmlspecialchars($maintenance['created_by_name']); ?> le <?php echo date('d F Y \à G:i', strtotime($maintenance['created_at'])); ?></p>
                                    <?php if ($maintenance['updated_at']): ?>
                                        <p>Dernière mise à jour le <?php echo date('d F Y \à G:i', strtotime($maintenance['updated_at'])); ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="maintenance-sidebar">
                                <div class="card sidebar-card">
                                    <div class="card-header">
                                        <h4>Détails de la Maintenance</h4>
                                    </div>
                                    <div class="card-body">
                                        <div class="detail-group">
                                            <div class="label">ID</div>
                                            <div class="value">#<?php echo $maintenance['id']; ?></div>
                                        </div>
                                        
                                        <div class="detail-group">
                                            <div class="label">Priorité</div>
                                            <div class="value">
                                                <span class="status-indicator status-<?php echo getPriorityBadgeClass($maintenance['priority']); ?>">
                                                    <?php 
                                                        switch($maintenance['priority']) {
                                                            case 'low':
                                                                echo 'Basse';
                                                                break;
                                                            case 'medium':
                                                                echo 'Moyenne';
                                                                break;
                                                            case 'high':
                                                                echo 'Haute';
                                                                break;
                                                            case 'emergency':
                                                                echo 'Urgence';
                                                                break;
                                                            default:
                                                                echo ucfirst($maintenance['priority']);
                                                        }
                                                    ?>
                                                </span>
                                            </div>
                                        </div>
                                        
                                        <div class="detail-group">
                                            <div class="label">Date de Début</div>
                                            <div class="value"><?php echo formatDate($maintenance['start_date']); ?></div>
                                        </div>
                                        
                                        <div class="detail-group">
                                            <div class="label">Date de Fin</div>
                                            <div class="value"><?php echo formatDate($maintenance['end_date']); ?></div>
                                        </div>
                                        
                                        <div class="detail-group">
                                            <div class="label">Durée</div>
                                            <div class="value">
                                                <?php 
                                                $start = new DateTime($maintenance['start_date']);
                                                $end = new DateTime($maintenance['end_date']);
                                                $interval = $start->diff($end);
                                                echo $interval->days + 1; ?> jours
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php if (!empty($comments)): ?>
                            <div class="comment-section">
                                <h4>Commentaires</h4>
                                <div class="comment-list">
                                    <?php foreach ($comments as $comment): ?>
                                        <div class="comment">
                                            <div class="comment-header">
                                                <div class="comment-author"><?php echo htmlspecialchars($comment['user_name']); ?></div>
                                                <div class="comment-time"><?php echo date('d M Y G:i', strtotime($comment['created_at'])); ?></div>
                                            </div>
                                            <div class="comment-content">
                                                <?php echo nl2br(htmlspecialchars($comment['comment'])); ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </main>
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
    </script>
</body>
</html>
