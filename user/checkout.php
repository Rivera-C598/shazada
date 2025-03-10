<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as buyer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'buyer') {
    header("Location: ../login.php");
    exit();
}

// Redirect if cart is empty
if (empty($_SESSION['cart'])) {
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

    // Insert orders for each item in the cart
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $total_price = $item['price'] * $item['quantity'] + $logistic['shipping_fee']; // Include shipping fee

        $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, quantity, total_price, status, payment_method, logistics_partner, delivery_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $item['quantity'], $total_price, 'To be packed', $payment_method, $logistic['name'], json_encode($address)]);

        // Update product stock
        $stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();

        $new_stock = $product['stock'] - $item['quantity'];
        $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE product_id = ?");
        $stmt->execute([$new_stock, $product_id]);
    }

    // Clear the cart
    unset($_SESSION['cart']);

    // Redirect to orders page
    header("Location: orders.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Shazada.com</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
                        <?php
                        $total_cart_price = 0;
                        foreach ($_SESSION['cart'] as $product_id => $item):
                            $total_price = $item['price'] * $item['quantity'];
                            $total_cart_price += $total_price;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td>₱<?php echo number_format($total_price, 2); ?></td>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>