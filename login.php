<?php 
session_start();
include('db.php');

// Redirect if already logged in
if (isset($_SESSION['username'])) {
    header("Location: home.php");
    exit();
}

$error = ""; // Store error messages

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // Prepared statement to prevent SQL Injection
    $sql = "SELECT id, username, password FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if user exists
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify hashed password
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_id'] = $user['id'];
            header("Location: home.php");
            exit();
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
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
    <title>Login</title>
    <link href="style_main.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <h1>Login</h1>
        <?php if (!empty($error)): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="post">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>
            
            <button type="submit">Login</button>
        </form>
        <p>New account? <a href="register.php">Register here</a>.</p>
    </div>
</body>
</html>
