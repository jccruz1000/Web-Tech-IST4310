<?php
require_once(__DIR__ . '/../functions/db_connect.php');
require_once(__DIR__ . '/../functions/app_helpers.php');

// If already logged in, redirect to dashboard
if (is_logged_in()) {
    header('Location: ../index.php?page=dashboard');
    exit();
}

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    
    // Basic validation
    if (empty($email) || empty($password)) {
        $error_message = "Email and password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $error_message = "Password must be at least 6 characters long.";
    } else {
        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $sql = "INSERT INTO users (email, password_hash) VALUES (?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ss', $email, $hashed_password);
        
        if ($stmt->execute()) {
            // AUTO-LOGIN: Set session variables
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['is_admin'] = 0;
            
            // Redirect to dashboard
            header("Location: ../index.php?page=dashboard");
            exit();
        } else {
            // Error: duplicate email
            if ($conn->errno === 1062) {
                $error_message = "This email is already registered. <a href='../index.php?page=login' style='color: #FFFF00;'>Login instead?</a>";
            } else {
                $error_message = "Error: " . $conn->error;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Safebyte Registration</title>
</head>
<body>
    <h1>Create Your Safebyte Account</h1>
    
    <p style="color: #AAAAAA; max-width: 450px; margin-bottom: 20px;">
        Join Safebyte to protect your online privacy with military-grade VPN encryption.
    </p>
    
    <?php if (!empty($error_message)): ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php endif; ?>
    
    <form method="POST" action="../index.php?page=register">
        <label for="email">Email Address:</label>
        <input type="email" name="email" id="email" placeholder="your@email.com" required>
        
        <label for="password">Password:</label>
        <input type="password" name="password" id="password" placeholder="Minimum 6 characters" required>
        
        <button type="submit">Create Account & Login</button>
    </form>
    
    <p style="margin-top: 20px;">
        Already have an account? <a href="../index.php?page=login" style="color: #FFFF00;">Log In</a>
    </p>
</body>
</html>