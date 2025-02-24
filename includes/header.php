<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="/SYSTEM/includes/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>FurCare Hub</title>
    <link rel="icon" href="../images/pet3.png">
    <style>
    :root {
        --primary: #FFF2D7;
        --secondary-green: #543306;
        --text-dark: brown;
        --background-light: #FFF2D7;
        --button-hover: #F8C794;
    }

    body {
        font-family: Arial, sans-serif;
        background-color: var(--background-light);
        margin: 0;
        padding: 0;
    }

    nav {
        background-color: var(--primary);
        padding: 15px 50px;
        display: fixed;
        justify-content: space-between;
        align-items: center;
    }

    .logo-container {
        display: flex;
        align-items: center;
    }

    .logo img {
        width: 60px;
        margin-right: 10px;
    }

    .title {
        font-size: 24px;
        font-weight: bold;
        color: var(--text-dark);
    }

    .links a {
        color: var(--text-dark);
        text-decoration: none;
        font-weight: bold;
        margin: 0 15px;
        transition: 0.3s;
    }

    .links a:hover {
        color: var(--secondary-green);
    }

    .icon a {
        color: var(--text-dark);
        font-size: 18px;
        margin-left: 15px;
    }

    .icon a:hover {
        color: var(--secondary-green);
    }

    .logout-btn {
        background-color: var(--button-hover);
        border: none;
        padding: 8px 12px;
        font-weight: bold;
        cursor: pointer;
        border-radius: 5px;
        color: var(--text-dark);
        transition: 0.3s;
    }

    .logout-btn:hover {
        background-color: var(--secondary-green);
        color: white;
    }
    </style>
</head>
<body>
    <nav>
        <div class="logo-container" data-aos="fade-right">
            <div class="logo">
                
            </div>
            <div class="title">FurCare Hub</div>
        </div>

        <div class="links">
            <a href="/FurCareHub/index.php">HOME</a>
            <a href="contact.php">CONTACT</a>

            <?php if (isset($_SESSION['owner_id'])): ?>
                <!-- Show only if owner is logged in -->
                <a href="/FurCareHub/pet/petinfo.php">PET INFO</a>
                <a href="/FurCareHub/users/membership.php">MEMBERSHIP</a>
            <?php else: ?>
                <!-- Show only if owner is NOT logged in -->
                <a href="/FurCareHub/users/login.php">LOG IN</a>
                <a href="/FurCareHub/users/register.php">REGISTER</a>
            <?php endif; ?>
        </div>

        <div class="icon">
            <?php if (isset($_SESSION['owner_id'])): ?>
                <!-- Show Profile Icon & Logout Button if logged in -->
                <a href="/FurCareHub/users/userprofile.php">
                    <i class="fa-solid fa-user fa-lg"></i>
                </a>
                <form action="/FurCareHub/users/logout.php" method="POST" style="display:inline;">
                    <button type="submit" class="logout-btn">LOG OUT</button>
                </form>
            <?php endif; ?>
        </div>
    </nav>
</body>
</html>
