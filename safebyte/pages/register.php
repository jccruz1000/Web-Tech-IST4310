<?php
require_once('../functions/db_connect.php');

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
            $success_message = "Registration successful! You can now log in.";
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
    <h1>Register for Safebyte</h1>
    
    <?php if ($error_message): ?>
        <p style="color: red;"><?php echo $error_message; ?></p>
    <?php endif; ?>
    
    <?php if ($success_message): ?>
        <p style="color: green;"><?php echo $success_message; ?></p>
        <p>Go to the Login Page.</p>
    <?php endif; ?>

    <form method="POST" action="register.php">
        <label for="email">Email:</label><br>
        <input type="email" id="email" name="email" required><br><br>
        
        <label for="password">Password:</label><br>
        <input type="password" id="password" name="password" required><br><br>
        
        <button type="submit">Sign Up</button>
    </form>
</body>
</html>