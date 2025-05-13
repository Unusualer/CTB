document.addEventListener('DOMContentLoaded', function () {
    // Initialize the dashboard
    initializeDashboard();
    setupSidebar();
    setupDarkMode();
    setupCharts();
    initializeDataTables();
    setupNotifications();
    setupEventListeners();
});

/**
 * Initialize dashboard components and settings
 */
function initializeDashboard() {
    // Load user preferences from localStorage
    loadUserPreferences();

    // Initialize tooltips
    if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(tooltip => {
            new bootstrap.Tooltip(tooltip);
        });
    }

    // Initialize popovers
    if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
        const popovers = document.querySelectorAll('[data-bs-toggle="popover"]');
        popovers.forEach(popover => {
            new bootstrap.Popover(popover);
        });
    }

    // Update dashboard stats
    updateDashboardStats();
}

/**
 * Load user preferences from localStorage
 */
function loadUserPreferences() {
    // Check for dark mode preference
    const darkMode = localStorage.getItem('darkMode') === 'true';
    if (darkMode) {
        document.body.classList.add('dark-mode');
        const darkModeSwitch = document.getElementById('darkModeSwitch');
        if (darkModeSwitch) {
            darkModeSwitch.checked = true;
        }
    }

    // Check for collapsed sidebar preference
    const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (sidebarCollapsed) {
        document.querySelector('.admin-wrapper').classList.add('sidebar-collapsed');
    }
}

/**
 * Setup sidebar functionality
 */
function setupSidebar() {
    // Toggle sidebar
    const sidebarToggle = document.getElementById('sidebarToggle');
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            const adminWrapper = document.querySelector('.admin-wrapper');
            adminWrapper.classList.toggle('sidebar-collapsed');
            localStorage.setItem('sidebarCollapsed', adminWrapper.classList.contains('sidebar-collapsed'));
        });
    }

    // Mobile sidebar toggle
    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
    if (mobileSidebarToggle) {
        mobileSidebarToggle.addEventListener('click', function () {
            const sidebar = document.querySelector('.admin-sidebar');
            sidebar.classList.toggle('open');

            const overlay = document.querySelector('.sidebar-overlay');
            overlay.classList.toggle('active');
        });
    }

    // Close sidebar when overlay is clicked
    const overlay = document.querySelector('.sidebar-overlay');
    if (overlay) {
        overlay.addEventListener('click', function () {
            const sidebar = document.querySelector('.admin-sidebar');
            sidebar.classList.remove('open');
            this.classList.remove('active');
        });
    }

    // Handle submenu toggles
    const submenuToggles = document.querySelectorAll('.menu-item.has-submenu');
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function (e) {
            if (e.target === this || e.target.parentNode === this) {
                e.preventDefault();
                this.classList.toggle('open');
            }
        });
    });
}

/**
 * Setup dark mode functionality
 */
function setupDarkMode() {
    const darkModeSwitch = document.getElementById('darkModeSwitch');
    if (darkModeSwitch) {
        darkModeSwitch.addEventListener('change', function () {
            document.body.classList.toggle('dark-mode');
            localStorage.setItem('darkMode', this.checked);

            // Update charts for dark mode
            if (typeof updateChartsForTheme === 'function') {
                updateChartsForTheme();
            }
        });
    }
}

/**
 * Setup charts for the dashboard
 */
function setupCharts() {
    // Setup Revenue Chart
    setupRevenueChart();

    // Setup User Growth Chart
    setupUserGrowthChart();

    // Setup Task Distribution Chart
    setupTaskDistributionChart();
}

/**
 * Setup revenue chart
 */
