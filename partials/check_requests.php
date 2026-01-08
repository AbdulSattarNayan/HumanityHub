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

// Fetch all pending aid requests
$requests_sql = "SELECT request_id, full_name, contact_number, aid_type, description, created_at, status 
                 FROM requests 
                 WHERE status = 'pending' 
                 ORDER BY created_at DESC";
$requests_result = $conn->query($requests_sql);
$requests = $requests_result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Aid Requests - Humanity Hub</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../styles/admin.css">
    <style>
        .status-buttons button {
            margin: 0 5px;
            padding: 5px 10px;
            cursor: pointer;
            border: none;
            border-radius: 3px;
            color: white;
        }
        .status-buttons .done-btn {
            background-color: #28a745; /* Green for Done */
        }
        .status-buttons .cancel-btn {
            background-color: #dc3545; /* Red for Cancel */
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="logo missing" id="logo">
        </div>
        <h1>Check Aid Requests</h1>
        
        <div class="menu">
            <a href="index.php">Home</a>
            <a href="projects.php">Projects</a>
            <a href="beneficiary.php">Beneficiary</a>
            <a href="admin.php" class="active">Admin</a>
            <a href="../actions/logout.php">Log Out</a>
        </div>
    </header>

    <main>
        <div class="requests-section">
            <center><h2>Aid Requests</h2></center>
            <?php if (count($requests) > 0): ?>
                <table class="requests-table">
                    <thead>
                        <tr>
                            <th>Request ID</th>
                            <th>Full Name</th>
                            <th>Contact Number</th>
                            <th>Aid Type</th>
                            <th>Description</th>
                            <th>Submitted At</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr data-request-id="<?php echo htmlspecialchars($request['request_id']); ?>">
                                <td><?php echo htmlspecialchars($request['request_id']); ?></td>
                                <td><?php echo htmlspecialchars($request['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['contact_number']); ?></td>
                                <td><?php echo htmlspecialchars($request['aid_type']); ?></td>
                                <td><?php echo htmlspecialchars($request['description']); ?></td>
                                <td><?php echo htmlspecialchars($request['created_at']); ?></td>
                                <td class="status-buttons">
                                    <button class="done-btn" onclick="updateStatus(<?php echo htmlspecialchars($request['request_id']); ?>, 'done')">Done</button>
                                    <button class="cancel-btn" onclick="updateStatus(<?php echo htmlspecialchars($request['request_id']); ?>, 'canceled')">Cancel</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No pending aid requests found.</p>
            <?php endif; ?>
        </div>
    </main>

    <?php include '../footer.php'; ?>
    <script src="../js/script.js"></script>
    <script>
        function updateStatus(requestId, status) {
            // Send AJAX request to update the status
            const formData = new FormData();
            formData.append('request_id', requestId);
            formData.append('status', status);

            fetch('/HumanityHub/actions/update_request_status.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                console.log("Response from update_request_status.php:", data);
                // Remove the row from the table
                const row = document.querySelector(`tr[data-request-id="${requestId}"]`);
                if (row) {
                    row.remove();
                }
                // Check if there are any rows left
                const tableBody = document.querySelector('.requests-table tbody');
                if (tableBody.children.length === 0) {
                    tableBody.parentElement.insertAdjacentHTML('afterend', '<p>No pending aid requests found.</p>');
                    tableBody.parentElement.remove();
                }
            })
            .catch(error => {
                console.error('Error updating status:', error);
                alert('Failed to update status. Please try again.');
            });
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>