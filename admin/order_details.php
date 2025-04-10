<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Get the order_id from the query parameter
if (!isset($_GET['order_id'])) {
    header("Location: orders.php");
    exit();
}
$order_id = $_GET['order_id'];

// Fetch the order details
$stmt = $conn->prepare("SELECT orders.*, users.username FROM orders 
                        JOIN users ON orders.user_id = users.user_id 
                        WHERE orders.order_id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: orders.php");
    exit();
}

// Fetch the items in the order
$stmt = $conn->prepare("SELECT order_items.*, products.name AS product_name FROM order_items 
                        JOIN products ON order_items.product_id = products.product_id 
                        WHERE order_items.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $status = $_POST['status'];

    // Update order status and set updated_at timestamp
    $stmt = $conn->prepare("UPDATE orders SET status = ?, updated_at = NOW() WHERE order_id = ?");
    $stmt->execute([$status, $order_id]);

    // Fetch order details for email notification
    $stmt = $conn->prepare("SELECT orders.*, users.email FROM orders 
                            JOIN users ON orders.user_id = users.user_id 
                            WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    // Send email notifications (same as before)
    if ($status == 'To be packed' || $status == 'Paid') {
        $subject = "Order Confirmation";
        $message = "Your order (#$order_id) has been confirmed. Status: $status.";
        // mail($order['email'], $subject, $message); // Uncomment to send real emails
    } elseif ($status == 'Delivered') {
        $subject = "Order Delivered";
        $message = "Your order (#$order_id) has been delivered. Thank you for shopping with Shazada.com!";
        // mail($order['email'], $subject, $message); // Uncomment to send real emails
    }

    // Record transaction if applicable (same as before)
    if ($status == 'Paid' && $order['payment_method'] == 'Online Payment') {
        $stmt = $conn->prepare("INSERT INTO transactions (order_id, amount, payment_method, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $order['total_price'], $order['payment_method'], 'Paid']);
    } elseif ($status == 'Delivered' && $order['payment_method'] == 'COD') {
        $stmt = $conn->prepare("INSERT INTO transactions (order_id, amount, payment_method, status) VALUES (?, ?, ?, ?)");
        $stmt->execute([$order_id, $order['total_price'], $order['payment_method'], 'Paid']);
    }

    // Redirect to the same page to refresh the data
    header("Location: order_details.php?order_id=$order_id");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - Shazada.com</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Shazada.com</a>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Products</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="orders.php">Orders</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="transactions.php">Transactions</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="concerns.php">Customer Concerns</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logistics.php">Logistics</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="account.php">Account</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">Users</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1>Order Details - #<?php echo $order['order_id']; ?></h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title">Customer: <?php echo htmlspecialchars($order['username']); ?></h5>
                <h6 class="card-subtitle mb-2 text-muted">Total Price: ₱<?php echo number_format($order['total_price'], 2); ?></h6>
                <h6 class="card-subtitle mb-2 text-muted">Payment Method: <?php echo $order['payment_method']; ?></h6>
                <h6 class="card-subtitle mb-2 text-muted">Status: <?php echo $order['status']; ?></h6>
                <h6 class="card-subtitle mb-2 text-muted">Order Date: <?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></h6>
                <h6 class="card-subtitle mb-2 text-muted">Updated At: <?php echo date('M d, Y h:i A', strtotime($order['updated_at'])); ?></h6>

                <h5 class="mt-4">Items:</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($order['status'] != 'Delivered'): ?>
                    <h5 class="mt-4">Update Status:</h5>
                    <form method="POST">
                        <div class="mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select class="form-select" id="status" name="status" required>
                                <?php
                                $status_options = [];
                                if ($order['payment_method'] == 'COD') {
                                    $status_options = [
                                        'To be packed',
                                        'Packed and Shipped',
                                        'Received by Logistics',
                                        'Out for Delivery',
                                        'Delivered'
                                    ];
                                } else {
                                    $status_options = [
                                        'Paid',
                                        'To be packed',
                                        'Packed and Shipped',
                                        'Received by Logistics',
                                        'Out for Delivery',
                                        'Delivered'
                                    ];
                                }

                                // Determine the current status index
                                $current_status_index = array_search($order['status'], $status_options);

                                // Display only the next and previous status options
                                if ($current_status_index > 0) {
                                    echo '<option value="' . $status_options[$current_status_index - 1] . '">' . $status_options[$current_status_index - 1] . '</option>';
                                }
                                echo '<option value="' . $status_options[$current_status_index] . '" selected>' . $status_options[$current_status_index] . '</option>';
                                if ($current_status_index < count($status_options) - 1) {
                                    echo '<option value="' . $status_options[$current_status_index + 1] . '">' . $status_options[$current_status_index + 1] . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>