function setupRevenueChart() {
    const revenueChartEl = document.getElementById('revenueChart');
    if (!revenueChartEl || typeof Chart === 'undefined') return;

    const isDarkMode = document.body.classList.contains('dark-mode');
    const textColor = isDarkMode ? '#e0e0e0' : '#5c5c5c';
    const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

    const ctx = revenueChartEl.getContext('2d');
    window.revenueChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Revenue',
                data: [30500, 25200, 32800, 28400, 29900, 38600, 42100, 39400, 45200, 40800, 46900, 51200],
                borderColor: '#4361ee',
                backgroundColor: 'rgba(67, 97, 238, 0.1)',
                borderWidth: 2,
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: isDarkMode ? '#2c2c2c' : '#fff',
                    titleColor: isDarkMode ? '#fff' : '#333',
                    bodyColor: isDarkMode ? '#e0e0e0' : '#666',
                    borderColor: isDarkMode ? '#555' : '#e0e0e0',
                    borderWidth: 1,
                    padding: 10,
                    boxPadding: 5,
                    usePointStyle: true,
                    callbacks: {
                        label: function (context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                label += new Intl.NumberFormat('en-US', { style: 'currency', currency: 'MAD' }).format(context.parsed.y);
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: true,
                        color: gridColor
                    },
                    ticks: {
                        color: textColor
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        display: true,
                        color: gridColor
                    },
                    ticks: {
                        color: textColor,
                        callback: function (value) {
                            return value.toLocaleString() + ' MAD';
                        }
                    }
                }
            }
        }
    });
}

/**
 * Setup user growth chart
 */
function setupUserGrowthChart() {
    const userGrowthChartEl = document.getElementById('userGrowthChart');
    if (!userGrowthChartEl || typeof Chart === 'undefined') return;

    const isDarkMode = document.body.classList.contains('dark-mode');
    const textColor = isDarkMode ? '#e0e0e0' : '#5c5c5c';
    const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

    const ctx = userGrowthChartEl.getContext('2d');
    window.userGrowthChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'New Users',
                data: [65, 59, 80, 81, 56, 55],
                backgroundColor: '#3a0ca3',
                borderRadius: 4
            }, {
                label: 'Returning Users',
                data: [28, 48, 40, 19, 86, 27],
                backgroundColor: '#4cc9f0',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: textColor,
                        boxWidth: 12,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: isDarkMode ? '#2c2c2c' : '#fff',
                    titleColor: isDarkMode ? '#fff' : '#333',
                    bodyColor: isDarkMode ? '#e0e0e0' : '#666',
                    borderColor: isDarkMode ? '#555' : '#e0e0e0',
                    borderWidth: 1
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    },
                    ticks: {
                        color: textColor
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: gridColor
                    },
                    ticks: {
                        color: textColor
                    }
                }
            }
        }
    });
}

/**
 * Setup task distribution chart
 */
function setupTaskDistributionChart() {
    const taskChartEl = document.getElementById('taskDistributionChart');
    if (!taskChartEl || typeof Chart === 'undefined') return;

    const isDarkMode = document.body.classList.contains('dark-mode');
    const textColor = isDarkMode ? '#e0e0e0' : '#5c5c5c';

    const ctx = taskChartEl.getContext('2d');
    window.taskChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Completed', 'In Progress', 'Pending', 'Cancelled'],
            datasets: [{
                data: [45, 25, 20, 10],
                backgroundColor: ['#4cc9f0', '#4361ee', '#f72585', '#b5b5b5'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: textColor,
                        boxWidth: 12,
                        padding: 20
                    }
                },
                tooltip: {
                    backgroundColor: isDarkMode ? '#2c2c2c' : '#fff',
                    titleColor: isDarkMode ? '#fff' : '#333',
                    bodyColor: isDarkMode ? '#e0e0e0' : '#666',
                    borderColor: isDarkMode ? '#555' : '#e0e0e0',
                    borderWidth: 1
                }
            }
        }
    });
}

/**
 * Update charts based on current theme
 */
