<?php
session_start();
include('../../includes/config.php');
include('../../includes/alert.php');

// Validate required fields
if (!isset($_SESSION['apt_id'])) {
    $_SESSION['error'] = "Invalid request payment.";
    header("Location: /FurCareHub/appointment/hotel/create.php");
    exit();
}

$apt_id = intval($_SESSION['apt_id']);

// Retrieve additional days and hours from session
$additional_days = isset($_SESSION['additional_days']) ? intval($_SESSION['additional_days']) : 0;
$additional_hours = isset($_SESSION['additional_hours']) ? intval($_SESSION['additional_hours']) : 0;

// Fetch appointment details
$query = "SELECT a.check_in, a.check_out, p.name AS pet_name, r.room, 
                 CONCAT(o.fname, ' ', COALESCE(o.m_i, ''), ' ', o.lname) AS owner_name,
                 ar.rate AS apt_rate
          FROM appointment a
          JOIN pet p ON a.pet_id = p.pet_id
          JOIN owner o ON a.owner_id = o.owner_id
          JOIN room r ON a.room_id = r.room_id
          JOIN apt_rate ar ON a.aptrate_id = ar.aptrate_id
          WHERE a.apt_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    $_SESSION['error'] = "Database error: " . $conn->error;
    header("Location: /FurCareHub/appointment/hotel/create.php");
    exit();
}
$stmt->bind_param("i", $apt_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Appointment not found.";
    header("Location: /FurCareHub/appointment/hotel/create.php");
    exit();
}

$appointment = $result->fetch_assoc();
$stmt->close();

// Fetch total cost of selected services
$query = "SELECT SUM(s.price) AS total_service_cost
          FROM apt_services aps
          JOIN services s ON aps.service_id = s.service_id
          WHERE aps.apt_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    $_SESSION['error'] = "Database error: " . $conn->error;
    header("Location: /FurCareHub/appointment/hotel/create.php");
    exit();
}
$stmt->bind_param("i", $apt_id);
$stmt->execute();
$result = $stmt->get_result();
$service_cost = $result->fetch_assoc();
$stmt->close();

$total_service_cost = isset($service_cost['total_service_cost']) ? $service_cost['total_service_cost'] : 0;

// Calculate extra charges
$extra_hours_charge = $additional_hours * 100; // ₱100 per extra hour
$extra_days_charge = ($additional_days-1) * $appointment['apt_rate']; // Multiply days by rate

// Compute total amount including extra charges
$appointment_total = $appointment['apt_rate'] + $extra_days_charge + $extra_hours_charge;
$total_amount = $appointment_total + $total_service_cost;

// Fetch membership ID if owner is a member
if (!isset($_SESSION['owner_id'])) {
    $_SESSION['error'] = "Session expired. Please log in again.";
    header("Location: /FurCareHub/login.php");
    exit();
}

$query = "SELECT membership_id FROM membership WHERE owner_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    $_SESSION['error'] = "Database error: " . $conn->error;
    header("Location: /FurCareHub/appointment/hotel/create.php");
    exit();
}
$stmt->bind_param("i", $_SESSION['owner_id']);
$stmt->execute();
$result = $stmt->get_result();
$membership = $result->fetch_assoc();
$stmt->close();

$membership_id = $membership ? $membership['membership_id'] : null;

// Apply 20% discount if the user is a member
$discount = 0;
$discounted_total = $total_amount;

if ($membership_id) {
    $discount = $total_amount * 0.20; // 20% discount
    $discounted_total = $total_amount - $discount;
}

