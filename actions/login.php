<?php
session_start();
include '../connect.php';

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $volunteer_id = $_POST['volunteer_id'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($volunteer_id) || empty($password)) {
        echo "<script>alert('Volunteer ID and password are required.'); window.location.href='/HumanityHub/partials/login.html';</script>";
        exit();
    }

    // Check if the volunteers table exists
    $table_check = $conn->query("SHOW TABLES LIKE 'volunteers'");
    if ($table_check->num_rows == 0) {
        die("The 'volunteers' table does not exist in the database.");
    }

    // Check if the volunteer_id exists
    $sql = "SELECT volunteer_id, password, make_admin FROM volunteers WHERE volunteer_id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        $error = "Prepare failed: " . $conn->error;
        error_log("Prepare failed in login.php: " . $conn->error);
        die($error);
    }
    $stmt->bind_param("s", $volunteer_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if ($user && password_verify($password, $user['password'])) {
        // Set session variables
        $_SESSION['volunteer_id'] = $user['volunteer_id'];
        $_SESSION['is_admin'] = $user['make_admin'];

        // Debug: Log the session variable
        error_log("Login successful. Volunteer ID: " . $_SESSION['volunteer_id']);

        // Redirect based on user role
        if ($user['make_admin'] == 1) {
            header("Location: /HumanityHub/partials/admin.php");
        } else {
            header("Location: /HumanityHub/actions/volunteerdashboard.php");
        }
        exit();
    } else {
        echo "<script>alert('Invalid Volunteer ID or password.'); window.location.href='/HumanityHub/partials/login.php';</script>";
        exit();
    }
}
$conn->close();
?>