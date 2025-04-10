// Dark mode functionality
document.addEventListener('DOMContentLoaded', function () {
    const darkModeToggle = document.getElementById('darkModeToggle');

    if (darkModeToggle) {
        // Check for saved dark mode preference
        if (localStorage.getItem('darkMode') === 'enabled') {
            document.documentElement.setAttribute('data-theme', 'dark');
            darkModeToggle.checked = true;
        }

        // Dark mode toggle event listener
        darkModeToggle.addEventListener('change', function () {
            if (this.checked) {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('darkMode', 'enabled');
            } else {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('darkMode', null);
            }
        });
    }
}); 