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

// Check if the database exists, create it if it doesn't
$checkDB = "CREATE DATABASE IF NOT EXISTS $dbname";
if ($conn->query($checkDB) !== TRUE) {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db($dbname);

// Check if the table exists
$tableCheck = $conn->query("SHOW TABLES LIKE 'advisors'");
if ($tableCheck->num_rows == 0) {
    // Table doesn't exist, create it
    $createTable = "CREATE TABLE advisors (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        experience INT NOT NULL,
        hourly_rate DECIMAL(10,2) NOT NULL,
        expertise TEXT NOT NULL,
        bio TEXT NOT NULL,
        profile_pic VARCHAR(255) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    
    if (!$conn->query($createTable)) {
        die("Failed to create database table: " . $conn->error);
    }
}

// Process form data if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize input data to prevent SQL injection
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $experience = (int)$_POST['experience']; // Cast to integer
    $hourly_rate = (float)$_POST['hourly_rate'];
    $expertise = mysqli_real_escape_string($conn, $_POST['expertise']);
    $bio = mysqli_real_escape_string($conn, $_POST['bio']);
    $phone = isset($_POST['phone']) ? mysqli_real_escape_string($conn, $_POST['phone']) : "";

    // Handle file upload
    $upload_dir = "uploads/";

    // Create directory if it doesn't exist
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $profile_pic = "";

    // Check if file was uploaded without errors
    if(isset($_FILES["profile_pic"]) && $_FILES["profile_pic"]["error"] == 0) {
        $allowed_types = array("jpg" => "image/jpg", "jpeg" => "image/jpeg", "gif" => "image/gif", "png" => "image/png");
        $file_name = $_FILES["profile_pic"]["name"];
        $file_type = $_FILES["profile_pic"]["type"];
        $file_size = $_FILES["profile_pic"]["size"];
        
        // Generate unique filename to prevent overwriting
        $new_file_name = uniqid() . "_" . $file_name;
        $file_path = $upload_dir . $new_file_name;
        
        // Verify file type
        $ext = pathinfo($file_name, PATHINFO_EXTENSION);
        if(array_key_exists($ext, $allowed_types) && in_array($file_type, $allowed_types)) {
            // Limit file size to 5MB
            if($file_size <= 5 * 1024 * 1024) { // 5MB
                if(move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $file_path)) {
                    $profile_pic = $file_path;
                } else {
                    echo "Error: There was a problem uploading your file. Please try again.";
                    exit;
                }
            } else {
                echo "Error: File is too large. Maximum file size is 5MB.";
                exit;
            }
        } else {
            echo "Error: Unsupported file format. Please upload an image file (JPG, JPEG, PNG, GIF).";
            exit;
        }
    } else {
        // Use default image if no profile picture is uploaded
        $profile_pic = "images/default-profile.jpg";
    }

    // Insert data into database
    $sql = "INSERT INTO advisors (name, email, phone, experience, hourly_rate, expertise, bio, profile_pic) 
        VALUES ('$name', '$email', '$phone', $experience, $hourly_rate, '$expertise', '$bio', '$profile_pic')";

    if ($conn->query($sql) === TRUE) {
        // Success message
        $_SESSION['success_message'] = "Registration successful! Your profile has been created.";
        header("Location: seek-advice.html");
        exit;
    } else {
        // Error message
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close connection
$conn->close();
?>