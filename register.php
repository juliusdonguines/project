<?php 
session_start();
include('db.php');

if (isset($_SESSION['username'])) {
    header("Location: home.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
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
    $profile_picture = null;
    if (!empty($_FILES["profile_picture"]["name"])) {
        $target_dir = "uploads/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Create uploads folder if it doesn't exist
        }
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validate image type
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        if (in_array($imageFileType, $allowed_types)) {
            move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file);
            $profile_picture = $target_file;
        }
    }

    // Check if username exists
    $stmt = $conn->prepare("SELECT * FROM users WHERE username=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $error = "Username already exists";
    } else {
        // Insert user into database
        $stmt = $conn->prepare("INSERT INTO users 
            (username, password, first_name, last_name, email, birthday, phone, address, province, city, barangay, profile_picture) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt->bind_param("ssssssssssss", $username, $hashedPassword, $first_name, $last_name, $email, $birthday, $phone, $address, $province, $city, $barangay, $profile_picture);
        
        if ($stmt->execute()) {
            $_SESSION['username'] = $username;
            header("Location: home.php");
            exit();
        } else {
            $error = "Registration failed";
        }
    }
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link href="style_main.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Register</h1>
        <?php if (isset($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="post" enctype="multipart/form-data">
            <label for="first_name">First Name:</label>
            <input type="text" id="first_name" name="first_name" required><br>

            <label for="last_name">Last Name:</label>
            <input type="text" id="last_name" name="last_name" required><br>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required><br>

            <label for="birthday">Birthday:</label>
            <input type="date" id="birthday" name="birthday" required><br>

            <label for="phone">Phone Number:</label>
            <input type="tel" id="phone" name="phone" required><br>

            <label for="address">Address:</label>
            <input type="text" id="address" name="address" required><br>

            <label for="province">Province:</label>
            <input type="text" id="province" name="province" required><br>

            <label for="city">City:</label>
            <input type="text" id="city" name="city" required><br>

            <label for="barangay">Barangay:</label>
            <input type="text" id="barangay" name="barangay" required><br>

            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required><br>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required><br>

            <label for="profile_picture">Profile Picture:</label>
            <input type="file" id="profile_picture" name="profile_picture" accept="image/*"><br>

            <button type="submit">Register</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a>.</p>
    </div>
</body>
</html>
