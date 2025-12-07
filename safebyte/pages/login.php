<?php
require_once(__DIR__ . '/../functions/db_connect.php');
require_once(__DIR__ . '/../functions/app_helpers.php');

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    header('Location: ../index.php?page=dashboard');
    exit();
}

$error_message = '';
$success_message = '';

// Check if user just registered
if (isset($_GET['registered']) && $_GET['registered'] === 'true') {
    $success_message = "Account created successfully! Please log in.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        // Fetch user from database
        $sql = "SELECT user_id, password_hash, is_admin FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password
            if (password_verify($password, $user['password_hash'])) {
                // Set session variables
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                // Redirect to admin dashboard if admin, otherwise regular dashboard
                if ($user['is_admin'] == 1) {
                    header('Location: ../admin/index.php');
                } else {
                    header('Location: ../index.php?page=dashboard');
                }
                exit();
            } else {
                $error_message = "Invalid email or password.";
            }
        } else {
            $error_message = "Invalid email or password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Safebyte Login</title>
</head>
<body>
    <h1>Safebyte Login</h1>
    
    <p style="color: #AAAAAA; max-width: 450px; margin-bottom: 20px;">
        Welcome back! Log in to access your VPN dashboard and configuration files.
    </p>
    
    <?php if ($success_message): ?>
        <p class="success-message"><?php echo $success_message; ?></p>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php endif; ?>
    
    <form method="POST" action="../index.php?page=login">
        <label for="email">Email Address:</label>
        <input type="email" id="email" name="email" placeholder="your@email.com" required>
        
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" placeholder="Enter your password" required>
        
        <button type="submit">Log In Securely</button>
    </form>
    
    <p style="margin-top: 20px;">
        Don't have an account? <a href="../index.php?page=register" style="color: #FFFF00;">Sign Up Here</a>
    </p>
</body>
</html>