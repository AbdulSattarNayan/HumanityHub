<?php
// Include the database connection
include '../connect.php';

// Fetch all event costs from the database
$sql = "SELECT event_name, cost_sector, amount, date, image, goal FROM event_cost ORDER BY date ASC, event_name ASC";
$result = $conn->query($sql);
$events = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Create a unique key for each event based on event_name and date
        $event_key = $row['event_name'] . '|' . $row['date'];
        if (!isset($events[$event_key])) {
            $events[$event_key] = [
                'event_name' => $row['event_name'],
                'date' => $row['date'],
                'image' => $row['image'] ?? '../img/default-event.jpg', // Default image if NULL
                'goal' => $row['goal'] ?? 'Not specified', // Default goal if NULL
                'costs' => [],
            ];
        }
        $events[$event_key]['costs'][] = [
            'cost_sector' => $row['cost_sector'],
            'amount' => $row['amount'],
        ];
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Charity Event Costs</title>
  <link rel="stylesheet" href="../styles.css">
  <style>
    .cost-breakdown {
      margin-top: 10px;
    }
    .cost-breakdown p {
      margin: 5px 0;
    }
    .cost-breakdown .total {
      font-weight: bold;
      margin-top: 10px;
    }
    .image-upload {
      margin-top: 10px;
    }
    .image-upload input[type="file"] {
      display: inline-block;
      margin-right: 10px;
    }
    .image-upload button {
      padding: 5px 10px;
      background-color: #2196F3;
      color: white;
      border: none;
      border-radius: 3px;
      cursor: pointer;
    }
    .image-upload button:hover {
      background-color: #45a049;
    }
    .add-cost-btn, .update-cost-btn {
      margin-right: 10px;
      padding: 10px 20px;
      background-color: #2196F3;
      color: white;
      border: none;
      border-radius: 3px;
      cursor: pointer;
    }
    .update-cost-btn {
      background-color: #2196F3;
      margin-top: 20px;
    }
    .add-cost-btn:hover {
      background-color: #45a049;
    }
    .update-cost-btn:hover {
      background-color: #45a049;
      
    }
    /* Modal Styles */
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0.4);
    }
    .modal-content {
        background-color: #fefefe;
        margin: 5% auto;
        padding: 20px;
        border: 1px solid #888;
        width: 80%;
        max-width: 500px;
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
    }
    .close {
        color: #aaa;
        float: right;
        font-size: 28px;
        font-weight: bold;
        cursor: pointer;
    }
    .close:hover,
    .close:focus {
        color: black;
        text-decoration: none;
    }
    /* Form Styling */
    form label {
        display: block;
        margin-top: 10px;
    }
    form input, form select {
        width: 100%;
        padding: 8px;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 3px;
    }
    .submit-btn {
        margin-top: 15px;
        padding: 10px;
        background-color: #2196F3;
        color: white;
        border: none;
        border-radius: 3px;
        cursor: pointer;
        width: 100%;
    }
    .submit-btn:hover {
        background-color: #45a049;
    }
    .button{
      /* display: flex; */
      
    }
  </style>