// Set payment statuses
$cash_payment_status = 4; // Paid for Cash
$cashless_payment_status = 1; // Pending for Cashless
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; padding: 20px; }
        .container {
            background: #543306;
            padding: 20px;
            border-radius: 50px;
            box-shadow: 5px 5px 20px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            max-height: 630px; 
            height: 100%;
            text-align: center; 
            margin: 70px auto;
            font-family: 'Darumadrop One', sans-serif;
        }
        h2 { color: #333; }
        select, input, button { width: 100%; padding: 10px; margin-top: 10px; }
        .hidden { display: none; }
        .cancel-button { 
            width: 100%;    
            font-family: 'Darumadrop One', sans-serif;
            background-color: #dc3545; 
            color: white;
        }
        button {
            margin-top: 30px;
            padding: 10px;
            width: 50%;
            background-color: #f5efe0da;
            color: #543306;
            border: none;
            border-radius: 20px;
            cursor: pointer;
            font-family: 'Darumadrop One', sans-serif;
        }
        .button-container {
            display: flex;
            justify-content: center;
            gap: 10px; 
            margin-top: 20px;
        }

        .button-container button {
            flex: 2; 
            padding: 12px;
            border-radius: 20px;
        }
    </style>
</head>
<body>

<div class="container">
    <br><h3>Payment Details</h3><br>
    
    <p><strong>Pet:</strong> <?php echo htmlspecialchars($appointment['pet_name']); ?></p>
    <p><strong>Owner:</strong> <?php echo htmlspecialchars($appointment['owner_name']); ?></p>
    <p><strong>Room:</strong> <?php echo htmlspecialchars($appointment['room']); ?></p>
    <p><strong>Check-In:</strong> <?php echo date('M d, Y h:i A', strtotime($appointment['check_in'])); ?></p>
    <p><strong>Check-Out:</strong> <?php echo date('M d, Y h:i A', strtotime($appointment['check_out'])); ?></p>

    <p><strong>Additional Days:</strong> <?php echo $additional_days; ?> days</p>
    <p><strong>Additional Hours:</strong> <?php echo $additional_hours; ?> hours</p>

    <p><strong>Appointment Rate:</strong> ₱<?php echo number_format($appointment['apt_rate'], 2); ?></p>
    <p><strong>Total Service Cost:</strong> ₱<?php echo number_format($total_service_cost, 2); ?></p>
    <p><strong>Total Amount:</strong> ₱<?php echo number_format($total_amount, 2); ?></p>

    <?php if ($membership_id): ?>
        <p><strong>Discount (20%):</strong> -₱<?php echo number_format($discount, 2); ?></p>
        <p><strong>Discounted Total:</strong> ₱<?php echo number_format($discounted_total, 2); ?></p>
    <?php endif; ?>

    <form action="process_payment.php" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="apt_id" value="<?php echo $apt_id; ?>">
        <input type="hidden" name="membership_id" value="<?php echo $membership_id; ?>">
        <input type="hidden" name="total_amount" value="<?php echo $membership_id ? $discounted_total : $total_amount; ?>">

        <input type="hidden" id="is_member" value="<?php echo $membership_id ? '1' : '0'; ?>">

        <br>
<label for="payment_type">Select Payment Method:</label>
<select name="payment_type" id="payment_type" required>
    <option value="1">Cash</option>
    <option value="2">Cashless</option>
</select>

<!-- Cashless Payment Options -->
<div id="cashless-options" class="hidden">
    <label for="cashless_choice">Choose Payment Option:</label>
    <select name="cashless_choice" id="cashless_choice">
        <?php if ($membership_id): ?>
            <option id="membership-option" value="balance">Deduct from Membership Balance</option>
        <?php endif; ?>
        <option value="gcash">Pay via GCash</option>
    </select>

    <!-- GCash Payment Fields -->
    <div id="gcash-fields" class="hidden">
        <label for="reference_number">Reference Number:</label>
        <input type="text" name="reference_number" id="reference_number">

        <label for="payment_img">Upload Payment Proof:</label>
        <input type="file" name="payment_img" id="payment_img" accept="image/*">
    </div>
</div>

<div class="button-container">
    <button type="submit">Confirm Payment</button>
</form>

    <form action="/FurCareHub/appointment/cancel_appointment.php" method="POST" onsubmit="return confirm('Are you sure you want to cancel this payment? This will delete your appointment.');">
        <input type="hidden" name="apt_id" value="<?php echo $apt_id; ?>">
        <button type="submit" class="cancel-button">Cancel Payment</button>
    </form>
    </div>
</div>
<script src="/FurCareHub/includes/payment.js"></script>
</body>
</html>