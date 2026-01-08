<?php
session_start();

// Include the database connection file
include '../connect.php';

// Include FPDF library with error handling
$fpdf_path = '../lib/fpdf/fpdf.php';
if (!file_exists($fpdf_path)) {
    die("FPDF library not found at: $fpdf_path. Please ensure the FPDF library is installed in HumanityHub/lib/fpdf/.");
}
require $fpdf_path;

// Include the Polygon extension
$fpdf_polygon_path = '../lib/fpdf/fpdf_polygon.php';
if (!file_exists($fpdf_polygon_path)) {
    die("FPDF Polygon extension not found at: $fpdf_polygon_path. Please ensure fpdf_polygon.php is in HumanityHub/lib/fpdf/.");
}
require $fpdf_polygon_path;

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['volunteer_id'])) {
    header("Location: login.php");
    exit();
} elseif (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1) {
    header("Location: ../partials/admin.php");
    exit();
}
$volunteer_id = $_SESSION['volunteer_id'];

// Check if the volunteers table exists
$table_check = $conn->query("SHOW TABLES LIKE 'volunteers'");
if ($table_check->num_rows == 0) {
    die("The 'volunteers' table does not exist in the database.");
}

// Fetch volunteer details, including photo_path
$sql = "SELECT name, volunteer_id, father_name, mother_name, phone_number, blood_group, email, photo_path 
        FROM volunteers 
        WHERE volunteer_id = ?";
$stmt = $conn->prepare($sql);
if ($stmt === false) {
    $error = "Prepare failed: " . $conn->error;
    error_log("Prepare failed in volunteerdashboard.php (fetch volunteer): " . $conn->error);
    die($error);
}

$stmt->bind_param("s", $volunteer_id);
$stmt->execute();
$result = $stmt->get_result();
$volunteer = $result->fetch_assoc();

if (!$volunteer) {
    die("No volunteer data found for ID: " . htmlspecialchars($volunteer_id));
}

// Fetch the events the volunteer has joined
$events_sql = "SELECT e.id AS event_id, e.event_name AS title, e.event_date, e.description, e.location 
               FROM events e 
               INNER JOIN volunteer_events ve ON e.id = ve.event_id 
               WHERE ve.volunteer_id = ?";
$events_stmt = $conn->prepare($events_sql);
if ($events_stmt === false) {
    $error = "Prepare failed for events query: " . $conn->error;
    error_log("Prepare failed in volunteerdashboard.php (fetch events): " . $conn->error);
    die($error);
}
$events_stmt->bind_param("s", $volunteer_id);
$events_stmt->execute();
$events_result = $events_stmt->get_result();
$joined_events = $events_result->fetch_all(MYSQLI_ASSOC);
$events_stmt->close();

// Handle event joining (when "Join Now" is clicked)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['join_event'])) {
    $event_id = $_POST['event_id'];
    
    // Check if the volunteer has already joined the event
    $check_sql = "SELECT id FROM volunteer_events WHERE volunteer_id = ? AND event_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    if ($check_stmt === false) {
        $error = "Prepare failed for event check: " . $conn->error;
        error_log("Prepare failed in volunteerdashboard.php (check event): " . $conn->error);
        die($error);
    }
    $check_stmt->bind_param("si", $volunteer_id, $event_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows === 0) {
        // Insert the join record
        $insert_sql = "INSERT INTO volunteer_events (volunteer_id, event_id) VALUES (?, ?)";
        $insert_stmt = $conn->prepare($insert_sql);
        if ($insert_stmt === false) {
            $error = "Prepare failed for event insert: " . $conn->error;
            error_log("Prepare failed in volunteerdashboard.php (insert event): " . $conn->error);
            die($error);
        }
        $insert_stmt->bind_param("si", $volunteer_id, $event_id);
        $insert_stmt->execute();
        $insert_stmt->close();
    }
    $check_stmt->close();
    
    // Refresh the page to update the "Your Events" section
    header("Location: volunteerdashboard.php");
    exit();
}

