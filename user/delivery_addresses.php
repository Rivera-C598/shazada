<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as buyer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'buyer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch user's delivery addresses
$stmt = $conn->prepare("SELECT * FROM delivery_addresses WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$user_id]);
$addresses = $stmt->fetchAll();

// Handle address deletion
if (isset($_GET['delete'])) {
    $address_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM delivery_addresses WHERE address_id = ? AND user_id = ?");
    $stmt->execute([$address_id, $user_id]);
    header("Location: delivery_addresses.php");
    exit();
}

// Handle form submission (add address)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $country = $_POST['country'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $home_address = $_POST['home_address'];
    $zip_code = $_POST['zip_code'];

    // Check if the user already has 3 addresses
    if (count($addresses) >= 3) {
        $error = "You can only have a maximum of 3 delivery addresses.";
    } else {
        $stmt = $conn->prepare("INSERT INTO delivery_addresses (user_id, country, province, city, home_address, zip_code) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $country, $province, $city, $home_address, $zip_code]);
        header("Location: delivery_addresses.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Addresses - Shazada.com</title>
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
        <h1>Delivery Addresses</h1>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <div class="mb-4">
            <h3>Add New Address</h3>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <label for="country" class="form-label">Country</label>
                        <input type="text" class="form-control" id="country" name="country" required>
                    </div>
                    <div class="col-md-6">
                        <label for="province" class="form-label">Province</label>
                        <input type="text" class="form-control" id="province" name="province" required>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-6">
                        <label for="city" class="form-label">City</label>
                        <input type="text" class="form-control" id="city" name="city" required>
                    </div>
                    <div class="col-md-6">
                        <label for="zip_code" class="form-label">Zip Code</label>
                        <input type="text" class="form-control" id="zip_code" name="zip_code" required>
                    </div>
                </div>
                <div class="mt-3">
                    <label for="home_address" class="form-label">Home Address</label>
                    <textarea class="form-control" id="home_address" name="home_address" rows="3" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary mt-3">Add Address</button>
            </form>
        </div>

        <h3>Your Addresses</h3>
        <?php if (empty($addresses)): ?>
            <div class="alert alert-info">No delivery addresses found.</div>
        <?php else: ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Country</th>
                        <th>Province</th>
                        <th>City</th>
                        <th>Home Address</th>
                        <th>Zip Code</th>
                        <th>Added On</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($addresses as $address): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($address['country']); ?></td>
                            <td><?php echo htmlspecialchars($address['province']); ?></td>
                            <td><?php echo htmlspecialchars($address['city']); ?></td>
                            <td><?php echo htmlspecialchars($address['home_address']); ?></td>
                            <td><?php echo htmlspecialchars($address['zip_code']); ?></td>
                            <td><?php echo date('M d, Y h:i A', strtotime($address['created_at'])); ?></td>
                            <td>
                                <a href="edit_address.php?id=<?php echo $address['address_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="delivery_addresses.php?delete=<?php echo $address['address_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this address?')">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>