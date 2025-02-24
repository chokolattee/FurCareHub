<?php
session_start();
include('../../includes/config.php');

// Validate required fields
if (!isset($_POST['apt_id'], $_POST['payment_type'])) {
    $_SESSION['error'] = "Invalid request. Missing required fields.";
    header("Location: /FurCareHub/appointment/daycare/create.php");
    exit();
}

// Retrieve user input safely
$apt_id = intval($_POST['apt_id']);
$payment_type = intval($_POST['payment_type']); // 1 = Cash, 2 = Cashless
$cashless_choice = isset($_POST['cashless_choice']) ? $_POST['cashless_choice'] : null;
$reference_number = isset($_POST['reference_number']) ? htmlspecialchars($_POST['reference_number']) : null;
$membership_id = isset($_POST['membership_id']) && !empty($_POST['membership_id']) ? intval($_POST['membership_id']) : null;
$total_amount = floatval($_POST['total_amount']);

// Check if appointment exists
$query = "SELECT * FROM appointment WHERE apt_id = ?";
$stmt = $conn->prepare($query);
if (!$stmt) {
    $_SESSION['error'] = "Database error: Failed to prepare the query.";
    header("Location: /FurCareHub/appointment/daycare/create.php");
    exit();
}
$stmt->bind_param("i", $apt_id);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Appointment not found.";
    header("Location: /FurCareHub/appointment/daycare/create.php");
    exit();
}

// Fetch membership balance if user is a member
$balance = 0.00;
if ($membership_id) {
    $query = "SELECT balance FROM membership WHERE membership_id = ? AND status_id = 11";
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        $_SESSION['error'] = "Database error: Failed to prepare the query.";
        header("Location: /FurCareHub/appointment/daycare/payment.php");
        exit();
    }
    $stmt->bind_param("i", $membership_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $membership = $result->fetch_assoc();
    $stmt->close();

    if ($membership) {
        $balance = floatval($membership['balance']);
    }
}

// ** If user chooses "Deduct from Membership Balance" **
if ($membership_id && $cashless_choice === "balance") {
    if ($total_amount > $balance) {
        $_SESSION['error'] = "Insufficient balance. Do you want to top up?";
        $_SESSION['redirect_url'] = "/FurCareHub/users/member.php"; // Redirect for top-up
        header("Location: /FurCareHub/appointment/daycare/payment.php");
        exit();
    }

    // Deduct amount from balance
    $new_balance = $balance - $total_amount;
    $update_balance = "UPDATE membership SET balance = ? WHERE membership_id = ? AND status_id = 11";
    $stmt = $conn->prepare($update_balance);
    if (!$stmt) {
        $_SESSION['error'] = "Database error: Failed to update membership balance.";
        header("Location: /FurCareHub/appointment/daycare/payment.php");
        exit();
    }
    $stmt->bind_param("di", $new_balance, $membership_id);
    if (!$stmt->execute()) {
        $_SESSION['error'] = "Failed to update membership balance.";
        header("Location: /FurCareHub/appointment/daycare/payment.php");
        exit();
    }
    $stmt->close();

    $pmtstatus_id = 5; // Paid via membership balance
    $payment_img = "Deducted from Membership Balance";
    $reference_number = ""; // No reference number needed
} elseif ($payment_type == 2) { // **GCash Payment**
    if ($cashless_choice === "gcash") {
        if (empty($reference_number)) {
            $_SESSION['error'] = "Reference number is required for GCash payments.";
            header("Location: /FurCareHub/appointment/daycare/payment.php");
            exit();
        }

        // Check if reference number already exists
        $check_reference = "SELECT * FROM payment WHERE reference_number = ?";
        $stmt = $conn->prepare($check_reference);
        $stmt->bind_param("s", $reference_number);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Reference number already exists.";
            header("Location: /FurCareHub/appointment/daycare/payment.php");
            exit();
        }

        // Validate payment image
        if (!isset($_FILES['payment_img']) || $_FILES['payment_img']['error'] != 0) {
            $_SESSION['error'] = "Please upload a valid payment proof.";
            header("Location: /FurCareHub/appointment/daycare/payment.php");
            exit();
        }

        // Validate file type & size
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
        $file_extension = strtolower(pathinfo($_FILES['payment_img']['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_types)) {
            $_SESSION['error'] = "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
            header("Location: /FurCareHub/appointment/daycare/payment.php");
            exit();
        }

        if ($_FILES['payment_img']['size'] > 5 * 1024 * 1024) { // 5MB max
            $_SESSION['error'] = "File size exceeds the maximum limit of 5MB.";
            header("Location: /FurCareHub/appointment/daycare/payment.php");
            exit();
        }

        // Upload payment proof
        $upload_dir = __DIR__ . "/../../uploads/payment/";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $filename = "payment_" . time() . "_" . bin2hex(random_bytes(5)) . "." . $file_extension;
        $target_path = $upload_dir . $filename;

        if (move_uploaded_file($_FILES['payment_img']['tmp_name'], $target_path)) {
            $payment_img = "/uploads/payment/" . $filename;
        } else {
            $_SESSION['error'] = "Failed to upload payment proof.";
            header("Location: /FurCareHub/appointment/daycare/payment.php");
            exit();
        }
        $pmtstatus_id = 1; // For Verification
    }
} else {
    $pmtstatus_id = 4; // Paid via cash
    $payment_img = "Cash (At the Counter)";
}

// Insert payment record
$insert_payment = "INSERT INTO payment (payment_for_id, pmttype_id, reference_number, membership_id, apt_id, pmtstatus_id, payment_img) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_payment);
if (!$stmt) {
    $_SESSION['error'] = "Database error: Failed to prepare the payment query.";
    header("Location: /FurCareHub/appointment/daycare/payment.php");
    exit();
}

$payment_for_id = 2; // 2 = Appointment

$stmt->bind_param(
    "iisiiis",
    $payment_for_id, // payment_for_id (2 = Appointment)
    $payment_type,   // pmttype_id (1 = Cash, 2 = Cashless)
    $reference_number, // reference_number (for cashless payments)
    $membership_id,  // membership_id (if applicable)
    $apt_id,         // apt_id (appointment ID)
    $pmtstatus_id,   // Payment status
    $payment_img    // payment_img (proof for cashless payments)
);

// Execute the INSERT query
if ($stmt->execute()) {
    $_SESSION['success'] = "Appointment is being processed!";
    $stmt->close();
    header("Location: /FurCareHub/appointment/index.php");
    exit();
} else {
    $stmt->close();
    $_SESSION['error'] = "Payment processing failed: " . $conn->error;
    header("Location: /FurCareHub/appointment/daycare/payment.php");
    exit();
}
?>
