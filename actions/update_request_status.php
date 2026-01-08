<?php
session_start();

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if the user is logged in and is an admin
if (!isset($_SESSION['volunteer_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../actions/login.php");
    exit();
}

// Include the database connection
include '../connect.php';

// Log the start of the script
error_log("update_request_status.php started");

// Check if the request ID and status are provided
if (!isset($_POST['request_id']) || !isset($_POST['status'])) {
    error_log("Missing request_id or status in POST data");
    header("Location: ../partials/check_requests.php?error=Missing parameters");
    exit();
}

$request_id = $_POST['request_id'];
$status = $_POST['status'];

// Validate the status
if (!in_array($status, ['done', 'canceled'])) {
    error_log("Invalid status: " . $status);
    header("Location: ../partials/check_requests.php?error=Invalid status");
    exit();
}

// Update the status in the database
$sql = "UPDATE requests SET status = ? WHERE request_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    header("Location: ../partials/check_requests.php?error=Database error");
    exit();
}

$stmt->bind_param("si", $status, $request_id);
if ($stmt->execute()) {
    error_log("Status updated successfully for request_id: " . $request_id . ", status: " . $status);
} else {
    error_log("Update failed: " . $stmt->error);
    header("Location: ../partials/check_requests.php?error=Update failed");
    exit();
}

$stmt->close();
$conn->close();

// Redirect back to check_requests.php
header("Location: ../partials/check_requests.php?success=Status updated");
exit();
?>