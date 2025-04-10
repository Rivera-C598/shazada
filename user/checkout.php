<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as buyer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'buyer') {
    header("Location: ../login.php");
    exit();
}

// Redirect if no items are selected
if (!isset($_SESSION['selected_items']) || empty($_SESSION['selected_items'])) {
    header("Location: cart.php");
    exit();
}

// Fetch selected items from the cart
$selected_items = $_SESSION['selected_items'];
$cart_items = [];
$total_cart_price = 0;

foreach ($selected_items as $product_id) {
    if (isset($_SESSION['cart'][$product_id])) {
        // Check if the product exists in the database
        $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if ($product) {
            // Add the product to the cart_items array if it exists
            $cart_items[$product_id] = $_SESSION['cart'][$product_id];
            $total_cart_price += $_SESSION['cart'][$product_id]['price'] * $_SESSION['cart'][$product_id]['quantity'];
        } else {
            // Remove the product from the cart if it no longer exists
            unset($_SESSION['cart'][$product_id]);
            $_SESSION['error'] = "One or more products in your cart are no longer available. They have been removed.";
        }
    }
}

// Redirect if the cart is empty after validation
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// Fetch user's delivery addresses
$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM delivery_addresses WHERE user_id = ?");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll();

// Fetch logistics partners
$stmt = $conn->query("SELECT * FROM logistics_partners");
$logistics = $stmt->fetchAll();

// ==================== VALIDATION ====================
// Check if the user has delivery addresses
if (empty($addresses)) {
    $_SESSION['error'] = "Please add a delivery address before proceeding to checkout.";
    header("Location: cart.php");
    exit();
}

// Check if logistics partners are available
if (empty($logistics)) {
    $_SESSION['error'] = "No logistics partners available. Please contact support.";
    header("Location: cart.php");
    exit();
}
// ==================== END VALIDATION ====================

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $address_id = $_POST['address'];
    $logistics_id = $_POST['logistics'];
    $payment_method = $_POST['payment_method'];

    // Fetch selected address and logistics
    $stmt = $conn->prepare("SELECT * FROM delivery_addresses WHERE address_id = ?");
    $stmt->execute([$address_id]);
    $address = $stmt->fetch();

    $stmt = $conn->prepare("SELECT * FROM logistics_partners WHERE logistics_id = ?");
    $stmt->execute([$logistics_id]);
    $logistic = $stmt->fetch();

    // Calculate total price including shipping fee
    $total_price = $total_cart_price + $logistic['shipping_fee'];

    // Insert a single order for all items
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total_price, status, payment_method, logistics_partner, delivery_address) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$user_id, $total_price, 'To be packed', $payment_method, $logistic['name'], json_encode($address)]);
    $order_id = $conn->lastInsertId();

    // Insert each item into the order_items table
    foreach ($cart_items as $product_id => $item) {
        // Check if the product still exists before inserting
        $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        if ($product) {
            // Insert into order_items
            $stmt = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $product_id, $item['quantity'], $item['price']]);

            // Update product stock
            $new_stock = $product['stock'] - $item['quantity'];
            $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE product_id = ?");
            $stmt->execute([$new_stock, $product_id]);
        } else {
            // Handle the case where the product no longer exists
            $_SESSION['error'] = "One or more products in your order are no longer available. Please review your cart.";
            header("Location: cart.php");
            exit();
        }
    }

    // Store order details in the session for the confirmation page
    $_SESSION['order_details'] = [
        'order_id' => $order_id,
        'items' => $cart_items,
        'total_cart_price' => $total_cart_price,
        'shipping_fee' => $logistic['shipping_fee'],
        'total_price' => $total_price,
        'address' => $address['home_address'] . ', ' . $address['city'] . ', ' . $address['province'] . ', ' . $address['country'],
        'logistic' => $logistic['name'],
        'payment_method' => $payment_method
    ];

    // Simulate sending an email
    $subject = "Order Confirmation";
    $message = "Thank you for shopping with Shazada.com!\n\n";
    $message .= "Order Details:\n";
    foreach ($cart_items as $item) {
        $message .= "- " . $item['name'] . " (₱" . number_format($item['price'], 2) . " x " . $item['quantity'] . ")\n";
    }
    $message .= "Total Price: ₱" . number_format($total_price, 2) . "\n";
    $message .= "Shipping Address: " . $address['home_address'] . ', ' . $address['city'] . ', ' . $address['province'] . ', ' . $address['country'] . "\n";
    $message .= "Logistics Partner: " . $logistic['name'] . "\n";
    $message .= "Payment Method: " . $payment_method . "\n";

    // Uncomment to send real emails
    // mail($_SESSION['email'], $subject, $message);

    // Clear the selected items and cart
    unset($_SESSION['selected_items']);
    unset($_SESSION['cart']);

    // Redirect to order confirmation page
    header("Location: order_confirmation.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Shazada.com</title>
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
        <h1>Checkout</h1>
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-8">
                <h3>Order Summary</h3>
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
                        <?php foreach ($cart_items as $product_id => $item): ?>
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
                            <td>₱<?php echo number_format($total_cart_price, 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Shipping Fee</strong></td>
                            <td>₱<?php echo number_format($logistics[0]['shipping_fee'], 2); ?></td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-end"><strong>Total Price</strong></td>
                            <td>₱<?php echo number_format($total_cart_price + $logistics[0]['shipping_fee'], 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div class="col-md-4">
                <h3>Shipping Details</h3>
                <form method="POST">
                    <div class="mb-3">
                        <label for="address" class="form-label">Delivery Address</label>
                        <select class="form-select" id="address" name="address" required>
                            <?php foreach ($addresses as $address): ?>
                                <option value="<?php echo $address['address_id']; ?>">
                                    <?php echo htmlspecialchars($address['home_address'] . ', ' . $address['city'] . ', ' . $address['province'] . ', ' . $address['country']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="logistics" class="form-label">Logistics Partner</label>
                        <select class="form-select" id="logistics" name="logistics" required>
                            <?php foreach ($logistics as $logistic): ?>
                                <option value="<?php echo $logistic['logistics_id']; ?>">
                                    <?php echo htmlspecialchars($logistic['name'] . ' - ₱' . number_format($logistic['shipping_fee'], 2)); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="payment_method" class="form-label">Payment Method</label>
                        <select class="form-select" id="payment_method" name="payment_method" required>
                            <option value="COD">Cash on Delivery (COD)</option>
                            <option value="Online Payment">Online Payment (Gmaya.com)</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Place Order</button>
                </form>
            </div>
        </div>
    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>