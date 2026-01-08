<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - Humanity Hub</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="logo missing" id="logo">
        </div>
        <h1>Manage Events</h1>
        <div class="menu">
            <a href="admin.php">Back to Admin</a>
        </div>
    </header>

    <main>
       
        <h1>Here you can manage all charity and volunteer events.</h1>

        <div class="events-container">
            <a href="add-event.php" class="event-box">Add Event</a>
            <a href="past-events.php" class="event-box">Past Events</a>
            <a href="upcoming-events.php" class="event-box">Upcoming Events</a>
        </div>
    </main>

    <?php include '../footer.php'; ?>
    <script src="../js/script.js"></script>
</body>
</html>
