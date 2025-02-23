<?php
include('../includes/config.php');


if (!isset($_GET['id']) || !isset($_GET['role'])) {
    echo "Invalid request. ID and role are required.";
    exit();
}

$userId = $_GET['id']; // This will be the admin_id or owner_id
$table = $_GET['role']; // Either 'admin' or 'owner'

// Validate the role to ensure it's either 'admin' or 'owner'
if ($table != 'admin' && $table != 'owner') {
    echo "Invalid role.";
    exit();
}

// Fetch the user details based on the table and ID
if ($table == 'admin') {
    $query = "SELECT admin_id, name, contact, email, password, img_path FROM admin WHERE admin_id = ?";
} else {
    $query = "SELECT owner_id, name, contact, email, password, img_path FROM owner WHERE owner_id = ?";
}

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Check if user exists
if (!$user) {
    echo "User not found.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $contact = $_POST['contact'];
    $email = $_POST['email'];
    $password = $_POST['password']; // Handle password field
    $imgPath = $_POST['img_path']; // Handle img_path field

    // Handle file upload
    if (isset($_FILES['img_path']) && $_FILES['img_path']['error'] == 0) {
        $imgPath = 'uploads/' . basename($_FILES['img_path']['name']);
        move_uploaded_file($_FILES['img_path']['tmp_name'], $imgPath);
    }

    // Update the user data based on table
    if ($table == 'admin') {
        $updateQuery = "UPDATE admin SET name = ?, contact = ?, email = ?, password = ?, img_path = ? WHERE admin_id = ?";
    } else {
        $updateQuery = "UPDATE owner SET name = ?, contact = ?, email = ?, password = ?, img_path = ? WHERE owner_id = ?";
    }

    // Prepare the statement for updating
    $updateStmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($updateStmt, 'sssssi', $name, $contact, $email, $password, $imgPath, $userId);

    // Execute the update query
    if (mysqli_stmt_execute($updateStmt)) {
        echo "User updated successfully.";
        header('Location: user_list.php'); // Redirect back to user list after success
    } else {
        echo "Error updating user.";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Pet a' Pat</title>
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="icon" href="images/pet3.png">
    <style>
        :root {
            --primary-green: #4CAF50;
            --background-color: #f7f7f7;
            --form-background: #fff;
            --button-hover: #45a049;
            --table-background: #543306;
            --text-color: #3d3d3d;
            --label-color: #555;
            --input-border: #ddd;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--background-color);
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 600px; /* Adjust width for responsiveness */
            margin: 50px auto;
            background-color: var(--form-background);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }

        h2 {
            text-align: center;
            color: var(--text-color);
            font-family: 'Montserrat', sans-serif;
            margin-bottom: 20px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            font-size: 16px;
            margin-bottom: 8px;
            color: var(--label-color);
        }

        input[type="text"], input[type="email"], input[type="password"], input[type="file"] {
            padding: 10px;
            font-size: 16px;
            margin-bottom: 20px;
            border: 2px solid var(--input-border);
            border-radius: 10px;
            outline: none;
            width: 100%; /* Ensure inputs take full width of the container */
            box-sizing: border-box;
        }

        input[type="text"]:focus, input[type="email"]:focus, input[type="password"]:focus, input[type="file"]:focus {
            border-color: var(--primary-green);
        }

        .password-note {
            font-size: 12px;
            color: #888;
            margin-top: -12px;
            margin-bottom: 20px;
        }

        button {
            padding: 12px;
            background-color: var(--primary-green);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            margin-top: 10px;
        }

        button:hover {
            background-color: var(--button-hover);
        }

        .form-group {
            display: flex;
            flex-direction: column;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Edit User</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="contact">Contact</label>
                <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($user['contact']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Leave blank if you don't want to change">
                <p class="password-note">Leave this blank if you don't want to change the password.</p>
            </div>

            <div class="form-group">
                <label for="img_path">Image</label>
                <input type="file" id="img_path" name="img_path">
            </div>

            <button type="submit">Update</button>
        </form>
    </div>
</body>
</html>