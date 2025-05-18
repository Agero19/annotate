<?php

// start Session

session_start();

// connect to config files
require_once dirname(__DIR__) . "/config/config.php";
require_once ROOT_PATH . "/config/database.php";

// Function to check if user is logged in
function isLoggedIn()
{
    return isset($_SESSION['user_id']);
}

// Function to get current user 
function getCurrentUser()
{
    if (isLoggedIn()) {
        require_once ROOT_PATH . '/models/User.php';
        $database = new Database();
        $db = $database->getConnection();
        $user = new User($db);
        $user->readOne($_SESSION['user_id']);
        return $user;
    }
    return null;
}

// Get current url and page name
$current_url = $_SERVER['REQUEST_URI'];
$current_page = basename($_SERVER['PHP_SELF']);

// Get current user information
$current_user = isLoggedIn() ? getCurrentUser() : null;

?>

<!DOCTYPE html>
<html lang="uk">

<head>
    <meta charset="UTF-8">

    <!-- Viewport -->
    <meta name="viewport"
        content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, viewport-fit=cover" />

    <title><?php echo isset($page_title) ? $page_title . ' - AnnotateX' : 'AnnotateX'; ?></title>

    <!-- Preloaded web font (Inter) -->
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap"
        rel="stylesheet" />

    <!-- Custom CSS -->
    <link rel="stylesheet" href="<?php echo url('assets/css/style.css'); ?>">
    <link rel="stylesheet" href="<?php echo url('assets/css/reset.css'); ?>">

    <?php if (isset($page_styles)): ?>
        <?php echo $page_styles; ?>
    <?php endif; ?>
</head>

<body>
    <div class="main-container">
        <header class="header">
            <div class="container">
                <a href="<?php echo url(); ?>" class="logo">
                    <img src="assets/images/Logo.svg" alt="Logo" />
                </a>

                <!-- Main nav -->
                <nav class="nav">
                    <ul class="list-unstyled">
                        <li><a href="<?php echo url(); ?>">Home</a></li>
                        <li><a href="<?php echo url('about.php'); ?>">About</a></li>
                        <li><a href="<?php echo url('contact.php'); ?>">Contact</a></li>
                        <li><a href="<?php echo url('uploads.php'); ?>">Uploads</a></li>
                    </ul>
                </nav>

                <!-- Search -->
                <div class="search-container">
                    <input type="text" id="search" placeholder="Search..." />
                    <button type="submit" class="search-btn">
                        <svg class="search-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                        </svg>
                    </button>
                </div>

                <?php if (isLoggedIn()): ?>
                    <!-- User icon -->
                    <div class="user-dropdown">
                        <button class="user-btn">
                            <svg class="user-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="7" r="4"></circle>
                                <path d="M5 20h14c0-5-3-7-7-7H12c-4 0-7 2-7 7z"></path>
                            </svg>
                        </button>
                        <div class="dropdown-content">
                            <a href="<?php echo url('profile.php'); ?>">Profile</a>
                            <a href="<?php echo url('settings.php'); ?>">Settings</a>
                            <a href="<?php echo url('logout.php'); ?>">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Login button -->
                    <div class="user-login">
                        <a href="<?php echo url('login.php'); ?>" class="btn btn-primary">Login</a>
                    </div>
                <?php endif; ?>
            </div>
        </header>
        <div>

        </div>