<?php
// Include database connection
include('../includes/config.php'); 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['full_name']; 
    $contactNumber = $_POST['contact_number'];  
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    if ($password != $confirmPassword) {
        echo "Passwords do not match!";
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO owner (name, email, contact, password) 
                VALUES (?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssss", $fullName, $email, $contactNumber, $hashedPassword);

        if ($stmt->execute()) {
            header("Location: login.php");
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error preparing the SQL query.";
    }

    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../gui/style.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Pet a' Pat - Register</title>
    <link rel="icon" href="images/pet3.png">
    <style>
        /* Resetting box-sizing for all elements */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #FFF2D7;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            flex-direction: column;
            margin: 0;
        }

        nav {
            width: 100%;
            height: 80px; /* Adjusted for consistent navbar size */
            display: flex;
            justify-content: space-between;
            padding: 20px 50px;
            align-items: center;
            position: fixed;
            top: 0;
            left: 0;
            background-color: #FFF2D7;
            z-index: 100;
            box-shadow: 3px 3px 10px #683f0679;
        }

        .register-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
            margin-top: 120px;
        }

        .register-box h2 {
            margin-bottom: 20px;
        }

        .register-box input, .register-box select {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .register-box button {
            width: 100%;
            padding: 12px;
            background: #5cb85c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }

        .register-box button:hover {
            background: #4cae4c;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .login-link a {
            text-decoration: none;
            color: #e67e22;
        }

        .login-link a:hover {
            color: #c25e17;
        }
    </style>
</head>

<body>
<nav>
        <div class="wrapper">
            <div class="logo" data-aos="fade-right">
                <img src="" alt="">
            </div>
            <div class="links">
                <a href="index.html">HOME</a>
                <a href="">CONTACT</a>
                <a href="">LOG IN</a>
                <a href="">REGISTER</a>
                <a href="">
                    <i class="fa-solid fa-user fa-lg"></i>
                </a>
            </div>
        </div>
    </nav>


    <div class="register-box">
        <h2>Register</h2>
        <form action="register.php" method="POST">
            <input type="text" name="full_name" placeholder="Full Name" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="tel" name="contact_number" placeholder="Contact Number" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <button type="submit">Register</button>
        </form>
        <div class="login-link">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>

</html>
