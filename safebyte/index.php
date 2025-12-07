<?php
require_once('functions/db_connect.php');
require_once('functions/app_helpers.php');

// --------------------------------------
// Allowed pages
// --------------------------------------
$valid_pages = [
    'marketing',
    'login',
    'register',
    'dashboard',
    'pricing',
    'cart',
    'receipt'
];

// Default page
$page = 'marketing';

// --------------------------------------
// Determine requested page (if any)
// --------------------------------------
if (isset($_GET['page'])) {
    $requested_page = strtolower($_GET['page']);
    if (in_array($requested_page, $valid_pages, true)) {
        $page = $requested_page;
    }
}

// --------------------------------------
// Auth-based redirects
// --------------------------------------
if ($page === 'dashboard' && !is_logged_in()) {
    // Not logged in? Send to login.
    $page = 'login';
} elseif (in_array($page, ['login', 'register'], true) && is_logged_in()) {
    // Already logged in? Skip login/register.
    $page = 'dashboard';
}

// --------------------------------------
// Content file
// --------------------------------------
$content_file = __DIR__ . '/pages/' . $page . '.php';
$user_status  = is_logged_in() ? "Logged In" : "Guest";
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
        <a href="index.php?page=cart">Cart</a>
        <?php if (is_logged_in()): ?>
            <a href="index.php?page=dashboard">Dashboard</a>
            <?php if (is_admin()): ?>
                <a href="admin/index.php" style="color: #dc3545;">Admin Panel</a>
            <?php endif; ?>
            <a href="pages/logout.php">Logout</a>
        <?php else: ?>
            <a href="index.php?page=login">Login</a>
            <a href="index.php?page=register">Sign Up</a>
        <?php endif; ?>
    </nav>
</header>

<main>
    <?php 
    if (file_exists($content_file)) {
        include $content_file;
    } else {
        echo "<h2>Error: Page Not Found</h2>
              <p>The content file " . htmlspecialchars($content_file) . " could not be loaded.</p>";
    }
    ?>
</main>

<footer>
    &copy; <?php echo date('Y'); ?> Safebyte Privacy Service. 
    All Rights Reserved. (User Status: <?php echo $user_status; ?>)
</footer>
</body>
</html>