// Handle certificate generation
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['generate_certificate'])) {
    // Check if the volunteer has joined any events
    if (count($joined_events) === 0) {
        echo "<script>alert('You must join at least one event to generate a certificate.');</script>";
    } else {
        $event_id = $_POST['event_id'];
        // Find the selected event
        $selected_event = null;
        foreach ($joined_events as $event) {
            if ($event['event_id'] == $event_id) {
                $selected_event = $event;
                break;
            }
        }

        if ($selected_event) {
            $volunteer_name = $volunteer['name'];
            $event_name = $selected_event['title'];

            // Generate a unique certificate ID (e.g., "C" + random number)
            $certificate_id = "C" . str_pad(rand(10000, 99999), 5, "0", STR_PAD_LEFT);

            // Create a new PDF document (A4 size, portrait orientation) using FPDF_Polygon
            $pdf = new FPDF_Polygon('P', 'mm', 'A4');
            $pdf->AddPage();

            // Check if Great Vibes font exists, otherwise use Helvetica as fallback
            $greatvibes_font_path = '../lib/fpdf/font/greatvibes.php';
            $use_greatvibes = file_exists($greatvibes_font_path);
            if ($use_greatvibes) {
                $pdf->AddFont('GreatVibes', '', 'greatvibes.php');
            }

            // Double border (yellow inner, black outer)
            $pdf->SetDrawColor(255, 204, 0); // Yellow
            $pdf->SetLineWidth(1);
            $pdf->Rect(10, 10, 190, 277); // Inner border
            $pdf->SetDrawColor(0, 0, 0); // Black
            $pdf->SetLineWidth(0.5);
            $pdf->Rect(8, 8, 194, 281); // Outer border

            // Certificate ID (top-left corner)
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->SetTextColor(0, 0, 0); // Black
            $pdf->SetXY(15, 15);
            $pdf->Cell(0, 0, 'Certificate ID: ' . $certificate_id, 0, 0, 'L');

            // Add Humanity Hub logo at the top middle with a white background
            if (file_exists('../img/logo2.png')) {
                // Draw a white background rectangle behind the logo
                $logo_width = 50; // Logo width in mm
                $logo_height = 40; // Logo height in mm (assuming a square logo; adjust if needed)
                $logo_x = 75; // Centered X position (A4 width = 210mm, logo width = 40mm, so 85 = (210-40)/2)
                $logo_y = 13; // Y position at the top
                $pdf->SetFillColor(255, 255, 255); // White background
                $pdf->Rect($logo_x, $logo_y, $logo_width, $logo_height, 'F'); // Filled rectangle
                // Place the logo on top of the white background
                $pdf->Image('../img/logo2.png', $logo_x, $logo_y, $logo_width, $logo_height);
            } else {
                // Fallback text if logo is missing
                $pdf->SetFont('Helvetica', 'B', 14);
                $pdf->SetXY(0, 10);
                $pdf->Cell(0, 0, 'Humanity Hub', 0, 0, 'C');
            }

            // Background watermark (more visible "Humanity Hub" in the middle)
            $pdf->SetFont('Helvetica', '', 70); // Increased font size for visibility
            $pdf->SetTextColor(200, 200, 200); // Slightly darker gray for better visibility
            $pdf->SetXY(0, 120); // Adjusted Y position to center vertically on A4 (297mm height)
            $pdf->Cell(0, 0, 'Humanity Hub', 0, 0, 'C');

            // Decorative corner shapes (simulating the black and yellow geometric shapes)
            // Top-right corner
            $pdf->SetFillColor(0, 0, 0); // Black
            $pdf->Polygon([180, 10, 200, 10, 200, 30], 'F');
            $pdf->SetFillColor(255, 204, 0); // Yellow
            $pdf->Polygon([185, 10, 200, 10, 200, 25], 'F');

            // Bottom-left corner
            $pdf->SetFillColor(0, 0, 0); // Black
            $pdf->Polygon([10, 257, 30, 287, 10, 287], 'F');
            $pdf->SetFillColor(255, 204, 0); // Yellow
            $pdf->Polygon([10, 262, 25, 287, 10, 287], 'F');

            // Title: "CERTIFICATE OF PARTICIPATION" (adjusted position to account for logo)
            $pdf->SetFont('Helvetica', 'B', 30);
            $pdf->SetTextColor(0, 0, 0); // Black
            $pdf->SetXY(0, 60); // Moved down to make space for the logo
            $pdf->Cell(0, 0, 'CERTIFICATE OF PARTICIPATION', 0, 0, 'C');

            // "This is to certify that"
            $pdf->SetFont('Helvetica', '', 16);
            $pdf->SetXY(0, 90); // Adjusted position
            $pdf->Cell(0, 0, 'This is to certify that', 0, 0, 'C');

            // Volunteer Name (in script font if available, otherwise Helvetica)
            if ($use_greatvibes) {
                $pdf->SetFont('GreatVibes', '', 40);
            } else {
                $pdf->SetFont('Helvetica', 'B', 30);
            }
            $pdf->SetTextColor(0, 0, 0); // Black
            $pdf->SetXY(0, 110); // Adjusted position
            $pdf->Cell(0, 0, $volunteer_name, 0, 0, 'C');

            // "has participated in"
            $pdf->SetFont('Helvetica', '', 16);
            $pdf->SetXY(0, 140); // Adjusted position
            $pdf->Cell(0, 0, 'has participated in', 0, 0, 'C');

            // Event Name
            $pdf->SetFont('Helvetica', 'B', 20);
            $pdf->SetXY(0, 160); // Adjusted position
            $pdf->Cell(0, 0, $event_name, 0, 0, 'C');

            // Footer: Signature and Name
            // Add the signature image above the CEO's name
            if (file_exists('../img/signature.png')) {
                $pdf->Image('../img/signature.png', 133, 210, 80); // Signature image (50mm wide, positioned at bottom-right)
            }

            // CEO's name and title (adjusted position to make space for the signature image)
            if ($use_greatvibes) {
                $pdf->SetFont('GreatVibes', '', 20);
            } else {
                $pdf->SetFont('Helvetica', '', 16);
            }
            $pdf->SetTextColor(0, 0, 0); // Black
            $pdf->SetXY(125, 230); // Moved down to make space for the signature image
            $pdf->Cell(0, 0, 'Md Abdul Sattar Nayan', 0, 0, 'R'); // CEO name
            $pdf->SetFont('Helvetica', '', 12);
            $pdf->SetXY(125, 240); // Adjusted position
            $pdf->Cell(0, 0, 'CEO, Humanity Hub', 0, 0, 'R');

            // Output the PDF as a download
            $pdf->Output('D', 'certificate_' . $volunteer_id . '_' . $event_id . '.pdf');
            exit();
        } else {
            echo "<script>alert('Invalid event selected.');</script>";
        }
    }
}

