<?php
session_start();
include '../connect.php';

// Initialize error message variable
$error = '';
$success_message = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'] ?? '';
    $father_name = $_POST['father_name'] ?? '';
    $mother_name = $_POST['mother_name'] ?? '';
    $phone_number = $_POST['phone'] ?? '';
    $blood_group = $_POST['blood_group'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirmPassword'] ?? '';

    // Validation
    if (empty($name) || empty($father_name) || empty($mother_name) || empty($phone_number) || empty($blood_group) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "All fields are required.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone_number)) {
        $error = "Invalid phone number. Please enter a valid number (10-15 digits).";
    } elseif (strlen($password) < 8) {
        $error = "Password must be at least 8 characters long.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        $valid_blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
        if (!in_array($blood_group, $valid_blood_groups)) {
            $error = "Invalid blood group selected.";
        } else {
            // Check if the volunteers table exists
            $table_check = $conn->query("SHOW TABLES LIKE 'volunteers'");
            if ($table_check->num_rows == 0) {
                $error = "The 'volunteers' table does not exist in the database.";
            } else {
                // Check if email already exists
                $check_sql = "SELECT volunteer_id FROM volunteers WHERE email = ?"; // Changed 'id' to 'volunteer_id'
                $check_stmt = $conn->prepare($check_sql);
                if ($check_stmt === false) {
                    $error = "Prepare failed: " . $conn->error;
                    error_log("Prepare failed for email check: " . $conn->error);
                } else {
                    $check_stmt->bind_param("s", $email);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();

                    if ($check_result->num_rows > 0) {
                        $error = "Email already exists.";
                    } else {
                        // Generate volunteer_id
                        $count_sql = "SELECT COUNT(*) as total FROM volunteers";
                        $count_result = $conn->query($count_sql);
                        $count_row = $count_result->fetch_assoc();
                        $new_count = $count_row['total'] + 1;
                        $volunteer_id = "V-" . str_pad($new_count, 5, "0", STR_PAD_LEFT); // e.g., V-00001

                        // Hash the password
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

                        // Insert the new volunteer with volunteer_id
                        $sql = "INSERT INTO volunteers (volunteer_id, name, father_name, mother_name, phone_number, blood_group, email, password) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt = $conn->prepare($sql);
                        if ($stmt === false) {
                            $error = "Prepare failed: " . $conn->error;
                            error_log("Prepare failed for insert: " . $conn->error);
                        } else {
                            $stmt->bind_param("ssssssss", $volunteer_id, $name, $father_name, $mother_name, $phone_number, $blood_group, $email, $hashed_password);

                            if ($stmt->execute()) {
                                // Log the user in by setting the session
                                $_SESSION['volunteer_id'] = $volunteer_id;
                                $_SESSION['is_admin'] = false; // Not an admin

                                // Store the success message in the session
                                $_SESSION['signup_message'] = "Registration successful! Your Volunteer ID is $volunteer_id.";

                                // Redirect to volunteerdashboard.php
                                header("Location: /HumanityHub/actions/volunteerdashboard.php");
                                exit();
                            } else {
                                $error = "Error during registration: " . $stmt->error;
                                error_log("Insert failed: " . $stmt->error);
                            }
                            $stmt->close();
                        }
                    }
                    $check_stmt->close();
                }
            }
        }
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - Humanity Hub</title>
    <link rel="stylesheet" href="/HumanityHub/styles.css">
</head>
<body>
    <header>
    <div class="logo">
            <img src="../img/logo.png" alt="logo missing" id="logo">
        </div>
        
        <div class="menu">
            <a href="../partials/index.php">Home</a>
            <a href="../partials/projects.php">Projects</a>
            <a href="../partials/beneficiary.php">Beneficiary</a>
            <a href="logout.php">Log Out</a>
        </div>
        <h1>Volunteer Sign Up</h1>
        <a href="../partials/donor.php"><button class="Donate">Donate Now</button></a>
    </header>

    <main>
        <h2>Create a Volunteer Account</h2>
        <?php if (!empty($error)): ?>
            <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>
        <form action="/HumanityHub/actions/signup.php" method="POST">
            <input type="text" name="name" placeholder="Full Name" required>
            <input type="text" name="father_name" placeholder="Father's Name" required>
            <input type="text" name="mother_name" placeholder="Mother's Name" required>
            <input type="tel" name="phone" placeholder="Phone Number" required>
            
            <select name="blood_group" required>
                <option value="" disabled selected>Blood Group</option>
                <option value="A+">A+</option>
                <option value="A-">A-</option>
                <option value="B+">B+</option>
                <option value="B-">B-</option>
                <option value="O+">O+</option>
                <option value="O-">O-</option>
                <option value="AB+">AB+</option>
                <option value="AB-">AB-</option>
            </select>

            <input type="email" name="email" placeholder="Enter Email" required>
            <input type="password" name="password" placeholder="Enter Password" required>
            <input type="password" name="confirmPassword" placeholder="Confirm Password" required>

            <button type="submit">Sign Up</button>
        </form>

        <p>Already have an account? <a href="/HumanityHub/partials/login.php">Log in here</a></p>
    </main>
    <?php include '../footer.php'; ?>
    <script src="/HumanityHub/js/script.js"></script>
    <script src="/HumanityHub/js/signup.js"></script>
</body>
</html>