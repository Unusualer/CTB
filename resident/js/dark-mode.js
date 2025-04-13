// Dark mode functionality
document.addEventListener('DOMContentLoaded', function () {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const themeToggleArea = document.querySelector('.theme-toggle');

    if (darkModeToggle) {
        // Check for saved dark mode preference
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.documentElement.setAttribute('data-theme', 'dark');
            darkModeToggle.checked = true;
        }

        // Function to toggle dark mode
        function toggleDarkMode() {
            if (darkModeToggle.checked) {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('darkMode', 'enabled');
            } else {
                document.documentElement.setAttribute('data-theme', 'light');
                localStorage.setItem('darkMode', 'disabled');
            }
        }

        // Listen for checkbox change
        darkModeToggle.addEventListener('change', toggleDarkMode);

        // Make the entire theme-toggle area clickable
        if (themeToggleArea) {
            themeToggleArea.addEventListener('click', function (e) {
                // Prevent the click from affecting the checkbox directly
                if (e.target !== darkModeToggle) {
                    e.preventDefault();
                    darkModeToggle.checked = !darkModeToggle.checked;
                    toggleDarkMode();
                }
            });
        }
    }
}); 