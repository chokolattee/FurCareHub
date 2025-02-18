<?php
session_start();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>FurCareHub</title>
    <link rel="icon" href="images/pet3.png">
</head>
<body>

<nav>
    <div class="logo">
        <h2>FurCare Hub</h2>
    </div>

    <div class="links">
        <?php if (isset($_SESSION['owner_id'])): ?>
            <a href="">HOME</a>
            <a href="">SERVICES</a>
            <a href="">PRICING</a>
            <a href="">CONTACT</a>
            <a href="petinfo.php">PET INFO</a>
        <?php else: ?>
            <a href="">HOME</a>
            <a href="">CONTACT</a>
            <a href="/FurCareHub/users/login.php">Log In</a>
            <a href="/FurCareHub/users/register.php">Register</a>
        <?php endif; ?>
    </div>

    <div class="profile">
        <?php if (isset($_SESSION['owner_id'])): ?>
            <a href="profile.php">
                <i class="fa-solid fa-user fa-lg"></i> <!-- Profile icon -->
            </a>
        <?php endif; ?>
    </div>
</nav>

<section>
    <div class="content">
        <h1> We provide the best care for your pets. <br> <span> FurCareHub</span></h1>
        <p> They’re not just pets – they’re family.</p>
        <button onclick="window.location.href='home.html';">VIEW MORE</button>
    </div>
    <div class="image" data-aos="fade-left">
        <div class="box green">
            <img class="clickable-image" src="images/pet1.png" alt="Pet Image">
        </div>
        <div class="box orange">
            <img class="clickable-image" src="images/pet2.png" alt="Pet Image">
        </div>
        <div class="box pink">
            <img class="clickable-image" src="images/pet3.png" alt="Pet Image">
        </div>
    </div>
</section>

<script src="script.js"></script>
<script src="https://unpkg.com/aos@next/dist/aos.js"></script>
<script>
    AOS.init({
        duration: 1000,
        delay: 100
    });

    document.addEventListener("DOMContentLoaded", function () {
        const images = document.querySelectorAll(".clickable-image");

        images.forEach(image => {
            image.addEventListener("click", function () {
                this.classList.toggle("enlarged"); // Toggle zoom
            });
        });
    });
</script>

</body>
</html>
