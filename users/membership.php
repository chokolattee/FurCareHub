<?php
session_start();
include('../includes/config.php');

if (!isset($_SESSION['owner_id'])) {
    die("Error: User not logged in");
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $card_number = $_POST['card_number'];
    $balance = $_POST['balance'];

    $query = "SELECT owner_id FROM owner WHERE email = '$email'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $owner_id = $row['owner_id'];

        $query = "INSERT INTO membership (owner_id, balance, card_number) 
          VALUES ('$owner_id', '$balance', '$card_number')";


        if ($conn->query($query) === TRUE) {
            echo "Payment Successfull.";
        } else {
            echo "Error: " . $query . "<br>" . $conn->error;
        }
    } else {
        echo "No owner found with this email.";
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
    <div class="membership-form" id="membership-form">
        <h2>Fill Up Membership Form</h2><br>
        <form action="membership.php" method="POST">
            <input type="text" name="email" value="<?php echo isset($_SESSION['email']) ? $_SESSION['email'] : ''; ?>" placeholder="Email" required>
            <input type="text" name="card_number" placeholder="Card Number" required>
            <input type="number" name="balance" value="5000" readonly required>
            <button type="submit">Pay</button>
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
