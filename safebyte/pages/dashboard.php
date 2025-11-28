<?php
require_once('../functions/db_connect.php');
require_once('../functions/app_helpers.php');

if (!is_logged_in()) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$is_subscribed = false;
$plan_name = 'None';
$subscription_status = 'Inactive';
$config_link = '#';

$today = date('Y-m-d');

$sql = "SELECT plan_name, end_date FROM subscriptions WHERE user_id = ? AND end_date >= ? ORDER BY end_date DESC LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param('is', $user_id, $today);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $sub = $result->fetch_assoc();
    
    $is_subscribed = true;
    $plan_name = $sub['plan_name'];
    $subscription_status = 'Active until ' . $sub['end_date'];
    
    $config_link = 'safebyte_vpn_config_' . $plan_name . '.ovpn';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Safebyte Dashboard</title>
</head>
<body>
    <h1>Welcome to the Safebyte Dashboard!</h1>
    
    <p>Your User ID: <?php echo $user_id; ?></p>
    <p>Subscription Status: <strong><?php echo $subscription_status; ?></strong></p>
    <p>Current Plan: <strong><?php echo $plan_name; ?></strong></p>
    
    <hr>
    
    <h2>Protected VPN Resources</h2>

    <?php if ($is_subscribed): ?>
        <div style="border: 2px solid green; padding: 15px;">
            <p>✅ **ACCESS GRANTED!** You are authorized to download your configuration file.</p>
            <p>Download Link: <a href="<?php echo $config_link; ?>" download><?php echo $config_link; ?></a></p>
        </div>
    <?php else: ?>
        <div style="border: 2px solid red; padding: 15px;">
            <p>❌ **ACCESS DENIED.** Please subscribe to gain access to VPN configuration files.</p>
            <p><a href="#">View Subscription Plans</a></p>
        </div>
    <?php endif; ?>

    <hr>
    <p><a href="logout.php">Log Out</a></p>
</body>
</html>