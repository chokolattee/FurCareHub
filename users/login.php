<?php
session_start();
include('../includes/config.php');

$error = ""; // Initialize error message

// Check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    // Validate user input
    if (empty($email) || empty($password)) {
        $error = "All fields are required!";
    } else {
        // Correct SQL statement with the actual table name
        $sql = "SELECT * FROM owner WHERE email = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            // If user exists
            if ($result->num_rows == 1) {
                $user = $result->fetch_assoc();
                $role = $user['role']; // Fetch the role from the database

                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Set session variables
                    $_SESSION['owner_id'] = $user['owner_id']; // Change to match your DB column
                    $_SESSION['role'] = $role;

                    // Redirect based on role
                    if ($role === 'admin') {
                        header("Location: admin_dashboard.php");
                    } else {
                        header("Location: ../index.php");
                    }
                    exit();
                } else {
                    $error = "Incorrect password!";
                }
            } else {
                $error = "User not found!";
            }

            $stmt->close();
        } else {
            $error = "Database error. Please try again later.";
        }
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
    <title>FurCareHub - Login</title>
    <link rel="icon" href="images/pet3.png">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #FFF2D7;
            flex-direction: column;
        }

        .login-box {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            width: 400px;
            text-align: center;
            margin-top: 80px;
        }

        .login-box h2 {
            margin-bottom: 20px;
        }

        .login-box input, .login-box select {
            width: 100%;
            padding: 10px;
            margin: 8px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .login-box button {
            width: 100%;
            padding: 10px;
            background: #5cb85c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }

        .login-box button:hover {
            background: #4cae4c;
        }

        .error-message {
            color: red;
            margin-bottom: 10px;
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }

        .register-link a {
            text-decoration: none;
            color: #e67e22;
        }

        .register-link a:hover {
            color: #c25e17;
        }
    </style>
</head>
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
<body>
    <div class="login-box">
        <h2>Login</h2>
        <?php if ($error): ?>
            <p class="error-message"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST">
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit">Login</button>
            <div class="register-link">
                <p>Don't have an account? <a href="/FurCareHub/users/register.php">Register here</a></p>
            </div>
        </form>
    </div>
</body>

</html>
