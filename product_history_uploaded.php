<?php
session_start();
include('db.php');

// Ensure user is logged in
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Handle product deletion
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['delete_id'])) {
    $delete_id = $_POST['delete_id'];

    // Delete product from database
    $stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND username = ?");
    $stmt->bind_param("is", $delete_id, $username);
    
    if ($stmt->execute()) {
        $message = "Product deleted successfully!";
    } else {
        $message = "Failed to delete product.";
    }
}

// Fetch user-uploaded products
$sql = "SELECT id, item, price, address, details, photo, created_at FROM products WHERE username = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload History</title>
    <link href="style_main.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Upload History</h1>

        <?php if (isset($message)): ?>
            <p class="success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <table border="1">
            <tr>
                <th>Photo</th>
                <th>Item</th>
                <th>Price</th>
                <th>Address</th>
                <th>Details</th>
                <th>Uploaded On</th>
                <th>Action</th>
            </tr>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><img src="<?php echo htmlspecialchars($row['photo']); ?>" width="80" height="80" alt="Product Image"></td>
                        <td><?php echo htmlspecialchars($row['item']); ?></td>
                        <td>₱<?php echo number_format($row['price'], 2); ?></td>
                        <td><?php echo htmlspecialchars($row['address']); ?></td>
                        <td><?php echo htmlspecialchars($row['details']); ?></td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td>
                            <form method="post" onsubmit="return confirm('Are you sure you want to delete this product?');">
                                <input type="hidden" name="delete_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" class="delete-btn">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="7">No products uploaded yet.</td></tr>
            <?php endif; ?>
        </table>

        <br>
        <a href="home.php">Back to Home</a>
    </div>
</body>
</html>
