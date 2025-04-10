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
    $stmt = $conn->query("SELECT orders.*, users.username FROM orders 
                          JOIN users ON orders.user_id = users.user_id 
                          WHERE orders.status = 'Delivered'
                          ORDER BY orders.created_at DESC");
} else {
    $stmt = $conn->query("SELECT orders.*, users.username FROM orders 
                          JOIN users ON orders.user_id = users.user_id 
                          WHERE orders.status != 'Delivered'
                          ORDER BY orders.created_at DESC");
}
$orders = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders - Shazada.com</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .clickable-row {
            cursor: pointer;
        }
    </style>
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
                    <th>Total Price</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Order Date</th>
                    <th>Updated At</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order): ?>
                    <tr class="clickable-row" onclick="window.location.href='order_details.php?order_id=<?php echo $order['order_id']; ?>'">
                        <td><?php echo $order['order_id']; ?></td>
                        <td><?php echo htmlspecialchars($order['username']); ?></td>
                        <td>₱<?php echo number_format($order['total_price'], 2); ?></td>
                        <td><?php echo $order['payment_method']; ?></td>
                        <td><?php echo $order['status']; ?></td>
                        <td><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></td>
                        <td><?php echo date('M d, Y h:i A', strtotime($order['updated_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>