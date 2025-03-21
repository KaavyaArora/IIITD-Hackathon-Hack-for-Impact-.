<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "advisors_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Process form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validate form data
    if (empty($_POST['advisor_id']) || empty($_POST['name']) || empty($_POST['email']) || empty($_POST['date']) || empty($_POST['time']) || empty($_POST['duration'])) {
        $errorMessage = urlencode("All required fields must be filled out.");
        header("Location: schedule-call.php?advisor_id={$_POST['advisor_id']}&error=$errorMessage");
        exit;
    }

    // Additional validation
    $advisor_id = (int)$_POST['advisor_id'];

    // Check if advisor exists
    $advisorCheck = $conn->prepare("SELECT id FROM advisors WHERE id = ?");
    $advisorCheck->bind_param("i", $advisor_id);
    $advisorCheck->execute();
    $advisorResult = $advisorCheck->get_result();
    if ($advisorResult->num_rows == 0) {
        $errorMessage = urlencode("Selected advisor does not exist.");
        header("Location: index.php?error=$errorMessage");
        exit;
    }

    // Sanitize and prepare data for insertion
    $client_name = htmlspecialchars($_POST['name']);
    $client_email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $call_date = $_POST['date'];
    $call_time = $_POST['time'];
    $duration = (int)$_POST['duration'];
    $message = isset($_POST['message']) ? htmlspecialchars($_POST['message']) : '';

    // Validate email
    if (!filter_var($client_email, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = urlencode("Invalid email format.");
        header("Location: schedule-call.php?advisor_id=$advisor_id&error=$errorMessage");
        exit;
    }

    // Validate date and time
    $currentDate = date('Y-m-d');
    $currentTime = date('H:i:s');
    if ($call_date < $currentDate) {
        $errorMessage = urlencode("Cannot schedule calls in the past.");
        header("Location: schedule-call.php?advisor_id=$advisor_id&error=$errorMessage");
        exit;
    } elseif ($call_date == $currentDate && $call_time < $currentTime) {
        $errorMessage = urlencode("Cannot schedule calls in the past time.");
        header("Location: schedule-call.php?advisor_id=$advisor_id&error=$errorMessage");
        exit;
    }

    // Check for availability (no overlapping calls)
    $availabilityCheck = $conn->prepare("SELECT id FROM scheduled_calls WHERE advisor_id = ? AND call_date = ? AND ((call_time <= ? AND ADDTIME(call_time, SEC_TO_TIME(duration * 60)) > ?) OR (call_time < ADDTIME(?, SEC_TO_TIME(? * 60)) AND call_time >= ?))");
    $availabilityCheck->bind_param("issssii", $advisor_id, $call_date, $call_time, $call_time, $call_time, $duration, $call_time);
    $availabilityCheck->execute();
    $availabilityResult = $availabilityCheck->get_result();
    if ($availabilityResult->num_rows > 0) {
        $errorMessage = urlencode("The advisor is not available at the selected time. Please choose another time slot.");
        header("Location: schedule-call.php?advisor_id=$advisor_id&error=$errorMessage");
        exit;
    }

    // Generate a mock meeting link (in a real app, this might integrate with Zoom/Meet/etc.)
    $meeting_link = "https://meet.example.com/" . md5($advisor_id . $client_email . time());

    // Prepare and execute the SQL statement
    $stmt = $conn->prepare("INSERT INTO scheduled_calls (advisor_id, client_name, client_email, call_date, call_time, duration, message, meeting_link) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssiis", $advisor_id, $client_name, $client_email, $call_date, $call_time, $duration, $message, $meeting_link);
    if ($stmt->execute()) {
        // Send confirmation email (mock function - would be implemented in real app)
        // Set success message and redirect
        $_SESSION['success_message'] = "Your call has been scheduled successfully! Check your email for meeting details.";
        header("Location: schedule-call.php?id=$advisor_id");
        exit;
    } else {
        $errorMessage = urlencode("Error scheduling call: " . $conn->error);
        header("Location: schedule-call.php?advisor_id=$advisor_id&error=$errorMessage");
        exit;
    }
} else {
    // Display the scheduling form
    // Get advisor id from query string
    $advisorId = isset($_GET['advisor_id']) ? (int)$_GET['advisor_id'] : 0;
    if ($advisorId <= 0) {
        header("Location: index.php?error=" . urlencode("Invalid advisor selected."));
        exit;
    }

    // Get advisor details
    $advisorStmt = $conn->prepare("SELECT * FROM advisors WHERE id = ?");
    $advisorStmt->bind_param("i", $advisorId);
    $advisorStmt->execute();
    $advisorResult = $advisorStmt->get_result();
    if ($advisorResult->num_rows == 0) {
        header("Location: index.php?error=" . urlencode("Advisor not found."));
        exit;
    }
    $advisor = $advisorResult->fetch_assoc();

    // Include header
    include('includes/header.php');
    ?>
    <h2>Schedule a Call with <?php echo $advisor['name']; ?></h2>
    <form action="" method="post">
        <input type="hidden" name="advisor_id" value="<?php echo $advisorId; ?>">
        <label for="name">Your Name:</label>
        <input type="text" id="name" name="name" required>
        <label for="email">Your Email:</label>
        <input type="email" id="email" name="email" required>
        <label for="date">Date:</label>
        <input type="date" id="date" name="date" required>
        <label for="time">Time:</label>
        <input type="time" id="time" name="time" required>
        <label for="duration">Duration (minutes):</label>
        <input type="number" id="duration" name="duration" required>
        <label for="message">Message:</label>
        <textarea id="message" name="message"></textarea>
        <button type="submit">Schedule Call</button>
    </form>
    <?php
}
?>
