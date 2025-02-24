<?php
session_start();
include('../includes/config.php');

// Validate appointment ID
if (!isset($_POST['apt_id'])) {
    $_SESSION['error'] = "Invalid request.";
    header("Location: /FurCareHub/appointment/select_services.php");
    exit();
}

$apt_id = intval($_POST['apt_id']);

$get_room_id = "SELECT room_id FROM appointment WHERE apt_id = ?";
$stmt = $conn->prepare($get_room_id);
$stmt->bind_param("i", $apt_id);
$stmt->execute();
$stmt->bind_result($room_id);
$stmt->fetch();
$stmt->close();

if (!$room_id) {
    $_SESSION['error'] = "Appointment not found or no room assigned.";
    header("Location: /FurCareHub/appointment/index.php");
    exit();
}

// Delete related services first
$delete_services = "DELETE FROM apt_services WHERE apt_id = ?";
$stmt = $conn->prepare($delete_services);
$stmt->bind_param("i", $apt_id);
$stmt->execute();
$stmt->close();

// Delete the appointment
$delete_appointment = "DELETE FROM appointment WHERE apt_id = ?";
$stmt = $conn->prepare($delete_appointment);
$stmt->bind_param("i", $apt_id);
$stmt->execute();
$stmt->close();

$update_room_status = "UPDATE room SET status_id = 1 WHERE room_id = ?";
$stmt = $conn->prepare($update_room_status);
$stmt->bind_param("i", $room_id);
$stmt->execute();
$stmt->close();

$_SESSION['success'] = "Appointment has been canceled.";
header("Location: /FurCareHub/appointment/index.php");
exit();
?>
