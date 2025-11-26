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
<html lang="fr" class="no-js">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Connexion - Complexe Tanger Boulevard</title>

    <script>
        document.documentElement.classList.remove('no-js');
        document.documentElement.classList.add('js');
    </script>

    <link rel="stylesheet" href="css/vendor.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">

    <link rel="icon" type="image/png" href="images/logo.png">
    <link rel="shortcut icon" href="images/logo.png" type="image/png">
    <link rel="apple-touch-icon" href="images/logo.png">
    <link rel="manifest" href="site.webmanifest">

    <style>
        /* Root Colors */
        :root {
            --primary-color: #2a9d8f;
            --secondary-color: #e9c46a;
            --accent-color: #e76f51;
            --info-color: #219ebc;
            --success-color: #50b563;
            --danger-color: #e63946;
            --warning-color: #cc8f16;
            --dark-color: #264653;
            --light-color: #f1faee;
            --white-color: #ffffff;
            --black-color: #000000;
            --font-primary: 'Poppins', sans-serif;
            --border-radius: 8px;
            --box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            --transition: all 0.3s ease;
            --gold: #E6B325;
            --gold-light: #F7D463;
            --gold-dark: #C89B1B;
            --color-2: #244454;
            --color-2-light: #36677E;
            --color-2-dark: #19333F;
        }
        
        /* Gold Accent Bar */
        .gold-accent-bar {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, var(--gold-dark), var(--gold), var(--gold-light), var(--gold), var(--gold-dark));
            background-size: 200% 100%;
            animation: shimmer 3s infinite linear;
            z-index: 999;
        }
        
        @keyframes shimmer {
            0% { background-position: 0% 0; }
            100% { background-position: 200% 0; }
        }

        /* Root variables */
        :root {
            --white: rgba(255, 255, 255, 0.9);
            --black: #000;
            --info-color: #36b9cc;
            --success-color: #1cc884;
            --danger-color: #e74a3b;
            --gold: #F9A828;
            --gold-light: hsla(37, 95%, 67%, 1);
            --gold-dark: hsla(37, 95%, 47%, 1);
            --color-2: hsla(194, 89%, 26%, 1);
            --color-2-light: hsla(194, 89%, 36%, 1);
            --color-2-lighter: hsla(194, 89%, 46%, 1);
            
            /* Original gold colors */
            --gold: #d4af37;
            --gold-light: #e6c458;
            --gold-dark: #b38728;
            
            /* Dark colors */
            --dark-blue: #0f172a;
            --navy: #1e293b;
            --slate: #334155;
            --slate-light: #475569;
            
            /* Text colors */
            --text: #1e293b;
            --text-light: #64748b;
            
            /* Status colors */
            --success: #10b981;
            --error: #ef4444;
            
            /* Shadow colors */
            --shadow-color: rgba(15, 23, 42, 0.08);
            --shadow-color-dark: rgba(15, 23, 42, 0.16);
        }

        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text);
            background-color: var(--off-white);
            min-height: 100vh;
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
            font-weight: 600;
        }

        /* Background Animation */
        .animated-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -2;
            overflow: hidden;
            background: linear-gradient(125deg, #0f172a 0%, #1e293b 100%);
        }

        .animated-bg::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(212, 175, 55, 0.05) 0%, rgba(212, 175, 55, 0) 70%);
            animation: rotate 50s linear infinite;
            z-index: -1;
        }

        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        /* Light particles */
        .light-particle {
            position: absolute;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            pointer-events: none;
        }

        .light-particle:nth-child(1) {
            top: 20%;
            left: 20%;
            width: 100px;
            height: 100px;
            animation: float 12s ease-in-out infinite;
        }

        .light-particle:nth-child(2) {
            top: 60%;
            left: 80%;
            width: 150px;
            height: 150px;
            animation: float 14s ease-in-out infinite 1s;
        }

        .light-particle:nth-child(3) {
            top: 25%;
            left: 70%;
            width: 120px;
            height: 120px;
            animation: float 16s ease-in-out infinite 2s;
        }

        .light-particle:nth-child(4) {
            top: 75%;
            left: 30%;
            width: 80px;
            height: 80px;
            animation: float 18s ease-in-out infinite 3s;
        }

        @keyframes float {
            0% {
                transform: translate(0, 0);
            }
            50% {
                transform: translate(40px, 20px);
            }
            100% {
                transform: translate(0, 0);
            }
        }

        /* Main Content */
        .login-area {
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px;
            position: relative;
            z-index: 1;
        }

        /* Glass Card Effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            overflow: hidden;
            width: 90%;
            max-width: 1200px;
            height: 90vh;
            min-height: 600px;
            display: flex;
            flex-direction: column;
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.3);
            position: relative;
            transform-style: preserve-3d;
            perspective: 1000px;
            margin: 0 auto;
        }

        @media (min-width: 992px) {
            .glass-card {
                flex-direction: row;
                /* Using 90vh height set in the main glass-card style */
            }
            
            .brand-section {
                display: block;
                flex: 5;
            }
            
            .form-section {
                flex: 4.5;
                padding: 40px;
            }
        }
        
        @media (max-width: 991px) {
            .glass-card {
                width: 95%;
                max-width: 700px;
                height: 85vh;
            }
        }
        
        @media (max-width: 576px) {
            .glass-card {
                width: 98%;
                height: 95vh;
            }
        }

        /* Gold Accent */
        .gold-accent {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--gold-dark), var(--gold), var(--gold-light), var(--gold), var(--gold-dark));
            background-size: 200% 100%;
            animation: shimmer 3s infinite linear;
            z-index: 2;
        }

        @keyframes shimmer {
            0% { background-position: 0% 0; }
            100% { background-position: 200% 0; }
        }

        /* Branding Section */
        .brand-section {
            display: none;
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 992px) {
            .brand-section {
                display: block;
                flex: 5;
            }
        }

        .brand-bg {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            transform: scale(1.1);
            transition: transform 10s ease;
            z-index: -1;
        }

        .glass-card:hover .brand-bg {
            transform: scale(1);
        }

        .brand-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(15, 23, 42, 0.8) 0%, rgba(15, 23, 42, 0.6) 100%);
            z-index: 0;
        }

        /* Brand Content Adjustments */
        .brand-content {
            position: relative;
            z-index: 1;
            padding: 30px 30px;
            color: var(--white);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            overflow-y: auto;
        }

        .brand-logo {
            margin-bottom: 10px;
            width: 100px;
            filter: brightness(0) invert(1);
            opacity: 0.9;
        }

        .brand-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 10px;
            line-height: 1.1;
            background: linear-gradient(90deg, var(--white) 0%, rgba(255, 255, 255, 0.8) 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            text-fill-color: transparent;
            max-width: 95%;
        }

        .brand-subtitle {
            font-size: 16px;
            font-weight: 300;
            margin-bottom: 20px;
            line-height: 1.5;
            color: rgba(255, 255, 255, 0.9);
        }

        .feature-list {
            list-style: none;
            margin-bottom: 20px;
        }

        .feature-item {
            display: flex;
            align-items: center;
            margin-bottom: 14px;
            font-size: 16px;
            font-weight: 300;
            color: rgba(255, 255, 255, 0.9);
        }

        .feature-icon {
            min-width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(78, 115, 223, 0.2);
            margin-right: 14px;
            color: var(--primary-color);
            font-size: 14px;
        }

        .brand-footer {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.6);
            padding-top: 15px;
            margin-top: auto;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Form Section Adjustments */
        .form-section {
            flex: 4;
            padding: 40px 25px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        @media (min-width: 576px) {
            .form-section {
                padding: 40px;
            }
        }

        .form-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at top right, rgba(255, 255, 255, 0.1), transparent 70%);
            z-index: -1;
        }
        
        /* Form decorative elements */
        .form-section::after {
            content: '';
            position: absolute;
            bottom: -80px;
            right: -80px;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(78, 115, 223, 0.03);
            z-index: -1;
        }
        
        .form-decor {
            position: absolute;
            border-radius: 50%;
            background: rgba(78, 115, 223, 0.03);
            z-index: -1;
        }
        
        .form-decor-1 {
            top: 15%;
            left: 15%;
            width: 8px;
            height: 8px;
            background: var(--primary-color);
            opacity: 0.3;
            animation: pulsate 3s ease-out infinite;
        }
        
        .form-decor-2 {
            bottom: 20%;
            right: 10%;
            width: 12px;
            height: 12px;
            border: 1px solid var(--primary-color);
            background: transparent;
            opacity: 0.2;
        }
        
        .form-decor-3 {
            top: 75%;
            left: 75%;
            width: 6px;
            height: 6px;
            background: var(--info-color);
            opacity: 0.2;
            animation: pulsate 4s ease-out infinite;
        }
        
        @keyframes pulsate {
            0% { transform: scale(1); opacity: 0.3; }
            50% { transform: scale(1.4); opacity: 0.1; }
            100% { transform: scale(1); opacity: 0.3; }
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
            position: relative;
        }
        
        .form-header::after {
            content: '';
            display: block;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, var(--primary-color), var(--primary-color), var(--info-color));
            margin: 15px auto 0;
            border-radius: 2px;
        }

        .form-title {
            color: var(--white);
            font-size: 36px;
            font-weight: 600;
            margin-bottom: 10px;
            position: relative;
            display: inline-block;
        }
        
        .form-title::before {
            content: """;
            position: absolute;
            left: -20px;
            top: -10px;
            font-size: 60px;
            color: rgba(78, 115, 223, 0.15);
            font-family: 'Playfair Display', serif;
        }
        
        .form-title::after {
            content: """;
            position: absolute;
            right: -20px;
            bottom: -40px;
            font-size: 60px;
            color: rgba(78, 115, 223, 0.15);
            font-family: 'Playfair Display', serif;
        }

        .form-subtitle {
            color: rgba(255, 255, 255, 0.7);
            font-size: 16px;
        }

        .login-form {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
            position: relative;
        }
        
        .form-alert {
            padding: 18px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 16px;
            display: flex;
            align-items: flex-start;
            background: rgba(255, 255, 255, 0.03);
            border-left: 3px solid transparent;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: opacity 0.5s ease;
        }

        .form-alert i {
            margin-right: 14px;
            font-size: 18px;
            margin-top: 2px;
        }

        .form-alert.error {
            border-left-color: var(--danger-color);
            color: var(--danger-color);
            background-color: rgba(231, 74, 59, 0.05);
        }

        .form-alert.success {
            border-left-color: var(--success-color);
            color: var(--success-color);
            background-color: rgba(28, 200, 138, 0.05);
        }

        .input-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-group label {
            display: block;
            margin-bottom: 10px;
            font-size: 17px;
            font-weight: 500;
            color: rgba(255, 255, 255, 0.9);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
        }
        
        .input-group label::before {
            content: '';
            display: inline-block;
            width: 6px;
            height: 6px;
            background-color: var(--primary-color);
            border-radius: 50%;
            margin-right: 10px;
            opacity: 0.7;
        }

        /* Base input and icon styles */
        .input-wrapper {
            position: relative;
            transition: all 0.3s ease;
            height: 56px;
        }
        
        .input-wrapper:hover {
            transform: translateY(-2px);
        }
        
        .input-wrapper.input-focused {
            transform: translateY(-2px);
        }

        .input-field {
            width: 100%;
            height: 56px;
            padding: 0 20px 0 75px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.15);
            border-radius: 10px;
            font-size: 16px;
            color: var(--white);
            transition: all 0.3s ease;
            font-family: 'Inter', sans-serif;
            backdrop-filter: blur(5px);
            -webkit-backdrop-filter: blur(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            line-height: 56px; /* Match height */
        }

        .input-field:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.05), 0 4px 12px rgba(0, 0, 0, 0.15);
            background: rgba(255, 255, 255, 0.05);
        }

        .input-field:focus + .input-icon {
            color: var(--primary-color);
        }

        .input-field::placeholder {
            color: rgba(255, 255, 255, 0.3);
        }

        /* All icon base styles */
        .input-icon, .password-toggle {
            position: absolute;
            top: 0;
            height: 56px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            transition: all 0.3s ease;
        }

        /* Position for left icons */
        .input-icon {
            left: 35px;
            color: rgba(255, 255, 255, 0.5);
        }

        /* Position for password toggle */
        .password-toggle {
            right: 25px;
            color: rgba(255, 255, 255, 0.4);
            cursor: pointer;
            z-index: 1;
            font-size: 16px;
        }

        .password-toggle:hover {
            color: rgba(78, 115, 223, 0.8);
        }

        .form-options {
            display: flex;
            justify-content: center;
            align-items: center;
            margin-bottom: 30px;
            position: relative;
            z-index: 1;
            padding: 0 5px;
        }

        .remember-option {
            display: flex;
            align-items: center;
            cursor: pointer;
        }

        .custom-checkbox {
            position: relative;
            width: 20px;
            height: 20px;
            margin-right: 10px;
        }

        .custom-checkbox input {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        .checkmark {
            position: absolute;
            top: 0;
            left: 0;
            width: 20px;
            height: 20px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 4px;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .custom-checkbox:hover .checkmark {
            border-color: rgba(78, 115, 223, 0.3);
            box-shadow: 0 0 0 2px rgba(78, 115, 223, 0.05);
        }

        .custom-checkbox input:checked ~ .checkmark {
            background: var(--primary-color);
            border-color: var(--primary-color);
            box-shadow: 0 0 10px rgba(78, 115, 223, 0.3);
        }

        .checkmark:after {
            content: "";
            position: absolute;
            display: none;
            left: 7px;
            top: 3px;
            width: 4px;
            height: 10px;
            border: solid white;
            border-width: 0 2px 2px 0;
            transform: rotate(45deg);
        }

        .custom-checkbox input:checked ~ .checkmark:after {
            display: block;
            animation: checkmark-appear 0.3s forwards;
        }
        
        @keyframes checkmark-appear {
            0% { opacity: 0; transform: rotate(45deg) scale(0.8); }
            100% { opacity: 1; transform: rotate(45deg) scale(1); }
        }

        .remember-text {
            font-size: 15px;
            color: rgba(255, 255, 255, 0.7);
            user-select: none;
            font-weight: 500;
        }

        .forgot-link {
            font-size: 15px;
            color: var(--info-color);
            text-decoration: none;
            transition: all 0.3s ease;
            position: relative;
            font-weight: 500;
            padding: 5px;
        }
        
        .forgot-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 1px;
            bottom: 2px;
            left: 5px;
            background-color: var(--primary-color);
            transition: width 0.3s ease;
        }
        
        .forgot-link:hover {
            color: var(--primary-color);
        }
        
        .forgot-link:hover::after {
            width: calc(100% - 10px);
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(90deg, var(--color-2), var(--gold));
            border: none;
            border-radius: 10px;
            color: var(--white);
            font-size: 17px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2), 0 0 0 2px rgba(7, 97, 125, 0.1);
            text-shadow: 0 1px 1px rgba(0, 0, 0, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .submit-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: all 0.6s ease;
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3), 0 0 0 2px rgba(249, 168, 40, 0.2);
        }
        
        .submit-btn:active {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2), 0 0 0 2px rgba(7, 97, 125, 0.2);
        }

        .submit-btn:hover::before {
            left: 100%;
        }

        .submit-btn i {
            margin-right: 8px;
            position: relative;
            top: 0;
        }
        
        .form-footer {
            text-align: center;
            margin-top: 30px;
            font-size: 16px;
            color: rgba(255, 255, 255, 0.6);
            position: relative;
        }
        
        .form-footer::before,
        .form-footer::after {
            content: '';
            position: absolute;
            top: 50%;
            width: 50px;
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
        }
        
        .form-footer::before {
            left: calc(50% - 120px);
        }
        
        .form-footer::after {
            right: calc(50% - 120px);
        }

        .support-link {
            color: var(--info-color);
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            position: relative;
        }
        
        .support-link::after {
            content: '';
            position: absolute;
            width: 0;
            height: 1px;
            bottom: -2px;
            left: 0;
            background-color: var(--primary-color);
            transition: width 0.3s ease;
        }
        
        .support-link:hover {
            color: var(--primary-color);
        }
        
        .support-link:hover::after {
            width: 100%;
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-section * {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .input-group:nth-child(1) { animation-delay: 0.1s; }
        .input-group:nth-child(2) { animation-delay: 0.2s; }
        .form-options { animation-delay: 0.3s; }
        .submit-btn { animation-delay: 0.4s; }
        .form-footer { animation-delay: 0.5s; }

        /* Hide default elements */
        #preloader, .s-header, .s-footer {
            display: none;
        }

        .login-form {
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }

        /* Email field specific styling */
        .input-group:has(#email) .input-field {
            border: 1px solid rgba(7, 97, 125, 0.2);
            border-left: 5px solid var(--color-2-lighter); 
            padding-left: 75px;
            box-shadow: inset 0 0 10px rgba(7, 97, 125, 0.05), 0 4px 12px rgba(0, 0, 0, 0.08);
        }

        .input-group:has(#email) .input-icon {
            left: 35px;
            color: var(--color-2);
        }

        .input-group:has(#email) .input-field:focus {
            border: 1px solid var(--color-2-light);
            border-left: 5px solid var(--color-2);
            box-shadow: inset 0 0 10px rgba(7, 97, 125, 0.08), 0 0 0 3px rgba(7, 97, 125, 0.1), 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .input-group:has(#email) .input-field:focus + .input-icon {
            color: var(--color-2);
        }

        .input-group:has(#email) label::before {
            background-color: var(--color-2);
            box-shadow: 0 0 8px var(--color-2-light);
        }

        /* Password field specific styling */
        .input-group:has(#password) .input-field {
            border: 1px solid rgba(212, 175, 55, 0.2);
            border-left: 5px solid var(--gold);
            padding-right: 60px;
            padding-left: 75px;
            box-shadow: inset 0 0 10px rgba(212, 175, 55, 0.05), 0 4px 12px rgba(0, 0, 0, 0.08);
        }
        
        .input-group:has(#password) .input-icon {
            left: 35px;
            color: var(--gold);
        }

        .input-group:has(#password) .input-field:focus {
            border: 1px solid var(--gold-light);
            border-left: 5px solid var(--gold);
            box-shadow: inset 0 0 10px rgba(212, 175, 55, 0.08), 0 0 0 3px rgba(212, 175, 55, 0.1), 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .input-group:has(#password) .input-field:focus + .input-icon {
            color: var(--gold-dark);
        }

        .input-group:has(#password) label::before {
            background-color: var(--gold);
            box-shadow: 0 0 8px var(--gold-light);
        }
    </style>
</head>

<body id="top">
    <div class="gold-accent-bar"></div>
    <div class="auth-container">
        <!-- Animated Background -->
        <div class="animated-bg">
            <div class="light-particle"></div>
            <div class="light-particle"></div>
            <div class="light-particle"></div>
            <div class="light-particle"></div>
        </div>

        <!-- Login Area -->
        <main class="login-area">
            <div class="glass-card">
                <!-- Gold Accent Line -->
                <div class="gold-accent"></div>

                <!-- Brand Section -->
                <div class="brand-section">
                    <img src="images/portfolio/architecture.jpg" alt="Complexe Tanger Boulevard" class="brand-bg">
                    <div class="brand-overlay"></div>
                    <div class="brand-content">
                        <img src="images/logo.png" alt="CTB" class="brand-logo">
                        <h1 class="brand-title">Complexe Tanger Boulevard</h1>
                        <p class="brand-subtitle">
                            Découvrez un cadre de vie luxueux au sein du premier complexe résidentiel et commercial de Tanger.
                            Connectez-vous pour accéder à vos services exclusifs.
                        </p>

                        <ul class="feature-list">
                            <li class="feature-item">
                                <span class="feature-icon"><i class="fas fa-building"></i></span>
                                <span>Équipements et installations premium</span>
                            </li>
                            <li class="feature-item">
                                <span class="feature-icon"><i class="fas fa-concierge-bell"></i></span>
                                <span>Services de conciergerie et d'assistance 24/7</span>
                            </li>
                            <li class="feature-item">
                                <span class="feature-icon"><i class="fas fa-calendar-check"></i></span>
                                <span>Planification d'événements et réservation d'espaces</span>
                            </li>
                            <li class="feature-item">
                                <span class="feature-icon"><i class="fas fa-shield-alt"></i></span>
                                <span>Accès sécurisé et gestion immobilière</span>
                            </li>
                        </ul>

                        <div class="brand-footer">
                            &copy; <?php echo date('Y'); ?> Complexe Tanger Boulevard. Tous droits réservés.
                        </div>
        </div>
    </div>

                <!-- Form Section -->
                <div class="form-section">
                    <div class="form-decor form-decor-1"></div>
                    <div class="form-decor form-decor-2"></div>
                    <div class="form-decor form-decor-3"></div>
                    
                    <div class="form-header">
                        <h2 class="form-title">Bienvenue</h2>
                        <p class="form-subtitle">Connectez-vous pour accéder à votre compte exclusif</p>
            </div>
                            
                            <?php if (!empty($error_message)): ?>
                    <div class="form-alert error">
                        <i class="fas fa-exclamation-circle"></i>
                        <div><?php echo $error_message; ?></div>
                    </div>
                            <?php endif; ?>
                            
                            <?php if (!empty($success_message)): ?>
                    <div class="form-alert success">
                        <i class="fas fa-check-circle"></i>
                        <div><?php echo $success_message; ?></div>
                    </div>
                            <?php endif; ?>
                            
                    <form action="auth.php" method="post" class="login-form">
                        <div class="input-group">
                            <label for="email">Adresse Email</label>
                            <div class="input-wrapper">
                                <input type="email" id="email" name="email" class="input-field" placeholder="Entrez votre email" required>
                                <i class="fas fa-envelope input-icon"></i>
                            </div>
                                </div>
                                
                        <div class="input-group">
                                    <label for="password">Mot de passe</label>
                            <div class="input-wrapper">
                                <input type="password" id="password" name="password" class="input-field" placeholder="Entrez votre mot de passe" required>
                                <i class="fas fa-lock input-icon"></i>
                                <i class="fas fa-eye password-toggle" id="passwordToggle"></i>
                            </div>
                                </div>
                                
                        <div class="form-options">
                            <label class="remember-option" for="remember-me">
                                <div class="custom-checkbox">
                                    <input type="checkbox" name="remember_me" id="remember-me" value="1">
                                    <span class="checkmark"></span>
                                </div>
                                <span class="remember-text">Se souvenir de moi</span>
                            </label>
                        </div>
                        
                        <button type="submit" class="submit-btn">
                            <i class="fas fa-sign-in-alt"></i> Se Connecter
                        </button>
                        
                        <div class="form-footer">
                            Besoin d'aide? <a class="support-link">Contactez notre équipe d'assistance</a>
                        </div>
                    </form>
                </div>
            </div>
        </main>
            </div>

    <!-- Scripts -->
    <script src="js/plugins.js"></script>
    <script src="js/main.js"></script>
    <script>
        // Password toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const passwordField = document.getElementById('password');
            const passwordToggle = document.getElementById('passwordToggle');
            const emailField = document.getElementById('email');
            const rememberCheckbox = document.getElementById('remember-me');
            
            // Cookie functions
            function setCookie(name, value, days) {
                let expires = "";
                if (days) {
                    const date = new Date();
                    date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                    expires = "; expires=" + date.toUTCString();
                }
                document.cookie = name + "=" + (value || "") + expires + "; path=/; SameSite=Strict";
            }
            
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
            
            function eraseCookie(name) {
                document.cookie = name + '=; Max-Age=-99999999; path=/';
            }
            
            // Check if there's a saved email in cookies
            const savedEmail = getCookie('ctb_email');
            if (savedEmail) {
                emailField.value = savedEmail;
                rememberCheckbox.checked = true;
            }
            
            // Handle form submission
            const loginForm = document.querySelector('.login-form');
            loginForm.addEventListener('submit', function(e) {
                if (rememberCheckbox.checked) {
                    // Save email for 30 days
                    setCookie('ctb_email', emailField.value, 30);
                } else {
                    // Remove saved email if remember me is unchecked
                    eraseCookie('ctb_email');
                }
                
                const submitBtn = this.querySelector('.submit-btn');
                submitBtn.innerHTML = '<div style="display: flex; align-items: center; justify-content: center;"><i class="fas fa-spinner fa-spin"></i><span style="margin-left: 8px;">Connexion en cours...</span></div>';
                submitBtn.disabled = true;
                // Form submission continues normally
            });
            
            passwordToggle.addEventListener('click', function() {
                // Toggle password visibility
                if (passwordField.type === 'password') {
                    passwordField.type = 'text';
                    passwordToggle.classList.remove('fa-eye');
                    passwordToggle.classList.add('fa-eye-slash');
                } else {
                    passwordField.type = 'password';
                    passwordToggle.classList.remove('fa-eye-slash');
                    passwordToggle.classList.add('fa-eye');
                }
                
                // Focus the password field
                passwordField.focus();
            });

            // Add input focus animations
            const inputs = document.querySelectorAll('.input-field');
            
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.parentElement.classList.add('input-focused');
                });
                
                input.addEventListener('blur', function() {
                    this.parentElement.classList.remove('input-focused');
                });
            });

            // Alert auto-dismiss
            const alerts = document.querySelectorAll('.form-alert');
            if (alerts.length > 0) {
                setTimeout(() => {
                    alerts.forEach(alert => {
                        alert.style.opacity = '0';
                        setTimeout(() => {
                            alert.style.display = 'none';
                        }, 500);
                    });
                }, 5000);
            }
        });
    </script>
</body>
</html> 