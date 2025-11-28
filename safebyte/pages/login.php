<?php
require_once('../functions/db_connect.php');
require_once('../functions/app_helpers.php');

redirect_if_logged_in('dashboard.php');

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        
        $sql = "SELECT user_id, password_hash FROM users WHERE email = ?";
        
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            
            if (password_verify($password, $user['password_hash'])) {
                
                
                $_SESSION['user_id'] = $user['user_id'];
                
                
                header('Location: dashboard.php');
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
    
    <?php if ($error_message): ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>
        
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        
        <button type="submit">Log In</button>
    </form>
    
    <p>Don't have an account? <a href="register.php">Sign Up</a></p>
</body>
</html>