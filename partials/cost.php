<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cost - Humanity Hub</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="logo missing" id="logo">
        </div>
        <h1>Cost Overview</h1>
        <div class="menu">
            <a href="accounts.php">Back to Accounts</a>
        </div>
    </header>

    <main>
       
        <h1>Manage all expenses for events and daily operations.</h1>

        <div class="cost-container">
            <div class="cost-box">
                <h3>Events</h3>
                <p>Track the cost of organizing charity events.</p>
                <a href="event-cost.php"><button>View Details</button></a>
            </div>

            <div class="cost-box">
                <h3>Regular</h3>
                <p>Monitor daily operational expenses.</p>
                <a href="regular.php"><button>View Details</button></a>
            </div>
        </div>
    </main>

    <?php include '../footer.php'; ?>
    <script src="../js/script.js"></script>
</body>
</html>