// Handle photo upload
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['photo'])) {
    $target_dir = "../img/uploads/";
    $file_extension = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
    $target_file = $target_dir . "volunteer_" . $volunteer_id . "." . $file_extension;
    $uploadOk = 1;
    $imageFileType = strtolower($file_extension);

    // Check if the file is an actual image
    $check = getimagesize($_FILES['photo']['tmp_name']);
    if ($check === false) {
        echo "<script>alert('File is not an image.');</script>";
        $uploadOk = 0;
    }

    // Check file size (limit to 5MB)
    if ($_FILES['photo']['size'] > 5000000) {
        echo "<script>alert('Sorry, your file is too large. Maximum size is 5MB.');</script>";
        $uploadOk = 0;
    }

    // Allow only certain file formats
    if ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        echo "<script>alert('Sorry, only JPG, JPEG, PNG & GIF files are allowed.');</script>";
        $uploadOk = 0;
    }

    // Check if $uploadOk is set to 0 by an error
    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES['photo']['tmp_name'], $target_file)) {
            // Update the photo_path in the database
            $photo_path = "img/uploads/volunteer_" . $volunteer_id . "." . $file_extension;
            $update_sql = "UPDATE volunteers SET photo_path = ? WHERE volunteer_id = ?";
            $update_stmt = $conn->prepare($update_sql);
            if ($update_stmt === false) {
                $error = "Prepare failed for photo update: " . $conn->error;
                error_log("Prepare failed in volunteerdashboard.php (update photo): " . $conn->error);
                die($error);
            }
            $update_stmt->bind_param("ss", $photo_path, $volunteer_id);
            if ($update_stmt->execute()) {
                echo "<script>alert('Photo uploaded successfully!'); window.location.href='volunteerdashboard.php';</script>";
            } else {
                echo "<script>alert('Error updating photo path in database: " . $conn->error . "');</script>";
            }
            $update_stmt->close();
        } else {
            echo "<script>alert('Sorry, there was an error uploading your file.');</script>";
        }
    }
}

