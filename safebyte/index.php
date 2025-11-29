<?php
require_once('functions/db_connect.php');
require_once('functions/app_helpers.php');

$page = 'marketing';

if (isset($_GET['page'])) {
    $requested_page = $_GET['page'];
    
    if (in_array($requested_page, ['login', 'register', 'dashboard', 'pricing'])) {
        $page = $requested_page;
    }
}

if ($page === 'dashboard' && !is_logged_in()) {
    $page = 'login';
} elseif (($page === 'login' || $page === 'register') && is_logged_in()) {
    $page = 'dashboard';
}

$content_file = 'pages/' . $page . '.php';

$user_status = is_logged_in() ? "Logged In" : "Guest";

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Safebyte VPN - <?php echo ucfirst($page); ?></title>
    <link rel="stylesheet" href="css/style.css"> 
</head>
<body>

    <header>
    <div class="logo">
        <img src="images/logo.png" alt="Safebyte Logo" class="logo-img">
        <span class="logo-text">SAFEBYTE</span>
    </div>
    
    <nav class="nav-links">
            <a href="index.php?page=marketing">Home</a>
            <a href="index.php?page=pricing">Pricing</a>
            
            <?php if (is_logged_in()): ?>
                <a href="index.php?page=dashboard">Dashboard</a>
                <a href="pages/logout.php">Logout</a>
            <?php else: ?>
                <a href="index.php?page=login">Login</a>
                <a href="index.php?page=register">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <main>
        <?php 
        if (file_exists($content_file)) {
            include $content_file;
        } else {
            echo "<h2>Error: Page Not Found</h2><p>The content file " . htmlspecialchars($content_file) . " could not be loaded.</p>";
        }
        ?>
    </main>

    <footer>
        &copy; <?php echo date('Y'); ?> Safebyte Privacy Service. All Rights Reserved. (User Status: <?php echo $user_status; ?>)
    </footer>

</body>
</html>