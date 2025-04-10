<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Handle logistics partner deletion
if (isset($_GET['delete'])) {
    $logistics_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM logistics_partners WHERE logistics_id = ?");
    $stmt->execute([$logistics_id]);
    header("Location: logistics.php");
    exit();
}

// Handle form submission (add logistics partner)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $shipping_fee = $_POST['shipping_fee'];

    if (!empty($name) && !empty($shipping_fee)) {
        $stmt = $conn->prepare("INSERT INTO logistics_partners (name, shipping_fee) VALUES (?, ?)");
        $stmt->execute([$name, $shipping_fee]);
        header("Location: logistics.php");
        exit();
    } else {
        $error = "Please fill in all fields.";
    }
}

// Fetch all logistics partners
$stmt = $conn->query("SELECT * FROM logistics_partners ORDER BY created_at DESC");
$logistics = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logistics - Shazada.com</title>
    <link href="../bootstrap/css/bootstrap.min.css" rel="stylesheet">
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
        <h1>Logistics Partners</h1>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <div class="mb-4">
            <h3>Add New Logistics Partner</h3>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <label for="name" class="form-label">Name</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="col-md-6">
                        <label for="shipping_fee" class="form-label">Shipping Fee</label>
                        <input type="number" step="0.01" class="form-control" id="shipping_fee" name="shipping_fee" required>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Add Partner</button>
            </form>
        </div>

        <h3>Current Logistics Partners</h3>
        <?php if (empty($logistics)): ?>
            <div class="alert alert-info">No logistics partners found.</div>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Shipping Fee</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logistics as $logistic): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($logistic['name']); ?></td>
                            <td>₱<?php echo number_format($logistic['shipping_fee'], 2); ?></td>
                            <td>
                                <a href="logistics.php?delete=<?php echo $logistic['logistics_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this logistics partner?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script src="../bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>