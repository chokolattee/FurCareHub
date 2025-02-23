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

$_SESSION['success'] = "Appointment has been canceled.";
header("Location: /FurCareHub/appointment/select_services.php");
exit();
?>
