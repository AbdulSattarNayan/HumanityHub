<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beneficiary - Humanity Hub</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="logo missing" id="logo">
        </div>
        
        <div class="menu">
            <a href="index.php">Home</a>
            <a href="projects.php">Projects</a>
            <a href="beneficiary.php" class="active">Beneficiary</a>
            <a href="login.php">Log in</a>
        </div>

        <h1>Beneficiary</h1>
        <a href="donor.php"><button class="Donate">Donate Now</button></a>
    </header>

    <main>
        <h2>Request Aid</h2>

        <form id="aidForm" action="../actions/submit_request.php" method="POST">
            <label for="beneficiaryName">Full Name:</label>
            <input type="text" id="beneficiaryName" name="full_name" placeholder="Enter your full name" required>

            <label for="beneficiaryContact">Contact Number:</label>
            <input type="tel" id="beneficiaryContact" name="contact_number" placeholder="Enter your contact number" required>

            <label for="aidType">Aid Request Type:</label>
            <select id="aidType" name="aid_type" required>
                <option value="" disabled selected>Select Aid Type</option>
                <option value="Medical">Medical Assistance</option>
                <option value="Education">Educational Support</option>
                <option value="Food">Food Supplies</option>
                <option value="Shelter">Shelter Assistance</option>
                <option value="Other">Other</option>
            </select>

            <label for="aidRequest">Describe Your Need:</label>
            <textarea id="aidRequest" name="description" placeholder="Provide details about your aid request" required></textarea>

            <button type="submit">Submit Request</button>
        </form>
    </main>

    <!-- Pop-up Confirmation -->
    <div class="overlay" id="overlay"></div>
    <div class="popup" id="popup">
        <h3>✅ Aid Request Submitted</h3>
        <p id="popupMessage"></p>
        <button onclick="closePopup()">Close</button>
    </div>

    <?php include '../footer.php'; ?>

    <script src="../js/script.js"></script>
    <script src="../js/beneficiary.js"></script>
</body>
</html>