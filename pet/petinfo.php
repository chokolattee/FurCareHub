<?php
session_start();
include('../includes/config.php');

if (!isset($_SESSION['owner_id'])) {
    die("Error: User not logged in");
}

$owner_id = $_SESSION['owner_id'];

$query_type = "SELECT * FROM type";
$result_type = $conn->query($query_type);

$query_size = "SELECT * FROM size";
$result_size = $conn->query($query_size);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['pet-name']);
    $age = intval($_POST['pet-age']);
    $type_id = intval($_POST['pet-type']);
    $breed = trim($_POST['pet-breed']);
    $size_id = intval($_POST['pet-size']);
    $instructions = trim($_POST['pet-activity']);

    $target_dir = "../uploads/pet";
    $file_name = basename($_FILES["pet-image"]["name"]);
    $target_file = $target_dir . $file_name;

    if (move_uploaded_file($_FILES["pet-image"]["tmp_name"], $target_file)) {
        $sql = "INSERT INTO pet (owner_id, name, age, img_path, instructions, type_id, breed, size_id) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("isissssi", $owner_id, $name, $age, $target_file, $instructions, $type_id, $breed, $size_id);
            
            if ($stmt->execute()) {
                echo "New pet record created successfully";
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    } else {
        echo "Error uploading image.";
    }
    
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="/FurCareHub/includes/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Darumadrop+One&display=swap" rel="stylesheet">
    <title>FurCareHub</title>
    <link rel="icon" href="pet3.png">
    <style>
        body {
            background-image: url('includes/images/blob-scene-haikei.png'); 
            background-size: cover; 
            background-position: center; 
            background-repeat: no-repeat;
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
        <a href="login.php">Log In</a>
        <a href="register.php">Register</a>
    </div>
    <div class="profile">
        <a href="">
            <i class="fa-solid fa-user fa-lg"></i>
        </a>
    </div>
</nav>

<div class="container">
    <div class="image-container">
        <img src="images/dog_png.png" alt="Image">
    </div>

    <div class="form-container">
        <h3>Pet Info</h3>
        <form action="petinfo.php" method="POST" enctype="multipart/form-data">
            <label for="pet-image">Upload Pet Image:</label>
            <input type="file" id="pet-image" name="pet-image" accept="image/*" required>

            <div class="form-row">
                <div>
                    <label for="pet-name">Pet Name:</label>
                    <input type="text" id="pet-name" name="pet-name" required>
                </div>
                <div>
                    <label for="pet-age">Age:</label>
                    <input type="number" id="pet-age" name="pet-age" required>
                </div>
            </div>

            <div class="form-row">
                <div class="dropdown">
                    <label for="pet-type">Pet Type:</label>
                    <select id="pet-type" name="pet-type" required>
                        <option value="" disabled selected>Select Pet Type</option>
                        <?php
                        if ($result_type->num_rows > 0) {
                            while ($row = $result_type->fetch_assoc()) {
                                echo "<option value='" . $row['type_id'] . "'>" . $row['type'] . "</option>";
                            }
                        } else {
                            echo "<option value='' disabled>No pet types available</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="dropdown">
                    <label for="pet-size">Pet Size:</label>
                    <select id="pet-size" name="pet-size" required>
                        <option value="" disabled selected>Select Size</option>
                        <?php
                        if ($result_size->num_rows > 0) {
                            while ($row = $result_size->fetch_assoc()) {
                                echo "<option value='" . $row['size_id'] . "'>" . $row['size'] . "</option>";
                            }
                        } else {
                            echo "<option value='' disabled>No pet sizes available</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="pet-breed">Breed:</label>
                    <input type="text" id="pet-breed" name="pet-breed" placeholder="Enter breed" required>
                </div>
            </div>
            <label for="pet-activity">Note:</label>
            <input type="text" id="pet-activity" name="pet-activity">

            <button type="submit">Submit</button>
        </form>
    </div>
</div>

</body>
</html>
