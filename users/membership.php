<?php
session_start();
include('../includes/config.php');

if (!isset($_SESSION['owner_id'])) {
    die("Error: User not logged in");
}

$owner_id = $_SESSION['owner_id'];
$email = "";
$full_name = "";
$fname = "";
$lname = "";

if ($owner_id) {
    $query = "SELECT email, fname, m_i, lname FROM owner WHERE owner_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $stmt->bind_result($email, $fname, $m_i, $lname);
    $stmt->fetch();
    $stmt->close();

    $full_name = trim("$fname $m_i $lname");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $reference_number = $_POST['reference_number'];
    $balance = $_POST['amount'];

    // Check if files are uploaded before accessing them
    if (!isset($_FILES["valid_id"]) || $_FILES["valid_id"]["error"] != 0) {
        die("Error: No valid ID uploaded.");
    }
    if (!isset($_FILES["payment_img"]) || $_FILES["payment_img"]["error"] != 0) {
        die("Error: No payment screenshot uploaded.");
    }

    // **File Upload Handling for Valid ID (Membership)**
    $valid_id_dir = "../uploads/valid_ids/";
    $valid_id_type = strtolower(pathinfo($_FILES["valid_id"]["name"], PATHINFO_EXTENSION));
    $valid_id_name = $fname . "_" . $lname . "_" . $owner_id . "." . $valid_id_type; 
    $valid_id_path = $valid_id_dir . $valid_id_name;

    // **File Upload Handling for Payment Image**
    $payment_dir = "../uploads/payment/";
    $payment_type = strtolower(pathinfo($_FILES["payment_img"]["name"], PATHINFO_EXTENSION));
    $payment_name = "payment_" . $reference_number . "_" . $owner_id . "." . $payment_type; 
    $payment_path = $payment_dir . $payment_name;

    // Allowed file types
    $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];
    if (!in_array($valid_id_type, $allowed_types) || !in_array($payment_type, $allowed_types)) {
        die("Error: Only JPG, JPEG, PNG, and GIF files are allowed.");
    }

    // Move files
    if (move_uploaded_file($_FILES["valid_id"]["tmp_name"], $valid_id_path) &&
        move_uploaded_file($_FILES["payment_img"]["tmp_name"], $payment_path)) {
        
        // Insert into membership table
        $query = "INSERT INTO membership (owner_id, balance, status_id, img_path) VALUES (?, ?, 10, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iis", $owner_id, $balance, $valid_id_path);

        if ($stmt->execute()) {
            // Get the last inserted membership_id
            $membership_id = $stmt->insert_id;

            // Insert into payment table
            $query = "INSERT INTO payment (membership_id, payment_for_id, pmttype_id, reference_number, pmtstatus_id, payment_img) 
                      VALUES (?, 1, 2, ?, 1, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("iss", $membership_id, $reference_number, $payment_path);

            if ($stmt->execute()) {
                echo "Payment Successful.";
            } else {
                echo "Error inserting into payment table: " . $stmt->error;
            }
        } else {
            echo "Error inserting into membership table: " . $stmt->error;
        }
    } else {
        echo "Error uploading images.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width-device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="/FurCareHub/includes/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>FurCareHub</title>
    <link rel="icon" href="pet3.png">

    <style>
        .content {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
        }

        .membership-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 20px;
            margin-top: 50px;
        }
        .membership {
            background-color: #543306;
            color: white;
            padding: 20px;
            border-radius: 10px;
            width: 250px;
            text-align: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .membership:nth-child(2) {
            background-color: white;
            color: #543306;
            border: 2px solid #543306;
        }
        .membership h2 {
            color: #ff5733;
            font-size: 24px;
        }
        .membership h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }
        .membership p {
            font-size: 18px;
            margin-bottom: 10px;
        }
        .get-started {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 20px;
            background-color: #ff5733;
            color: white;
            text-decoration: none;
            border-radius: 5px;
        }

        .membership-form {
            display: none;
            margin-top: 50px;
            padding: 20px;
            background-color: #f4f4f4;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            width: 400px;
        }
        .membership-form input,
        .membership-form select {
            margin-bottom: 15px;
            padding: 10px;
            width: 100%;
            border-radius: 5px;
            border: 1px solid #ddd;
        }
        .membership-form button {
            padding: 10px 20px;
            background-color: #ff5733;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body> 
            <nav>
                <div class="logo">
                    <h2>FurCare Hub</h2>
                </div>
                <div class="links">
                    <a href="">HOME</a>
                    <a href="">CONTACT</a>
                    <a href="">Log In</a>
                    <a href="">Register</a>
                </div>
            <div class="profile">
                <a href="">
                    <i class="fa-solid fa-user fa-lg"></i> 
                </a>
            </div>
            </nav>
    
    <section id="membership">
        <div class="content">
        <h1><span>Membership</span></h1>
            <p>Sign up today and enjoy exclusive benefits for your furry friend!</p>
        </div> 

                <div class="membership-container">
                    <div class="membership">
                        <h2>01</h2>
                        <h3>No Membership Fee: </h3>
                        <p>Becoming a member is completely free!</p>
                    </div>
                    <div class="membership">
                        <h2>02</h2>
                        <h3>Minimum Top-Up</h3>
                        <p>Start with a minimum top-up of 5000</p>
                        <p>which can be used on all our services:</p>
                        <p>+ Grooming</p>
                        <p>+ Hotel</p>
                        <a href="#" class="get-started" onclick="scrollToMembershipForm()">Get Started</a>
                    </div>
                    <div class="membership">
                        <h2>03</h2>
                        <h3>Member Discount</h3>
                        <p>20% discount on:</p>
                        <p>+ Grooming</p>
                        <p>+ Hotel</p>
                    </div>
                </div>
  
    </section>

    <section id="membership-form-section">
    <div class="membership-form" id="membership-form" enctype="multipart/form-data">
        <h2>Fill Up Membership Form</h2><br>
        <form action="membership.php" method="POST" enctype="multipart/form-data">
    <input type="text" name="email" value="<?php echo htmlspecialchars($email); ?>" placeholder="Email" readonly required>
    <input type="text" name="full_name" value="<?php echo htmlspecialchars(trim($full_name)); ?>" placeholder="Full Name" readonly required>
    <input type="text" name="reference_number" placeholder="Enter Reference Number" required>
    <input type="number" name="amount" id="amount" min="5000" required>
    
    <label for="valid_id">Upload Valid ID:</label>
    <input type="file" name="valid_id" accept="image/*" required>

    <label for="payment_img">Upload Payment Screenshot:</label>
    <input type="file" name="payment_img" accept="image/*" required>

    <button type="submit">Pay</button>
</form>

</form>

    </div>
</section>
<script src="/FurCareHub/includes/pet.js"></script>
    <script>
        function scrollToMembershipForm() {
            document.getElementById("membership-form").scrollIntoView({
                behavior: "smooth"
            });
            document.getElementById("membership-form").style.display = "block";
        }
    </script>
</body>
</html>

