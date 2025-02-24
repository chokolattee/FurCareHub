<?php
session_start();
include('../includes/config.php');

if (!isset($_SESSION['owner_id'])) {
    die("Error: User not logged in");
}

$owner_id = $_SESSION['owner_id'];

if (!isset($_GET['pet_id'])) {
    die("Error: Pet ID is required");
}

$pet_id = intval($_GET['pet_id']);

// Fetch pet details
$query_pet = "SELECT * FROM pet WHERE pet_id = ? AND owner_id = ?";
$stmt_pet = $conn->prepare($query_pet);
$stmt_pet->bind_param("ii", $pet_id, $owner_id);
$stmt_pet->execute();
$result_pet = $stmt_pet->get_result();
$pet = $result_pet->fetch_assoc();

if (!$pet) {
    die("Error: Pet not found");
}

// Fetch pet types and sizes
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

    // Handle image upload
    if (!empty($_FILES["pet-image"]["name"])) {
        $target_dir = "../uploads/";
        $file_name = basename($_FILES["pet-image"]["name"]);
        $target_file = $target_dir . $file_name;
        if (move_uploaded_file($_FILES["pet-image"]["tmp_name"], $target_file)) {
            $img_path = $target_file;
        } else {
            die("Error uploading image.");
        }
    } else {
        $img_path = $pet['img_path']; // Keep existing image if no new upload
    }

    $sql = "UPDATE pet SET name=?, age=?, img_path=?, instructions=?, type_id=?, breed=?, size_id=? WHERE pet_id=? AND owner_id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sississii", $name, $age, $img_path, $instructions, $type_id, $breed, $size_id, $pet_id, $owner_id);
    
    if ($stmt->execute()) {
        echo "Pet information updated successfully";
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
    <link rel="stylesheet" href="/furcare/includes/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Darumadrop+One&display=swap" rel="stylesheet">
    <title>FurCareHub</title>
    <link rel="icon" href="pet3.png">
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
        <img src="../images/dog_png.png" alt="Image">
    </div>
    <div class="form-container">
        <h3>Pet Update</h3>
        <form action="pet-edit.php?pet_id=<?php echo $pet_id; ?>" method="POST" enctype="multipart/form-data">
            <label for="pet-image">Update Pet Image:</label>
            <input type="file" name="pet-image" accept="image/*">
            

            <div class="form-row">
                <div>
                    <label>Pet Name:</label>
                    <input type="text" name="pet-name" value="<?php echo htmlspecialchars($pet['name']); ?>" required>
                </div>
                <div>
                    <label>Age:</label>
                    <input type="number" name="pet-age" value="<?php echo $pet['age']; ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="dropdown">
                    <label for="pet-type">Pet Type:</label>
                    <select id="pet-type" name="pet-type" required>
                        <option value="" disabled selected>Select Pet Type</option>
                        <?php while ($row = $result_type->fetch_assoc()) {
                            $selected = ($row['type_id'] == $pet['type_id']) ? 'selected' : '';
                            echo "<option value='{$row['type_id']}' $selected>{$row['type']}</option>";
                        } ?>
                    </select>
                </div>

                <div class="dropdown">
                    <label for="pet-size">Pet Size:</label>
                    <select id="pet-size" name="pet-size" required>
                        <option value="" disabled selected>Select Size</option>
                        <?php while ($row = $result_size->fetch_assoc()) {
                            $selected = ($row['size_id'] == $pet['size_id']) ? 'selected' : '';
                            echo "<option value='{$row['size_id']}' $selected>{$row['size']}</option>";
                        } ?>
                    </select>
                </div>

                <div>
                    <label for="pet-breed">Breed:</label>
                    <input type="text" id="pet-breed" name="pet-breed" placeholder="Enter breed" value="<?php echo htmlspecialchars($pet['breed']); ?>" required>
                </div>
            </div>
            <label for="pet-activity">Note:</label>
            <input type="text" id="pet-activity" name="pet-activity" value="<?php echo htmlspecialchars($pet['instructions']); ?>" required>

            <button type="submit">Update</button>
        </form>
    </div>
</div>

</body>
</html>