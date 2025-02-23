<?php
session_start();
include('../../includes/config.php');

// Validate required fields
if (!isset($_POST['pet_id'], $_POST['room_id'], $_POST['check_in'], $_POST['duration_id'], $_POST['check_out'], $_POST['additional_days'], $_POST['additional_hours'])) {
    $_SESSION['error'] = "Missing required fields.";
    header("Location: /FurCareHub/appointment/hotel/create.php");
    exit();
}

// Retrieve user input safely
$pet_id = intval($_POST['pet_id']);
$room_id = intval($_POST['room_id']);
$duration_id = intval($_POST['duration_id']);
$duration = intval($_POST['duration']);
$aptrate_id = intval($_POST['aptrate_id']);
$rate = isset($_POST['rate']) ? floatval($_POST['rate']) : 0.00; 
// Retrieve additional days and additional hours separately
$additional_days = isset($_POST['additional_days']) ? intval($_POST['additional_days']) : 0;
$additional_hours = isset($_POST['additional_hours']) ? intval($_POST['additional_hours']) : 0;

// Convert additional_days to hours and add to additional_hours
$total_additional_hours = ($additional_days * 24) + $additional_hours;
 // Now using converted hours
$check_in = $_POST['check_in']; // Format: YYYY-MM-DD HH:MM
$check_out = $_POST['check_out']; // Format: YYYY-MM-DD HH:MM

// Convert check-in and check-out datetime to MySQL format
$check_in_mysql = date('Y-m-d H:i:s', strtotime($check_in));
if ($check_in_mysql === false) {
    $_SESSION['error'] = "Invalid check-in datetime format.";
    header("Location: /FurCareHub/appointment/hotel/create.php");
    exit();
}

$check_out_mysql = date('Y-m-d H:i:s', strtotime($check_out));
if ($check_out_mysql === false) {
    $_SESSION['error'] = "Invalid check-out datetime format.";
    header("Location: /FurCareHub/appointment/hotel/create.php");
    exit();
}

// Fetch the owner_id and type_id associated with the pet_id
$query = "SELECT owner_id, type_id FROM pet WHERE pet_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    $_SESSION['error'] = "Database error: Failed to prepare the query.";
    header("Location: /FurCareHub/appointment/hotel/create.php");
    exit();
}
$stmt->bind_param("i", $pet_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $pet_data = $result->fetch_assoc();
    $owner_id = $pet_data['owner_id'];
    $type_id = $pet_data['type_id'];
} else {
    $_SESSION['error'] = "Invalid pet selected.";
    header("Location: /FurCareHub/appointment/hotel/create.php");
    exit();
}

// Insert the appointment into the database
$query = "INSERT INTO appointment (pet_id, owner_id, room_id, type_id, apttype_id, status_id, aptrate_id, check_in, additional_hours, check_out, overtime_hours) 
          VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($query);
if (!$stmt) {
    $_SESSION['error'] = "Database error: Failed to prepare the query.";
    header("Location: /FurCareHub/appointment/hotel/create.php");
    exit();
}

// Set default values for apttype_id, status_id, and overtime_hours
$apttype_id = 2; // apttype_id = 2 means "Hotel"
$status_id = 3; // status_id = 3 means "Pending"
$overtime_hours = 0;

// Bind parameters for the INSERT query
$stmt->bind_param(
    "iiiiiiisisi",
    $pet_id,
    $owner_id,
    $room_id,
    $type_id,
    $apttype_id,
    $status_id,
    $aptrate_id,
    $check_in_mysql,
    $total_additional_hours, // Now using converted_hours instead of additional_hours
    $check_out_mysql,
    $overtime_hours
);

// Execute the INSERT query
if ($stmt->execute()) {
    $appointment_id = $stmt->insert_id;

    $update_room_query = "UPDATE room SET status_id = 10 WHERE room_id = ?";
    $update_room_stmt = $conn->prepare($update_room_query);
    if (!$update_room_stmt) {
        $_SESSION['error'] = "Database error: Failed to prepare the room status update query.";
        header("Location: /FurCareHub/appointment/hotel/create.php");
        exit();
    }
    $update_room_stmt->bind_param("i", $room_id);
    if (!$update_room_stmt->execute()) {
        $_SESSION['error'] = "Failed to update room status.";
        header("Location: /FurCareHub/appointment/hotel/create.php");
        exit();
    }
    
    // Handle additional services if any
    if (isset($_POST['services']) && is_array($_POST['services'])) {
        foreach ($_POST['services'] as $service_id) {
            $service_id = intval($service_id);
            $service_query = "INSERT INTO apt_services (apt_id, service_id) VALUES (?, ?)";
            $service_stmt = $conn->prepare($service_query);
            if (!$service_stmt) {
                $_SESSION['error'] = "Database error: Failed to prepare the service query.";
                header("Location: /FurCareHub/appointment/hotel/create.php");
                exit();
            }
            $service_stmt->bind_param("ii", $appointment_id, $service_id);
            if (!$service_stmt->execute()) {
                $_SESSION['error'] = "Failed to add service to the appointment.";
                header("Location: /FurCareHub/appointment/hotel/create.php");
                exit();
            }
        }
    }

    // Set the appointment ID in the session
    $_SESSION['apt_id'] = $appointment_id;
    $additional_days = isset($_POST['additional_days']) ? intval($_POST['additional_days']) : 0;
    $additional_hours = isset($_POST['additional_hours']) ? intval($_POST['additional_hours']) : 0;
    
    // Store them in session so they can be retrieved in `payment.php`
    $_SESSION['additional_days'] = $additional_days;
    $_SESSION['additional_hours'] = $additional_hours;
    // Redirect to payment page with success message
    $_SESSION['success'] = "Appointment booked successfully!";
    
header("Location: /FurCareHub/appointment/hotel/payment.php");
exit();
} else {
    $_SESSION['error'] = "Failed to book the appointment.";
    header("Location: /FurCareHub/appointment/hotel/create.php");
    exit();
}
?>
