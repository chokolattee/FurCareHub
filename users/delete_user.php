<?php
include('../includes/config.php');

$userId = $_GET['id'];
$role = $_GET['role'];

if ($role == 'admin') {
    $query = "DELETE FROM admin WHERE admin_id = ?";
} else {
    $query = "DELETE FROM owner WHERE owner_id = ?";
}

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $userId);

if (mysqli_stmt_execute($stmt)) {
    echo "User deleted successfully.";
    header('Location: user_list.php'); // Redirect back to user list
} else {
    echo "Error deleting user.";
}

mysqli_close($conn);
?>
