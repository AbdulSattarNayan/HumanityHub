<?php
session_start();

// Include the database connection file
include '../connect.php';

// Check if the admin is already logged in
if (isset($_SESSION['admin_id'])) {
    header("Location: admindashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Basic validation
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } else {
        // Query the admins table to find the admin by email
        $sql = "SELECT admin_id, email, password FROM admins WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        if ($admin && password_verify($password, $admin['password'])) {
            // Login successful, set session variable
            $_SESSION['admin_id'] = $admin['admin_id'];
            header("Location: admindashboard.php");
            exit();
        } else {
            $error = "Invalid email or password.";
        }
        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Humanity Hub</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="logo missing" id="logo">
        </div>
        <div class="menu">
            <a href="../partials/index.php">Home</a>
            <a href="../partials/projects.php">Projects</a>
            <a href="../partials/volunteer.php">Volunteer</a>
            <a href="../partials/beneficiary.php">Beneficiary</a>
        </div>
        <h1>Admin Login</h1>
        <a href="../partials/donor.php"><button class="Donate">Donate Now</button></a>
    </header>

    <div class="login-container">
        <h2>Admin Login</h2>
        <?php if (isset($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form method="POST" action="adminlogin.php">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an admin account? Contact the system administrator.</p>
    </div>

    <footer>
        <?php include 'footer.php'; ?>
    </footer>
</body>
</html>
<?php $conn->close(); ?>