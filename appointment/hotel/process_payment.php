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
$reference_number = isset($_POST['reference_number']) ? htmlspecialchars($_POST['reference_number']) : null;
$membership_id = isset($_POST['membership_id']) && !empty($_POST['membership_id']) ? intval($_POST['membership_id']) : null;
$payment_img = 
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

// Set payment status based on type
$pmtstatus_id = ($payment_type == 1) ? 4 : 1; // Cash = Paid (4), Cashless = Pending (1)
$payment_img = null;

if ($payment_type == 2) { // Cashless payment
    if (empty($reference_number)) {
        $_SESSION['error'] = "Reference number is required for cashless payments.";
        header("Location: /FurCareHub/appointment/payment.php?apt_id=" . $apt_id);
        exit();
    }

    // Check if reference number already exists (optional)
    $check_reference = "SELECT * FROM payment WHERE reference_number = ?";
    $stmt = $conn->prepare($check_reference);
    $stmt->bind_param("s", $reference_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Reference number already exists.";
        header("Location: /FurCareHub/appointment/payment.php?apt_id=" . $apt_id);
        exit();
    }

    if (!isset($_FILES['payment_img']) || $_FILES['payment_img']['error'] != 0) {
        $_SESSION['error'] = "Please upload a valid payment proof.";
        header("Location: /FurCareHub/appointment/payment.php?apt_id=" . $apt_id);
        exit();
    }

    // File upload validation
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($_FILES['payment_img']['name'], PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_types)) {
        $_SESSION['error'] = "Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.";
        header("Location: /FurCareHub/appointment/payment.php?apt_id=" . $apt_id);
        exit();
    }

    // Validate file size (max 5MB)
    $max_file_size = 5 * 1024 * 1024; // 5MB
    if ($_FILES['payment_img']['size'] > $max_file_size) {
        $_SESSION['error'] = "File size exceeds the maximum limit of 5MB.";
        header("Location: /FurCareHub/appointment/payment.php?apt_id=" . $apt_id);
        exit();
    }

    // Create upload directory if it doesn't exist
    $upload_dir = __DIR__ . "/../../uploads/payment/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Generate a unique file name
    $filename = "payment_" . time() . "_" . bin2hex(random_bytes(5)) . "." . $file_extension;
    $target_path = $upload_dir . $filename;

    // Move the uploaded file
    if (move_uploaded_file($_FILES['payment_img']['tmp_name'], $target_path)) {
        $payment_img = "/uploads/payment/" . $filename; // Save relative path in DB
    } else {
        $_SESSION['error'] = "Failed to upload payment proof.";
        header("Location: /FurCareHub/appointment/payment.php?apt_id=" . $apt_id);
        exit();
    }
} else {
    $payment_img = "Cash (At the Counter)"; // Store text for cash payments
}


// Insert payment record
$insert_payment = "INSERT INTO payment (payment_for_id, pmttype_id, reference_number, membership_id, apt_id, pmtstatus_id, payment_img) 
                   VALUES (?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($insert_payment);
if (!$stmt) {
    $_SESSION['error'] = "Database error: Failed to prepare the payment query.";
    header("Location: /FurCareHub/appointment/daycare/payment.php?apt_id=" . $apt_id);
    exit();
}

// Set payment_for_id to 2 (appointment)
$payment_for_id = 2; // 2 = Appointment

// Bind parameters for the INSERT query
$stmt->bind_param(
    "iisiiis",
    $payment_for_id, // payment_for_id (2 = Appointment)
    $payment_type,   // pmttype_id (1 = Cash, 2 = Cashless)
    $reference_number, // reference_number (for cashless payments)
    $membership_id,  // membership_id (if applicable)
    $apt_id,         // apt_id (appointment ID)
    $pmtstatus_id,   // pmtstatus_id (4 = Paid, 1 = Pending)
    $payment_img     // payment_img (proof for cashless payments)
);

// Execute the INSERT query
if ($stmt->execute()) {
    
    $_SESSION['success'] = "Appoinment is being processed!";
    $stmt->close();
    header("Location: /FurCareHub/appointment/index.php");
    exit();
} else {
    $stmt->close();
    $_SESSION['error'] = "Payment processing failed: " . $conn->error;
    header("Location: /FurCareHub/appointment/payment.php?apt_id=" . $apt_id);
    exit();
}
?>