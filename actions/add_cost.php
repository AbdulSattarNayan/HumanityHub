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
error_log("add_cost.php started");

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    header("Location: ../partials/regular.php?status=error&message=Invalid request method");
    exit();
}

// Log the raw POST data
error_log("POST data received: " . print_r($_POST, true));

// Get form data
$sector = $_POST['sector'] ?? '';
$amount = floatval($_POST['amount'] ?? 0); // Explicitly cast to float
$date = $_POST['date'] ?? '';

// Validate form data
if (empty($sector) || $amount <= 0 || empty($date)) {
    error_log("Invalid form data: sector=$sector, amount=$amount, date=$date");
    header("Location: ../partials/regular.php?status=error&message=All fields are required and amount must be greater than 0");
    exit();
}

// Log the validated data
error_log("Validated data: sector=$sector, amount=$amount, date=$date");

// Insert the cost into the database
$sql = "INSERT INTO regular_cost (sector, amount, date) VALUES (?, ?, ?)";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Prepare failed: " . $conn->error);
    header("Location: ../partials/regular.php?status=error&message=Database error: Prepare failed - " . urlencode($conn->error));
    exit();
}

$stmt->bind_param("sds", $sector, $amount, $date);
$execute_result = $stmt->execute();
if ($execute_result && $stmt->affected_rows > 0) {
    error_log("Cost added successfully: sector=$sector, amount=$amount, date=$date, affected_rows=" . $stmt->affected_rows);
    header("Location: ../partials/regular.php?status=success&message=Cost added successfully");
} else {
    $error = $stmt->error ?: "No rows affected";
    error_log("Insert failed: " . $error);
    header("Location: ../partials/regular.php?status=error&message=Error adding cost: " . urlencode($error));
}

$stmt->close();
$conn->close();
exit();
?>