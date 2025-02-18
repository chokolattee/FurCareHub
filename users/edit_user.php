<?php
include('../includes/config.php');

// Get the user ID and role from the URL
$userId = $_GET['id']; // This will be the admin_id or owner_id
$table = $_GET['role']; // Either 'admin' or 'owner'

// Fetch the user details based on the table and ID
if ($table == 'admin') {
    $query = "SELECT admin_id, name, contact, email, password, img_path FROM admin WHERE admin_id = ?";
} else if ($table == 'owner') {
    $query = "SELECT owner_id, name, contact, email, password, img_path FROM owner WHERE owner_id = ?";
} else {
    echo "Invalid role.";
    exit();
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
    <title>Edit User</title>
</head>
<body>
    <h2>Edit User</h2>
    <form method="POST">
        <label for="name">Name</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required><br>

        <label for="contact">Contact</label>
        <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($user['contact']); ?>" required><br>

        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required><br>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" placeholder="Enter new password if you want to change"><br>

        <label for="img_path">Image Path</label>
        <input type="text" id="img_path" name="img_path" value="<?php echo htmlspecialchars($user['img_path']); ?>"><br>

        <button type="submit">Update</button>
    </form>
</body>
</html>
