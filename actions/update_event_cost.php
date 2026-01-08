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
error_log("update_event_cost.php started");

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log("Invalid request method: " . $_SERVER['REQUEST_METHOD']);
    header("Location: /HumanityHub/partials/event-cost.php?status=error&message=Invalid request method");
    exit();
}

// Log the raw POST data
error_log("POST data received: " . print_r($_POST, true));

// Get form data
$event_key = $_POST['event_key'] ?? '';
$cost_sector = $_POST['cost_sector'] ?? '';
$amount = floatval($_POST['amount'] ?? 0);
$new_cost_sector = $_POST['new_cost_sector'] ?? '';
$new_amount = floatval($_POST['new_amount'] ?? 0);

// Validate form data (at least one set of fields must be filled)
if (empty($event_key)) {
    error_log("Invalid form data: event_key=$event_key");
    header("Location: /HumanityHub/partials/event-cost.php?status=error&message=Event selection is required");
    exit();
}

if (empty($cost_sector) && empty($new_cost_sector)) {
    error_log("Invalid form data: Both cost_sector and new_cost_sector are empty");
    header("Location: /HumanityHub/partials/event-cost.php?status=error&message=At least one cost sector must be provided");
    exit();
}

if (!empty($cost_sector) && $amount <= 0) {
    error_log("Invalid form data: cost_sector=$cost_sector, amount=$amount");
    header("Location: /HumanityHub/partials/event-cost.php?status=error&message=Amount must be greater than 0 for existing cost sector");
    exit();
}

if (!empty($new_cost_sector) && $new_amount <= 0) {
    error_log("Invalid form data: new_cost_sector=$new_cost_sector, new_amount=$new_amount");
    header("Location: /HumanityHub/partials/event-cost.php?status=error&message=New amount must be greater than 0 for new cost sector");
    exit();
}

// Parse event_key to get event_name and date
list($event_name, $date) = explode('|', $event_key);

// Validate event_name and date
if (empty($event_name) || empty($date)) {
    error_log("Invalid event_key format: event_key=$event_key");
    header("Location: /HumanityHub/partials/event-cost.php?status=error&message=Invalid event selection");
    exit();
}

// Log the validated data
error_log("Validated data: event_name=$event_name, date=$date, cost_sector=$cost_sector, amount=$amount, new_cost_sector=$new_cost_sector, new_amount=$new_amount");

// Verify the event exists
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

if ($result->num_rows == 0) {
    // Event doesn't exist
    error_log("Event does not exist: event_name=$event_name, date=$date");
    header("Location: /HumanityHub/partials/event-cost.php?status=error&message=Event does not exist");
    exit();
}
$stmt->close();

// Begin transaction to handle multiple updates
$conn->begin_transaction();

try {
    // Update or insert the existing cost sector
    if (!empty($cost_sector)) {
        // Check if the cost_sector exists for this event
        $check_cost_sql = "SELECT * FROM event_cost WHERE event_name = ? AND date = ? AND cost_sector = ?";
        $stmt = $conn->prepare($check_cost_sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed (check cost sector): " . $conn->error);
        }
        $stmt->bind_param("sss", $event_name, $date, $cost_sector);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Cost sector exists, update the amount
            $update_sql = "UPDATE event_cost SET amount = ? WHERE event_name = ? AND date = ? AND cost_sector = ?";
            $stmt = $conn->prepare($update_sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed (update cost): " . $conn->error);
            }
            $stmt->bind_param("dsss", $amount, $event_name, $date, $cost_sector);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                error_log("Cost updated successfully: event_name=$event_name, date=$date, cost_sector=$cost_sector, amount=$amount");
            }
        } else {
            // Cost sector doesn't exist, insert a new entry
            $insert_sql = "INSERT INTO event_cost (event_name, cost_sector, amount, date) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_sql);
            if ($stmt === false) {
                throw new Exception("Prepare failed (insert cost): " . $conn->error);
            }
            $stmt->bind_param("ssds", $event_name, $cost_sector, $amount, $date);
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                error_log("New cost added successfully: event_name=$event_name, date=$date, cost_sector=$cost_sector, amount=$amount");
            }
        }
        $stmt->close();
    }

    // Insert the new cost sector if provided
    if (!empty($new_cost_sector)) {
        $insert_sql = "INSERT INTO event_cost (event_name, cost_sector, amount, date) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_sql);
        if ($stmt === false) {
            throw new Exception("Prepare failed (insert new cost): " . $conn->error);
        }
        $stmt->bind_param("ssds", $event_name, $new_cost_sector, $new_amount, $date);
        $stmt->execute();
        if ($stmt->affected_rows > 0) {
            error_log("New cost category added successfully: event_name=$event_name, date=$date, new_cost_sector=$new_cost_sector, new_amount=$new_amount");
        }
        $stmt->close();
    }

    // Commit the transaction
    $conn->commit();
    error_log("Transaction committed successfully");
    header("Location: /HumanityHub/partials/event-cost.php?status=success&message=Event cost updated successfully!");
} catch (Exception $e) {
    // Rollback the transaction on error
    $conn->rollback();
    $error = $e->getMessage();
    error_log("Transaction failed: " . $error);
    header("Location: /HumanityHub/partials/event-cost.php?status=error&message=Error updating event cost: " . urlencode($error));
}

$conn->close();
exit();
?>