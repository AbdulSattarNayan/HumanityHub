<?php
// Include the database connection
include '../connect.php';

// Fetch all costs from the database
$sql = "SELECT sector, amount, date FROM regular_cost ORDER BY date ASC";
$result = $conn->query($sql);
$costs = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $month = date('F', strtotime($row['date'])); // Get the month name (e.g., January)
        $costs[$month][] = $row;
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Monthly Cost Report</title>
    <link rel="stylesheet" href="../styles.css">
</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="logo missing" id="logo">
        </div>
        <h1>Monthly Cost Report</h1>
    </header>

    <main>
        <!-- Add Cost Button -->
        <button id="addCostBtn" class="add-cost-btn">Add Cost</button>

        <!-- Pop-up Form -->
        <div id="addCostModal" class="modal">
            <div class="modal-content">
                <span class="close">×</span>
                <h2>Add Cost</h2>
                <form id="costForm" action="../actions/add_cost.php" method="POST">
                    <label for="sector">Cost Sector:</label>
                    <input type="text" id="sector" name="sector" required>

                    <label for="amount">Amount ($):</label>
                    <input type="number" id="amount" name="amount" required>

                    <label for="date">Date:</label>
                    <input type="date" id="date" name="date" required>

                    <button type="submit" class="submit-btn">Submit</button>
                </form>
            </div>
        </div>

        <!-- Display costs by month -->
        <?php if (!empty($costs)): ?>
            <?php foreach ($costs as $month => $month_costs): ?>
                <section class="month-table">
                    <h2><?php echo htmlspecialchars($month); ?></h2>
                    <table>
                        <thead>
                            <tr>
                                <th>Cost Sector</th>
                                <th>Amount ($)</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $total = 0;
                            foreach ($month_costs as $cost):
                                $total += $cost['amount'];
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cost['sector']); ?></td>
                                    <td><?php echo htmlspecialchars($cost['amount']); ?></td>
                                    <td><?php echo htmlspecialchars($cost['date']); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <!-- Sum row -->
                            <tr class="sum-row">
                                <td><strong>Sum</strong></td>
                                <td><strong><?php echo htmlspecialchars($total); ?></strong></td>
                                <td>-</td>
                            </tr>
                        </tbody>
                    </table>
                </section>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No costs recorded yet.</p>
        <?php endif; ?>

        <!-- Pop-up for success/error messages -->
        <div id="overlay" style="display: none;">
            <div id="popup">
                <p id="popupMessage"></p>
                <button onclick="closePopup()">Close</button>
            </div>
        </div>
    </main>

    <?php include '../footer.php'; ?>

    <script src="../js/script.js"></script>
    <script src="../js/add-cost.js"></script>
    <script>
        // Check for a success/error message in the URL query parameters
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            const message = urlParams.get('message');

            if (status && message) {
                console.log("Showing pop-up with status:", status, "and message:", message);
                const popupMessage = document.getElementById('popupMessage');
                popupMessage.textContent = message;

                // Show the pop-up and overlay
                document.getElementById('overlay').style.display = 'block';
                document.getElementById('popup').style.display = 'block';

                // Clear the URL parameters to prevent the pop-up from showing on refresh
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });

        function closePopup() {
            // Hide the pop-up and overlay
            document.getElementById('overlay').style.display = 'none';
            document.getElementById('popup').style.display = 'none';
        }
    </script>
</body>
</html>