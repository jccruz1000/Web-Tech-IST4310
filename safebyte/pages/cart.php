<?php
require_once(__DIR__ . '/../functions/db_connect.php');
require_once(__DIR__ . '/../functions/app_helpers.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$success_message = '';
$error_message   = '';

// Handle "Add to cart" from pricing page
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['plan_name']) && !isset($_POST['confirm_checkout'])) {
    $plan_name  = trim($_POST['plan_name']);
    $price      = isset($_POST['price']) ? (float) $_POST['price'] : 0.0;
    $billing    = $_POST['billing_cycle'] ?? 'monthly';
    $duration   = (int) ($_POST['duration_days'] ?? 30);
    
    // Basic validation
    if ($plan_name === '' || $price <= 0) {
        $error_message = "Invalid plan data received.";
    } else {
        $_SESSION['cart'] = [
            'plan_name'      => $plan_name,
            'price'          => $price,
            'billing_cycle'  => $billing,
            'duration_days'  => $duration,
        ];
        $success_message = "Plan added to cart successfully!";
    }
}

// Handle "Clear cart"
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    unset($_SESSION['cart']);
    header('Location: index.php?page=cart');
    exit();
}

// Handle "Confirm checkout" - Require login and insert into subscriptions table
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_checkout'])) {
    // Make sure user is logged in
    if (!is_logged_in()) {
        $error_message = "You must be logged in to complete checkout.";
    } elseif (empty($_SESSION['cart'])) {
        $error_message = "Your cart is empty. Please select a plan first.";
    } else {
        $cart      = $_SESSION['cart'];
        $user_id   = $_SESSION['user_id'];
        $plan_name = $cart['plan_name'];
        $price     = $cart['price'];
        $duration  = $cart['duration_days'];
        
        $start_date = date('Y-m-d');
        $end_date   = date('Y-m-d', strtotime('+' . $duration . ' days'));
        
        // Insert into subscriptions table
        $sql = "INSERT INTO subscriptions (user_id, plan_name, price, start_date, end_date)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if ($stmt) {
            $stmt->bind_param('isdss', $user_id, $plan_name, $price, $start_date, $end_date);
            
            if ($stmt->execute()) {
                $subscription_id = $stmt->insert_id;
                
                // Store receipt info
                $_SESSION['receipt'] = [
                    'subscription_id' => $subscription_id,
                    'plan'            => $plan_name,
                    'price'           => $price,
                    'start_date'      => $start_date,
                    'end_date'        => $end_date
                ];
                
                // Clear cart
                unset($_SESSION['cart']);
                
                // Redirect to receipt page - FIXED PATH
                header('Location: index.php?page=receipt');
                exit();
            } else {
                $error_message = "Could not complete checkout. Please try again. Error: " . $conn->error;
            }
        } else {
            $error_message = "Database error while preparing checkout.";
        }
    }
}

$cart = $_SESSION['cart'] ?? null;

// Calculate totals
$tax_rate = 0.00; // Adjust tax rate as needed
$subtotal = $cart ? $cart['price'] : 0;
$tax      = $subtotal * $tax_rate;
$total    = $subtotal + $tax;
?>

<h1>Your Safebyte Cart</h1>

<?php if ($success_message): ?>
    <p class="success-message"><?php echo htmlspecialchars($success_message); ?></p>
<?php endif; ?>

<?php if ($error_message): ?>
    <p class="error-message"><?php echo htmlspecialchars($error_message); ?></p>
<?php endif; ?>

<?php if (!$cart): ?>
    <div class="dashboard-info">
        <p>Your cart is currently empty.</p>
    </div>
    <a href="index.php?page=pricing" class="btn-primary">Browse Plans</a>
    
<?php else: ?>
    <div class="dashboard-info access-granted">
        <h2>Selected Plan</h2>
        <p><strong>Plan:</strong> <?php echo htmlspecialchars($cart['plan_name']); ?></p>
        <p><strong>Billing Cycle:</strong> <?php echo htmlspecialchars(ucfirst($cart['billing_cycle'])); ?></p>
        <p><strong>Access Duration:</strong> <?php echo (int) $cart['duration_days']; ?> days</p>
    </div>
    
    <div class="dashboard-info">
        <h3>Order Summary</h3>
        <p><strong>Subtotal:</strong> $<?php echo number_format($subtotal, 2); ?></p>
        <p><strong>Tax:</strong> $<?php echo number_format($tax, 2); ?></p>
        <p style="font-size: 1.2em; color: #FFFF00;"><strong>Total:</strong> $<?php echo number_format($total, 2); ?></p>
    </div>
    
    <?php if (!is_logged_in()): ?>
        <div class="dashboard-info access-denied">
            <p style="font-size: 18px;"><strong>🔒 Login Required</strong></p>
            <p>You must be logged in to complete checkout.</p>
            <p style="margin-top: 15px;">
                <a href="index.php?page=login" class="btn-primary" style="margin-right: 10px;">Login</a>
                <a href="index.php?page=register" class="btn-primary">Create Account</a>
            </p>
        </div>
    <?php else: ?>
        <form method="POST" action="index.php?page=cart">
            <input type="hidden" name="confirm_checkout" value="1">
            <button type="submit" class="btn-primary">Confirm & Activate Subscription</button>
        </form>
    <?php endif; ?>
    
    <p style="margin-top: 15px;">
        <a href="index.php?page=pricing" style="color: #FFFF00;">Change Plan</a> |
        <a href="index.php?page=cart&action=clear" style="color: #f8d7da;">Clear Cart</a>
    </p>
<?php endif; ?>