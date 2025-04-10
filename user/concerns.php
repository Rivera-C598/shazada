<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as buyer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'buyer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's concerns
$stmt = $conn->prepare("SELECT * FROM customer_concerns WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$concerns = $stmt->fetchAll();

// Fetch user's orders
$stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'];
    $selected_orders = $_POST['orders'] ?? []; // Get selected orders

    if (!empty($message)) {
        // Insert the concern into the database
        $stmt = $conn->prepare("INSERT INTO customer_concerns (user_id, message) VALUES (?, ?)");
        $stmt->execute([$user_id, $message]);
        $concern_id = $conn->lastInsertId(); // Get the ID of the newly inserted concern

        // Attach selected orders to the concern
        if (!empty($selected_orders)) {
            foreach ($selected_orders as $order_id) {
                $stmt = $conn->prepare("INSERT INTO concern_orders (concern_id, order_id) VALUES (?, ?)");
                $stmt->execute([$concern_id, $order_id]);
            }
        }

        header("Location: concerns.php");
        exit();
    } else {
        $error = "Message cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Concerns - Shazada.com</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="shopping.php">Shazada.com</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="account.php">Account</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="cart.php">Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="concerns.php">Customer Concerns</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Customer Concerns</h1>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST" class="mb-4">
            <div class="mb-3">
                <label for="message" class="form-label">Your Message</label>
                <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
            </div>
            <div class="mb-3">
                <button type="button" id="toggleOrders" class="btn btn-secondary btn-sm">Attach Orders</button>
                <div id="orderSelection" class="mt-2" style="display: none;">
                    <select class="form-select" id="orders" name="orders[]" multiple>
                        <?php foreach ($orders as $order): ?>
                            <option value="<?php echo $order['order_id']; ?>">
                                Order #<?php echo $order['order_id']; ?> - <?php echo htmlspecialchars($order['status']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-text text-muted">Hold Ctrl (Windows) or Command (Mac) to select multiple orders.</small>
                    <button type="button" id="clearSelection" class="btn btn-secondary btn-sm mt-2">Clear Selection</button>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>

        <h3>Your Messages</h3>
        <?php if (empty($concerns)): ?>
            <div class="alert alert-info">You have no messages yet.</div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($concerns as $concern): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-1"><strong>You:</strong> <?php echo htmlspecialchars($concern['message']); ?></p>
                                <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($concern['created_at'])); ?></small>
                            </div>
                            <?php if (!empty($concern['admin_reply'])): ?>
                                <div class="text-end">
                                    <p class="mb-1"><strong>Admin:</strong> <?php echo htmlspecialchars($concern['admin_reply']); ?></p>
                                    <small class="text-muted">Replied on <?php echo date('M d, Y h:i A', strtotime($concern['updated_at'])); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                        <!-- Display attached orders -->
                        <?php
                        $stmt = $conn->prepare("SELECT orders.* FROM concern_orders 
                                                JOIN orders ON concern_orders.order_id = orders.order_id 
                                                WHERE concern_orders.concern_id = ?");
                        $stmt->execute([$concern['concern_id']]);
                        $attached_orders = $stmt->fetchAll();
                        ?>
                        <?php if (!empty($attached_orders)): ?>
                            <div class="mt-3">
                                <h6>Attached Orders:</h6>
                                <ul>
                                    <?php foreach ($attached_orders as $order): ?>
                                        <li>Order #<?php echo $order['order_id']; ?> - <?php echo htmlspecialchars($order['status']); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
    <script>
        // Toggle order selection dropdown
        document.getElementById('toggleOrders').addEventListener('click', function() {
            const orderSelection = document.getElementById('orderSelection');
            if (orderSelection.style.display === 'none') {
                orderSelection.style.display = 'block'; // Show the dropdown
            } else {
                orderSelection.style.display = 'none'; // Hide the dropdown
                clearSelection(); // Unselect all orders when hidden
            }
        });

        // Clear selection button
        document.getElementById('clearSelection').addEventListener('click', clearSelection);

        // Function to clear all selected orders
        function clearSelection() {
            const orderSelect = document.getElementById('orders');
            for (let i = 0; i < orderSelect.options.length; i++) {
                orderSelect.options[i].selected = false; // Unselect all options
            }
        }

        
    </script>
</body>
</html>