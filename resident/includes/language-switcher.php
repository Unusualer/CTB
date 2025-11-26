<?php
require_once '../../includes/language.php';

// Handle language change request
if (isset($_GET['lang']) && !empty($_GET['lang'])) {
    $lang = $_GET['lang'];
    set_language($lang);
    
    // Redirect to the same page without the lang parameter
    $redirect_url = strtok($_SERVER['REQUEST_URI'], '?');
    $query_params = $_GET;
    unset($query_params['lang']);
    
    if (!empty($query_params)) {
        $redirect_url .= '?' . http_build_query($query_params);
    }
    
    header("Location: $redirect_url");
    exit;
}

// Get current language
$current_language = get_current_language();
$current_language_name = get_language_name($current_language);
$available_languages = get_available_languages();
?>

<div class="language-switcher">
    <div class="dropdown">
        <button class="btn btn-sm dropdown-toggle" type="button" id="languageDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-globe"></i> <?php echo $current_language_name; ?>
        </button>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="languageDropdown">
            <?php foreach ($available_languages as $lang_code => $lang_name): ?>
                <?php if ($lang_code !== $current_language): ?>
                    <a class="dropdown-item" href="?lang=<?php echo $lang_code; ?>">
                        <?php echo $lang_name; ?>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Make sure jQuery and Bootstrap JS are loaded for the dropdown to work -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Check if jQuery is loaded
    if (typeof jQuery === 'undefined') {
        var script = document.createElement('script');
        script.src = 'https://code.jquery.com/jquery-3.6.0.min.js';
        document.head.appendChild(script);
    }

    // Check if Bootstrap JS is loaded
    if (typeof bootstrap === 'undefined') {
        var script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js';
        document.head.appendChild(script);
    }
    
    // Handle dropdown manually if needed
    document.querySelector('#languageDropdown').addEventListener('click', function() {
        this.closest('.dropdown').classList.toggle('show');
        this.nextElementSibling.classList.toggle('show');
    });
});
</script> 