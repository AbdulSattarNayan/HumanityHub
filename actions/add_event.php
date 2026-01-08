<?php
// Start a session to check if any session data is present
session_start();

// Include the database connection
include '../connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log the start of the script
error_log("add_event.php started");

// Log the current database
error_log("Current database: " . $conn->query("SELECT DATABASE()")->fetch_row()[0]);

// Log the session data (if any)
error_log("Session data: " . print_r($_SESSION, true));

// Log the raw POST data
error_log("POST data received: " . print_r($_POST, true));

// Log the raw input
$raw_input = file_get_contents('php://input');
error_log("Raw input: " . $raw_input);

// Get form data
$event_name = $_POST['event_name'] ?? 'N/A';
$event_date = $_POST['event_date'] ?? 'N/A';
$location = $_POST['location'] ?? 'N/A';
$description = $_POST['description'] ?? 'N/A';

// Log the extracted data
error_log("Extracted data: event_name=$event_name, event_date=$event_date, location=$location, description=$description");

// Insert the event into the database
$sql = "INSERT INTO events (event_name, description, event_date, location, created_at) VALUES (?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    $status = 'error';
    $message = 'Database error: ' . $conn->error;
    error_log("Prepare failed: " . $conn->error);
} else {
    $stmt->bind_param("ssss", $event_name, $description, $event_date, $location);
    if ($stmt->execute()) {
        // Log the success
        error_log("Insert successful. Affected rows: " . $stmt->affected_rows);
        $status = 'success';
        $message = 'Event added successfully!';
    } else {
        // Log the failure
        error_log("Insert failed: " . $stmt->error);
        $status = 'error';
        $message = 'Error adding event: ' . $stmt->error;
    }

    $stmt->close();
}

$conn->close();

// Log the redirect URL
$redirect_url = "/HumanityHub/partials/add-event.php?status=" . urlencode($status) . "&message=" . urlencode($message);
error_log("Redirecting to: " . $redirect_url);

// Set the redirect header
header("Location: " . $redirect_url);
exit();
?>