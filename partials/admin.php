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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Humanity Hub</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../partials/styles/admin.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="logo missing" id="logo">
        </div>
        <h1>Admin Dashboard</h1>
        
        <div class="menu">
            <a href="index.php">Home</a>
            <a href="projects.php">Projects</a>
            <a href="beneficiary.php">Beneficiary</a>
            <a href="admin.php" class="active">Admin</a>
            <a href="../actions/logout.php">Log Out</a>
        </div>
    </header>

    <main>
        <div class="admin-container">
            <a href="events.php" class="admin-box">Events</a>
            <a href="accounts.php" class="admin-box">Accounts</a>
            <a href="manage_users.php" class="admin-box">Manage Users</a>
            <a href="check_requests.php" class="admin-box">Check Aid Requests</a> <!-- New box -->
        </div>
    </main>
    <?php include '../footer.php'; ?>

    <div id="footer-placeholder"></div>
    <script src="../js/script.js"></script>
</body>
</html>