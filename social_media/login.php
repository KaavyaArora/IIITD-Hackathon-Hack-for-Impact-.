<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "social_media";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $sql = "SELECT * FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Store more user info in session for use in social media pages
            $_SESSION['login_user'] = $username;
            $_SESSION['user_id'] = $row['id']; // Make sure your users table has an 'id' column
            $_SESSION['user_email'] = $row['email']; // Optional, if you have this field
            
            // Redirect to social media home page instead of welcome.php
            header("Location: home.php");
            exit;
        } else {
            echo "<p style='color:red;'>Invalid username or password</p>";
        }
    } else {
        echo "<p style='color:red;'>Invalid username or password</p>";
    }
}
$conn->close();
// If the script reaches here, the login failed or it's a GET request
// Display the form again (or just the error message if POST)
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    header("Location: index.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Error</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Login Failed</h2>
        <p>Please <a href="index.html">try again</a></p>
    </div>
    <!-- Voice Assistant Button and Panel -->
<div class="voice-assistant">
    <button id="mic-button" class="mic-button">
        <i class="fas fa-microphone"></i>
    </button>
    <div class="voice-panel" id="voice-panel">
        <div class="voice-panel-header">
            <h3>Voice Assistant</h3>
            <button id="close-panel" class="close-panel">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="voice-status">
            <div id="voice-status-indicator" class="voice-status-indicator"></div>
            <p id="voice-status-text">Click the mic to start</p>
        </div>
        <div class="voice-commands">
            <h4>Available Commands:</h4>
            <ul id="command-list">
                <!-- Commands will be dynamically updated based on page -->
            </ul>
        </div>
    </div>
    <div id="voice-toast" class="voice-toast"></div>
</div>
    <script src="voice-assistant.js"></script>
</body>
</html>