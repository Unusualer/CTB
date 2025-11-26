<!-- Sidebar Footer -->
<div class="sidebar-footer">
    <div class="theme-toggle" id="theme-toggle">
        <i class="fas fa-moon"></i>
        <span class="mode-text"><?php echo __("Dark / Light"); ?></span>
        <label class="switch">
            <input type="checkbox" id="darkModeToggle">
            <span class="slider round"></span>
        </label>
    </div>
    <a href="../logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i>
        <span><?php echo __("Logout"); ?></span>
    </a>
</div>

<style>
.language-switcher {
    display: flex;
    align-items: center;
    position: relative;
    padding: 10px 15px;
    cursor: pointer;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

.language-switcher .fas {
    margin-right: 10px;
    font-size: 16px;
}

.language-dropdown {
    position: absolute;
    bottom: 100%;
    left: 0;
    width: 100%;
    background-color: var(--sidebar-bg);
    border-top: 1px solid rgba(255, 255, 255, 0.1);
    display: none;
    flex-direction: column;
    z-index: 10;
}

.language-switcher:hover .language-dropdown {
    display: flex;
}

.lang-option {
    padding: 10px 15px;
    text-decoration: none;
    color: var(--text-color);
    display: flex;
    align-items: center;
    gap: 8px;
}

.lang-option:hover, .lang-option.active {
    background-color: rgba(255, 255, 255, 0.1);
}

.language-code {
    font-weight: bold;
    display: inline-block;
    width: 24px;
    text-align: center;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fix relative paths if needed
    const langLinks = document.querySelectorAll('.lang-option');
    langLinks.forEach(link => {
        // Ensure the URL is correct when in subdirectories
        const href = link.getAttribute('href');
        if (href.startsWith('../admin/') && window.location.pathname.includes('/admin/')) {
            link.setAttribute('href', href.replace('../admin/', './'));
        }
    });
});
</script> 