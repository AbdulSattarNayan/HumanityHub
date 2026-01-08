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

// Include the database connection file
include '../connect.php';

// Fetch donation records
$sql = "SELECT donor_name, amount, donation_date FROM donations ORDER BY donation_date DESC";
$result = $conn->query($sql);
$donations = $result->fetch_all(MYSQLI_ASSOC);

// Calculate total balance from the balance table
$sql = "SELECT total_balance FROM balance WHERE id = 1";
$result = $conn->query($sql);
$total_balance = $result->fetch_assoc()['total_balance'] ?? 0.00;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Earn - Humanity Hub</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="logo missing" id="logo">
        </div>
        <h1>Donation Records</h1>
        <div class="menu">
            <a href="accounts.php">Back to Accounts</a>
            <a href="admin.php">Admin Dashboard</a>
            <a href="../actions/logout.php">Log Out</a>
        </div>
    </header>

    <main>
        <p>Keep track of all received donations.</p>

        <table class="donation-table">
            <thead>
                <tr>
                    <th>Donor Name</th>
                    <th>Amount ($)</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody id="donationBody">
                <?php if (count($donations) > 0): ?>
                    <?php foreach ($donations as $donation): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($donation['donor_name']); ?></td>
                            <td><?php echo number_format($donation['amount'], 2); ?></td>
                            <td><?php echo date('Y-m-d', strtotime($donation['donation_date'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3">No donations recorded yet.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td><strong>Total</strong></td>
                    <td id="totalAmount"><strong>$<?php echo number_format($total_balance, 2); ?></strong></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </main>

    <?php include '../footer.php'; ?>
    <script src="../js/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>