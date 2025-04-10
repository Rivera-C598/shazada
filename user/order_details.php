<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as buyer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'buyer') {
    header("Location: ../login.php");
    exit();
}

// Fetch order details
if (isset($_GET['id'])) {
    $order_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order = $stmt->fetch();

    if (!$order || $order['user_id'] != $_SESSION['user_id']) {
        header("Location: orders.php");
        exit();
    }

    // Fetch product details
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$order['product_id']]);
    $product = $stmt->fetch();

    // Decode delivery address
    $delivery_address = json_decode($order['delivery_address'], true);
} else {
    header("Location: orders.php");
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
        <h1>Order Details</h1>
        <div class="row">
            <div class="col-md-6">
                <h3>Product Details</h3>
                <div class="card">
                    <img src="<?php echo json_decode($product['images'])[0]; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                        <p class="card-text">₱<?php echo number_format($product['price'], 2); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <h3>Order Information</h3>
                <table class="table table-bordered">
                    <tr>
                        <th>Order ID</th>
                        <td><?php echo $order['order_id']; ?></td>
                    </tr>
                    <tr>
                        <th>Quantity</th>
                        <td><?php echo $order['quantity']; ?></td>
                    </tr>
                    <tr>
                        <th>Total Price</th>
                        <td>₱<?php echo number_format($order['total_price'], 2); ?></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td><?php echo $order['status']; ?></td>
                    </tr>
                    <tr>
                        <th>Payment Method</th>
                        <td><?php echo $order['payment_method']; ?></td>
                    </tr>
                    <tr>
                        <th>Logistics Partner</th>
                        <td><?php echo $order['logistics_partner']; ?></td>
                    </tr>
                    <tr>
                        <th>Delivery Address</th>
                        <td>
                            <?php echo htmlspecialchars($delivery_address['home_address'] . ', ' . $delivery_address['city'] . ', ' . $delivery_address['province'] . ', ' . $delivery_address['country']); ?>
                        </td>
                    </tr>
                    <tr>
                        <th>Order Date</th>
                        <td><?php echo date('M d, Y h:i A', strtotime($order['created_at'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>