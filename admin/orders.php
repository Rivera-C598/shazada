<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Check if the "Delivered Orders" button is clicked
$show_delivered = isset($_GET['show_delivered']) && $_GET['show_delivered'] == 'true';

// Fetch orders based on the filter
if ($show_delivered) {
    $stmt = $conn->query("SELECT orders.*, users.username, products.name AS product_name FROM orders 
                          JOIN users ON orders.user_id = users.user_id 
                          JOIN products ON orders.product_id = products.product_id 
                          WHERE orders.status = 'Delivered'
                          ORDER BY orders.created_at DESC");
} else {
    $stmt = $conn->query("SELECT orders.*, users.username, products.name AS product_name FROM orders 
                          JOIN users ON orders.user_id = users.user_id 
                          JOIN products ON orders.product_id = products.product_id 
                          WHERE orders.status != 'Delivered'
                          ORDER BY orders.created_at DESC");
}
$orders = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
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
    header("Location: orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Shazada.com</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <h1>Orders</h1>
        <div class="mb-3">
            <a href="orders.php" class="btn btn-primary">Active Orders</a>
            <a href="orders.php?show_delivered=true" class="btn btn-secondary">Delivered Orders</a>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th>Updated At</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?php echo $order['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['username']); ?></td>
                        <td><?php echo htmlspecialchars($order['product_name']); ?></td>
                        <td><?php echo $order['quantity']; ?></td>
                        <td>₱<?php echo number_format($order['total_price'], 2); ?></td>
                        <td><?php echo $order['payment_method']; ?></td>
                        <td><?php echo $order['status']; ?></td>
                        <td><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></td>
                        <td><?php echo date('M d, Y h:i A', strtotime($order['updated_at'])); ?></td>
                        <td>
                            <?php if ($order['status'] != 'Delivered'): ?>
                                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#updateStatusModal<?php echo $order['order_id']; ?>">
                                    Update Status
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>

                    <!-- Update Status Modal -->
                    <div class="modal fade" id="updateStatusModal<?php echo $order['order_id']; ?>" tabindex="-1" aria-labelledby="updateStatusModalLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="updateStatusModalLabel">Update Order Status</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <form method="POST">
                                        <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
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
                                        <button type="submit" name="update_status" class="btn btn-primary">Update</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>