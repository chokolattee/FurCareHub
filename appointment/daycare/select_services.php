<?php
session_start();
include('../../includes/config.php');

// Validate required fields
if (!isset($_POST['pet_id'], $_POST['room_id'], $_POST['check_in_date'], $_POST['check_in_time'], $_POST['duration_id'])) {
    header("Location: /FurCareHub/appointment/daycare/create.php");
    exit();
}

// Retrieve user input safely
$pet_id = intval($_POST['pet_id']);
$room_id = intval($_POST['room_id']);
$duration_id = intval($_POST['duration_id']);
$duration = isset($_POST['duration']) ? intval($_POST['duration']) : 0; // Prevent undefined key warning
$rate = isset($_POST['rate']) ? floatval($_POST['rate']) : 0.00; 
$additional_hours = isset($_POST['additional_hours']) ? intval($_POST['additional_hours']) : 0;

$check_in_date = $_POST['check_in_date']; // Format: YYYY-MM-DD
$check_in_time = $_POST['check_in_time']; // Format: HH:MM
$check_in = $check_in_date . ' ' . $check_in_time; // Combine date and time

$check_out_date = $_POST['check_out_date']; // Format: YYYY-MM-DD
$check_out_time = $_POST['check_out_time']; // Format: HH:MM
$check_out= $check_out_date . ' ' . $check_out_time; // Combine date and time

// Fetch the duration_id and duration from the apt_rate table
$query = "SELECT d.duration_id, d.duration, ar.aptrate_id, ar.rate
          FROM apt_rate ar
          JOIN duration d ON ar.duration_id = d.duration_id
          WHERE ar.aptrate_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    $_SESSION['error'] = "Database error: Failed to prepare the query.";
    header("Location: /FurCareHub/appointment/daycare/create.php");
    exit();
}
$stmt->bind_param("i", $duration_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $duration_data = $result->fetch_assoc();
    $aptrate_id = $duration_data['aptrate_id'];
    $duration_id = $duration_data['duration_id']; // Fetch the correct duration_id
    $duration = $duration_data['duration']; // Fetch the duration value
} else {
    $_SESSION['error'] = "Invalid duration selected.";
    header("Location: /FurCareHub/appointment/daycare/create.php");
    exit();
}

// Fetch available services
$services_query = "SELECT service_id, service, price FROM services";
$services = $conn->query($services_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Additional Services</title>
    <style>
        /* General Styling */
body {
    font-family: Arial, sans-serif;
    text-align: center;
    padding: 20px;
    background-color: #f5f5f5;
}

/* Container for form */
.container {
    max-width: 450px;
    margin: auto;
    padding: 20px;
    border: 1px solid #ccc;
    border-radius: 8px;
    background-color: #ffffff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Header */
h2 {
    color: #333;
    font-size: 22px;
    margin-bottom: 15px;
}

/* Form Styling */
form {
    text-align: left;
    display: flex;
    flex-direction: column;
}

/* Labels */
.label-service {
    font-weight: bold;
    margin-bottom: 5px;
}

/* Checkbox items */
.service-item {
    display: flex;
    align-items: center;
    margin-bottom: 12px;
    padding: 8px;
    border-radius: 5px;
    background: #f8f8f8;
    transition: background 0.2s ease-in-out;
}

.service-item:hover {
    background: #eaeaea;
}

input[type="checkbox"] {
    margin-right: 10px;
    transform: scale(1.2);
    cursor: pointer;
}

/* Buttons */
.button-container {
    display: flex;
    justify-content: space-between;
    margin-top: 15px;
}

.button-primary,
.button-secondary {
    width: 48%;
    padding: 10px;
    border: none;
    cursor: pointer;
    border-radius: 5px;
    font-size: 16px;
}

.button-primary {
    background-color: #28a745;
    color: white;
}

.button-primary:hover {
    background-color: #218838;
}

.button-secondary {
    background-color: #dc3545;
    color: white;
}

.button-secondary:hover {
    background-color: #c82333;
}

</style>
</head>
<body>

<div class="container">
    <h2>Select Additional Services</h2>
    <form action="/FurCareHub/appointment/daycare/store.php" method="POST">
        <!-- Hidden fields to pass data to store.php -->
        <input type="hidden" name="pet_id" value="<?php echo $pet_id; ?>">
        <input type="hidden" name="room_id" value="<?php echo $room_id; ?>">
        <input type="hidden" name="check_in" value="<?php echo $check_in; ?>">
        <input type="hidden" name="duration_id" value="<?php echo $duration_id; ?>">
        <input type="hidden" name="additional_hours" value="<?php echo $additional_hours; ?>">
        <input type="hidden" name="duration_id" value="<?php echo $duration_id; ?>">
        <input type="hidden" name="duration" value="<?php echo $duration; ?>"> <!-- Pass duration -->
        <input type="hidden" name="aptrate_id" value="<?php echo $aptrate_id; ?>"> <!-- Pass apt rate -->
        <input type="hidden" name="rate" value="<?php echo $rate; ?>"> <!-- Pass rate -->
        <input type="hidden" name="check_out" value="<?php echo $check_out; ?>"> 
        <input type="hidden" name="check_out_date" value="<?php echo $check_out_date; ?>"> <!-- Pass check-out date -->
        <input type="hidden" name="check_out_time" value="<?php echo $check_out_time; ?>"
        
        
        <!-- Display available services -->
        <?php if ($services->num_rows > 0): ?>
            <?php while ($service = $services->fetch_assoc()): ?>
                <div class="service-item">
                    <input type="checkbox" name="services[]" value="<?php echo $service['service_id']; ?>">
                    <label><?php echo htmlspecialchars($service['service']) . " - ₱" . number_format($service['price'], 2); ?></label>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p>No additional services available.</p>
        <?php endif; ?>
    
        <!-- Submit button -->
        <button type="submit">Proceed to Payment</button>
        <button type="submit">Back</button>
    </form>
</div>

</body>
</html>