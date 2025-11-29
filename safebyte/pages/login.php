<?php
require_once(__DIR__ . '/../functions/db_connect.php');
require_once(__DIR__ . '/../functions/app_helpers.php');
redirect_if_logged_in('dashboard.php');
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = $_POST['email'];
    $password = $_POST['password'];
    if (empty($email) || empty($password)) {
        $error_message = "Please enter both email and password.";
    } else {
        
        $sql = "SELECT user_id, password_hash, is_admin FROM users WHERE email = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password_hash'])) {
                
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['is_admin'] = $user['is_admin'];
                
                // Redirect to admin dashboard if admin, otherwise regular dashboard
                if ($user['is_admin'] == 1) {
                    header('Location: ../admin/index.php');
                } else {
                    header('Location: index.php?page=dashboard');
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
    
    <?php if ($error_message): ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php endif; ?>
    <form method="POST" action="index.php?page=login">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>
        
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        
        <button type="submit">Log In Securely</button>
    </form>
    
    <p>Don't have an account? <a href="index.php?page=register" style="color: #FFFF00;">Sign Up Here</a></p>
</body>
</html>