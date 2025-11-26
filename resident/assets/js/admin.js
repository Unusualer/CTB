/**
 * CTB Property Management Admin Dashboard
 * Core JavaScript functionality for the admin interface
 */

document.addEventListener('DOMContentLoaded', function () {
    // Initialize all components
    initializeAdmin();
});

/**
 * Initialize all admin dashboard components
 */
function initializeAdmin() {
    // Initialize tooltips
    initTooltips();

    // Initialize sidebar functionality
    initSidebar();

    // Setup dark mode toggle
    setupDarkMode();

    // Initialize scroll to top button
    initScrollToTop();

    // Add counter animations to dashboard stats
    animateCounters();
}

/**
 * Initialize Bootstrap tooltips
 */
function initTooltips() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize sidebar toggle functionality
 */
function initSidebar() {
    const sidebarToggle = document.querySelector('#sidebarToggle');
    const mobileSidebarToggle = document.querySelector('#mobileSidebarToggle');
    const sidebarOverlay = document.querySelector('#sidebarOverlay');
    const adminSidebar = document.querySelector('.admin-sidebar');

    // Toggle sidebar on desktop
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function (e) {
            e.preventDefault();
            document.body.classList.toggle('sidebar-collapsed');

            // Save state to localStorage
            const isCollapsed = document.body.classList.contains('sidebar-collapsed');
            localStorage.setItem('sidebar_collapsed', isCollapsed);
        });
    }

    // Toggle sidebar on mobile
    if (mobileSidebarToggle) {
        mobileSidebarToggle.addEventListener('click', function (e) {
            e.preventDefault();
            document.body.classList.toggle('sidebar-mobile-open');

            if (adminSidebar) {
                adminSidebar.classList.toggle('active');
            }

            if (sidebarOverlay) {
                sidebarOverlay.classList.toggle('active');
            }
        });
    }

    // Close sidebar when clicking overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function () {
            document.body.classList.remove('sidebar-mobile-open');

            if (adminSidebar) {
                adminSidebar.classList.remove('active');
            }

            sidebarOverlay.classList.remove('active');
        });
    }

    // Load saved sidebar state
    const savedSidebarState = localStorage.getItem('sidebar_collapsed');
    if (savedSidebarState === 'true') {
        document.body.classList.add('sidebar-collapsed');
    }
}

/**
 * Setup dark mode toggle functionality
 */
function setupDarkMode() {
    const darkModeToggle = document.querySelector('#darkModeToggle');

    if (darkModeToggle) {
        // Set initial state based on saved preference or system preference
        const savedDarkMode = localStorage.getItem('dark_mode');
        const prefersDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;

        if (savedDarkMode === 'true' || (savedDarkMode === null && prefersDarkMode)) {
            document.documentElement.setAttribute('data-theme', 'dark');
            darkModeToggle.checked = true;
        }

        // Toggle dark mode on change
        darkModeToggle.addEventListener('change', function () {
            if (this.checked) {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('dark_mode', 'true');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('dark_mode', 'false');
            }

            // Update chart colors if charts exist
            if (typeof updateChartsForTheme === 'function') {
                updateChartsForTheme();
            }
        });
    }
}

/**
 * Initialize scroll to top button
 */
function initScrollToTop() {
    const scrollToTopButton = document.querySelector('.scroll-to-top');

    if (scrollToTopButton) {
        // Show/hide button based on scroll position
        window.addEventListener('scroll', function () {
            if (window.pageYOffset > 100) {
                scrollToTopButton.classList.add('active');
            } else {
                scrollToTopButton.classList.remove('active');
            }
        });

        // Scroll to top when clicked
        scrollToTopButton.addEventListener('click', function (e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
    }
}

/**
 * Animate counter elements in dashboard
 */
function animateCounters() {
    const counterElements = document.querySelectorAll('.counter-animate');

    counterElements.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-target'));
        animateValue(counter, 0, target, 1000);
    });
}

/**
 * Animate a number value from start to end
 */
function animateValue(element, start, end, duration) {
    if (!element) return;

    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const currentValue = Math.floor(progress * (end - start) + start);

        // Format with commas if it's a regular number counter
        if (element.classList.contains('currency')) {
            element.textContent = '$' + currentValue.toLocaleString();
        } else {
            element.textContent = currentValue.toLocaleString();
        }

        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };

    window.requestAnimationFrame(step);
} 