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

// Get count statistics
try {
    $db = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Count users by role
    $stmt = $db->prepare("SELECT role, COUNT(*) as count FROM users GROUP BY role");
    $stmt->execute();
    $users_by_role = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_users = 0;
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
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM properties");
    $stmt->execute();
    $total_properties = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
    
    // Count tickets by status
    $stmt = $db->prepare("SELECT status, COUNT(*) as count FROM tickets GROUP BY status");
    $stmt->execute();
    $tickets_by_status = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $total_tickets = 0;
    $open_tickets = 0;
    $in_progress_tickets = 0;
    $closed_tickets = 0;
    
    foreach ($tickets_by_status as $status_data) {
        if ($status_data['status'] == 'open') {
            $open_tickets = $status_data['count'];
        } elseif ($status_data['status'] == 'in_progress') {
            $in_progress_tickets = $status_data['count'];
        } elseif ($status_data['status'] == 'closed') {
            $closed_tickets = $status_data['count'];
        }
        $total_tickets += $status_data['count'];
    }
    
    // Get total payments 
    $stmt = $db->prepare("SELECT SUM(amount) as total FROM payments");
    $stmt->execute();
    $total_payments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    // Get monthly payment data for chart (last 6 months)
    $stmt = $db->prepare("
        SELECT 
            DATE_FORMAT(payment_date, '%Y-%m') as month,
            SUM(amount) as total_amount
        FROM 
            payments
        WHERE 
            payment_date >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
        GROUP BY 
            DATE_FORMAT(payment_date, '%Y-%m')
        ORDER BY 
            month ASC
    ");
    $stmt->execute();
    $payment_data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent activity (last 10 entries)
    $stmt = $db->prepare("
        SELECT a.*, u.name, u.email
        FROM activity_log a
        LEFT JOIN users u ON a.user_id = u.id
        ORDER BY a.created_at DESC
        LIMIT 10
    ");
    $stmt->execute();
    $recent_activity = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent tickets (5 most recent)
    $stmt = $db->prepare("
        SELECT t.*, u.name, p.identifier as property_name
        FROM tickets t
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN properties p ON t.property_id = p.id
        ORDER BY t.created_at DESC
        LIMIT 5
    ");
    $stmt->execute();
    $recent_tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $_SESSION['error'] = "Database error: " . $e->getMessage();
    $total_users = $total_properties = $total_tickets = $total_payments = 0;
    $recent_activity = $recent_tickets = [];
    $payment_data = [];
}

// Page title
$page_title = "Admin Dashboard";
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <li class="active">
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
                    <li>
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
            <div class="dashboard-content">
                <div class="page-header">
                    <h1>Dashboard Overview</h1>
                    <div class="header-actions">
                        <button class="btn btn-primary">
                            <i class="fas fa-download"></i>
                            Generate Report
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
                            <h3>Total Users</h3>
                            <p class="stat-number"><?php echo number_format($total_users); ?></p>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-user-shield"></i> Admins: <?php echo number_format($total_admins ?? 0); ?></span>
                                <span><i class="fas fa-user-tie"></i> Managers: <?php echo number_format($total_managers); ?></span>
                                <span><i class="fas fa-user"></i> Residents: <?php echo number_format($total_residents); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon properties">
                            <i class="fas fa-building"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Properties</h3>
                            <p class="stat-number"><?php echo number_format($total_properties); ?></p>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-home"></i> Total Units</span>
                                <span><i class="fas fa-check-circle"></i> Active Properties</span>
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
                                <span><i class="fas fa-exclamation-circle"></i> Open: <?php echo number_format($open_tickets); ?></span>
                                <span><i class="fas fa-clock"></i> In Progress: <?php echo number_format($in_progress_tickets); ?></span>
                                <span><i class="fas fa-check"></i> Closed: <?php echo number_format($closed_tickets); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon payments">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-details">
                            <h3>Total Payments</h3>
                            <p class="stat-number">$<?php echo number_format($total_payments, 2); ?></p>
                            <div class="stat-breakdown">
                                <span><i class="fas fa-chart-line"></i> Last 30 Days</span>
                                <span><i class="fas fa-calendar"></i> Monthly Average</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="charts-grid">
                    <div class="chart-card">
                        <div class="chart-header">
                            <h3>Monthly Payments Overview</h3>
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
                            <h3>Ticket Status Distribution</h3>
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
                            <h3>Recent Activity</h3>
                            <a href="activity-log.php" class="view-all">View All</a>
                        </div>
                        <div class="activity-list">
                            <?php if (!empty($recent_activity)): ?>
                                <?php foreach ($recent_activity as $activity): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon <?php echo $activity['action']; ?>">
                                            <?php
                                            $icon_class = '';
                                            switch ($activity['action']) {
                                                case 'create':
                                                    $icon_class = 'fas fa-plus';
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
                                                <?php echo ucfirst($activity['action']); ?>d
                                                <?php echo ucfirst($activity['entity_type']); ?>
                                                <?php if (!empty($activity['entity_id'])): ?>
                                                    #<?php echo $activity['entity_id']; ?>
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
                                    <p>No recent activity</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="content-card">
                        <div class="card-header">
                            <h3>Recent Tickets</h3>
                            <a href="tickets.php" class="view-all">View All</a>
                        </div>
                        <div class="tickets-list">
                            <?php if (!empty($recent_tickets)): ?>
                                <?php foreach ($recent_tickets as $ticket): ?>
                                    <div class="ticket-item">
                                        <div class="ticket-status <?php echo $ticket['status']; ?>">
                                            <?php echo ucfirst($ticket['status']); ?>
                                        </div>
                                        <div class="ticket-details">
                                            <h4><?php echo htmlspecialchars($ticket['title']); ?></h4>
                                            <p>
                                                <span class="ticket-property">
                                                    <i class="fas fa-building"></i>
                                                    <?php echo htmlspecialchars($ticket['property_name']); ?>
                                                </span>
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
                                    <p>No recent tickets</p>
                                </div>
                            <?php endif; ?>
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

    // Monthly Payments Chart
    var monthlyPaymentsChart = document.getElementById("monthlyPaymentsChart");
    var paymentLabels = [];
    var paymentData = [];

    <?php foreach ($payment_data as $data): ?>
        paymentLabels.push("<?php echo date('M Y', strtotime($data['month'] . '-01')); ?>");
        paymentData.push(<?php echo $data['total_amount']; ?>);
    <?php endforeach; ?>

    if (monthlyPaymentsChart) {
        var myLineChart = new Chart(monthlyPaymentsChart, {
            type: 'line',
            data: {
                labels: paymentLabels,
                datasets: [{
                    label: "Payment Amount",
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
                        callbacks: {
                            label: function(context) {
                                return 'Payment Amount: $' + context.raw.toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    },
                    y: {
                        ticks: {
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }

    // Ticket Status Chart
    var ticketStatusChart = document.getElementById("ticketStatusChart");
    var ticketStatuses = ["Open", "In Progress", "Closed"];
    var ticketData = [
        <?php echo $open_tickets; ?>,
        <?php echo $in_progress_tickets; ?>,
        <?php echo $closed_tickets; ?>
    ];

    if (ticketStatusChart) {
        var myPieChart = new Chart(ticketStatusChart, {
            type: 'doughnut',
            data: {
                labels: ticketStatuses,
                datasets: [{
                    data: ticketData,
                    backgroundColor: ['#4e73df', '#1cc88a', '#36b9cc'],
                    hoverBackgroundColor: ['#2e59d9', '#17a673', '#2c9faf'],
                    hoverBorderColor: "rgba(234, 236, 244, 1)",
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
                cutout: '60%',
            },
        });
    }

    // Dark mode toggle
    const darkModeToggle = document.getElementById('darkModeToggle');
    const body = document.body;

    // Check for saved theme preference
    if (localStorage.getItem('darkMode') === 'enabled') {
        body.classList.add('dark-mode');
        darkModeToggle.checked = true;
    }

    darkModeToggle.addEventListener('change', () => {
        if (darkModeToggle.checked) {
            body.classList.add('dark-mode');
            localStorage.setItem('darkMode', 'enabled');
        } else {
            body.classList.remove('dark-mode');
            localStorage.setItem('darkMode', null);
        }
    });
    </script>
</body>
</html> 