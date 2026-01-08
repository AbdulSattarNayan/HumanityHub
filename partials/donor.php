<?php
session_start();

// Include the database connection file
include '../connect.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $donor_name = $_POST['donor_name'] ?? '';
    $email = $_POST['email'] ?? '';
    $amount = $_POST['amount'] ?? '';

    // Basic validation
    if (empty($donor_name) || empty($amount)) {
        $error = "Please fill in all required fields.";
    } elseif (!is_numeric($amount) || $amount <= 0) {
        $error = "Please enter a valid donation amount.";
    } elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        $conn->begin_transaction();

        try {
            $sql = "INSERT INTO donations (donor_name, email, amount) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssd", $donor_name, $email, $amount);
            $stmt->execute();
            $stmt->close();

            $sql = "UPDATE balance SET total_balance = total_balance + ? WHERE id = 1";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("d", $amount);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $success = true;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error processing your donation: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Donor - Humanity Hub</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.6);
        }

        .modal-content {
            background-color: #fff;
            margin: 15% auto;
            padding: 30px 20px 20px 20px;
            border-radius: 10px;
            width: 90%;
            max-width: 400px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            position: relative;
        }

        .modal-content h3 {
            margin-top: 0;
        }

        .modal-content button.close-btn {
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .modal-content button.close-btn:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="logo missing" id="logo">
        </div>

        <div class="menu">
            <a href="index.php">Home</a>
            <a href="projects.php">Projects</a>
            <a href="beneficiary.php">Beneficiary</a>
            <a href="../partials/login.php">Log in</a>
        </div>

        <h1>Donate</h1>
        <a href="donor.php"><button class="Donate">Donate Now</button></a>
    </header>

    <main>
        <center><h2>Make a Donation</h2></center>

        <div id="donationForm">
            <?php if (isset($error)): ?>
                <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
            <?php endif; ?>

            <form action="donor.php" method="POST">
                <input type="text" name="donor_name" placeholder="Your Name" required>
                <input type="email" name="email" placeholder="Your Email (optional)">
                <input type="number" name="amount" placeholder="Donation Amount ($)" step="0.01" min="1" required>
                <button type="submit">Donate Now</button>
            </form>
        </div>
    </main>
    <?php include '../footer.php'; ?>

    <!-- Modal HTML -->
    <div id="thankYouModal" class="modal">
        <div class="modal-content">
            <h3>Thank You, <?php echo isset($donor_name) ? htmlspecialchars($donor_name) : ''; ?>!</h3>
            <p>Your donation of $<?php echo isset($amount) ? number_format($amount, 2) : ''; ?> is greatly helping us.</p>
            <button class="close-btn" onclick="closeModal()">Close</button>
        </div>
    </div>

    <script src="../js/script.js"></script>

    <script>
        function closeModal() {
            document.getElementById("thankYouModal").style.display = "none";
        }

        <?php if (isset($success) && $success): ?>
            document.getElementById("thankYouModal").style.display = "block";
        <?php endif; ?>
    </script>
</body>
</html>

<?php if (isset($conn)) $conn->close(); ?>
