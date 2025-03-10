<?php
session_start();
include '../includes/db.php';

// Redirect if not logged in as buyer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'buyer') {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch address details
if (isset($_GET['id'])) {
    $address_id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM delivery_addresses WHERE address_id = ? AND user_id = ?");
    $stmt->execute([$address_id, $user_id]);
    $address = $stmt->fetch();

    if (!$address) {
        header("Location: delivery_addresses.php");
        exit();
    }
} else {
    header("Location: delivery_addresses.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $country = $_POST['country'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $home_address = $_POST['home_address'];
    $zip_code = $_POST['zip_code'];

    $stmt = $conn->prepare("UPDATE delivery_addresses SET country = ?, province = ?, city = ?, home_address = ?, zip_code = ? WHERE address_id = ?");
    $stmt->execute([$country, $province, $city, $home_address, $zip_code, $address_id]);
    header("Location: delivery_addresses.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Address - Shazada.com</title>
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
        <h1>Edit Address</h1>
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    <label for="country" class="form-label">Country</label>
                    <input type="text" class="form-control" id="country" name="country" value="<?php echo htmlspecialchars($address['country']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="province" class="form-label">Province</label>
                    <input type="text" class="form-control" id="province" name="province" value="<?php echo htmlspecialchars($address['province']); ?>" required>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-6">
                    <label for="city" class="form-label">City</label>
                    <input type="text" class="form-control" id="city" name="city" value="<?php echo htmlspecialchars($address['city']); ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="zip_code" class="form-label">Zip Code</label>
                    <input type="text" class="form-control" id="zip_code" name="zip_code" value="<?php echo htmlspecialchars($address['zip_code']); ?>" required>
                </div>
            </div>
            <div class="mt-3">
                <label for="home_address" class="form-label">Home Address</label>
                <textarea class="form-control" id="home_address" name="home_address" rows="3" required><?php echo htmlspecialchars($address['home_address']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary mt-3">Update Address</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>