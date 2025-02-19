<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];

// Fetch user details
$stmt = $conn->prepare("SELECT first_name, last_name, email, birthday, phone, address, province, city, barangay, profile_picture FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $birthday = $_POST['birthday'];
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $province = $_POST['province'];
    $city = $_POST['city'];
    $barangay = $_POST['barangay'];

    // Handle profile picture upload
    if (!empty($_FILES["profile_picture"]["name"])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image type
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (in_array($imageFileType, $allowed_types)) {
            move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
            $profile_picture = $target_file;

            // Update profile picture in DB
            $stmt = $conn->prepare("UPDATE users SET profile_picture = ? WHERE username = ?");
            $stmt->bind_param("ss", $profile_picture, $username);
            $stmt->execute();
        }
    }

    // Update user details
    $stmt = $conn->prepare("UPDATE users SET first_name = ?, last_name = ?, email = ?, birthday = ?, phone = ?, address = ?, province = ?, city = ?, barangay = ? WHERE username = ?");
    $stmt->bind_param("ssssssssss", $first_name, $last_name, $email, $birthday, $phone, $address, $province, $city, $barangay, $username);

    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
        // Refresh data
        $stmt = $conn->prepare("SELECT first_name, last_name, email, birthday, phone, address, province, city, barangay, profile_picture FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
    } else {
        $error = "Profile update failed!";
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="style_main.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Edit Profile</h1>
        <?php if (isset($success)): ?>
            <p class="success"><?php echo htmlspecialchars($success); ?></p>
        <?php endif; ?>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        
        <form method="post" enctype="multipart/form-data">
            <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? 'default.png'); ?>" width="100" height="100" alt="Profile Picture"><br>

            <label for="profile_picture">Change Profile Picture:</label>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/*"><br>

            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required><br>

            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>

            <label for="birthday">Birthday:</label>
            <input type="date" id="birthday" name="birthday" value="<?php echo htmlspecialchars($user['birthday']); ?>" required><br>

            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required><br>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" value="<?php echo htmlspecialchars($user['address']); ?>" required><br>

            <label for="province">Province:</label>
            <input type="text" id="province" name="province" value="<?php echo htmlspecialchars($user['province']); ?>" required><br>

            <label for="city">City:</label>
            <input type="text" id="city" name="city" value="<?php echo htmlspecialchars($user['city']); ?>" required><br>

            <label for="barangay">Barangay:</label>
            <input type="text" id="barangay" name="barangay" value="<?php echo htmlspecialchars($user['barangay']); ?>" required><br>

            <button type="submit">Update Profile</button>
        </form>
        
        <p><a href="userprofile.php">Back to Profile</a></p>
    </div>
</body>
</html>
