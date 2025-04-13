/**
 * Sidebar JavaScript functionality
 * Handles sidebar toggle, responsive behavior, and dark mode
 */
document.addEventListener('DOMContentLoaded', function () {
    // Elements
    const sidebar = document.getElementById('adminSidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const darkModeToggle = document.getElementById('darkModeToggle');

    // Toggle sidebar collapse state
    function toggleSidebar() {
        sidebar.classList.toggle('collapsed');

        // Save state to cookie (1 year expiration)
        const isCollapsed = sidebar.classList.contains('collapsed');
        document.cookie = `sidebar_collapsed=${isCollapsed}; max-age=${60 * 60 * 24 * 365}; path=/`;
    }

    // Toggle mobile sidebar
    function toggleMobileSidebar() {
        sidebar.classList.toggle('mobile-open');
        sidebarOverlay.classList.toggle('active');
        document.body.classList.toggle('sidebar-mobile-open');
    }

    // Toggle dark mode
    function toggleDarkMode() {
        const isDarkMode = darkModeToggle.checked;
        document.documentElement.setAttribute('data-theme', isDarkMode ? 'dark' : 'light');

        // Save state to cookie (1 year expiration)
        document.cookie = `dark_mode=${isDarkMode}; max-age=${60 * 60 * 24 * 365}; path=/`;
    }

    // Initialize dark mode from cookie
    function initDarkMode() {
        const isDarkMode = darkModeToggle.checked;
        document.documentElement.setAttribute('data-theme', isDarkMode ? 'dark' : 'light');
    }

    // Close sidebar when clicking outside on mobile
    function handleOutsideClick(e) {
        if (sidebar.classList.contains('mobile-open') && !sidebar.contains(e.target) && !mobileSidebarToggle.contains(e.target)) {
            toggleMobileSidebar();
        }
    }

    // Add event listeners
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', toggleSidebar);
    }

    if (mobileSidebarToggle) {
        mobileSidebarToggle.addEventListener('click', toggleMobileSidebar);
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', toggleMobileSidebar);
    }

    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', toggleDarkMode);
        initDarkMode(); // Initialize on page load
    }

    // Handle window resize
    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(function () {
            if (window.innerWidth > 992 && sidebar.classList.contains('mobile-open')) {
                sidebar.classList.remove('mobile-open');
                sidebarOverlay.classList.remove('active');
                document.body.classList.remove('sidebar-mobile-open');
            }
        }, 250);
    });

    // Close sidebar when clicking outside
    document.addEventListener('click', handleOutsideClick);

    // Submenu toggle functionality
    const submenuToggleButtons = document.querySelectorAll('.sidebar-submenu-toggle');
    submenuToggleButtons.forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            const submenu = this.nextElementSibling;

            // Toggle submenu
            submenu.classList.toggle('open');
            this.classList.toggle('active');

            // Animate height
            if (submenu.classList.contains('open')) {
                submenu.style.maxHeight = submenu.scrollHeight + 'px';
            } else {
                submenu.style.maxHeight = '0';
            }
        });
    });
}); 