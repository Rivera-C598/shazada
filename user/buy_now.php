<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as buyer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'buyer') {
    header("Location: ../login.php");
    exit();
}

// Fetch product details
if (isset($_GET['id'])) {
    $product_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
    $stmt->execute([$product_id]);
    $product = $stmt->fetch();

    if (!$product) {
        header("Location: shopping.php");
        exit();
    }
} else {
    header("Location: shopping.php");
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
    $quantity = $_POST['quantity'];
    $address_id = $_POST['address'];
    $logistics_id = $_POST['logistics'];
    $payment_method = $_POST['payment_method'];

    // Validate quantity
    if ($quantity > $product['stock']) {
        $error = "Quantity exceeds available stock.";
    } else {
        // Fetch selected address and logistics
        $stmt = $conn->prepare("SELECT * FROM delivery_addresses WHERE address_id = ?");
        $stmt->execute([$address_id]);
        $address = $stmt->fetch();

        $stmt = $conn->prepare("SELECT * FROM logistics_partners WHERE logistics_id = ?");
        $stmt->execute([$logistics_id]);
        $logistic = $stmt->fetch();

        // Calculate total price
        $total_price = $quantity * $product['price'] + $logistic['shipping_fee'];

        // Insert order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, quantity, total_price, status, payment_method, logistics_partner, delivery_address) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $product_id, $quantity, $total_price, 'To be packed', $payment_method, $logistic['name'], json_encode($address)]);

        // Update product stock
        $new_stock = $product['stock'] - $quantity;
        $stmt = $conn->prepare("UPDATE products SET stock = ? WHERE product_id = ?");
        $stmt->execute([$new_stock, $product_id]);

        // Redirect to orders page
        header("Location: orders.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Now - Shazada.com</title>
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
        <h1>Buy Now</h1>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <img src="<?php echo json_decode($product['images'])[0]; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
                        <p class="card-text">₱<?php echo number_format($product['price'], 2); ?></p>
                        <p class="card-text">Stock: <?php echo $product['stock']; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <form method="POST">
                    <div class="mb-3">
                        <label for="quantity" class="form-label">Quantity</label>
                        <input type="number" class="form-control" id="quantity" name="quantity" min="1" max="<?php echo $product['stock']; ?>" required>
                    </div>
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
                    <button type="submit" class="btn btn-primary">Place Order</button>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>