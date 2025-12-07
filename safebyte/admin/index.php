<?php
require_once(__DIR__ . '/../functions/db_connect.php');
require_once(__DIR__ . '/../functions/app_helpers.php');

// Check if user is admin
redirect_if_not_admin();

$success_message = '';
$error_message = '';

// Handle subscription updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_subscription') {
        $user_id = intval($_POST['user_id']);
        $plan_name = $conn->real_escape_string($_POST['plan_name']);
        $end_date = $_POST['end_date'];
        
        // Check if subscription already exists
        $check_sql = "SELECT subscription_id FROM subscriptions WHERE user_id = ? AND end_date >= CURDATE()";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param('i', $user_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            // Update existing subscription
            $sql = "UPDATE subscriptions SET plan_name = ?, end_date = ? WHERE user_id = ? AND end_date >= CURDATE()";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ssi', $plan_name, $end_date, $user_id);
        } else {
            // Insert new subscription
            $sql = "INSERT INTO subscriptions (user_id, plan_name, end_date) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iss', $user_id, $plan_name, $end_date);
        }
        
        if ($stmt->execute()) {
            $success_message = "Subscription updated successfully!";
        } else {
            $error_message = "Error updating subscription: " . $conn->error;
        }
    } elseif ($_POST['action'] === 'delete_subscription') {
        $subscription_id = intval($_POST['subscription_id']);
        $sql = "DELETE FROM subscriptions WHERE subscription_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $subscription_id);
        
        if ($stmt->execute()) {
            $success_message = "Subscription deleted successfully!";
        } else {
            $error_message = "Error deleting subscription: " . $conn->error;
        }
    }
}

// Get search parameter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Fetch all users with their subscription info
$sql = "SELECT u.user_id, u.email, u.is_admin, u.pref_location, u.pref_protocol,
               s.subscription_id, s.plan_name, s.end_date
        FROM users u
        LEFT JOIN subscriptions s ON u.user_id = s.user_id AND s.end_date >= CURDATE()";

if (!empty($search)) {
    $search_param = '%' . $conn->real_escape_string($search) . '%';
    $sql .= " WHERE u.email LIKE '$search_param' OR u.user_id LIKE '$search_param'";
}

$sql .= " ORDER BY u.user_id ASC";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - Safebyte</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../images/logo.png" alt="Safebyte Logo" class="logo-img">
            <span class="logo-text">SAFEBYTE ADMIN</span>
        </div>
        <nav class="nav-links">
            <a href="../index.php?page=dashboard">User Dashboard</a>
            <a href="../pages/logout.php">Logout</a>
        </nav>
    </header>
    
    <main>
        <div class="admin-header">
            <h1>🛡️ Admin Control Panel</h1>
            <p style="color: #AAAAAA;">Manage users and subscriptions</p>
        </div>
        
        <?php if ($success_message): ?>
            <p class="success-message"><?php echo $success_message; ?></p>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <p class="error-message"><?php echo $error_message; ?></p>
        <?php endif; ?>
        
        <?php
        // Calculate statistics
        $total_users = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
        $active_subs = $conn->query("SELECT COUNT(DISTINCT user_id) as count FROM subscriptions WHERE end_date >= CURDATE()")->fetch_assoc()['count'];
        $total_admins = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_admin = 1")->fetch_assoc()['count'];
        ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $active_subs; ?></div>
                <div class="stat-label">Active Subscriptions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $total_admins; ?></div>
                <div class="stat-label">Administrators</div>
            </div>
        </div>
        
        <div class="search-bar">
            <form method="GET" action="" style="margin: 0;">
                <input type="text" name="search" placeholder="Search by email or user ID..." value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit">Search</button>
                <?php if (!empty($search)): ?>
                    <a href="index.php" style="color: #FFFF00; margin-left: 10px;">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        
        <table class="users-table">
            <thead>
                <tr>
                    <th>User ID</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Subscription</th>
                    <th>End Date</th>
                    <th>Preferences</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td>
                            <?php if ($user['is_admin']): ?>
                                <span class="badge badge-admin">ADMIN</span>
                            <?php else: ?>
                                <span class="badge" style="background-color: #444;">User</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($user['plan_name']): ?>
                                <span class="badge badge-active"><?php echo htmlspecialchars($user['plan_name']); ?></span>
                            <?php else: ?>
                                <span class="badge badge-inactive">No Subscription</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            if ($user['end_date']) {
                                echo date('M j, Y', strtotime($user['end_date']));
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td style="font-size: 12px;">
                            <?php echo htmlspecialchars($user['pref_location']); ?> / 
                            <?php echo htmlspecialchars($user['pref_protocol']); ?>
                        </td>
                        <td>
                            <button class="action-btn btn-edit" onclick="openModal(<?php echo $user['user_id']; ?>, '<?php echo htmlspecialchars($user['email']); ?>', '<?php echo htmlspecialchars($user['plan_name'] ?? ''); ?>', '<?php echo $user['end_date'] ?? ''; ?>')">
                                Edit Sub
                            </button>
                            <?php if ($user['subscription_id']): ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Delete this subscription?');">
                                    <input type="hidden" name="action" value="delete_subscription">
                                    <input type="hidden" name="subscription_id" value="<?php echo $user['subscription_id']; ?>">
                                    <button type="submit" class="action-btn btn-delete">Delete</button>
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </main>
    
    <!-- Modal for adding/editing subscription -->
    <div id="subscriptionModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 style="color: #FFFF00; margin-bottom: 20px;">Manage Subscription</h2>
            <form method="POST">
                <input type="hidden" name="action" value="add_subscription">
                <input type="hidden" name="user_id" id="modal_user_id">
                
                <div class="form-group">
                    <label>User Email:</label>
                    <input type="text" id="modal_email" readonly style="background-color: #333;">
                </div>
                
                <div class="form-group">
                    <label>Plan:</label>
                    <select name="plan_name" required>
                        <option value="">Select Plan</option>
                        <option value="Basic Shield">Basic Shield ($5/month)</option>
                        <option value="Premium Guard">Premium Guard ($12/month)</option>
                        <option value="Enterprise Fortress">Enterprise Fortress ($25/month)</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>End Date:</label>
                    <input type="date" name="end_date" id="modal_end_date" required>
                </div>
                
                <button type="submit" class="btn-primary">Save Subscription</button>
            </form>
        </div>
    </div>
    
    <script>
        function openModal(userId, email, planName, endDate) {
            document.getElementById('modal_user_id').value = userId;
            document.getElementById('modal_email').value = email;
            
            // Set plan dropdown
            const planSelect = document.querySelector('select[name="plan_name"]');
            if (planName) {
                planSelect.value = planName;
            }
            
            // Set end date (default to 30 days from now if empty)
            if (endDate) {
                document.getElementById('modal_end_date').value = endDate;
            } else {
                const futureDate = new Date();
                futureDate.setDate(futureDate.getDate() + 30);
                document.getElementById('modal_end_date').value = futureDate.toISOString().split('T')[0];
            }
            
            document.getElementById('subscriptionModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('subscriptionModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('subscriptionModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }
    </script>
</body>
</html>