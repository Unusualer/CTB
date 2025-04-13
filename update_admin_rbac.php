<?php
/**
 * Script to update all PHP files in the admin, manager, and resident directories
 * to use the role-based access control system with existing authentication functions
 */

// Directory to update
$directories = ['admin', 'manager', 'resident'];

// Counter for updated files
$updated_files = 0;

// Process each directory
foreach ($directories as $dir) {
    // Check if the directory exists
    if (!is_dir($dir)) {
        echo "Directory {$dir} not found.\n";
        continue;
    }
    
    // Get all PHP files in the directory
    $files = glob("{$dir}/*.php");
    
    // Role for this directory
    $role = $dir;
    
    // Process each file
    foreach ($files as $file) {
        // Read the file content
        $content = file_get_contents($file);
        
        // Skip files that already include role_access.php
        if (strpos($content, 'role_access.php') !== false) {
            echo "Skipping {$file} (already updated).\n";
            continue;
        }
        
        // Check if there's the standard authentication check pattern
        $authPattern = '/if\s*\(\s*!\s*isset\s*\(\s*\$_SESSION\s*\[\s*[\'"]user_id[\'"]\s*\]\s*\)\s*\|\|\s*\$_SESSION\s*\[\s*[\'"](?:user_)?role[\'"]\s*\]\s*\!\=\s*[\'"]\w+[\'"]\s*\)\s*\{.*?exit\s*\(\s*\)\s*;.*?\}/s';
        
        if (preg_match($authPattern, $content, $matches)) {
            // Replace the standard authentication check with requireRole function
            $replacement = "// Check if user is logged in and has appropriate role\nrequireRole('$role');";
            $new_content = preg_replace($authPattern, $replacement, $content);
            
            // Add require for role_access.php after config.php inclusion
            $configPattern = '/require_once\s+[\'"]\.\.\/includes\/config\.php[\'"]\s*;/';
            if (preg_match($configPattern, $new_content)) {
                $new_content = preg_replace($configPattern, "require_once '../includes/config.php';\nrequire_once '../includes/role_access.php';", $new_content);
                
                // Write the updated content back to the file
                file_put_contents($file, $new_content);
                echo "Updated {$file}\n";
                $updated_files++;
            } else {
                echo "No config.php include found in {$file} - skipping\n";
            }
        } else {
            echo "No standard auth check pattern found in {$file} - skipping\n";
        }
    }
}

echo "\nUpdate complete. {$updated_files} files were updated.\n";
?> 