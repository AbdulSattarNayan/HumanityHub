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
error_log("add_event_cost.php started");

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    header("Location: /HumanityHub/partials/event-cost.php?status=error&message=Invalid request method");
    exit();
}

// Log the raw POST data
error_log("POST data received: " . print_r($_POST, true));

// Get form data
$event_name = $_POST['event_name'] ?? '';
$cost_sector = $_POST['cost_sector'] ?? '';
$amount = floatval($_POST['amount'] ?? 0);
$date = $_POST['date'] ?? '';
$goal = $_POST['goal'] ?? 'Not specified';

// Validate form data
if (empty($event_name) || empty($cost_sector) || $amount <= 0 || empty($date)) {
    error_log("Invalid form data: event_name=$event_name, cost_sector=$cost_sector, amount=$amount, date=$date, goal=$goal");
    header("Location: /HumanityHub/partials/event-cost.php?status=error&message=All fields are required and amount must be greater than 0");
    exit();
}

// Log the validated data
error_log("Validated data: event_name=$event_name, cost_sector=$cost_sector, amount=$amount, date=$date, goal=$goal");

// Check if event already exists
$check_sql = "SELECT * FROM event_cost WHERE event_name = ? AND date = ?";
$stmt = $conn->prepare($check_sql);
if ($stmt === false) {
    error_log("Prepare failed (check event): " . $conn->error);
    header("Location: /HumanityHub/partials/event-cost.php?status=error&message=Database error: Prepare failed - " . urlencode($conn->error));
    exit();
}
$stmt->bind_param("ss", $event_name, $date);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Event exists, redirect with error
    error_log("Event already exists: event_name=$event_name, date=$date");
    header("Location: /HumanityHub/partials/event-cost.php?status=error&message=Event already exists. Use 'Update Event Cost' to add costs.");
    exit();
}
$stmt->close();

// Insert the new event cost
$sql = "INSERT INTO event_cost (event_name, cost_sector, amount, date, goal) VALUES (?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    error_log("Prepare failed (insert event): " . $conn->error);
    header("Location: /HumanityHub/partials/event-cost.php?status=error&message=Database error: Prepare failed - " . urlencode($conn->error));
    exit();
}

$stmt->bind_param("ssdss", $event_name, $cost_sector, $amount, $date, $goal);
$execute_result = $stmt->execute();
if ($execute_result && $stmt->affected_rows > 0) {
    error_log("Event added successfully: event_name=$event_name, cost_sector=$cost_sector, amount=$amount, date=$date, goal=$goal, affected_rows=" . $stmt->affected_rows);
    header("Location: /HumanityHub/partials/event-cost.php?status=success&message=New event added successfully!");
} else {
    $error = $stmt->error ?: "No rows affected";
    error_log("Insert failed: " . $error);
    header("Location: /HumanityHub/partials/event-cost.php?status=error&message=Error adding event: " . urlencode($error));
}

$stmt->close();
$conn->close();
exit();
?>