</head>
<body>
  <header>
    <div class="logo">
        <img src="../img/logo.png" alt="logo missing" id="logo">
    </div>
    <div><h1>Charity Event Costs</h1></div>
    <div class="button">
     <!-- Add New Event Button -->
    <button id="addEventBtn" class="add-cost-btn">Add New Event</button>
    <!-- Update Existing Event Button -->
    <button id="updateEventBtn" class="update-cost-btn">Update Event Cost</button>
  </div>
  </header>

  <main>
   

    <!-- Modal for Adding New Event -->
    <div id="addEventModal" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <h2>Add New Event</h2>
            <form id="addEventForm" action="/HumanityHub/actions/add_event_cost.php" method="POST">
                <label for="event_name">Event Name:</label>
                <input type="text" id="event_name" name="event_name" required>

                <label for="cost_sector">Cost Sector:</label>
                <input type="text" id="cost_sector" name="cost_sector" required>

                <label for="amount">Amount ($):</label>
                <input type="number" id="amount" name="amount" required>

                <label for="date">Date:</label>
                <input type="date" id="date" name="date" required>

                <label for="goal">Fundraising Goal:</label>
                <input type="text" id="goal" name="goal" placeholder="e.g., $5000">

                <button type="submit" class="submit-btn">Add Event</button>
            </form>
        </div>
    </div>

    <!-- Modal for Updating Existing Event -->
    <div id="updateEventModal" class="modal">
        <div class="modal-content">
            <span class="close">×</span>
            <h2>Update Event Cost</h2>
            <form id="updateEventForm" action="/HumanityHub/actions/update_event_cost.php" method="POST">
                <label for="event_key">Select Event:</label>
                <select id="event_key" name="event_key" required>
                    <option value="">-- Select an Event --</option>
                    <?php foreach ($events as $event_key => $event): ?>
                        <option value="<?php echo htmlspecialchars($event_key); ?>">
                            <?php echo htmlspecialchars($event['event_name']) . ' (' . htmlspecialchars(date('d M Y', strtotime($event['date']))) . ')'; ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <label for="update_cost_sector">Existing Cost Sector (Update Amount):</label>
                <input type="text" id="update_cost_sector" name="cost_sector" placeholder="e.g., Venue">

                <label for="update_amount">Amount ($):</label>
                <input type="number" id="update_amount" name="amount">

                <label for="new_cost_sector">New Cost Category:</label>
                <input type="text" id="new_cost_sector" name="new_cost_sector" placeholder="e.g., Catering">

                <label for="new_amount">New Amount ($):</label>
                <input type="number" id="new_amount" name="new_amount">

                <button type="submit" class="submit-btn">Update Cost</button>
            </form>
        </div>
    </div>

    <!-- Event List -->
    <div class="event-list">
      <?php if (!empty($events)): ?>
        <?php foreach ($events as $event_key => $event): ?>
          <div class="event" data-event-key="<?php echo htmlspecialchars($event_key); ?>">
            <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="missing" class="event-image">
            <h2><?php echo htmlspecialchars($event['event_name']); ?></h2>
            <p><?php echo htmlspecialchars(date('l d F Y', strtotime($event['date']))); ?></p>
            <p>Fundraising Goal: <?php echo htmlspecialchars($event['goal']); ?></p>
            <div class="cost-breakdown">
              <?php
              $total_cost = 0;
              foreach ($event['costs'] as $cost) {
                $total_cost += $cost['amount'];
                echo '<p>' . htmlspecialchars($cost['cost_sector']) . ': $' . htmlspecialchars($cost['amount']) . '</p>';
              }
              echo '<p class="total">Total Cost: $' . htmlspecialchars($total_cost) . '</p>';
              ?>
            </div>
            <div class="image-upload">
              <form action="/HumanityHub/actions/upload_event_image.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="event_key" value="<?php echo htmlspecialchars($event_key); ?>">
                <input type="file" name="event_image" accept="image/*" required>
                <button type="submit">Upload Image</button>
              </form>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No events recorded yet.</p>
      <?php endif; ?>
    </div>

    <!-- Pop-up for success/error messages -->
    <div id="overlay" style="display: none;">
      <div id="popup">
        <p id="popupMessage"></p>
      </div>
    </div>
  </main>
  <?php include '../footer.php'; ?>
  <script src="../js/script.js"></script>
  <script src="../js/add-cost.js"></script>
  <script>
    // Modal handling for Add New Event and Update Event Cost
    const addEventBtn = document.getElementById('addEventBtn');
    const addEventModal = document.getElementById('addEventModal');
    const updateEventBtn = document.getElementById('updateEventBtn');
    const updateEventModal = document.getElementById('updateEventModal');
    const closeModalSpans = document.getElementsByClassName('close');

    addEventBtn.onclick = function() {
        addEventModal.style.display = 'block';
    };

    updateEventBtn.onclick = function() {
        updateEventModal.style.display = 'block';
    };

    for (let span of closeModalSpans) {
        span.onclick = function() {
            addEventModal.style.display = 'none';
            updateEventModal.style.display = 'none';
        };
    }

    window.onclick = function(event) {
        if (event.target == addEventModal) {
            addEventModal.style.display = 'none';
        }
        if (event.target == updateEventModal) {
            updateEventModal.style.display = 'none';
        }
    };

    // Check for a success/error message in the URL query parameters
    document.addEventListener('DOMContentLoaded', function() {
      const urlParams = new URLSearchParams(window.location.search);
      const status = urlParams.get('status');
      const message = urlParams.get('message');

      if (status && message) {
        console.log("Showing pop-up with status:", status, "and message:", message);
        const popupMessage = document.getElementById('popupMessage');
        popupMessage.textContent = message;

        document.getElementById('overlay').style.display = 'block';
        document.getElementById('popup').style.display = 'block';

        window.history.replaceState({}, document.title, window.location.pathname);
      }
    });

    function closePopup() {
      document.getElementById('overlay').style.display = 'none';
      document.getElementById('popup').style.display = 'none';
    }
  </script>
</body>
</html>