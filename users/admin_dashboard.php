<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../gui/style.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>Admin Dashboard - Pet a' Pat</title>
    <link rel="icon" href="images/pet3.png">
    <style>
        .dashboard-box {
            text-align: center;
            margin-top: 100px;
        }

        .dashboard-box button {
            padding: 20px 40px;
            font-size: 18px;
            background-color: #5cb85c;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            width: 200px;
        }

        .dashboard-box button:hover {
            background-color: #4cae4c;
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
                <a href="home.html">HOME</a>
                <a href="logout.php">LOG OUT</a>
            </div>
            <div class="icon">
                <a href="">
                    <i class="fa-solid fa-user fa-lg"></i>
                </a>
            </div>
        </div>
    </nav>

    <div class="dashboard-box">
        <button onclick="window.location.href='user_list.php'">Users</button>
    </div>

</body>

</html>
