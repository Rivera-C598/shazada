<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as buyer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'buyer') {
    header("Location: ../login.php");
    exit();
}

// Redirect if no order details are available
if (!isset($_SESSION['order_details'])) {
    header("Location: cart.php");
    exit();
}

// Fetch order details from the session
$order_details = $_SESSION['order_details'];
unset($_SESSION['order_details']); // Clear the session data after displaying
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Shazada.com</title>
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
        <h1>Order Confirmation</h1>
        <div class="alert alert-success">
            <h4 class="alert-heading">Thank you for shopping with Shazada.com!</h4>
            <p>We've sent an email to <strong><?php echo htmlspecialchars($_SESSION['email']); ?></strong> with your order details.</p>
        </div>
        <div class="row">
            <div class="col-md-8">
                <h3>Order Details</h3>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($order_details['items'] as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total Cart Price</strong></td>
                            <td>₱<?php echo number_format($order_details['total_cart_price'], 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Shipping Fee</strong></td>
                            <td>₱<?php echo number_format($order_details['shipping_fee'], 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total Price</strong></td>
                            <td>₱<?php echo number_format($order_details['total_price'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="col-md-4">
                <h3>Shipping Details</h3>
                <p><strong>Address:</strong> <?php echo htmlspecialchars($order_details['address']); ?></p>
                <p><strong>Logistics Partner:</strong> <?php echo htmlspecialchars($order_details['logistic']); ?></p>
                <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($order_details['payment_method']); ?></p>
            </div>
        </div>
        <div class="mt-4">
            <a href="shopping.php" class="btn btn-primary">Continue Shopping</a>
        </div>
    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>