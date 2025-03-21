<?php
// Start session for flash messages
session_start();

// Database configuration
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "advisors_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get advisor ID and name from query parameters
$advisor_id = isset($_GET['advisor_id']) ? (int)$_GET['advisor_id'] : 0;
$advisor_name = isset($_GET['advisor_name']) ? htmlspecialchars($_GET['advisor_name']) : 'Advisor';

// Check if the advisor exists
$valid_advisor = false;
if ($advisor_id > 0) {
    $stmt = $conn->prepare("SELECT id, name, profile_pic, expertise, bio, hourly_rate FROM advisors WHERE id = ?");
    $stmt->bind_param("i", $advisor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $advisor = $result->fetch_assoc();
        $valid_advisor = true;
        $advisor_name = $advisor['name']; // Use the name from database
    }
    $stmt->close();
}

// Process form submission
$error_message = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form data
    if (empty($_POST['name']) || empty($_POST['email']) || 
        empty($_POST['date']) || empty($_POST['time']) || empty($_POST['duration'])) {
        $error_message = "All required fields must be filled out.";
    } else {
        // Sanitize input data
        $client_name = mysqli_real_escape_string($conn, $_POST['name']);
        $client_email = mysqli_real_escape_string($conn, $_POST['email']);
        $call_date = mysqli_real_escape_string($conn, $_POST['date']);
        $call_time = mysqli_real_escape_string($conn, $_POST['time']);
        $duration = (int)$_POST['duration'];
        $message = isset($_POST['message']) ? mysqli_real_escape_string($conn, $_POST['message']) : "";
        
        // Validate email format
        if (!filter_var($client_email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Invalid email format.";
        } else {
            // Generate a meeting link (in a real app, you'd integrate with Zoom, Google Meet, etc.)
            $meeting_id = uniqid();
            $meeting_link = "https://yourdomain.com/meeting/{$meeting_id}";
            
            // Insert data into database
            $sql = "INSERT INTO scheduled_calls (advisor_id, client_name, client_email, call_date, call_time, duration, message, meeting_link) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issssis", $advisor_id, $client_name, $client_email, $call_date, $call_time, $duration, $message, $meeting_link);
            
            if ($stmt->execute()) {
                $success_message = "Your call with {$advisor_name} has been scheduled successfully! You will receive a confirmation email shortly.";
                
                // In a real application, you would send confirmation emails here
                
                // Clear form data after successful submission
                $_POST = array();
            } else {
                $error_message = "Error scheduling your call: " . $conn->error;
            }
            
            $stmt->close();
        }
    }
}

