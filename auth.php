<?php
// Start session
session_start();

// Include database connection file
require_once 'includes/config.php';

// Enable debugging (REMOVE THIS IN PRODUCTION)
$debug = false;
function debugOutput($message) {
    global $debug;
    if ($debug) {
        echo $message . "<br>";
    }
}

if ($debug) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    echo "<h1>Debug Mode - Login Attempt</h1>";
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Collect form data and sanitize
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']) ? (int)$_POST['remember_me'] : 0;
    
    if ($debug) {
        debugOutput("Email: " . $email);
        debugOutput("Password: " . str_repeat('*', strlen($password)));
        debugOutput("Remember Me: " . $remember_me);
    }
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $_SESSION['login_error'] = "All fields are required.";
        if (!$debug) {
            header("Location: login.php");
            exit();
        } else {
            debugOutput("Error: All fields are required.");
        }
    }
    
    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['login_error'] = "Invalid email format.";
        if (!$debug) {
            header("Location: login.php");
            exit();
        } else {
            debugOutput("Error: Invalid email format.");
        }
    }
    
    try {
        debugOutput("Trying to connect to database...");
        
        // Show DB connection info for debugging
        if ($debug) {
            debugOutput("DB Host: " . $host);
            debugOutput("DB Name: " . $dbname);
            debugOutput("DB User: " . $username);
        }
        
        // Check if user exists in any role
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        
        debugOutput("SQL Query: SELECT * FROM users WHERE email = '" . $email . "' LIMIT 1");
        
        // Check if user exists
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            
            debugOutput("User found with ID: " . $user['id']);
            if ($debug) {
                debugOutput("User data: " . print_r($user, true));
            }
            
            // Get the user's role
            $role = $user['role'];
            
            // Determine redirect based on role
            switch ($role) {
                case 'resident':
                    $redirect = "resident/dashboard.php";
                    break;
                case 'manager':
                    $redirect = "manager/dashboard.php";
                    break;
                case 'admin':
                    $redirect = "admin/dashboard.php";
                    break;
                default:
                    $_SESSION['login_error'] = "Invalid user role.";
                    if (!$debug) {
                        header("Location: login.php");
                        exit();
                    } else {
                        debugOutput("Error: Invalid user role: " . $role);
                    }
            }
            
            // Verify the user is active
            if ($user['status'] !== 'active') {
                $_SESSION['login_error'] = "Your account is not active. Please contact management.";
                if (!$debug) {
                    header("Location: login.php");
                    exit();
                } else {
                    debugOutput("Error: User account is not active. Status: " . $user['status']);
                }
            }
            
            // Verify password
            debugOutput("Verifying password...");
            
            if ($debug) {
                debugOutput("Stored hash from DB: " . $user['password']);
                $new_hash = password_hash($password, PASSWORD_DEFAULT);
                debugOutput("New hash created with the provided password: " . $new_hash);
                debugOutput("password_verify() result: " . (password_verify($password, $user['password']) ? 'true' : 'false'));
            }
            
            if (password_verify($password, $user['password'])) {
                // Password is correct, set session variables
                debugOutput("Password verification successful!");
                
                // Set both role variables for compatibility with various scripts
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['email'] = $user['email']; 
                $_SESSION['role'] = $role;
                $_SESSION['user_role'] = $role;
                
                // Set name properly
                $_SESSION['name'] = $user['name'];
                $_SESSION['user_name'] = $user['name'];
                
                // Add phone if available
                if (!empty($user['phone'])) {
                    $_SESSION['phone'] = $user['phone'];
                }
                
                // Handle remember me functionality
                if ($remember_me == 1) {
                    // Set a cookie with the email that expires in 30 days
                    setcookie('ctb_email', $email, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                } else {
                    // Clear the cookie if remember me is not checked
                    if (isset($_COOKIE['ctb_email'])) {
                        setcookie('ctb_email', '', time() - 3600, '/');
                    }
                }
                
                // Log the successful login
                logActivity($user['id'], 'login', 'user', $user['id'], 'User logged in successfully');
                
                // Redirect to appropriate dashboard
                debugOutput("Redirecting to: " . $redirect);
                if (!$debug) {
                    header("Location: $redirect");
                    exit();
                }
            } else {
                // Password is incorrect
                debugOutput("Password verification failed!");
                $_SESSION['login_error'] = "Invalid email or password.";
                if (!$debug) {
                    header("Location: login.php");
                    exit();
                }
            }
        } else {
            // User does not exist
            debugOutput("User not found with email: " . $email);
            $_SESSION['login_error'] = "Invalid email or password.";
            if (!$debug) {
                header("Location: login.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        // Database error
        $errorMessage = "Database error: " . $e->getMessage();
        debugOutput("Exception caught: " . $errorMessage);
        $_SESSION['login_error'] = $errorMessage;
        if (!$debug) {
            header("Location: login.php");
            exit();
        }
    }
    
    if ($debug) {
        debugOutput("<a href='login.php'>Return to login page</a>");
    }
} else {
    // If not a POST request, redirect to login page
    if (!$debug) {
        header("Location: login.php");
        exit();
    } else {
        debugOutput("Not a POST request. Please submit the form.");
        debugOutput("<a href='login.php'>Go to login page</a>");
    }
}
?> 