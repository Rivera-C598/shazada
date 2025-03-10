<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as buyer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'buyer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's payment method
$stmt = $conn->prepare("SELECT * FROM payment_methods WHERE user_id = ?");
$stmt->execute([$user_id]);
$payment_method = $stmt->fetch();

// Handle form submission (link/unlink payment method)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['link'])) {
        // Link Gmaya.com account
        $bank_account = $_POST['bank_account'];
        $stmt = $conn->prepare("INSERT INTO payment_methods (user_id, bank_account) VALUES (?, ?)");
        $stmt->execute([$user_id, $bank_account]);
    } elseif (isset($_POST['unlink'])) {
        // Unlink Gmaya.com account
        $stmt = $conn->prepare("DELETE FROM payment_methods WHERE user_id = ?");
        $stmt->execute([$user_id]);
    }
    header("Location: payment_methods.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Methods - Shazada.com</title>
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
        <h1>Payment Methods</h1>
        <?php if ($payment_method): ?>
            <div class="alert alert-info">
                <p>Your current payment method: <strong>Gmaya.com - <?php echo htmlspecialchars($payment_method['bank_account']); ?></strong></p>
                <form method="POST">
                    <button type="submit" name="unlink" class="btn btn-danger">Unlink Account</button>
                </form>
            </div>
        <?php else: ?>
            <div class="alert alert-warning">
                <p>No payment method linked.</p>
                <form method="POST">
                    <div class="mb-3">
                        <label for="bank_account" class="form-label">Gmaya.com Bank Account</label>
                        <input type="text" class="form-control" id="bank_account" name="bank_account" placeholder="Enter your Gmaya.com account number" required>
                    </div>
                    <button type="submit" name="link" class="btn btn-primary">Link Account</button>
                </form>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>