<?php
// Start session - must be at the very top before any output
session_start();

// Check if there's an error message
$error_message = '';
if (isset($_SESSION['login_error'])) {
    $error_message = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

// Check if there's a success message
$success_message = '';
if (isset($_SESSION['login_success'])) {
    $success_message = $_SESSION['login_success'];
    unset($_SESSION['login_success']);
}
?>
<!DOCTYPE html>
<html lang="en" class="no-js" >
<head>

    <!--- basic page needs
    ================================================== -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Complexe Tanger Boulevard</title>

    <script>
        document.documentElement.classList.remove('no-js');
        document.documentElement.classList.add('js');
    </script>

    <!-- CSS
    ================================================== -->
    <link rel="stylesheet" href="css/vendor.css">
    <link rel="stylesheet" href="css/styles.css">

    <!-- favicons
    ================================================== -->
    <link rel="apple-touch-icon" sizes="180x180" href="apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="favicon-16x16.png">
    <link rel="manifest" href="site.webmanifest">

    <style>
        .login-form {
            background-color: #fff;
            padding: 40px;
            border-radius: 5px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            margin: 0 auto;
        }
        
        .login-form h2 {
            margin-bottom: 30px;
            text-align: center;
        }
        
        .login-form .form-group {
            margin-bottom: 20px;
        }
        
        .login-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
        }
        
        .login-form input[type="text"],
        .login-form input[type="password"],
        .login-form input[type="email"],
        .login-form select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        
        .login-form button {
            width: 100%;
            padding: 12px;
            background-color: #151515;
            color: #fff;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        
        .login-form button:hover {
            background-color: #333;
        }
        
        .form-footer {
            text-align: center;
            margin-top: 20px;
        }
        
        .login-message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        
        .login-message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .login-message.success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>

</head>

<body id="top">

    <!-- preloader
    ================================================== -->
    <div id="preloader">
        <div id="loader">
        </div>
    </div>


    <!-- page wrap
    ================================================== -->
    <div id="page" class="s-pagewrap">


        <!-- # site header 
        ================================================== -->
        <header class="s-header">

            <div class="s-header__logo">
                <a class="logo" href="index.php">
                    <img src="images/logo.svg" alt="Homepage">
                </a>
            </div>

            <a class="s-header__menu-toggle" href="#0" class="">
                <span class="s-header__menu-text">Menu</span>
                <span class="s-header__menu-icon"></span>
            </a>

            <nav class="s-header__nav">

                <a href="#0" class="s-header__nav-close-btn" title="close"><span>Close</span></a>
                <h3>CTB</h3>

                <ul class="s-header__nav-list">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="index.php#about">About</a></li>
                    <li><a href="index.php#services">Services</a></li>
                    <li><a href="index.php#portfolio">Facilities</a></li>
                    <li><a href="index.php#contact">Contact</a></li>
                </ul>

            </nav>

        </header> <!-- end s-header -->


        <!-- # site-content
        ================================================== -->
        <section id="content" class="s-content">

            <!-- Login Form Section -->
            <section class="s-about target-section">
                <div class="row" style="max-width: 800px; margin: 0 auto; padding-top: 120px; padding-bottom: 120px;">
                    <div class="column">
                        <div class="login-form">
                            <h2>Login to Your Account</h2>
                            
                            <?php if (!empty($error_message)): ?>
                                <div class="login-message error"><?php echo $error_message; ?></div>
                            <?php endif; ?>
                            
                            <?php if (!empty($success_message)): ?>
                                <div class="login-message success"><?php echo $success_message; ?></div>
                            <?php endif; ?>
                            
                            <form action="auth.php" method="post">
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="password">Password</label>
                                    <input type="password" id="password" name="password" required>
                                </div>
                                
                                <button type="submit">Login</button>
                                
                                <div class="form-footer">
                                    <p>Forgot your password? Please contact management.</p>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </section> <!-- end login section -->

        </section> <!-- end s-content -->


        <!-- footer
        ================================================== -->
        <footer id="colophon" class="s-footer">
            <div class="row">
                <div class="column lg-12 ss-copyright">
                    <span>© Copyright Complexe Tanger Boulevard 2023</span>
                </div>
            </div>

            <div class="ss-go-top">
                <a class="smoothscroll" title="Back to Top" href="#top">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" style="fill: rgba(0, 0, 0, 1);transform: ;msFilter:;"><path d="M6 4h12v2H6zm5 10v6h2v-6h5l-6-6-6 6z"></path></svg>
                </a>
            </div> <!-- end ss-go-top -->
        </footer> <!-- end s-footer -->


    </div> <!-- end s-pagewrap -->


    <!-- Java Script
    ================================================== -->
    <script src="js/plugins.js"></script>
    <script src="js/main.js"></script>

</body>
</html> 