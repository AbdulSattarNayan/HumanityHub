<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounts - Humanity Hub</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="logo missing" id="logo">
        </div>
        <h1>Accounts</h1>
        <div class="menu">
            <a href="admin.php">Back to Admin</a>
        </div>
    </header>

    <main>
        
        <h1>Track financial records efficiently.</h1>

        <div class="account-container">
            <div class="account-box">
                <h1>Cost</h1>
                <p>Monitor all expenses made by the organization.</p>
                <a href="cost.php"><button>View Details</button></a>
            </div>

            <div class="account-box">
                <h1>Earn</h1>
                <p>Track donations and other sources of revenue.</p>
                <a href="earn.php"><button>View Details</button></a>
            </div>
        </div>
    </main>

    <?php include '../footer.php'; ?>
    <script src="../js/script.js"></script>
</body>
</html>
