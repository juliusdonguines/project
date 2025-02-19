<?php
session_start();
include('db.php');

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$selectedUser = '';

if (isset($_GET['user'])) {
    $selectedUser = mysqli_real_escape_string($conn, $_GET['user']);
    $showChatBox = true;
} else {
    $showChatBox = false;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real-time Chat</title>
    <link href="style.css" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="header">
        <h1>My Account</h1>
        <a href="logout.php" class="logout">Logout</a>
        <a href="userprofile.php" class="profile">Profile</a>
        <a href="edit_profile.php" class="settings">Settings</a>
    </div>

    <div class="account-info">
        <div class="welcome">
            <h2>Welcome, <?php echo ucfirst($username); ?>!</h2>
        </div>

        <div class="user-list">
            <h2>Select a User to Chat With:</h2>
            <ul>
                <?php 
                $sql = "SELECT username FROM users WHERE username != ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("s", $username);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    $user = ucfirst($row['username']);
                    echo "<li><a href='home.php?user=" . htmlspecialchars($row['username']) . "'>$user</a></li>";
                }
                ?>
            </ul>
        </div>
    </div>

    <?php if ($showChatBox): ?>
    <div class="chat-box" id="chat-box">
        <div class="chat-box-header">
            <h2><?php echo ucfirst($selectedUser); ?></h2>
            <button class="close-btn" onclick="closeChat()">✖</button>
        </div>
        <div class="chat-box-body" id="chat-box-body">
            <!-- Chat messages will be loaded here -->
        </div>
        <form class="chat-form" id="chat-form">
            <input type="hidden" id="sender" value="<?php echo $username; ?>">
            <input type="hidden" id="receiver" value="<?php echo $selectedUser; ?>">
            <input type="text" id="message" placeholder="Type your message..." required>
            <button type="submit">Send</button>
        </form>
    </div>
    <?php endif; ?>
</div>

<div class="add-product">
    <a href="addproduct.php" class="btn">Add Product</a>
</div>

<div class="product-list">
    <h2>Preloved Items</h2>
    <table border="1">
        <tr>
            <th>Photo</th>
            <th>Item</th>
            <th>Price</th>
            <th>Address</th>
            <th>Details</th>
            <th>Posted By</th>
            <th>Actions</th>
        </tr>
        <?php
        $sql = "SELECT p.id, p.item, p.price, p.address, p.details, p.photo, p.likes, u.username 
                FROM products p 
                JOIN users u ON p.username = u.username
                ORDER BY p.created_at DESC";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $postedBy = htmlspecialchars($row['username']);
                echo "<tr>";
                echo "<td><img src='" . htmlspecialchars($row['photo']) . "' width='100' height='100' alt='Product Image' style='border-radius: 10px;'></td>";
                echo "<td>" . htmlspecialchars($row['item']) . "</td>";
                echo "<td>₱" . number_format($row['price'], 2) . "</td>";
                echo "<td>" . htmlspecialchars($row['address']) . "</td>";
                echo "<td>" . htmlspecialchars($row['details']) . "</td>";
                echo "<td>" . $postedBy . "</td>";
                echo "<td>
                        <button class='like-btn' data-id='" . $row['id'] . "'>
                            ❤️ <span class='like-count'>" . $row['likes'] . "</span>
                        </button>
                        <a href='home.php?user=" . $postedBy . "' class='message-btn'>💬 Message</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='7'>No products added yet.</td></tr>";
        }
        ?>
    </table>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script>
    $(document).ready(function() {
        $(".like-btn").click(function() {
            var button = $(this);
            var productId = button.data("id");

            $.ajax({
                url: "likeproduct.php",
                type: "POST",
                data: { product_id: productId },
                success: function(response) {
                    if (response !== "error") {
                        button.find(".like-count").text(response);
                    } else {
                        alert("Error liking the product.");
                    }
                }
            });
        });
    });

    function closeChat() {
        document.getElementById("chat-box").style.display = "none";
    }

    function fetchMessages() {
        var sender = $('#sender').val();
        var receiver = $('#receiver').val();
        
        $.ajax({
            url: 'fetch_messages.php',
            type: 'POST',
            data: {sender: sender, receiver: receiver},
            success: function(data) {
                $('#chat-box-body').html(data);
                scrollChatToBottom();
            }
        });
    }

    function scrollChatToBottom() {
        var chatBox = $('#chat-box-body');
        chatBox.scrollTop(chatBox.prop("scrollHeight"));
    }

    $(document).ready(function() {
        fetchMessages();
        setInterval(fetchMessages, 3000);
    });

    $('#chat-form').submit(function(e) {
        e.preventDefault();
        var sender = $('#sender').val();
        var receiver = $('#receiver').val();
        var message = $('#message').val();

        $.ajax({
            url: 'submit_message.php',
            type: 'POST',
            data: {sender: sender, receiver: receiver, message: message},
            success: function() {
                $('#message').val('');
                fetchMessages();
            }
        });
    });
</script>

<style>
.like-btn, .message-btn {
    background: none;
    border: none;
    font-size: 20px;
    cursor: pointer;
    margin-right: 10px;
    text-decoration: none;
}

.like-btn {
    color: gray;
}

.like-btn.liked {
    color: red;
}

.message-btn {
    color: blue;
    font-size: 18px;
    border: 1px solid blue;
    padding: 5px 10px;
    border-radius: 5px;
    cursor: pointer;
}
</style>

</body>
</html>
