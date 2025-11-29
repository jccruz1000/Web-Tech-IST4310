<?php
require_once(__DIR__ . '/../functions/db_connect.php');
require_once(__DIR__ . '/../functions/app_helpers.php');
$error_message = '';
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $email = $conn->real_escape_string($_POST['email']);
    $password = $_POST['password'];
    if (empty($email) || empty($password)) {
        $error_message = "Email and password are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format.";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO users (email, password_hash) VALUES ('$email', '$hashed_password')";
        if ($conn->query($sql) === TRUE) {
            header('Location: index.php?page=login&registered=true');
            exit();
        } else {
            if ($conn->errno === 1062) {
                $error_message = "This email is already registered.";
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
    
    <?php if ($error_message): ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
        <p class="success-message"><?php echo $success_message; ?></p>
        <p>Go to the <a href="index.php?page=login" style="color: #FFFF00;">Login Page</a>.</p>
    <?php endif; ?>
    <form method="POST" action="index.php?page=register">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>
        
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        
        <button type="submit">Secure Sign Up</button>
    </form>
    <p>Already have an account? <a href="index.php?page=login" style="color: #FFFF00;">Log In</a></p>
</body>
</html>