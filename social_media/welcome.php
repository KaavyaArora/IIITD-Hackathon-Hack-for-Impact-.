<?php
session_start();

if (!isset($_SESSION['login_user'])) {
    header("location: index.html");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['login_user']); ?>!</h2>
        <p>You have successfully logged in.</p>
        <p><a href="logout.php">Logout</a></p>
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