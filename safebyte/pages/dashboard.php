<?php
require_once(__DIR__ . '/../functions/db_connect.php');
require_once(__DIR__ . '/../functions/app_helpers.php');

if (!is_logged_in()) {
    header('Location: ../index.php?page=login');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Handle preference updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_preferences'])) {
    $pref_location = $conn->real_escape_string($_POST['pref_location']);
    $pref_protocol = $conn->real_escape_string($_POST['pref_protocol']);
    
    $sql = "UPDATE users SET pref_location = ?, pref_protocol = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ssi', $pref_location, $pref_protocol, $user_id);
    
    if ($stmt->execute()) {
        $success_message = "Preferences updated successfully!";
    } else {
        $error_message = "Error updating preferences.";
    }
}

// Fetch user data including preferences
$sql = "SELECT email, pref_location, pref_protocol FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

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
    $subscription_status = 'Active until ' . date('F j, Y', strtotime($sub['end_date']));
    
    // Create customized config filename based on preferences
    $location = str_replace(' ', '_', $user_data['pref_location']);
    $protocol = str_replace(' ', '_', $user_data['pref_protocol']);
    $config_link = 'safebyte_vpn_' . $location . '_' . $protocol . '.ovpn';
}

// Define plan details
$plans = [
    'Basic Shield' => [
        'price' => '$5/month',
        'devices' => '1 device',
        'servers' => '5 locations',
        'encryption' => 'AES-128',
        'support' => 'Email support'
    ],
    'Premium Guard' => [
        'price' => '$12/month',
        'devices' => '5 devices',
        'servers' => '50+ locations',
        'encryption' => 'AES-256',
        'support' => '24/7 Priority support'
    ],
    'Enterprise Fortress' => [
        'price' => '$25/month',
        'devices' => 'Unlimited devices',
        'servers' => '100+ locations',
        'encryption' => 'AES-256',
        'support' => 'Dedicated account manager'
    ]
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Safebyte Dashboard</title>
    <style>
        .dashboard-header {
            background: linear-gradient(135deg, #1a1a1a 0%, #000000 100%);
            padding: 30px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #FFFF00;
        }
        
        .dashboard-header h1 {
            margin-bottom: 10px;
            color: #FFFF00;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .status-active {
            background-color: #28a745;
            color: white;
        }
        
        .status-inactive {
            background-color: #dc3545;
            color: white;
        }
        
        .preferences-section {
            background-color: #1a1a1a;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 2px solid #333333;
        }
        
        .preferences-section h3 {
            color: #FFFF00;
            margin-bottom: 20px;
        }
        
        .preference-form {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .form-field {
            display: flex;
            flex-direction: column;
        }
        
        .form-field label {
            color: #AAAAAA;
            margin-bottom: 8px;
            font-weight: bold;
        }
        
        .form-field select {
            padding: 12px;
            background-color: #222222;
            border: 1px solid #333333;
            color: white;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .preference-info {
            background-color: #0a2f0a;
            border: 1px solid #28a745;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
            color: #d4edda;
        }
        
        .subscription-card {
            background-color: #1a1a1a;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 2px solid #333333;
        }
        
        .subscription-card h3 {
            color: #FFFF00;
            margin-bottom: 15px;
        }
        
        .plan-detail {
            display: flex;
            justify-content: space-between;
            padding: 10px 0;
            border-bottom: 1px solid #222222;
            color: #CCCCCC;
        }
        
        .plan-detail:last-child {
            border-bottom: none;
        }
        
        .plan-detail-label {
            color: #AAAAAA;
        }
        
        .plan-detail-value {
            color: #FFFFFF;
            font-weight: bold;
        }
        
        .available-plans {
            margin-top: 40px;
        }
        
        .plan-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .plan-card {
            background-color: #111111;
            border: 2px solid #333333;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s ease;
        }
        
        .plan-card:hover {
            border-color: #FFFF00;
            transform: translateY(-3px);
        }
        
        .plan-card.current-plan {
            border-color: #28a745;
            background-color: #0a2f0a;
        }
        
        .plan-card h4 {
            color: #FFFF00;
            margin-bottom: 10px;
        }
        
        .plan-price {
            font-size: 24px;
            color: #FFFFFF;
            margin-bottom: 15px;
        }
        
        .plan-feature {
            padding: 5px 0;
            color: #AAAAAA;
            font-size: 14px;
        }
        
        .current-plan-badge {
            background-color: #28a745;
            color: white;
            padding: 3px 10px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .download-section {
            background: linear-gradient(135deg, #004d00 0%, #001a00 100%);
            padding: 25px;
            border-radius: 8px;
            margin: 20px 0;
            border: 2px solid #28a745;
        }
        
        .download-section h3 {
            color: #d4edda;
            margin-bottom: 15px;
        }
        
        .download-button {
            display: inline-block;
            background-color: #FFFF00;
            color: #000000;
            padding: 12px 25px;
            border-radius: 4px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }
        
        .download-button:hover {
            background-color: #CCCC00;
        }
        
        .config-details {
            background-color: #003300;
            padding: 15px;
            border-radius: 4px;
            margin-top: 15px;
            font-family: monospace;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="dashboard-header">
        <h1>Welcome to Your Dashboard</h1>
        <p style="color: #AAAAAA;">User: <?php echo htmlspecialchars($user_data['email']); ?> (ID: <?php echo $user_id; ?>)</p>
        <span class="status-badge <?php echo $is_subscribed ? 'status-active' : 'status-inactive'; ?>">
            <?php echo $is_subscribed ? '● ACTIVE SUBSCRIPTION' : '● NO ACTIVE SUBSCRIPTION'; ?>
        </span>
    </div>
    
    <?php if ($success_message): ?>
        <p class="success-message"><?php echo $success_message; ?></p>
    <?php endif; ?>
    
    <?php if ($error_message): ?>
        <p class="error-message"><?php echo $error_message; ?></p>
    <?php endif; ?>
    
    <!-- VPN Preferences Section -->
    <div class="preferences-section">
        <h3>⚙️ VPN Configuration Preferences</h3>
        <p style="color: #AAAAAA; margin-bottom: 20px;">Customize your VPN settings. These preferences will be used to generate your personalized configuration file.</p>
        
        <form method="POST">
            <div class="preference-form">
                <div class="form-field">
                    <label>Preferred Server Location:</label>
                    <select name="pref_location" required>
                        <option value="USA" <?php echo ($user_data['pref_location'] == 'USA') ? 'selected' : ''; ?>>🇺🇸 United States</option>
                        <option value="Germany" <?php echo ($user_data['pref_location'] == 'Germany') ? 'selected' : ''; ?>>🇩🇪 Germany</option>
                        <option value="Japan" <?php echo ($user_data['pref_location'] == 'Japan') ? 'selected' : ''; ?>>🇯🇵 Japan</option>
                        <option value="UK" <?php echo ($user_data['pref_location'] == 'UK') ? 'selected' : ''; ?>>🇬🇧 United Kingdom</option>
                        <option value="Canada" <?php echo ($user_data['pref_location'] == 'Canada') ? 'selected' : ''; ?>>🇨🇦 Canada</option>
                        <option value="Australia" <?php echo ($user_data['pref_location'] == 'Australia') ? 'selected' : ''; ?>>🇦🇺 Australia</option>
                        <option value="Singapore" <?php echo ($user_data['pref_location'] == 'Singapore') ? 'selected' : ''; ?>>🇸🇬 Singapore</option>
                    </select>
                </div>
                
                <div class="form-field">
                    <label>Preferred VPN Protocol:</label>
                    <select name="pref_protocol" required>
                        <option value="OpenVPN" <?php echo ($user_data['pref_protocol'] == 'OpenVPN') ? 'selected' : ''; ?>>OpenVPN (Most Compatible)</option>
                        <option value="WireGuard" <?php echo ($user_data['pref_protocol'] == 'WireGuard') ? 'selected' : ''; ?>>WireGuard (Fastest)</option>
                        <option value="IKEv2" <?php echo ($user_data['pref_protocol'] == 'IKEv2') ? 'selected' : ''; ?>>IKEv2 (Mobile Optimized)</option>
                    </select>
                </div>
            </div>
            
            <button type="submit" name="update_preferences" class="btn-primary" style="margin-top: 20px;">💾 Save Preferences</button>
        </form>
        
        <div class="preference-info">
            ℹ️ <strong>Current Settings:</strong> Server: <?php echo htmlspecialchars($user_data['pref_location']); ?> | Protocol: <?php echo htmlspecialchars($user_data['pref_protocol']); ?>
        </div>
    </div>
    
    <?php if ($is_subscribed): ?>
        <div class="subscription-card">
            <h3>📋 Current Subscription Details</h3>
            <div class="plan-detail">
                <span class="plan-detail-label">Plan:</span>
                <span class="plan-detail-value"><?php echo $plan_name; ?></span>
            </div>
            <div class="plan-detail">
                <span class="plan-detail-label">Status:</span>
                <span class="plan-detail-value"><?php echo $subscription_status; ?></span>
            </div>
            <?php if (isset($plans[$plan_name])): ?>
                <div class="plan-detail">
                    <span class="plan-detail-label">Price:</span>
                    <span class="plan-detail-value"><?php echo $plans[$plan_name]['price']; ?></span>
                </div>
                <div class="plan-detail">
                    <span class="plan-detail-label">Devices:</span>
                    <span class="plan-detail-value"><?php echo $plans[$plan_name]['devices']; ?></span>
                </div>
                <div class="plan-detail">
                    <span class="plan-detail-label">Server Locations:</span>
                    <span class="plan-detail-value"><?php echo $plans[$plan_name]['servers']; ?></span>
                </div>
                <div class="plan-detail">
                    <span class="plan-detail-label">Encryption:</span>
                    <span class="plan-detail-value"><?php echo $plans[$plan_name]['encryption']; ?></span>
                </div>
            <?php endif; ?>
        </div>
        
        <div class="download-section">
            <h3>✅ VPN Configuration Access Granted</h3>
            <p style="color: #d4edda; margin-bottom: 15px;">Your subscription is active. Your personalized VPN configuration will be available soon:</p>
            <a href="index.php?page=dashboard" class="download-button">📥 Download VPN Config</a>
            
            <div class="config-details">
                <strong>Configuration Details:</strong><br>
                File: <?php echo $config_link; ?><br>
                Location: <?php echo htmlspecialchars($user_data['pref_location']); ?><br>
                Protocol: <?php echo htmlspecialchars($user_data['pref_protocol']); ?><br>
                Plan: <?php echo $plan_name; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="access-denied dashboard-info">
            <p style="font-size: 18px; margin-bottom: 15px;">🔒 <strong>Access Denied</strong></p>
            <p>You don't currently have an active subscription. Choose a plan below to get started with Safebyte VPN protection.</p>
        </div>
    <?php endif; ?>
    
    <div class="available-plans">
        <h2>Available Plans <?php echo $is_subscribed ? '& Upgrades' : ''; ?></h2>
        <p style="color: #AAAAAA; margin-bottom: 20px;">
            <?php echo $is_subscribed ? 'Want more features? Upgrade your plan anytime.' : 'Choose the perfect plan for your security needs.'; ?>
        </p>
        
        <div class="plan-grid">
            <?php foreach ($plans as $name => $details): ?>
                <div class="plan-card <?php echo ($plan_name === $name) ? 'current-plan' : ''; ?>">
                    <h4>
                        <?php echo $name; ?>
                        <?php if ($plan_name === $name): ?>
                            <span class="current-plan-badge">CURRENT</span>
                        <?php endif; ?>
                    </h4>
                    <div class="plan-price"><?php echo $details['price']; ?></div>
                    <div class="plan-feature">📱 <?php echo $details['devices']; ?></div>
                    <div class="plan-feature">🌍 <?php echo $details['servers']; ?></div>
                    <div class="plan-feature">🔒 <?php echo $details['encryption']; ?></div>
                    <div class="plan-feature">💬 <?php echo $details['support']; ?></div>
                    
                    <?php if ($plan_name !== $name): ?>
                        <a href="index.php?page=pricing" class="btn-primary" style="display: block; text-align: center; margin-top: 15px; font-size: 14px; padding: 10px;">
                            <?php echo $is_subscribed ? 'Upgrade' : 'Subscribe'; ?>
                        </a>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <hr style="border-top: 1px solid #333; margin: 40px 0;">
    
    <p style="text-align: center;">
        <a href="index.php?page=pricing" style="color: #FFFF00; margin-right: 20px;">View Full Pricing Details</a>
        <?php if (is_admin()): ?>
            <a href="admin/index.php" style="color: #dc3545; margin-right: 20px;">🛡️ Admin Panel</a>
        <?php endif; ?>
        <a href="pages/logout.php" style="color: #AAAAAA;">Log Out</a>
    </p>
</body>
</html>