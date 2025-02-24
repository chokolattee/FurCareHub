<?php
session_start();
include('../../includes/config.php');
include('../../includes/alert.php');

$owner_id = $_SESSION['owner_id'];

// Fetch pets that do not have an active appointment (status_id NOT IN (6, 7))
$pets = $conn->query("SELECT p.* 
    FROM pet p 
    LEFT JOIN appointment a ON p.pet_id = a.pet_id 
    AND a.status_id NOT IN (6, 7)
    WHERE p.owner_id = $owner_id 
    AND a.pet_id IS NULL");

$services = $conn->query("SELECT * FROM services");
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Hotel Appointment</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        form {
            max-width: 400px;
            margin: auto;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        label, select, input {
            display: block;
            width: 100%;
            margin-bottom: 10px;
        }
        button {
            background-color: #28a745;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>

<h2>Book a Hotel Appointment</h2>

<form action="" method="POST">
    <label for="pet_id">Choose Pet:</label>
    <select name="pet_id" id="pet_id" required onchange="this.form.submit()">
        <option value="">Select a pet</option>
        <?php while ($pet = $pets->fetch_assoc()) { ?>
            <option value="<?php echo $pet['pet_id']; ?>" <?php echo (isset($_POST['pet_id']) && $_POST['pet_id'] == $pet['pet_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($pet['name']); ?>
            </option>
        <?php } ?>
    </select>
</form>

<?php
// Fetch available rooms and durations if a pet is selected
if (isset($_POST['pet_id']) && !empty($_POST['pet_id'])) {
    $selected_pet_id = $_POST['pet_id'];

    // Fetch available hotel rooms for the selected pet
    $query = "SELECT r.* FROM room r 
              JOIN pet p ON r.size_id = p.size_id AND p.type_id = r.type_id 
              WHERE p.pet_id = ? AND r.status_id = 1"; // Only hotel rooms
    
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param("i", $selected_pet_id);
    $stmt->execute();
    $rooms = $stmt->get_result();

    // Fetch available durations for hotel stays
    $query = "SELECT ar.aptrate_id, d.duration, ar.rate 
              FROM apt_rate ar
              JOIN pet p ON ar.size_id = p.size_id
              JOIN duration d ON ar.duration_id = d.duration_id
              WHERE p.pet_id = ? AND ar.apttype_id = 2"; // Only hotel durations

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        die("Database error: " . $conn->error);
    }
    $stmt->bind_param("i", $selected_pet_id);
    $stmt->execute();
    $durations = $stmt->get_result();
?>
    
    <form action="/FurCareHub/appointment/hotel/select_services.php" method="POST">
        <input type="hidden" name="pet_id" value="<?php echo $selected_pet_id; ?>">
        <input type="hidden" name="apttype_id" value="2">

        <label for="room_id">Choose Room:</label>
        <select name="room_id" id="room_id" required>
            <option value="">Select a room</option>
            <?php while ($room = $rooms->fetch_assoc()) { ?>
                <option value="<?php echo $room['room_id']; ?>"><?php echo htmlspecialchars($room['room']); ?></option>
            <?php } ?>
        </select>

        <label for="check_in_date">Check-In Date:</label>
        <input type="date" name="check_in_date" id="check_in_date" required onchange="updateCheckoutDateTime();">

        <label for="check_in_time">Check-In Time:</label>
        <input type="time" name="check_in_time" id="check_in_time" required onchange="updateCheckoutDateTime();">

        <label for="duration_id">Choose Duration & Rate:</label>
        <select name="duration_id" id="duration_id" required onchange="updateCheckoutDateTime();">
            <option value="">Select duration</option>
            <?php while ($duration = $durations->fetch_assoc()) { ?>
                <option value="<?php echo $duration['aptrate_id']; ?>" 
                        data-duration="<?php echo $duration['duration']; ?>"
                        data-rate="<?php echo $duration['rate']; ?>">
                    <?php echo htmlspecialchars($duration['duration']) . " Hours - ₱" . number_format($duration['rate'], 2); ?>
                </option>
            <?php } ?>
        </select>

        <label for="check_out_date">Check-Out Date:</label>
        <input type="date" name="check_out_date" id="check_out_date" required onchange="validateCheckoutDate();">

        <label for="check_out_time">Check-Out Time:</label>
        <input type="time" name="check_out_time" id="check_out_time" required onchange="validateCheckoutTime();">

        <label for="additional_days">Total Days and Hours:</label>
        <input type="text" name="additional_days" id="additional_days" readonly>

        <input type="hidden" name="additional_days" id="hidden_additional_days">
<input type="hidden" name="additional_hours" id="hidden_additional_hours">


        <button type="submit" name="proceed">Proceed</button>
    </form>

    <script src="../../includes/hotel.js"></script>
<?php 
}
?>
</body>
</html>