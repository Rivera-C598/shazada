<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as buyer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'buyer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's concerns
$stmt = $conn->prepare("SELECT * FROM customer_concerns WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$concerns = $stmt->fetchAll();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = $_POST['message'];

    if (!empty($message)) {
        $stmt = $conn->prepare("INSERT INTO customer_concerns (user_id, message) VALUES (?, ?)");
        $stmt->execute([$user_id, $message]);
        header("Location: concerns.php");
        exit();
    } else {
        $error = "Message cannot be empty.";
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
        <h1>Customer Concerns</h1>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="POST" class="mb-4">
            <div class="mb-3">
                <label for="message" class="form-label">Your Message</label>
                <textarea class="form-control" id="message" name="message" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Send Message</button>
        </form>

        <h3>Your Messages</h3>
        <?php if (empty($concerns)): ?>
            <div class="alert alert-info">You have no messages yet.</div>
        <?php else: ?>
            <div class="list-group">
                <?php foreach ($concerns as $concern): ?>
                    <div class="list-group-item">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="mb-1"><strong>You:</strong> <?php echo htmlspecialchars($concern['message']); ?></p>
                                <small class="text-muted"><?php echo date('M d, Y h:i A', strtotime($concern['created_at'])); ?></small>
                            </div>
                            <?php if (!empty($concern['admin_reply'])): ?>
                                <div class="text-end">
                                    <p class="mb-1"><strong>Admin:</strong> <?php echo htmlspecialchars($concern['admin_reply']); ?></p>
                                    <small class="text-muted">Replied on <?php echo date('M d, Y h:i A', strtotime($concern['updated_at'])); ?></small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>