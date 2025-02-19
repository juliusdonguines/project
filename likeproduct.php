<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    echo "error";
    exit();
}

if (isset($_POST['product_id'])) {
    $product_id = intval($_POST['product_id']);
    
    $sql = "UPDATE products SET likes = likes + 1 WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        // Return the updated like count
        $result = $conn->query("SELECT likes FROM products WHERE id = $product_id");
        $row = $result->fetch_assoc();
        echo $row['likes'];
    } else {
        echo "error";
    }
    $stmt->close();
}
$conn->close();
?>
