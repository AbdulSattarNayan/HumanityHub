<?php
// Include the database connection
include '../connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log the start of the script
error_log("submit_request.php started");

// Log the current database
error_log("Current database: " . $conn->query("SELECT DATABASE()")->fetch_row()[0]);

// Log the raw POST data
error_log("POST data received: " . print_r($_POST, true));

// Log the raw input
$raw_input = file_get_contents('php://input');
error_log("Raw input: " . $raw_input);

// Get form data
$full_name = $_POST['full_name'] ?? 'N/A';
$contact_number = $_POST['contact_number'] ?? 'N/A';
$aid_type = $_POST['aid_type'] ?? 'N/A';
$description = $_POST['description'] ?? 'N/A';

// Log the extracted data
error_log("Extracted data: full_name=$full_name, contact_number=$contact_number, aid_type=$aid_type, description=$description");

// Insert the request into the database
$sql = "INSERT INTO requests (full_name, contact_number, aid_type, description) VALUES (?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    $status = 'error';
    $message = 'Database error: ' . $conn->error;
    error_log("Prepare failed: " . $conn->error);
} else {
    $stmt->bind_param("ssss", $full_name, $contact_number, $aid_type, $description);
    if ($stmt->execute()) {
        // Log the success
        error_log("Insert successful. Affected rows: " . $stmt->affected_rows);
        $status = 'success';
        $message = 'Your aid request has been submitted successfully! We will contact you soon ';
    } else {
        // Log the failure
        error_log("Insert failed: " . $stmt->error);
        $status = 'error';
        $message = 'Error submitting request: ' . $stmt->error;
    }

    $stmt->close();
}

$conn->close();

// Redirect back to beneficiary.html with status and message as query parameters
header("Location: /HumanityHub/partials/beneficiary.php?status=" . urlencode($status) . "&message=" . urlencode($message));
exit();
?>