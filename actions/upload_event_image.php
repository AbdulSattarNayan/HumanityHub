<?php
// Include the database connection
include '../connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Ensure autocommit is enabled
$conn->autocommit(true);

// Log the start of the script
error_log("upload_event_image.php started");

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    header("Location: ../partials/event-cost.php?status=error&message=Invalid request method");
    exit();
}

// Log the raw POST data
error_log("POST data received: " . print_r($_POST, true));
error_log("FILES data received: " . print_r($_FILES, true));

// Get the event key (event_name|date)
$event_key = $_POST['event_key'] ?? '';
if (empty($event_key)) {
    error_log("Missing event_key");
    header("Location: ../partials/event-cost.php?status=error&message=Missing event key");
    exit();
}

// Split the event_key into event_name and date
list($event_name, $date) = explode('|', $event_key);

// Check if a file was uploaded
if (!isset($_FILES['event_image']) || $_FILES['event_image']['error'] === UPLOAD_ERR_NO_FILE) {
    error_log("No file uploaded");
    header("Location: ../partials/event-cost.php?status=error&message=No file uploaded");
    exit();
}

// Handle the file upload
$file = $_FILES['event_image'];
$allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
$max_size = 5 * 1024 * 1024; // 5MB

// Validate file type and size
if (!in_array($file['type'], $allowed_types)) {
    error_log("Invalid file type: " . $file['type']);
    header("Location: ../partials/event-cost.php?status=error&message=Invalid file type. Only JPEG, PNG, and GIF are allowed");
    exit();
}

if ($file['size'] > $max_size) {
    error_log("File too large: " . $file['size']);
    header("Location: ../partials/event-cost.php?status=error&message=File too large. Maximum size is 5MB");
    exit();
}

// Define the upload directory
$upload_dir = '../img/';
$filename = uniqid() . '-' . basename($file['name']);
$upload_path = $upload_dir . $filename;

// Move the uploaded file
if (!move_uploaded_file($file['tmp_name'], $upload_path)) {
    error_log("Failed to move uploaded file");
    header("Location: ../partials/event-cost.php?status=error&message=Failed to upload file");
    exit();
}

// Update the image path in the database for all records of this event (same event_name and date)
$sql = "UPDATE event_cost SET image = ? WHERE event_name = ? AND date = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    header("Location: ../partials/event-cost.php?status=error&message=Database error: Prepare failed - " . urlencode($conn->error));
    exit();
}

$image_path = '../img/' . $filename;
$stmt->bind_param("sss", $image_path, $event_name, $date);
$execute_result = $stmt->execute();
if ($execute_result && $stmt->affected_rows > 0) {
    error_log("Image updated successfully: event_name=$event_name, date=$date, image=$image_path, affected_rows=" . $stmt->affected_rows);
    header("Location: ../partials/event-cost.php?status=success&message=Image uploaded successfully");
} else {
    $error = $stmt->error ?: "No rows affected";
    error_log("Update failed: " . $error);
    header("Location: ../partials/event-cost.php?status=error&message=Error uploading image: " . urlencode($error));
}

$stmt->close();
$conn->close();
exit();
?>