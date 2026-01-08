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
  <title>Charity Event Costs (Static View)</title>
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
            <a href="login.php">Log in</a>
           
        </div>
        <a href="donor.php"><button class="Donate">Donate Now</button></a>
    <h1>Projects</h1>
  </header>

  <main>
    <!-- Event List -->
    <div class="event-list">
      <?php if (!empty($events)): ?>
        <?php foreach ($events as $event_key => $event): ?>
          <div class="event">
            <img src="<?php echo htmlspecialchars($event['image']); ?>" alt="missing">
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
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p>No events recorded yet.</p>
      <?php endif; ?>
    </div>
  </main>
  <<?php include '../footer.php'; ?>
  <script src="../js/script.js"></script>
</body>
</html>