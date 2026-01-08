<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Event - Humanity Hub</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="logo missing" id="logo">
        </div>
        <div class="menu">
            <a href="events.php">Back to Events</a>
        </div>
    </header>

    <main>
        <h1>Add a New Event</h1>
        <form id="eventForm" action="/HumanityHub/actions/add_event.php" method="POST">
            <input type="text" id="eventName" name="event_name" placeholder="Event Name" required>
            <input type="date" id="eventDate" name="event_date" required>
            <input type="text" id="eventLocation" name="location" placeholder="Event Location" required>
            <textarea id="eventDescription" name="description" placeholder="Event Description" required></textarea>
            <button type="submit">Add Event</button>
        </form>

        <div id="overlay" style="display: none;">
            <div id="popup">
                <p id="popupMessage"></p>
                <!-- <button onclick="closePopup()">Close</button> -->
            </div>
        </div>
    </main>

    <?php include '../footer.php'; ?>
    <script src="../js/script.js"></script>
    <script src="../js/add-event.js"></script>
</body>
</html>