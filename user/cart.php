<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as buyer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'buyer') {
    header("Location: ../login.php");
    exit();
}

// Handle item removal
if (isset($_GET['remove'])) {
    $product_id = $_GET['remove'];
    unset($_SESSION['cart'][$product_id]);
    header("Location: cart.php");
    exit();
}

// Handle quantity update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        if ($quantity <= 0) {
            unset($_SESSION['cart'][$product_id]);
        } else {
            // Fetch the product stock from the database
            $stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
            $stmt->execute([$product_id]);
            $product = $stmt->fetch();

            // Ensure the quantity does not exceed the available stock
            if ($product && $quantity <= $product['stock']) {
                $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            } else {
                $_SESSION['error'] = "Quantity for one or more items exceeds available stock.";
            }
        }
    }
    header("Location: cart.php");
    exit();
}

// Handle checkout
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    if (isset($_POST['selected_items']) && !empty($_POST['selected_items'])) {
        // Store selected items in the session for checkout.php
        $_SESSION['selected_items'] = $_POST['selected_items'];
        header("Location: checkout.php");
        exit();
    } else {
        // No items selected, show an error message
        $_SESSION['error'] = "Please select at least one item to proceed to checkout.";
        header("Location: cart.php");
        exit();
    }
}

// Handle delete selected items
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_selected'])) {
    if (isset($_POST['selected_items']) && !empty($_POST['selected_items'])) {
        foreach ($_POST['selected_items'] as $product_id) {
            unset($_SESSION['cart'][$product_id]);
        }
        $_SESSION['success'] = "Selected items have been removed from the cart.";
    } else {
        $_SESSION['error'] = "No items selected for deletion.";
    }
    header("Location: cart.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - Shazada.com</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .select-all-checkbox {
            margin-right: 10px;
        }
    </style>
    <script>
        // JavaScript to handle "Select All" functionality
        function selectAll() {
            const checkboxes = document.querySelectorAll('input[name="selected_items[]"]');
            const selectAllCheckbox = document.getElementById('select-all');
            checkboxes.forEach(checkbox => {
                checkbox.checked = selectAllCheckbox.checked;
            });
        }
    </script>
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
        <h1>Your Cart</h1>
        <?php if (empty($_SESSION['cart'])): ?>
            <div class="alert alert-info">Your cart is empty.</div>
        <?php else: ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
            <?php endif; ?>
            <form method="POST">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" id="select-all" class="form-check-input select-all-checkbox" onclick="selectAll()">
                                <label for="select-all">Select All</label>
                            </th>
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
                            // Fetch the product stock from the database
                            $stmt = $conn->prepare("SELECT stock FROM products WHERE product_id = ?");
                            $stmt->execute([$product_id]);
                            $product = $stmt->fetch();
                            $max_quantity = $product ? $product['stock'] : 0;

                            $total_price = $item['price'] * $item['quantity'];
                            $total_cart_price += $total_price;
                        ?>
                            <tr>
                                <td>
                                    <input type="checkbox" name="selected_items[]" value="<?php echo $product_id; ?>" class="form-check-input">
                                </td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <input type="number" name="quantity[<?php echo $product_id; ?>]" value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo $max_quantity; ?>" class="form-control" style="width: 80px;">
                                </td>
                                <td>₱<?php echo number_format($total_price, 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-end"><strong>Total Cart Price</strong></td>
                            <td>₱<?php echo number_format($total_cart_price, 2); ?></td>
                        </tr>
                    </tfoot>
                </table>
                <div class="d-flex justify-content-between">
                    <button type="submit" name="update_cart" class="btn btn-warning">Update Cart</button>
                    <div>
                        <button type="submit" name="delete_selected" class="btn btn-danger">Delete Selected</button>
                        <button type="submit" name="checkout" class="btn btn-primary">Proceed to Checkout</button>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>