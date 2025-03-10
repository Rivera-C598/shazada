<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle filter
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';
$query = "SELECT transactions.*, users.username, orders.product_id, products.name AS product_name 
          FROM transactions 
          JOIN orders ON transactions.order_id = orders.order_id 
          JOIN users ON orders.user_id = users.user_id 
          JOIN products ON orders.product_id = products.product_id";

if ($filter == 'cod') {
    $query .= " WHERE transactions.payment_method = 'COD'";
} elseif ($filter == 'online') {
    $query .= " WHERE transactions.payment_method = 'Online Payment'";
}

$stmt = $conn->query($query);
$transactions = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Shazada.com</title>
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
        <h1>Transactions</h1>
        <div class="mb-3">
            <form method="GET">
                <label for="filter" class="form-label">Filter by Payment Method</label>
                <select class="form-select" id="filter" name="filter" onchange="this.form.submit()">
                    <option value="all" <?php echo $filter == 'all' ? 'selected' : ''; ?>>All</option>
                    <option value="cod" <?php echo $filter == 'cod' ? 'selected' : ''; ?>>Cash on Delivery (COD)</option>
                    <option value="online" <?php echo $filter == 'online' ? 'selected' : ''; ?>>Online Payment</option>
                </select>
            </form>
        </div>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Transaction ID</th>
                    <th>Customer</th>
                    <th>Product</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Transaction Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $transaction): ?>
                    <tr>
                        <td><?php echo $transaction['transaction_id']; ?></td>
                        <td><?php echo htmlspecialchars($transaction['username']); ?></td>
                        <td><?php echo htmlspecialchars($transaction['product_name']); ?></td>
                        <td>₱<?php echo number_format($transaction['amount'], 2); ?></td>
                        <td><?php echo $transaction['payment_method']; ?></td>
                        <td><?php echo $transaction['status']; ?></td>
                        <td><?php echo date('M d, Y h:i A', strtotime($transaction['created_at'])); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>