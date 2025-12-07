<?php
require_once(__DIR__ . '/../functions/app_helpers.php');

require_login();

// Debug: Check if receipt exists
if (!isset($_SESSION['receipt'])) {
    ?>
    <h1>No Receipt Found</h1>
    <div class="dashboard-info access-denied">
        <p><strong>⚠️ No receipt data available.</strong></p>
        <p>This usually means the subscription was not properly created or the session expired.</p>
        <p style="margin-top: 15px;">
            <a href="index.php?page=dashboard" class="btn-primary">Go to Dashboard</a>
            <a href="index.php?page=pricing" class="btn-primary" style="margin-left: 10px;">View Plans</a>
        </p>
    </div>
    <?php
    exit();
}

$receipt = $_SESSION['receipt'];
?>

<div style="max-width: 600px; margin: 0 auto;">
    <h1>✅ Payment Receipt</h1>
    
    <div class="dashboard-info access-granted">
        <h3>Subscription Confirmed!</h3>
        <p><strong>Receipt ID:</strong> #<?php echo $receipt['subscription_id']; ?></p>
        <p><strong>Plan:</strong> <?php echo htmlspecialchars($receipt['plan']); ?></p>
        <p><strong>Price:</strong> $<?php echo number_format($receipt['price'], 2); ?></p>
        <p><strong>Start Date:</strong> <?php echo date('F j, Y', strtotime($receipt['start_date'])); ?></p>
        <p><strong>End Date:</strong> <?php echo date('F j, Y', strtotime($receipt['end_date'])); ?></p>
        <p><strong>User ID:</strong> <?php echo $_SESSION['user_id']; ?></p>
    </div>
    
    <div class="success-message">
        <p><strong>🎉 Thank you for your purchase!</strong></p>
        <p>Your VPN subscription is now active. You can download your configuration file from your dashboard.</p>
    </div>
    
    <a href="index.php?page=dashboard" class="btn-primary" style="margin-top: 20px;">
        Go to Dashboard
    </a>
    
    <p style="margin-top: 20px; text-align: center;">
        <a href="index.php?page=pricing" style="color: #FFFF00;">Browse More Plans</a>
    </p>
</div>

<?php 
// Clear receipt from session after displaying
unset($_SESSION['receipt']); 
?>