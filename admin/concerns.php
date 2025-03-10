<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

// Fetch all concerns
$stmt = $conn->query("SELECT customer_concerns.*, users.username FROM customer_concerns 
                      JOIN users ON customer_concerns.user_id = users.user_id 
                      ORDER BY customer_concerns.created_at DESC");
$concerns = $stmt->fetchAll();

// Handle form submission (admin reply)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $concern_id = $_POST['concern_id'];
    $admin_reply = $_POST['admin_reply'];

    if (!empty($admin_reply)) {
        // Update the concern with the admin's reply and set the updated_at timestamp
        $stmt = $conn->prepare("UPDATE customer_concerns SET admin_reply = ?, updated_at = NOW() WHERE concern_id = ?");
        $stmt->execute([$admin_reply, $concern_id]);
        header("Location: concerns.php");
        exit();
    } else {
        $error = "Reply cannot be empty.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Concerns - Shazada.com</title>
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
        <h1>Customer Concerns</h1>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <div class="list-group">
            <?php foreach ($concerns as $concern): ?>
                <div class="list-group-item">
                    <div class="d-flex justify-content-between">
                        <div>
                            <p class="mb-1"><strong><?php echo htmlspecialchars($concern['username']); ?>:</strong> <?php echo htmlspecialchars($concern['message']); ?></p>
                            <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($concern['created_at'])); ?></small>
                        </div>
                        <?php if (!empty($concern['admin_reply'])): ?>
                            <div class="text-end">
                                <p class="mb-1"><strong>Admin:</strong> <?php echo htmlspecialchars($concern['admin_reply']); ?></p>
                                <small class="text-muted">Replied on <?php echo date('M d, Y h:i A', strtotime($concern['updated_at'])); ?></small>
                            </div>
                        <?php else: ?>
                            <form method="POST" class="text-end">
                                <input type="hidden" name="concern_id" value="<?php echo $concern['concern_id']; ?>">
                                <textarea class="form-control mb-2" name="admin_reply" rows="2" placeholder="Type your reply here..." required></textarea>
                                <button type="submit" class="btn btn-primary btn-sm">Reply</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>