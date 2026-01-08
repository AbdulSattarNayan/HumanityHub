<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Humanity Hub</title>
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
            <a href="beneficiary.php">Beneficiary</a>
            <a href="login.html" class="active">Log in</a>
        </div>
        
        <h1>Login</h1>
        <a href="donor.php"><button class="Donate">Donate Now</button></a>
    </header>

    <main>
        <center><h2>Login </h2></center>

        <div id="loginForm">
            <form action="../actions/login.php" method="POST">
                <input type="text" name="volunteer_id" placeholder="Enter  ID" required>
                <input type="password" name="password" placeholder="Enter Password" required>
                <button type="submit">Log In</button>
            </form>
            <p id="loginMessage"></p>
        </div>

        <p>New here? <a href="/HumanityHub/actions/signup.php">Sign Up</a></p>
    </main>

    <?php include '../footer.php'; ?>

    <script src="../js/script.js"></script>
    <script src="../js/log-in.js"></script>
</body>
</html>
