<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$message = "";

// Fetch user details
$sql = "SELECT username, email, first_name, last_name, address, barangay, city, phone, profile_picture, password FROM users WHERE username = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    die("Query Error: " . $conn->error);
}

$user = $result->fetch_assoc();

// Handle password change
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Verify current password
    if (!password_verify($current_password, $user['password'])) {
        $message = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $message = "New passwords do not match.";
    } else {
        // Update new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_sql = "UPDATE users SET password = ? WHERE username = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("ss", $hashed_password, $username);

        if ($update_stmt->execute()) {
            $message = "Password updated successfully!";
        } else {
            $message = "Error updating password.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="container">
    <h2>My Profile</h2>
    <?php if ($user): ?>
        <div class="profile-card">
            <img src="<?php echo !empty($user['profile_picture']) ? $user['profile_picture'] : 'default-avatar.png'; ?>" class="profile-pic" alt="Profile Picture">
            <h3><?php echo htmlspecialchars($user['first_name'] . " " . $user['last_name']); ?></h3>
            <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Address:</strong> <?php echo htmlspecialchars($user['address']); ?></p>
            <p><strong>Barangay:</strong> <?php echo htmlspecialchars($user['barangay']); ?></p>
            <p><strong>City:</strong> <?php echo htmlspecialchars($user['city']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
        </div>
    <?php else: ?>
        <p>Error: User not found.</p>
    <?php endif; ?>
</div>

<!-- Change Password Form -->
<div class="container">
    <h2>Change Password</h2>
    <?php if (!empty($message)): ?>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>
    <form method="POST">
        <label for="current_password">Current Password:</label>
        <input type="password" id="current_password" name="current_password" required><br>

        <label for="new_password">New Password:</label>
        <input type="password" id="new_password" name="new_password" required><br>

        <label for="confirm_password">Confirm New Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required><br>

        <button type="submit">Update Password</button>
    </form>
</div>

<p><a href="edit_profile.php">Edit your profile</a></p>
<p><a href="home.php">Back to home</a></p>
<p><a href="product_history_uploaded.php">Product history upload</a></p>

</body>
</html>

