<?php
// Start session
session_start();
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/role_access.php';

// Check if user is logged in and is an admin
requireRole('admin');

// Get count statistics
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Count users by role
    $stmt = $db->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $stmt->execute();
    $users_by_role = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_users = 0;
    $total_admins = 0;
    $total_managers = 0;
    $total_residents = 0;
    
    foreach ($users_by_role as $role_data) {
        if ($role_data['role'] == 'admin') {
            $total_admins = $role_data['count'];
        } elseif ($role_data['role'] == 'manager') {
            $total_managers = $role_data['count'];
        } elseif ($role_data['role'] == 'resident') {
            $total_residents = $role_data['count'];
        }
        $total_users += $role_data['count'];
    }
    
    // Count properties
    $stmt = $db->prepare("SELECT type, COUNT(*) as count FROM properties GROUP BY type");
    $stmt->execute();
    $properties_by_type = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_properties = 0;
    $total_apartments = 0;
    $total_parking = 0;
    
    foreach ($properties_by_type as $type_data) {
        if ($type_data['type'] == 'apartment') {
            $total_apartments = $type_data['count'];
        } elseif ($type_data['type'] == 'parking') {
            $total_parking = $type_data['count'];
        }
        $total_properties += $type_data['count'];
    }
    
    // Count occupied properties
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM properties WHERE user_id IS NOT NULL");
    $stmt->execute();
    $occupied_properties = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count tickets by status
    $stmt = $db->prepare("SELECT status, COUNT(*) as count FROM tickets GROUP BY status");
    $stmt->execute();
    $tickets_by_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_tickets = 0;
    $open_tickets = 0;
    $in_progress_tickets = 0;
    $closed_tickets = 0;
    $reopened_tickets = 0;
    
    foreach ($tickets_by_status as $status_data) {
        if ($status_data['status'] == 'open') {
            $open_tickets = $status_data['count'];
        } elseif ($status_data['status'] == 'in_progress') {
            $in_progress_tickets = $status_data['count'];
        } elseif ($status_data['status'] == 'closed') {
            $closed_tickets = $status_data['count'];
        } elseif ($status_data['status'] == 'reopened') {
            $reopened_tickets = $status_data['count'];
        }
        $total_tickets += $status_data['count'];
    }
    
    // Get total payments 
    $stmt = $db->prepare("SELECT SUM(amount) as total FROM payments");
    $stmt->execute();
    $total_payments = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?: 0;
    
    // Get payments by status
    $stmt = $db->prepare("SELECT status, SUM(amount) as total FROM payments GROUP BY status");
    $stmt->execute();
    $payments_by_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $paid_amount = 0;
    $pending_amount = 0;
    
    foreach ($payments_by_status as $payment_data) {
        if ($payment_data['status'] == 'paid') {
            $paid_amount = $payment_data['total'];
        } elseif ($payment_data['status'] == 'pending') {
            $pending_amount = $payment_data['total'];
        }
    }
    
    // Get monthly payment data for chart (last 6 months)
    $stmt = $db->prepare("
        SELECT 
            DATE_FORMAT(month, '%Y-%m') as month,
            SUM(amount) as total_amount
        FROM 
            payments
        WHERE 
            month >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY 
            DATE_FORMAT(month, '%Y-%m')
        ORDER BY 
            month ASC
    ");
    $stmt->execute();
    $payment_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent activity (last 10 entries)
    $stmt = $db->prepare("
        SELECT a.*, u.name
        FROM activity_log a
        LEFT JOIN users u ON a.user_id = u.id
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent tickets (5 most recent)
    $stmt = $db->prepare("
        SELECT t.*, u.name
        FROM tickets t
        LEFT JOIN users u ON t.user_id = u.id
        ORDER BY t.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recent_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get maintenance data
    $stmt = $db->prepare("
        SELECT status, COUNT(*) as count 
        FROM maintenance 
        GROUP BY status
    ");
    $stmt->execute();
    $maintenance_by_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_maintenance = 0;
    $scheduled_maintenance = 0;
    $in_progress_maintenance = 0;
    $completed_maintenance = 0;
    
    foreach ($maintenance_by_status as $status_data) {
        if ($status_data['status'] == 'scheduled') {
            $scheduled_maintenance = $status_data['count'];
        } elseif ($status_data['status'] == 'in_progress') {
            $in_progress_maintenance = $status_data['count'];
        } elseif ($status_data['status'] == 'completed') {
            $completed_maintenance = $status_data['count'];
        }
        $total_maintenance += $status_data['count'];
    }
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    $total_users = $total_properties = $total_tickets = $total_payments = 0;
    $recent_activity = $recent_tickets = [];
    $payment_data = [];
}

// Page title
$page_title = "Tableau de Bord Admin";
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
    <link rel="stylesheet" href="css/dashboard-style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="admin-container">
        <?php include 'includes/admin-sidebar.php'; ?>

        <!-- Main Content -->
        <main class="main-content">
            <div class="dashboard-content">
                <div class="page-header">
                    <h1>Aperçu du Tableau de Bord</h1>
                    <div class="header-actions">
                        <button class="btn btn-primary">
                            <i class="fas fa-download"></i>
                            Générer un Rapport
                        </button>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon users">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Utilisateurs Totaux</h3>
                            <p class="stat-number"><?php echo number_format($total_users); ?></p>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-user-shield"></i> Administrateurs: <?php echo number_format($total_admins ?? 0); ?></span>
                                <span><i class="fas fa-user-tie"></i> Gestionnaires: <?php echo number_format($total_managers); ?></span>
                                <span><i class="fas fa-user"></i> Résidents: <?php echo number_format($total_residents); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon properties">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Propriétés</h3>
                            <p class="stat-number"><?php echo number_format($total_properties); ?></p>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-home"></i> Appartements: <?php echo number_format($total_apartments); ?></span>
                                <span><i class="fas fa-car"></i> Parking: <?php echo number_format($total_parking); ?></span>
                                <span><i class="fas fa-check-circle"></i> Occupées: <?php echo number_format($occupied_properties); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon tickets">
                            <i class="fas fa-ticket-alt"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Tickets</h3>
                            <p class="stat-number"><?php echo number_format($total_tickets); ?></p>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-exclamation-circle"></i> Ouverts: <?php echo number_format($open_tickets); ?></span>
                                <span><i class="fas fa-clock"></i> En Cours: <?php echo number_format($in_progress_tickets); ?></span>
                                <span><i class="fas fa-check"></i> Fermés: <?php echo number_format($closed_tickets); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon payments">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Paiements Totaux</h3>
                            <p class="stat-number"><?php echo format_currency($total_payments); ?></p>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-check-circle"></i> Payés: <?php echo format_currency($paid_amount); ?></span>
                                <span><i class="fas fa-clock"></i> En Attente: <?php echo format_currency($pending_amount); ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-line"></i> Aperçu des Paiements Mensuels</h3>
                            <div class="chart-actions">
                                <button class="btn-icon"><i class="fas fa-ellipsis-v"></i></button>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="monthlyPaymentsChart"></canvas>
                        </div>
                    </div>

                    <div class="chart-card">
                        <div class="chart-header">
                            <h3><i class="fas fa-chart-pie"></i> Distribution des Statuts de Tickets</h3>
                            <div class="chart-actions">
                                <button class="btn-icon"><i class="fas fa-ellipsis-v"></i></button>
                            </div>
                        </div>
                        <div class="chart-body">
                            <canvas id="ticketStatusChart"></canvas>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity and Tickets -->
                <div class="content-grid">
                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-history"></i> Activité Récente</h3>
                            <a href="activity-log.php" class="view-all">Voir Tout</a>
                        </div>
                        <div class="activity-list dashboard-activity">
                            <?php if (!empty($recent_activity)): ?>
                                <?php foreach ($recent_activity as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon <?php echo $activity['action']; ?>">
                                            <?php
                                            $icon_class = '';
                                            switch ($activity['action']) {
                                                case 'create':
                                                case 'add':
                                                    $icon_class = 'fas fa-plus';
                                                    break;
                                                case 'payment':
                                                    $icon_class = 'fas fa-credit-card';
                                                    break;
                                                case 'update':
                                                    $icon_class = 'fas fa-edit';
                                                    break;
                                                case 'delete':
                                                    $icon_class = 'fas fa-trash';
                                                    break;
                                                case 'login':
                                                    $icon_class = 'fas fa-sign-in-alt';
                                                    break;
                                                default:
                                                    $icon_class = 'fas fa-history';
                                            }
                                            ?>
                                            <i class="<?php echo $icon_class; ?>"></i>
                                        </div>
                                        <div class="activity-details">
                                            <p class="activity-text">
                                                <strong><?php echo htmlspecialchars($activity['name']); ?></strong> 
                                                <?php 
                                                    $action_verb = $activity['action'];
                                                    switch ($action_verb) {
                                                        case 'payment':
                                                            echo "a effectué un paiement pour";
                                                            break;
                                                        case 'login':
                                                            echo "s'est connecté à";
                                                            break;
                                                        case 'create':
                                                            echo "a créé";
                                                            break;
                                                        case 'update':
                                                            echo "a mis à jour";
                                                            break;
                                                        case 'delete':
                                                            echo "a supprimé";
                                                            break;
                                                        default:
                                                            // Add 'e' to actions that end with 'e' already
                                                            $last_char = substr($action_verb, -1);
                                                            if ($last_char === 'e') {
                                                                echo ucfirst($action_verb) . "d";
                                                            } else {
                                                                echo ucfirst($action_verb) . "ed";
                                                            }
                                                    }
                                                ?>
                                                <?php echo strtolower($activity['entity_type']); ?>
                                                <?php if (!empty($activity['entity_id'])): ?>
                                                    #<?php echo $activity['entity_id']; ?>
                                                <?php endif; ?>
                                                <?php if (!empty($activity['details'])): ?>
                                                    - <?php echo htmlspecialchars($activity['details']); ?>
                                                <?php endif; ?>
                                            </p>
                                            <span class="activity-time">
                                                <?php echo time_elapsed_string($activity['created_at']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-history"></i>
                                    <p>Aucune activité récente</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="content-card">
                        <div class="card-header">
                            <h3><i class="fas fa-ticket-alt"></i> Tickets Récents</h3>
                            <a href="tickets.php" class="view-all">Voir Tout</a>
                        </div>
                        <div class="tickets-list dashboard-tickets">
                            <?php if (!empty($recent_tickets)): ?>
                                <?php foreach ($recent_tickets as $ticket): ?>
                                    <div class="ticket-item">
                                        <div class="ticket-status <?php echo $ticket['status']; ?>">
                                            <?php 
                                                $status_text = $ticket['status'];
                                                switch ($status_text) {
                                                    case 'open':
                                                        echo 'Ouvert';
                                                        break;
                                                    case 'in_progress':
                                                        echo 'En Cours';
                                                        break;
                                                    case 'closed':
                                                        echo 'Fermé';
                                                        break;
                                                    case 'reopened':
                                                        echo 'Réouvert';
                                                        break;
                                                    default:
                                                        echo ucfirst($status_text);
                                                }
                                            ?>
                                        </div>
                                        <div class="ticket-details">
                                            <h4><?php echo htmlspecialchars($ticket['subject']); ?></h4>
                                            <p class="ticket-info">
                                                <span class="ticket-user">
                                                    <i class="fas fa-user"></i>
                                                    <?php echo htmlspecialchars($ticket['name']); ?>
                                                </span>
                                            </p>
                                            <span class="ticket-time">
                                                <?php echo time_elapsed_string($ticket['created_at']); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="empty-state">
                                    <i class="fas fa-ticket-alt"></i>
                                    <p>Aucun ticket récent</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Maintenance Section -->
                <div class="content-card maintenance-card">
                    <div class="card-header">
                        <h3><i class="fas fa-tools"></i> Aperçu des Maintenances</h3>
                        <a href="maintenance.php" class="view-all">Voir Tout</a>
                    </div>
                    <div class="maintenance-stats">
                        <div class="maintenance-stat">
                            <div class="stat-circle scheduled">
                                <i class="fas fa-calendar"></i>
                                <span class="count"><?php echo $scheduled_maintenance; ?></span>
                            </div>
                            <p>Programmées</p>
                        </div>
                        <div class="maintenance-stat">
                            <div class="stat-circle in-progress">
                                <i class="fas fa-tools"></i>
                                <span class="count"><?php echo $in_progress_maintenance; ?></span>
                            </div>
                            <p>En Cours</p>
                        </div>
                        <div class="maintenance-stat">
                            <div class="stat-circle completed">
                                <i class="fas fa-check"></i>
                                <span class="count"><?php echo $completed_maintenance; ?></span>
                            </div>
                            <p>Complétées</p>
                        </div>
                        <div class="maintenance-stat">
                            <div class="stat-circle total">
                                <i class="fas fa-clipboard-list"></i>
                                <span class="count"><?php echo $total_maintenance; ?></span>
                            </div>
                            <p>Total</p>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    // Set new default font family and font color to mimic Bootstrap's default styling
    Chart.defaults.font.family = 'Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif';
    Chart.defaults.color = '#858796';

    document.addEventListener('DOMContentLoaded', function() {
        // Get theme from localStorage or default to light
        const currentTheme = localStorage.getItem('darkMode') === 'enabled' ? 'dark' : 'light';
        
        // Monthly Payments Chart
        var monthlyPaymentsChart = document.getElementById("monthlyPaymentsChart");
        var paymentLabels = [];
        var paymentData = [];

        <?php foreach ($payment_data as $data): ?>
            paymentLabels.push("<?php echo date('M Y', strtotime($data['month'] . '-01')); ?>");
            paymentData.push(<?php echo $data['total_amount']; ?>);
        <?php endforeach; ?>

        // Add a default dataset if none exists
        if (paymentLabels.length === 0) {
            const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun"];
            const currentYear = new Date().getFullYear();
            
            months.forEach(month => {
                paymentLabels.push(`${month} ${currentYear}`);
                paymentData.push(0);
            });
        }

        if (monthlyPaymentsChart) {
            var myLineChart = new Chart(monthlyPaymentsChart, {
                type: 'line',
                data: {
                    labels: paymentLabels,
                    datasets: [{
                        label: "Montant du Paiement",
                        lineTension: 0.3,
                        backgroundColor: "rgba(78, 115, 223, 0.05)",
                        borderColor: "rgba(78, 115, 223, 1)",
                        pointRadius: 3,
                        pointBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointBorderColor: "rgba(78, 115, 223, 1)",
                        pointHoverRadius: 3,
                        pointHoverBackgroundColor: "rgba(78, 115, 223, 1)",
                        pointHoverBorderColor: "rgba(78, 115, 223, 1)",
                        pointHitRadius: 10,
                        pointBorderWidth: 2,
                        data: paymentData,
                        fill: true
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: currentTheme === 'dark' ? '#2d3748' : '#fff',
                            titleColor: currentTheme === 'dark' ? '#fff' : '#5a5c69',
                            bodyColor: currentTheme === 'dark' ? '#e0e0e0' : '#858796',
                            borderColor: currentTheme === 'dark' ? '#4a5568' : '#e3e6f0',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'Montant: ' + context.raw.toLocaleString(undefined, {
                                        style: 'currency',
                                        currency: 'EUR',
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            },
                            ticks: {
                                color: currentTheme === 'dark' ? '#e0e0e0' : '#858796'
                            }
                        },
                        y: {
                            ticks: {
                                color: currentTheme === 'dark' ? '#e0e0e0' : '#858796',
                                callback: function(value) {
                                    return value.toLocaleString(undefined, {
                                        style: 'currency',
                                        currency: 'EUR',
                                        minimumFractionDigits: 0,
                                        maximumFractionDigits: 0
                                    });
                                }
                            },
                            grid: {
                                color: currentTheme === 'dark' ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)'
                            }
                        }
                    }
                }
            });
        }

        // Ticket Status Chart
        var ticketStatusChart = document.getElementById("ticketStatusChart");
        var ticketStatuses = ["Ouvert", "En Cours", "Fermé"];
        var ticketData = [
            <?php echo $open_tickets; ?>,
            <?php echo $in_progress_tickets; ?>,
            <?php echo $closed_tickets; ?>
        ];
        var ticketColors = [
            'rgba(78, 115, 223, 1)', 
            'rgba(28, 200, 138, 1)', 
            'rgba(54, 185, 204, 1)'
        ];
        var ticketHoverColors = [
            'rgba(46, 89, 217, 1)', 
            'rgba(23, 166, 115, 1)', 
            'rgba(44, 159, 175, 1)'
        ];

        if (ticketStatusChart) {
            var myPieChart = new Chart(ticketStatusChart, {
                type: 'doughnut',
                data: {
                    labels: ticketStatuses,
                    datasets: [{
                        data: ticketData,
                        backgroundColor: ticketColors,
                        hoverBackgroundColor: ticketHoverColors,
                        hoverBorderColor: currentTheme === 'dark' ? '#2c3035' : '#ffffff',
                        borderWidth: 2,
                        borderColor: currentTheme === 'dark' ? '#2d3748' : '#ffffff',
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'bottom',
                            labels: {
                                color: currentTheme === 'dark' ? '#e0e0e0' : '#858796',
                                padding: 20,
                                font: {
                                    size: 12,
                                    weight: '500'
                                }
                            }
                        },
                        tooltip: {
                            backgroundColor: currentTheme === 'dark' ? '#2d3748' : '#fff',
                            titleColor: currentTheme === 'dark' ? '#fff' : '#5a5c69',
                            bodyColor: currentTheme === 'dark' ? '#e0e0e0' : '#858796',
                            borderColor: currentTheme === 'dark' ? '#4a5568' : '#e3e6f0',
                            borderWidth: 1,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    var label = context.label || '';
                                    var value = context.raw;
                                    var total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    var percentage = Math.round((value / total) * 100);
                                    return label + ': ' + value + ' (' + percentage + '%)';
                                }
                            }
                        }
                    },
                    cutout: '70%',
                    animation: {
                        animateScale: true,
                        animateRotate: true,
                        duration: 2000,
                        easing: 'easeOutCirc'
                    }
                },
            });
        }
    });
    </script>
    <script src="js/dark-mode.js"></script>
    <script src="js/dashboard.js"></script>
</body>
</html> 