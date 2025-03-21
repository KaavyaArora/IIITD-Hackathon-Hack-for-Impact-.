<?php
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "advisors_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Database connection failed: ' . $conn->connect_error
    ]);
    exit;
}

// Check if the database exists
$checkDB = $conn->query("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
if ($checkDB->num_rows == 0) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Database does not exist: ' . $dbname
    ]);
    exit;
}

// Check if the table exists
$checkTable = $conn->query("SHOW TABLES LIKE 'advisors'");
if ($checkTable->num_rows == 0) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => 'Table does not exist: advisors'
    ]);
    exit;
}

try {
    // Select all advisors from the database
    $sql = "SELECT id, name, email, phone, experience, hourly_rate, expertise, bio, profile_pic FROM advisors";
    $result = $conn->query($sql);

    if ($result === false) {
        throw new Exception('Query failed: ' . $conn->error);
    }

    $advisors = [];

    if ($result->num_rows > 0) {
        // Output data of each row
        while($row = $result->fetch_assoc()) {
            $advisors[] = $row;
        }
    }

    // Close connection
    $conn->close();

    // Return data as JSON
    header('Content-Type: application/json');
    echo json_encode($advisors);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => true,
        'message' => $e->getMessage()
    ]);
}
?>