// Get current date and set min date to tomorrow
$tomorrow = date('Y-m-d', strtotime('+1 day'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule a Call with <?php echo $advisor_name; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        :root {
            --primary-color: #4CAF50;
            --secondary-color: #45a049;
            --accent-color: #f1f1f1;
            --text-color: #333;
            --error-color: #f44336;
            --success-color: #4CAF50;
        }
        
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: var(--text-color);
            background-color: #f9f9f9;
            padding: 20px;
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        
        h1 {
            font-size: 2rem;
            margin-bottom: 10px;
        }
        
        .back-link {
            color: var(--primary-color);
            text-decoration: none;
            display: flex;
            align-items: center;
            font-weight: 500;
        }
        
        .back-link i {
            margin-right: 5px;
        }
        
        .flash-message {
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            position: relative;
        }
        
        .flash-message.error {
            background-color: #ffebee;
            color: var(--error-color);
            border: 1px solid #ffcdd2;
        }
        
        .flash-message.success {
            background-color: #e8f5e9;
            color: var(--success-color);
            border: 1px solid #c8e6c9;
        }
        
        .close-flash {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            font-size: 18px;
            cursor: pointer;
            color: inherit;
        }
        
        .schedule-container {
            display: flex;
            gap: 30px;
            align-items: flex-start;
        }
        
        .advisor-info {
            flex: 1;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 20px;
            position: sticky;
            top: 20px;
        }
        
        .advisor-profile {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .advisor-image {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 15px;
        }
        
        .schedule-form {
            flex: 2;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        input[type="text"],
        input[type="email"],
        input[type="date"],
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        
        input:focus,
        select:focus,
        textarea:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.2);
        }
        
        button[type="submit"] {
            background-color: var(--primary-color);
            color: white;
            border: none;
            padding: 14px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: background-color 0.3s;
        }
        
        button[type="submit"]:hover {
            background-color: var(--secondary-color);
        }
        
        .cancel-btn {
            background-color: var(--accent-color);
            color: var(--text-color);
            border: none;
            padding: 14px 20px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            text-decoration: none;
            display: inline-block;
            margin-right: 10px;
            font-weight: 600;
        }
        
        .form-buttons {
            display: flex;
            justify-content: flex-end;
            margin-top: 30px;
        }
        
        .expertise-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin: 12px 0;
        }
        
        .expertise-tag {
            background-color: #f1f8e9;
            color: #558b2f;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        .hourly-rate {
            font-size: 18px;
            font-weight: bold;
            color: var(--primary-color);
            margin: 15px 0;
        }
        
        /* Responsive styles */
        @media (max-width: 768px) {
            .schedule-container {
                flex-direction: column;
            }
            
            .advisor-info {
                position: static;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Schedule a Call</h1>
            <a href="seek-advice.html" class="back-link"><i class="fas fa-arrow-left"></i> Back to Advisors</a>
        </header>
        
        <?php if(!$valid_advisor): ?>
            <div class="flash-message error">
                <span>Invalid advisor selected. Please go back and select a valid advisor.</span>
            </div>
        <?php else: ?>
            
            <?php if($error_message): ?>
                <div class="flash-message error">
                    <span><?php echo $error_message; ?></span>
                    <button class="close-flash" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endif; ?>
            
            <?php if($success_message): ?>
                <div class="flash-message success">
                    <span><?php echo $success_message; ?></span>
                    <button class="close-flash" onclick="this.parentElement.remove()">&times;</button>
                </div>
            <?php endif; ?>
            
            <div class="schedule-container">
                <div class="advisor-info">
                    <div class="advisor-profile">
                        <img src="<?php echo $advisor['profile_pic']; ?>" alt="<?php echo $advisor_name; ?>" class="advisor-image">
                        <div>
                            <h3><?php echo $advisor_name; ?></h3>
                            <?php 
                            $expertiseTags = explode(',', $advisor['expertise']);
                            $firstTag = trim($expertiseTags[0]) ?: 'Consultant';
                            ?>
                            <p><?php echo $firstTag; ?></p>
                        </div>
                    </div>
                    
                    <div class="hourly-rate">
                        <i class="fas fa-dollar-sign"></i> <?php echo number_format((float)$advisor['hourly_rate'], 2); ?> per hour
                    </div>
                    
                    <div class="expertise-tags">
                        <?php foreach($expertiseTags as $tag): ?>
                            <?php if(trim($tag)): ?>
                                <span class="expertise-tag"><?php echo trim($tag); ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    
                    <?php if($advisor['bio']): ?>
                        <p><?php echo $advisor['bio']; ?></p>
                    <?php else: ?>
                        <p>No bio available for this advisor.</p>
                    <?php endif; ?>
                </div>
                
                <div class="schedule-form">
                    <h2>Book Your Call</h2>
                    <p>Fill out the form below to schedule your call with <?php echo $advisor_name; ?>.</p>
                    
                    <form action="schedule-call.php?advisor_id=<?php echo $advisor_id; ?>&advisor_name=<?php echo urlencode($advisor_name); ?>" method="post">
                        <input type="hidden" name="advisor_id" value="<?php echo $advisor_id; ?>">
                        
                        <div class="form-group">
                            <label for="name">Your Name *</label>
                            <input type="text" id="name" name="name" required value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Your Email *</label>
                            <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="date">Preferred Date *</label>
                            <input type="date" id="date" name="date" min="<?php echo $tomorrow; ?>" required value="<?php echo isset($_POST['date']) ? htmlspecialchars($_POST['date']) : ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label for="time">Preferred Time *</label>
                            <select id="time" name="time" required>
                                <option value="" <?php echo !isset($_POST['time']) ? 'selected' : ''; ?>>Select a time</option>
                                <option value="09:00" <?php echo (isset($_POST['time']) && $_POST['time'] === '09:00') ? 'selected' : ''; ?>>9:00 AM</option>
                                <option value="10:00" <?php echo (isset($_POST['time']) && $_POST['time'] === '10:00') ? 'selected' : ''; ?>>10:00 AM</option>
                                <option value="11:00" <?php echo (isset($_POST['time']) && $_POST['time'] === '11:00') ? 'selected' : ''; ?>>11:00 AM</option>
                                <option value="13:00" <?php echo (isset($_POST['time']) && $_POST['time'] === '13:00') ? 'selected' : ''; ?>>1:00 PM</option>
                                <option value="14:00" <?php echo (isset($_POST['time']) && $_POST['time'] === '14:00') ? 'selected' : ''; ?>>2:00 PM</option>
                                <option value="15:00" <?php echo (isset($_POST['time']) && $_POST['time'] === '15:00') ? 'selected' : ''; ?>>3:00 PM</option>
                                <option value="16:00" <?php echo (isset($_POST['time']) && $_POST['time'] === '16:00') ? 'selected' : ''; ?>>4:00 PM</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="duration">Call Duration *</label>
                            <select id="duration" name="duration" required>
                                <option value="30" <?php echo (!isset($_POST['duration']) || (isset($_POST['duration']) && $_POST['duration'] === '30')) ? 'selected' : ''; ?>>30 minutes</option>
                                <option value="60" <?php echo (isset($_POST['duration']) && $_POST['duration'] === '60') ? 'selected' : ''; ?>>1 hour</option>
                                <option value="90" <?php echo (isset($_POST['duration']) && $_POST['duration'] === '90') ? 'selected' : ''; ?>>1.5 hours</option>
                                <option value="120" <?php echo (isset($_POST['duration']) && $_POST['duration'] === '120') ? 'selected' : ''; ?>>2 hours</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message (Optional)</label>
                            <textarea id="message" name="message" rows="4"><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <div class="form-buttons">
                            <a href="seek-advice.html" class="cancel-btn">Cancel</a>
                            <button type="submit">Schedule Call</button>
                        </div>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <script>
        // Auto close flash messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const flashMessages = document.querySelectorAll('.flash-message');
            flashMessages.forEach(message => {
                setTimeout(() => {
                    if (message.parentNode) {
                        message.remove();
                    }
                }, 5000);
            });
        });
        document.querySelectorAll('.form-group input, .form-group textarea, .form-group select').forEach(item => {
  item.addEventListener('focus', () => {
    item.closest('.form-group').classList.add('focused');
  });
  item.addEventListener('blur', () => {
    if (!item.value) {
      item.closest('.form-group').classList.remove('focused');
    }
  });
});
    </script>
</body>
</html>