// Handle volunteer information update
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_info'])) {
    $name = $_POST['name'] ?? '';
    $father_name = $_POST['father_name'] ?? '';
    $mother_name = $_POST['mother_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $blood_group = $_POST['blood_group'] ?? '';
    $email = $_POST['email'] ?? '';

    // Validation
    if (empty($name) || empty($father_name) || empty($mother_name) || empty($phone) || empty($blood_group) || empty($email)) {
        echo "<script>alert('All fields are required.');</script>";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<script>alert('Invalid email format.');</script>";
    } elseif (!preg_match('/^[0-9]{10,15}$/', $phone)) {
        echo "<script>alert('Invalid phone number. Please enter a valid number (10-15 digits).');</script>";
    } else {
        $valid_blood_groups = ['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'];
        if (!in_array($blood_group, $valid_blood_groups)) {
            echo "<script>alert('Invalid blood group selected.');</script>";
        } else {
            // Check if email is already used by another volunteer
            $email_check_sql = "SELECT volunteer_id FROM volunteers WHERE email = ? AND volunteer_id != ?";
            $email_stmt = $conn->prepare($email_check_sql);
            if ($email_stmt === false) {
                $error = "Prepare failed for email check: " . $conn->error;
                error_log("Prepare failed in volunteerdashboard.php (email check): " . $conn->error);
                die($error);
            }
            $email_stmt->bind_param("ss", $email, $volunteer_id);
            $email_stmt->execute();
            $email_result = $email_stmt->get_result();
            if ($email_result->num_rows > 0) {
                echo "<script>alert('Email already exists. Please use a different email.');</script>";
            } else {
                // Update volunteer information in the database
                $update_sql = "UPDATE volunteers SET name = ?, father_name = ?, mother_name = ?, phone_number = ?, blood_group = ?, email = ? WHERE volunteer_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                if ($update_stmt === false) {
                    $error = "Prepare failed for info update: " . $conn->error;
                    error_log("Prepare failed in volunteerdashboard.php (update info): " . $conn->error);
                    die($error);
                }
                $update_stmt->bind_param("sssssss", $name, $father_name, $mother_name, $phone, $blood_group, $email, $volunteer_id);

                if ($update_stmt->execute()) {
                    echo "<script>alert('Information updated successfully!'); window.location.href='volunteerdashboard.php';</script>";
                } else {
                    echo "<script>alert('Error updating information: " . $conn->error . "');</script>";
                }
                $update_stmt->close();
            }
            $email_stmt->close();
        }
    }
}

