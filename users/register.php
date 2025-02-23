<?php
include('../includes/config.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fname = $_POST['fname'];
    $mi = $_POST['m_i'];
    $lname = $_POST['lname'];
    $email = $_POST['email'];
    $contactNumber = $_POST['contact_number'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirm_password'];

    // Check if passwords match
    if ($password != $confirmPassword) {
        echo "Passwords do not match!";
        exit();
    }

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Handle profile picture upload (optional)
    $imgPath = null; // Default value if no image is uploaded
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $targetDir = "../uploads/user/"; // Directory to store uploaded images
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true); // Create the directory if it doesn't exist
        }

        $fileName = basename($_FILES['profile_picture']['name']);
        $targetFilePath = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFilePath, PATHINFO_EXTENSION));

        // Allow only certain file types (e.g., jpg, jpeg, png)
        $allowedTypes = ['jpg', 'jpeg', 'png'];
        if (in_array($fileType, $allowedTypes)) {
            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFilePath)) {
                $imgPath = 'uploads/user/' . $fileName; // Relative path for database
            } else {
                echo "Error uploading file.";
                exit();
            }
        } else {
            echo "Invalid file type. Only JPG, JPEG, and PNG are allowed.";
            exit();
        }
    }

    // Insert user data into the database
    $sql = "INSERT INTO owner (fname, m_i, lname, email, contact, password, img_path) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sssssss", $fname, $mi, $lname, $email, $contactNumber, $hashedPassword, $imgPath);

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
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>FurCareHub- Register</title>
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
                <a href="/FurCareHub/index.php">HOME</a>
                <a href="">CONTACT</a>
                <a href="/FurCareHub/users/login.php">LOG IN</a>
                <a href="/FurCareHub/users/register.php">REGISTER</a>
            </div>
        </div>
    </nav>


    <div class="register-box">
        <h2>Register</h2>
        <form action="register.php" method="POST" enctype="multipart/form-data">
            <input type="text" name="fname" placeholder="First Name" required>
            <input type="text" name="m_i" placeholder="Middle Initial">
            <input type="text" name="lname" placeholder="Surname" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="tel" name="contact_number" placeholder="Contact Number" required>
            <input type="password" name="password" placeholder="Password" required>
            <input type="password" name="confirm_password" placeholder="Confirm Password" required>
            <input type="file" name="profile_picture" accept="image/jpeg, image/png, image/jpg">
            <button type="submit">Register</button>
        </form>
        <div class="login-link">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>
</body>

</html>