function updateChartsForTheme() {
    const isDarkMode = document.body.classList.contains('dark-mode');
    const textColor = isDarkMode ? '#e0e0e0' : '#5c5c5c';
    const gridColor = isDarkMode ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

    // Update Revenue Chart
    if (window.revenueChart) {
        window.revenueChart.options.plugins.tooltip.backgroundColor = isDarkMode ? '#2c2c2c' : '#fff';
        window.revenueChart.options.plugins.tooltip.titleColor = isDarkMode ? '#fff' : '#333';
        window.revenueChart.options.plugins.tooltip.bodyColor = isDarkMode ? '#e0e0e0' : '#666';
        window.revenueChart.options.plugins.tooltip.borderColor = isDarkMode ? '#555' : '#e0e0e0';
        window.revenueChart.options.scales.x.ticks.color = textColor;
        window.revenueChart.options.scales.y.ticks.color = textColor;
        window.revenueChart.options.scales.x.grid.color = gridColor;
        window.revenueChart.options.scales.y.grid.color = gridColor;
        window.revenueChart.update();
    }

    // Update User Growth Chart
    if (window.userGrowthChart) {
        window.userGrowthChart.options.plugins.legend.labels.color = textColor;
        window.userGrowthChart.options.plugins.tooltip.backgroundColor = isDarkMode ? '#2c2c2c' : '#fff';
        window.userGrowthChart.options.plugins.tooltip.titleColor = isDarkMode ? '#fff' : '#333';
        window.userGrowthChart.options.plugins.tooltip.bodyColor = isDarkMode ? '#e0e0e0' : '#666';
        window.userGrowthChart.options.plugins.tooltip.borderColor = isDarkMode ? '#555' : '#e0e0e0';
        window.userGrowthChart.options.scales.x.ticks.color = textColor;
        window.userGrowthChart.options.scales.y.ticks.color = textColor;
        window.userGrowthChart.options.scales.y.grid.color = gridColor;
        window.userGrowthChart.update();
    }

    // Update Task Distribution Chart
    if (window.taskChart) {
        window.taskChart.options.plugins.legend.labels.color = textColor;
        window.taskChart.options.plugins.tooltip.backgroundColor = isDarkMode ? '#2c2c2c' : '#fff';
        window.taskChart.options.plugins.tooltip.titleColor = isDarkMode ? '#fff' : '#333';
        window.taskChart.options.plugins.tooltip.bodyColor = isDarkMode ? '#e0e0e0' : '#666';
        window.taskChart.options.plugins.tooltip.borderColor = isDarkMode ? '#555' : '#e0e0e0';
        window.taskChart.update();
    }
}

/**
 * Initialize DataTables
 */
function initializeDataTables() {
    if (typeof $.fn.DataTable !== 'undefined') {
        $('.datatable').each(function () {
            $(this).DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [5, 10, 25, 50],
                language: {
                    search: "<i class='fas fa-search'></i>",
                    lengthMenu: "_MENU_ records per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries"
                },
                dom: '<"top"fl>rt<"bottom"ip><"clear">',
                initComplete: function () {
                    // Add custom styles to the DataTable
                    const isDarkMode = document.body.classList.contains('dark-mode');
                    if (isDarkMode) {
                        $(this).closest('.dataTables_wrapper').addClass('dark-mode');
                    }
                }
            });
        });
    }
}

/**
 * Setup notifications
 */
function setupNotifications() {
    const notificationDropdown = document.getElementById('notificationDropdown');
    if (notificationDropdown) {
        notificationDropdown.addEventListener('click', function (e) {
            e.preventDefault();
            // Mark notifications as read
            const notificationBadge = document.querySelector('.notification-badge');
            if (notificationBadge) {
                notificationBadge.style.display = 'none';
            }
        });
    }

    // Demo: Simulate new notification after 30 seconds
    setTimeout(function () {
        const notificationBadge = document.querySelector('.notification-badge');
        if (notificationBadge) {
            notificationBadge.style.display = 'flex';
            notificationBadge.textContent = '1';

            // Show toast notification
            showToast('New Notification', 'You have a new maintenance request.', 'info');
        }
    }, 30000);
}

/**
 * Show toast notification
 * @param {string} title - Toast title
 * @param {string} message - Toast message
 * @param {string} type - Toast type (success, error, warning, info)
 */
function showToast(title, message, type = 'info') {
    if (typeof bootstrap !== 'undefined' && bootstrap.Toast) {
        // Create toast container if it doesn't exist
        let toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container position-fixed bottom-0 end-0 p-3';
            document.body.appendChild(toastContainer);
        }

        // Create toast element
        const toastEl = document.createElement('div');
        toastEl.className = `toast align-items-center text-white bg-${type} border-0`;
        toastEl.setAttribute('role', 'alert');
        toastEl.setAttribute('aria-live', 'assertive');
        toastEl.setAttribute('aria-atomic', 'true');

        // Create toast content
        toastEl.innerHTML = `
            <div class="d-flex">
                <div class="toast-body">
                    <strong>${title}</strong><br>
                    ${message}
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        `;

        // Add toast to container
        toastContainer.appendChild(toastEl);

        // Initialize and show toast
        const toast = new bootstrap.Toast(toastEl, {
            autohide: true,
            delay: 5000
        });
        toast.show();

        // Remove toast after it's hidden
        toastEl.addEventListener('hidden.bs.toast', function () {
            toastEl.remove();
        });
    }
}

