<?php
session_start();
include('../includes/config.php');

if (!isset($_SESSION['owner_id'])) {
    die("Error: User not logged in");
}

$owner_id = $_SESSION['owner_id'];

// Fetch pets owned by the logged-in user
$query_pets = "SELECT p.pet_id, p.name, p.age, p.img_path, p.instructions, t.type, p.breed, s.size 
               FROM pet p 
               JOIN type t ON p.type_id = t.type_id 
               JOIN size s ON p.size_id = s.size_id 
               WHERE p.owner_id = ?";

if ($stmt = $conn->prepare($query_pets)) {
    $stmt->bind_param("i", $owner_id);
    $stmt->execute();
    $result_pets = $stmt->get_result();
    $stmt->close();
} else {
    die("Error fetching pet records: " . $conn->error);
}

// Delete pet
if (isset($_GET['delete'])) {
    $pet_id = intval($_GET['delete']);
    $delete_query = "DELETE FROM pet WHERE pet_id = ? AND owner_id = ?";
    if ($stmt = $conn->prepare($delete_query)) {
        $stmt->bind_param("ii", $pet_id, $owner_id);
        if ($stmt->execute()) {
            header("Location: pet-view.php");
            exit();
        } else {
            echo "Error deleting pet: " . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Pets</title>
    <link rel="stylesheet" href="/FurCareHub/includes/style.css">
    <style>
        .container {
            background: #543306;
            padding: 20px;
            border-radius: 50px;
            box-shadow: 5px 5px 20px rgba(0, 0, 0, 0.1);
            max-width: 1050px;
            width: 100%;
            height: 100%;
            text-align: center;
            color: white;
            margin: 70px auto;
            font-family: 'Darumadrop One', sans-serif;
        }
        .table-container {
            overflow-x: auto;
        }
        table {
            width: 1000px;
            border-collapse: collapse;
            border: 1px solid #543306;
        }
        th, td {
            border: 1px solid #FFF2D7;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #543306;
        }
        img {
            border-radius: 5px;
            width: 50%;
            height: 50%;
        }
        .btn {
            padding: 5px 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 2px;
        }
        .btn-edit {
            background-color: #4CAF50;
            color: white;
        }
        .btn-delete {
            background-color: #f44336;
            color: white;
        }
    </style>
</head>
<div>
    <nav>
        <div class="logo"><h2>FurCare Hub</h2></div>
        <div class="links">
            <a href="index.php">HOME</a>
            <a href="contact.php">CONTACT</a>
            <a href="logout.php">Log Out</a>
        </div>
    </nav>

    <div class="form-container">
        <div class="container">
        <h3>My Pet</h3>
            <form action="petinfo.php">
                <button type="submit">Add New Pet</button>
            </form> <br>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Age</th>
                                <th>Type</th>
                                <th>Breed</th>
                                <th>Size</th>
                                <th>Note</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($pet = $result_pets->fetch_assoc()) { ?>
                                <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars('../uploads/' . basename($pet['img_path'])); ?>" 
                                        alt="Pet Image"
                                        onerror="this.onerror=null; this.src='../images/default-pet.png';">
                                </td>
                                    <td><?php echo htmlspecialchars($pet['name']); ?></td>
                                    <td><?php echo htmlspecialchars($pet['age']); ?></td>
                                    <td><?php echo htmlspecialchars($pet['type']); ?></td>
                                    <td><?php echo htmlspecialchars($pet['breed']); ?></td>
                                    <td><?php echo htmlspecialchars($pet['size']); ?></td>
                                    <td><?php echo htmlspecialchars($pet['instructions']); ?></td>
                                    <td>
                                        <a href="pet-edit.php?pet_id=<?php echo $pet['pet_id']; ?>" class="btn btn-edit">Edit</a>
                                        <a href="pet-view.php?delete=<?php echo $pet['pet_id']; ?>" class="btn btn-delete" onclick="return confirm('Are you sure you want to delete this pet?');">Delete</a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
        </div>
    </div>
</body>
</html>