$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Volunteer Dashboard - Humanity Hub</title>
    <link rel="stylesheet" href="../styles.css">
    <script>
        function toggleEditForm() {
            console.log("toggleEditForm called (inline)");
            const form = document.getElementById("editForm");
            const infoTable = document.querySelector(".info-table");
            const editButton = document.querySelector(".edit-button");

            if (!form || !infoTable || !editButton) {
                console.error("One or more elements not found");
                return;
            }

            if (form.style.display === "none" || form.style.display === "") {
                form.style.display = "block";
                infoTable.style.display = "none";
                editButton.textContent = "Hide Form";
            } else {
                form.style.display = "none";
                infoTable.style.display = "table";
                editButton.textContent = "Edit";
            }
        }

        // Function to toggle the events list
        function toggleEventsList() {
            const eventsList = document.getElementById("eventsList");
            if (eventsList.style.display === "none" || eventsList.style.display === "") {
                eventsList.style.display = "block";
            } else {
                eventsList.style.display = "none";
            }
        }
    </script>
</head>
<body>
    <header>
        <div class="logo">
            <img src="../img/logo.png" alt="logo missing" id="logo">
        </div>
        
        <div class="menu">
            <a href="../partials/index.php">Home</a>
            <a href="../partials/projects.php">Projects</a>
            <a href="../partials/volunteerdashboard.php" class="active">Volunteer</a>
            <a href="../partials/beneficiary.php">Beneficiary</a>
            <a href="logout.php">Log Out</a>
        </div>

        <h1>Volunteer Dashboard</h1>
        <a href="../partials/donor.php"><button class="Donate">Donate Now</button></a>
    </header>

    <div class="volunteer-container">
        <!-- Volunteer Picture -->
        <div class="volunteer-picture">
            <img src="<?php echo $volunteer['photo_path'] ? '../' . $volunteer['photo_path'] : '..Nayan.png'; ?>" alt="Volunteer Picture">
            <form action="volunteerdashboard.php" method="POST" enctype="multipart/form-data">
                <input type="file" name="photo" accept="image/*" required>
                <button type="submit">Upload Photo</button>
            </form>
        </div>

        <!-- Volunteer Sections -->
        <div class="volunteer-details">
            <!-- Information Section -->
            <div class="info-section">
                <h2>Information</h2>
                <div class="edit-button-container">
                    <button onclick="toggleEditForm()" class="edit-button">Edit</button>
                </div>
                <table class="info-table">
                    <tr>
                        <td class="info-label">Name:</td>
                        <td class="info-value"><?php echo htmlspecialchars($volunteer['name'] ?? 'Not set'); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Volunteer ID:</td>
                        <td class="info-value"><?php echo htmlspecialchars($volunteer['volunteer_id']); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Father's Name:</td>
                        <td class="info-value"><?php echo htmlspecialchars($volunteer['father_name'] ?? 'Not set'); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Mother's Name:</td>
                        <td class="info-value"><?php echo htmlspecialchars($volunteer['mother_name'] ?? 'Not set'); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Phone Number:</td>
                        <td class="info-value"><?php echo htmlspecialchars($volunteer['phone_number'] ?? 'Not set'); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Blood Group:</td>
                        <td class="info-value"><?php echo htmlspecialchars($volunteer['blood_group'] ?? 'Not set'); ?></td>
                    </tr>
                    <tr>
                        <td class="info-label">Email:</td>
                        <td class="info-value"><?php echo htmlspecialchars($volunteer['email'] ?? 'Not set'); ?></td>
                    </tr>
                </table>

                <!-- Edit Form (Hidden by Default) -->
                <form id="editForm" action="volunteerdashboard.php" method="POST" style="display: none;">
                    <input type="hidden" name="update_info" value="1">
                    <input type="text" name="name" value="<?php echo htmlspecialchars($volunteer['name'] ?? ''); ?>" placeholder="Full Name" required>
                    <input type="text" name="father_name" value="<?php echo htmlspecialchars($volunteer['father_name'] ?? ''); ?>" placeholder="Father's Name" required>
                    <input type="text" name="mother_name" value="<?php echo htmlspecialchars($volunteer['mother_name'] ?? ''); ?>" placeholder="Mother's Name" required>
                    <input type="tel" name="phone" value="<?php echo htmlspecialchars($volunteer['phone_number'] ?? ''); ?>" placeholder="Phone Number" required>
                    <select name="blood_group" required>
                        <option value="" disabled>Select Blood Group</option>
                        <option value="A+" <?php echo $volunteer['blood_group'] == 'A+' ? 'selected' : ''; ?>>A+</option>
                        <option value="A-" <?php echo $volunteer['blood_group'] == 'A-' ? 'selected' : ''; ?>>A-</option>
                        <option value="B+" <?php echo $volunteer['blood_group'] == 'B+' ? 'selected' : ''; ?>>B+</option>
                        <option value="B-" <?php echo $volunteer['blood_group'] == 'B-' ? 'selected' : ''; ?>>B-</option>
                        <option value="O+" <?php echo $volunteer['blood_group'] == 'O+' ? 'selected' : ''; ?>>O+</option>
                        <option value="O-" <?php echo $volunteer['blood_group'] == 'O-' ? 'selected' : ''; ?>>O-</option>
                        <option value="AB+" <?php echo $volunteer['blood_group'] == 'AB+' ? 'selected' : ''; ?>>AB+</option>
                        <option value="AB-" <?php echo $volunteer['blood_group'] == 'AB-' ? 'selected' : ''; ?>>AB-</option>
                    </select>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($volunteer['email'] ?? ''); ?>" placeholder="Enter Email" required>
                    <button type="submit">Save Changes</button>
                    <button type="button" onclick="toggleEditForm()">Cancel</button>
                </form>
            </div>
            
            <!-- "Your Events" Section -->
            <div class="event-section">
                <h2>Your Events</h2>
                <?php if (count($joined_events) > 0): ?>
                    <div class="event-container">
                        <?php foreach ($joined_events as $event): ?>
                            <div class="event1-box">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                                <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                                <p><?php echo htmlspecialchars($event['description']); ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p>You haven't joined any events yet.</p>
                <?php endif; ?>
            </div>

            <!-- "Join Event" Section -->
            <div class="event-section">
                <h2>Join Event</h2>
                <button onclick="toggleEventsList()">Join Event</button>
                <p id="volunteerMessage"></p>
                <div id="eventsList" style="display: none;">
                    <?php
                    // Fetch all upcoming events, including location
                    $upcoming_sql = "SELECT id AS event_id, event_name AS title, event_date, description, location FROM events WHERE event_date >= CURDATE()";
                    $upcoming_result = $conn->query($upcoming_sql);
                    $upcoming_events = $upcoming_result->fetch_all(MYSQLI_ASSOC);
                    ?>
                    <?php if (count($upcoming_events) > 0): ?>
                        <div class="event-container">
                            <?php foreach ($upcoming_events as $event): ?>
                                <div class="event1-box">
                                    <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                    <p><strong>Date:</strong> <?php echo htmlspecialchars($event['event_date']); ?></p>
                                    <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                                    <p><?php echo htmlspecialchars($event['description']); ?></p>
                                    <form method="POST" action="volunteerdashboard.php">
                                        <input type="hidden" name="join_event" value="1">
                                        <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                        <button type="submit" class="join-now-button">Join Now</button>
                                    </form>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p>No upcoming events available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Generate Certificate Section -->
            <div class="certificate-section">
                <h2>Generate Certificate</h2>
                <?php if (count($joined_events) > 0): ?>
                    <form method="POST" action="volunteerdashboard.php">
                        <input type="hidden" name="generate_certificate" value="1">
                        <select name="event_id" required>
                            <option value="" disabled selected>Select an event</option>
                            <?php foreach ($joined_events as $event): ?>
                                <option value="<?php echo $event['event_id']; ?>">
                                    <?php echo htmlspecialchars($event['title']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" onclick="generateCertificate()">Download Certificate</button>
                    </form>
                <?php else: ?>
                    <p>You must join at least one event to generate a certificate.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include '../footer.php'; ?>

    <script src="../js/script.js"></script>
</body>
</html> 
<?php $conn->close(); ?>