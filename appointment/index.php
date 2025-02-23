<?php
session_start();
include('../includes/config.php');
include('../includes/alert.php');

$owner_id = intval($_SESSION['owner_id']); // Ensure it's an integer

$result = $conn->query("
   SELECT apt_id, pet_name, room, services_selected, check_in, check_out, total_hours, 
          apt_type, status, payment_status, payment_type 
   FROM appointment_details 
   WHERE owner_id = $owner_id 
   AND status NOT IN ('Cancelled', 'Completed')  -- Use status instead of status_id
   ORDER BY check_in DESC;
");


?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Appointments</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background-color: #f4f4f4;
        }
    </style>
</head>

<body>
    <h2>Your Appointments</h2>
    <table>
    <thead>
    <tr>
        <th>Pet Name</th>
        <th>Room</th>
        <th>Services</th>
        <th>Check-In</th>
        <th>Check-Out</th>
        <th>Hours</th>
        <th>Appointment Type</th>
        <th>Status</th>
        <th>Payment Type</th>
        <th>Payment Status</th>
        <th>Action</th>
    </tr>
</thead>
<tbody>
    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['pet_name']); ?></td>
            <td><?php echo htmlspecialchars($row['room']); ?></td>
            <td><?php echo htmlspecialchars($row['services_selected']); ?></td>
            <td><?php echo date('M d, Y h:i A', strtotime($row['check_in'])); ?></td>
            <td><?php echo date('M d, Y h:i A', strtotime($row['check_out'])); ?></td>
            <td><?php echo (int)$row['total_hours']; ?> hours</td>
            <td><?php echo htmlspecialchars($row['apt_type']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>  <!-- Status instead of status_id -->
            <td><?php echo htmlspecialchars($row['payment_type'] ?? 'Not Set'); ?></td>
            <td><?php echo htmlspecialchars($row['payment_status'] ?? 'Not Paid'); ?></td>
            <td>
                <?php if ($row['status'] == 'For Approval') { ?>  <!-- Use status directly -->
                    <form action="cancel.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                        <input type="hidden" name="appointment_id" value="<?php echo (int)$row['apt_id']; ?>">
                        <button type="submit" class="btn btn-danger">Cancel</button>
                    </form>
                <?php } else { ?>
                    <button class="btn btn-secondary" disabled>
                        <?php echo ($row['status'] == 'Cancelled' || $row['status'] == 'Completed') ? 'Cancelled' : 'Cannot Cancel'; ?>
                    </button>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
</tbody>
    </table>
</body>

</html>