<?php
session_start();
include('../includes/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['appointment_id'])) {
    $appointment_id = intval($_POST['appointment_id']);
    $conn->begin_transaction();

    try {
        $stmt = $conn->prepare("SELECT room_id FROM appointment WHERE apt_id = ?");
        $stmt->bind_param("i", $appointment_id);
        $stmt->execute();
        $stmt->bind_result($room_id);
        $stmt->fetch();
        $stmt->close();

        if (!$room_id) {
            throw new Exception("Invalid Appointment ID");
        }

        // Update the appointment status to 'Cancelled' (status_id = 7)
        $stmt = $conn->prepare("UPDATE appointment SET status_id = 7 WHERE apt_id = ?");
        $stmt->bind_param("i", $appointment_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update appointment status");
        }
        $stmt->close();

        // Update the room status to 'Available' (status_id = 1)
        $stmt = $conn->prepare("UPDATE room SET status_id = 1 WHERE room_id = ?");
        $stmt->bind_param("i", $room_id);
        if (!$stmt->execute()) {
            throw new Exception("Failed to update room status");
        }
        $stmt->close();

        $conn->commit();

        header("Location: index.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback(); // Rollback on error
        header("Location: index.php" . urlencode($e->getMessage()));
        exit();
    }
} else {
    header("Location: index.php");
    exit();
}
?>
