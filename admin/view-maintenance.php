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
        
        /* Breadcrumb styles */
        .breadcrumb {
            display: flex;
            align-items: center;
            font-size: 0.875rem;
            color: var(--text-secondary);
        }
        
        .breadcrumb a {
            color: var(--text-primary);
            text-decoration: none;
            margin-right: 0.5rem;
            position: relative;
            padding-right: 1rem;
        }
        
        .breadcrumb a:after {
            content: '/';
            position: absolute;
            right: 0;
            color: var(--text-secondary);
        }
        
        .breadcrumb a:hover {
            text-decoration: underline;
        }
        
        .breadcrumb span {
            color: var(--text-primary);
        }
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        /* Profile header design */
        .profile-header {
            margin-bottom: 1.5rem;
        }
        
        .profile-header-content {
            display: flex;
            align-items: center;
            padding: 1.5rem;
        }
        
        .profile-avatar {
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, #4a80f0, #2c57b5);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1.5rem;
            color: #ffffff;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        
        .profile-avatar i {
            font-size: 36px;
        }
        
        .profile-details {
            flex: 1;
        }
        
        .profile-name-wrapper {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        
        .profile-name-wrapper h2 {
            margin: 0;
            margin-right: 1rem;
            font-size: 1.5rem;
            color: var(--text-primary);
        }
        
        .user-id-badge {
            background-color: var(--secondary-bg);
            color: var(--text-secondary);
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .profile-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            color: var(--text-secondary);
            font-size: 0.875rem;
        }
        
        .user-role, .user-status, .user-joined {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        
        .card-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        @media (min-width: 992px) {
            .card-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        @media (min-width: 576px) {
            .info-grid {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        .info-group {
            display: flex;
            flex-direction: column;
        }
        
        .info-group label {
            font-size: 0.875rem;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .info-value {
            font-weight: 500;
            color: var(--text-primary);
        }
        
        /* Status badge styling */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.875rem;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        
        .status-badge.success {
            background-color: rgba(25, 135, 84, 0.15);
            color: #198754;
            border: 1px solid rgba(25, 135, 84, 0.3);
        }
        
        .status-badge.primary {
            background-color: rgba(var(--primary-rgb), 0.15);
            color: var(--primary-color);
            border: 1px solid rgba(var(--primary-rgb), 0.3);
        }
        
        .status-badge.warning {
            background-color: rgba(255, 193, 7, 0.15);
            color: #ffc107;
            border: 1px solid rgba(255, 193, 7, 0.3);
        }
        
        .status-badge.danger {
            background-color: rgba(220, 53, 69, 0.15);
            color: #dc3545;
            border: 1px solid rgba(220, 53, 69, 0.3);
        }
        
        .status-badge.info {
            background-color: rgba(13, 202, 240, 0.15);
            color: #0dcaf0;
            border: 1px solid rgba(13, 202, 240, 0.3);
        }
        
        .status-badge.secondary {
            background-color: rgba(108, 117, 125, 0.15);
            color: #6c757d;
            border: 1px solid rgba(108, 117, 125, 0.3);
        }
        
        /* Dark mode styling */
        [data-theme="dark"] .breadcrumb {
            color: #b0b0b0;
        }
        
        [data-theme="dark"] .breadcrumb a {
            color: #ffffff;
        }
        
        [data-theme="dark"] .description-box {
            background-color: #2a2e35;
            border-color: #3f4756;
        }
        
        [data-theme="dark"] .status-badge.success {
            background-color: rgba(25, 135, 84, 0.25);
            color: #25c274;
            border-color: rgba(25, 135, 84, 0.5);
        }
        
        [data-theme="dark"] .status-badge.primary {
            background-color: rgba(var(--primary-rgb), 0.25);
            color: var(--primary-color-light);
            border-color: rgba(var(--primary-rgb), 0.5);
        }
        
        [data-theme="dark"] .status-badge.warning {
            background-color: rgba(255, 193, 7, 0.25);
            color: #ffda6a;
            border-color: rgba(255, 193, 7, 0.5);
        }
        
        [data-theme="dark"] .status-badge.danger {
            background-color: rgba(220, 53, 69, 0.25);
            color: #ff8085;
            border-color: rgba(220, 53, 69, 0.5);
        }
        
        [data-theme="dark"] .status-badge.info {
            background-color: rgba(13, 202, 240, 0.25);
            color: #6edbf7;
            border-color: rgba(13, 202, 240, 0.5);
        }
        
        [data-theme="dark"] .status-badge.secondary {
            background-color: rgba(108, 117, 125, 0.25);
            color: #a1a8ae;
            border-color: rgba(108, 117, 125, 0.5);
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="page-header">
                <div class="breadcrumb">
                    <a href="maintenance.php">Maintenance</a>
                    <span>Voir la Maintenance</span>
                </div>
                <div class="actions">
                    <a href="edit-maintenance.php?id=<?php echo $maintenance['id']; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Modifier la Maintenance
                    </a>
                </div>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
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

            <?php if ($maintenance): ?>
                <div class="content-wrapper">
                    <div class="profile-header card">
                        <div class="profile-header-content">
                            <div class="profile-avatar">
                                <i class="fas fa-tools"></i>
                            </div>
                            <div class="profile-details">
                                <div class="profile-name-wrapper">
                                    <h2><?php echo htmlspecialchars($maintenance['title']); ?></h2>
                                    <div class="user-id-badge">ID: <?php echo $maintenance['id']; ?></div>
                                </div>
                                <div class="profile-meta">
                                    <span class="user-status">
                                        <i class="fas fa-circle"></i> 
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
                                    <span class="status-badge <?php echo getPriorityBadgeClass($maintenance['priority']); ?>">
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
                                    <span class="user-joined"><i class="far fa-calendar-alt"></i> Créé le <?php echo date('d M Y', strtotime($maintenance['created_at'])); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-grid">
                        <!-- Maintenance Information Card -->
                        <div class="card user-info-card">
                            <div class="card-header">
                                <h3><i class="fas fa-info-circle"></i> Informations de la Maintenance</h3>
                            </div>
                            <div class="card-body">
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
                                
                                <div class="info-grid">
                                    <div class="info-group">
                                        <label><i class="fas fa-user"></i> Créé par:</label>
                                        <span class="info-value"><?php echo htmlspecialchars($maintenance['created_by_name']); ?></span>
                                    </div>
                                    <div class="info-group">
                                        <label><i class="fas fa-check-circle"></i> Statut:</label>
                                        <span class="status-badge <?php echo getStatusBadgeClass($maintenance['status']); ?>">
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
                                    <div class="info-group">
                                        <label><i class="fas fa-exclamation-triangle"></i> Priorité:</label>
                                        <span class="status-badge <?php echo getPriorityBadgeClass($maintenance['priority']); ?>">
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
                                    <div class="info-group">
                                        <label><i class="fas fa-calendar-plus"></i> Créé le:</label>
                                        <span class="info-value"><?php echo date('d F Y', strtotime($maintenance['created_at'])); ?></span>
                                    </div>
                                    <?php if (isset($maintenance['updated_at']) && !empty($maintenance['updated_at'])): ?>
                                    <div class="info-group">
                                        <label><i class="fas fa-edit"></i> Dernière mise à jour:</label>
                                        <span class="info-value"><?php echo date('d F Y', strtotime($maintenance['updated_at'])); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Maintenance Details Card -->
                        <div class="card">
                            <div class="card-header">
                                <h3><i class="fas fa-calendar-alt"></i> Calendrier de Maintenance</h3>
                            </div>
                            <div class="card-body">
                                <div class="info-grid">
                                    <div class="info-group">
                                        <label><i class="fas fa-play"></i> Date de Début:</label>
                                        <span class="info-value"><?php echo formatDate($maintenance['start_date']); ?></span>
                                    </div>
                                    
                                    <div class="info-group">
                                        <label><i class="fas fa-flag-checkered"></i> Date de Fin:</label>
                                        <span class="info-value"><?php echo formatDate($maintenance['end_date']); ?></span>
                                    </div>
                                    
                                    <div class="info-group">
                                        <label><i class="fas fa-hourglass-half"></i> Durée:</label>
                                        <span class="info-value">
                                            <?php 
                                            $start = new DateTime($maintenance['start_date']);
                                            $end = new DateTime($maintenance['end_date']);
                                            $interval = $start->diff($end);
                                            echo $interval->days + 1; ?> jours
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <?php if (!empty($comments)): ?>
                    <div class="card mt-4">
                        <div class="card-header">
                            <h3><i class="fas fa-comments"></i> Commentaires</h3>
                        </div>
                        <div class="card-body">
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
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="js/dark-mode.js"></script>
</body>
</html>
