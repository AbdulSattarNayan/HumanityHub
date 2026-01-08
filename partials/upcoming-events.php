<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upcoming Events - Humanity Hub</title>
    <link rel="stylesheet" href="../styles.css">
    <style>
        /* Style the event list and event boxes */
        .event-list {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            justify-content: center;
            margin: 20px 0;
        }
        .event {
            background-color: #00879E;
            padding: 20px;
            width: 400px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            border-radius: 15px;
            text-align: center;
            transition: transform 0.3s ease-in-out;
        }
        .event:hover {
    
            background-color: #FFAB5B;
            transform: translateY(-5px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .event h2 {
            font-size: 1.8em;
            margin: 0 0 10px;
            color: #333;
        }
        .event p {
            margin: 5px 0;
            color: white;
        }
        .event p strong {
            color: #FFF8F8;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="logo missing" id="logo">
        </div>
        <h1>Upcoming Events</h1>
        <div class="menu">
            <a href="events.php">Back to Events</a>
        </div>
    </header>

    <main>
        <h1>Be a part of our upcoming initiatives!</h1>

        <?php
        // Include the database connection
        include '../connect.php';

        // Current date for comparison (April 12, 2025)
        $current_date = '2025-04-12';

        // Query to fetch upcoming events (event_date >= current date)
        $sql = "SELECT id, event_name, description, event_date, location, created_at 
                FROM events 
                WHERE event_date >= ? 
                ORDER BY event_date ASC";
        $stmt = $conn->prepare($sql);
        if ($stmt === false) {
            echo "<p>Error preparing query: " . $conn->error . "</p>";
        } else {
            $stmt->bind_param("s", $current_date);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Start the event list
                echo '<div class="event-list">';

                // Fetch and display each event
                while ($row = $result->fetch_assoc()) {
                    echo '<div class="event">';
                    echo '<h2>' . htmlspecialchars($row['event_name']) . '</h2>';
                    echo '<p><strong>ID:</strong> ' . htmlspecialchars($row['id']) . '</p>';
                    echo '<p><strong>Description:</strong> ' . htmlspecialchars($row['description']) . '</p>';
                    echo '<p><strong>Event Date:</strong> ' . htmlspecialchars($row['event_date']) . '</p>';
                    echo '<p><strong>Location:</strong> ' . htmlspecialchars($row['location']) . '</p>';
                    echo '<p><strong>Created At:</strong> ' . htmlspecialchars($row['created_at']) . '</p>';
                    echo '</div>';
                }

                echo '</div>';
            } else {
                echo '<p>No upcoming events found.</p>';
            }

            $stmt->close();
        }

        $conn->close();
        ?>
    </main>

    <?php include '../footer.php'; ?>
    <script src="../js/script.js"></script>
</body>
</html>