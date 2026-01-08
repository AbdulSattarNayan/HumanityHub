<?php
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['volunteer_id']) || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: ../actions/login.php");
    exit();
}

// Include the database connection file
include '../connect.php';

// Handle form submission to update make_admin status
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_admin_status'])) {
    $volunteer_id = $_POST['volunteer_id'];
    $make_admin = isset($_POST['make_admin']) ? 1 : 0;

    $sql = "UPDATE volunteers SET make_admin = ? WHERE volunteer_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $make_admin, $volunteer_id);
    if ($stmt->execute()) {
        $message = "Admin status updated successfully.";
    } else {
        $message = "Error updating admin status: " . $conn->error;
    }
    $stmt->close();
}

// Fetch all volunteers
$sql = "SELECT volunteer_id, name, email, make_admin FROM volunteers";
$result = $conn->query($sql);
$volunteers = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Humanity Hub</title>
    <link rel="stylesheet" href="../styles.css">
    <link rel="stylesheet" href="../partials/styles/manage_users.css?v=<?php echo time(); ?>">
</head>
<body class="manage-users">
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="logo missing" id="logo">
        </div>
        
        <div class="menu">
            <a href="index.php">Home</a>
            <a href="projects.php">Projects</a>
            <a href="beneficiary.php">Beneficiary</a>
            <a href="admin.php" class="active">Admin</a>
            <a href="../actions/logout.php">Log Out</a>
        </div>
    </header>

    <main>
        <div class="manage_users">
            <h1>Manage Users</h1> <!-- Moved to main -->
            <?php if (isset($message)): ?>
                <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
            <?php endif; ?>
            <table>
                <thead>
                    <tr>
                        <th>Volunteer ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Admin Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($volunteers as $volunteer): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($volunteer['volunteer_id']); ?></td>
                            <td><?php echo htmlspecialchars($volunteer['name']); ?></td>
                            <td><?php echo htmlspecialchars($volunteer['email']); ?></td>
                            <td>
                                <form action="manage_users.php" method="POST">
                                    <input type="hidden" name="volunteer_id" value="<?php echo htmlspecialchars($volunteer['volunteer_id']); ?>">
                                    <input type="hidden" name="update_admin_status" value="1">
                                    <input type="checkbox" name="make_admin" <?php echo $volunteer['make_admin'] == 1 ? 'checked' : ''; ?> onchange="this.form.submit()">
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <?php include '../footer.php'; ?>
    <script src="../js/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>