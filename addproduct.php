<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $item = mysqli_real_escape_string($conn, $_POST['item']);
    $price = mysqli_real_escape_string($conn, $_POST['price']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $details = mysqli_real_escape_string($conn, $_POST['details']);

    $sql = "INSERT INTO products (username, item, price, address, details) VALUES ('$username', '$item', '$price', '$address', '$details')";

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Product added successfully!'); window.location.href='home.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>Add Product</h2>
    <form action="addproduct.php" method="POST" enctype="multipart/form-data">
    <label for="item">Item Name:</label>
    <input type="text" id="item" name="item" required>

    <label for="price">Price:</label>
    <input type="number" id="price" name="price" required>

    <label for="address">Address:</label>
    <input type="text" id="address" name="address" required>

    <label for="details">More Details:</label>
    <textarea id="details" name="details" rows="4" required></textarea>

    <label for="photo">Upload Photo:</label>
    <input type="file" id="photo" name="photo" accept="image/*" required>

    <button type="submit">Post product</button>
</form>

</div>

</body>
</html>
