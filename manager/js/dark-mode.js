// Dark mode functionality
document.addEventListener('DOMContentLoaded', function () {
    const darkModeToggle = document.getElementById('darkModeToggle');
    const themeToggleArea = document.querySelector('.theme-toggle');

    // Helper function to set a cookie
    function setCookie(name, value, days) {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        document.cookie = name + "=" + value + expires + "; path=/";
    }

    // Helper function to get a cookie value
    function getCookie(name) {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for (let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    }

    // First, apply the current theme based on localStorage or system preference
    const prefersDarkScheme = window.matchMedia("(prefers-color-scheme: dark)");
    const storedTheme = localStorage.getItem("darkMode") || getCookie("darkMode");

    if (storedTheme === "enabled" || (!storedTheme && prefersDarkScheme.matches)) {
        document.body.classList.add("dark-mode");
        document.documentElement.setAttribute('data-theme', 'dark');
        if (darkModeToggle) {
            darkModeToggle.checked = true;
        }
    } else {
        document.body.classList.remove("dark-mode");
        document.documentElement.setAttribute('data-theme', 'light');
        if (darkModeToggle) {
            darkModeToggle.checked = false;
        }
    }

    // Function to toggle dark mode
    function toggleDarkMode() {
        if (darkModeToggle && darkModeToggle.checked) {
            document.body.classList.add("dark-mode");
            document.documentElement.setAttribute('data-theme', 'dark');
            localStorage.setItem("darkMode", "enabled");
            setCookie("darkMode", "enabled", 365);
        } else {
            document.body.classList.remove("dark-mode");
            document.documentElement.setAttribute('data-theme', 'light');
            localStorage.setItem("darkMode", "disabled");
            setCookie("darkMode", "disabled", 365);
        }
    }

    // Listen for checkbox change
    if (darkModeToggle) {
        darkModeToggle.addEventListener('change', toggleDarkMode);
    }

    // Make the entire theme-toggle area clickable
    if (themeToggleArea) {
        themeToggleArea.addEventListener('click', function (e) {
            // Prevent the click from affecting the checkbox directly
            if (e.target !== darkModeToggle) {
                e.preventDefault();
                // Toggle the checkbox
                if (darkModeToggle) {
                    darkModeToggle.checked = !darkModeToggle.checked;
                    // Manually trigger the change event
                    toggleDarkMode();
                }
            }
        });
    }
}); 