/**
 * Setup additional event listeners
 */
function setupEventListeners() {
    // Close alert buttons
    const alertCloseButtons = document.querySelectorAll('.alert .close');
    alertCloseButtons.forEach(button => {
        button.addEventListener('click', function () {
            const alert = this.closest('.alert');
            alert.classList.add('fade');
            setTimeout(() => {
                alert.style.display = 'none';
            }, 150);
        });
    });

    // Card collapse buttons
    const cardCollapseButtons = document.querySelectorAll('.card-header .collapse-card');
    cardCollapseButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const card = this.closest('.card');
            const cardBody = card.querySelector('.card-body');

            if (cardBody.style.display === 'none') {
                cardBody.style.display = 'block';
                this.querySelector('i').classList.remove('fa-plus');
                this.querySelector('i').classList.add('fa-minus');
            } else {
                cardBody.style.display = 'none';
                this.querySelector('i').classList.remove('fa-minus');
                this.querySelector('i').classList.add('fa-plus');
            }
        });
    });

    // Card refresh buttons
    const cardRefreshButtons = document.querySelectorAll('.card-header .refresh-card');
    cardRefreshButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const card = this.closest('.card');
            const cardBody = card.querySelector('.card-body');

            // Add loading spinner
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

            // Simulate refresh
            setTimeout(() => {
                this.innerHTML = '<i class="fas fa-sync-alt"></i>';
                showToast('Updated', 'Card data has been refreshed', 'success');
            }, 1000);
        });
    });
}

/**
 * Update dashboard statistics
 */
function updateDashboardStats() {
    // Get elements to update
    const totalUsersEl = document.getElementById('totalUsers');
    const totalPropertiesEl = document.getElementById('totalProperties');
    const totalTicketsEl = document.getElementById('totalTickets');
    const totalRevenueEl = document.getElementById('totalRevenue');

    // Simulate API call to get dashboard stats
    simulateApiCall('/api/dashboard/stats')
        .then(data => {
            // Update stats with animation
            if (totalUsersEl) animateCounter(totalUsersEl, data.totalUsers);
            if (totalPropertiesEl) animateCounter(totalPropertiesEl, data.totalProperties);
            if (totalTicketsEl) animateCounter(totalTicketsEl, data.totalTickets);
            if (totalRevenueEl) animateCounterCurrency(totalRevenueEl, data.totalRevenue);
        })
        .catch(error => {
            console.error('Error fetching dashboard stats:', error);
        });
}

/**
 * Simulate API call
 * @param {string} endpoint - API endpoint
 * @returns {Promise} Promise that resolves with mock data
 */
function simulateApiCall(endpoint) {
    return new Promise((resolve) => {
        setTimeout(() => {
            // Mock data
            const mockData = {
                '/api/dashboard/stats': {
                    totalUsers: 256,
                    totalProperties: 128,
                    totalTickets: 64,
                    totalRevenue: 128500,
                    activeUsers: 210,
                    newUsers: 16,
                    completedTickets: 48,
                    pendingTickets: 16
                }
            };

            resolve(mockData[endpoint] || {});
        }, 500);
    });
}

/**
 * Animate counter
 * @param {HTMLElement} element - Element to animate
 * @param {number} target - Target value
 * @param {number} duration - Animation duration in ms
 */
function animateCounter(element, target, duration = 1000) {
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;

    const animateCount = () => {
        current += increment;
        element.textContent = Math.floor(current);

        if (current < target) {
            requestAnimationFrame(animateCount);
        } else {
            element.textContent = target;
        }
    };

    animateCount();
}

/**
 * Animate counter with currency formatting
 * @param {HTMLElement} element - Element to animate
 * @param {number} target - Target value
 * @param {number} duration - Animation duration in ms
 */
function animateCounterCurrency(element, target, duration = 1000) {
    const start = 0;
    const increment = target / (duration / 16);
    let current = start;

    const formatter = new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'MAD',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0
    });

    const animateCount = () => {
        current += increment;
        element.textContent = formatter.format(Math.floor(current));

        if (current < target) {
            requestAnimationFrame(animateCount);
        } else {
            element.textContent = formatter.format(target);
        }
    };

    animateCount();
} 