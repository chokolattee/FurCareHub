<?php
// Correct path to the db_connect.php file
include('../includes/config.php');

// Fetching both admin and owner users
$queryUsers = "
    SELECT admin_id AS id, name, contact, email, 'Admin' AS role FROM admin
    UNION
    SELECT owner_id AS id, name, contact, email, 'Owner' AS role FROM owner
";

$userResult = mysqli_query($conn, $queryUsers);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../gui/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <title>User List - Pet a' Pat</title>
    <link rel="icon" href="images/pet3.png">
</head>

<body>
    <nav>
        <div class="wrapper">
            <div class="logo">
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

    <div class="container" style="padding: 30px 20px; max-width: 95%; margin: 50px auto;">
        <h2 style="font-family: 'Montserrat', sans-serif; color: white;">User List</h2>
        <table style="width: 100%; margin-top: 20px; background-color: #543306; color: white; border-radius: 8px; overflow: hidden;">
            <thead>
                <tr>
                    <th style="padding: 12px; background-color: var(--primary-green); color: white;">ID</th>
                    <th style="padding: 12px; background-color: var(--primary-green); color: white;">Name</th>
                    <th style="padding: 12px; background-color: var(--primary-green); color: white;">Contact</th>
                    <th style="padding: 12px; background-color: var(--primary-green); color: white;">Email</th>
                    <th style="padding: 12px; background-color: var(--primary-green); color: white;">Role</th>
                    <th style="padding: 12px; background-color: var(--primary-green); color: white;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($userResult)) { ?>
                    <tr>
                        <td style="padding: 12px;"><?php echo $user['id']; ?></td>
                        <td style="padding: 12px;"><?php echo $user['name']; ?></td>
                        <td style="padding: 12px;"><?php echo $user['contact']; ?></td>
                        <td style="padding: 12px;"><?php echo $user['email']; ?></td>
                        <td style="padding: 12px;"><?php echo $user['role']; ?></td>
                        <td style="padding: 12px;">
                            <!-- Edit Button -->
                            <button onclick="window.location.href='edit_user.php?id=<?php echo $user['id']; ?>&role=<?php echo strtolower($user['role']); ?>'"
                                    style="padding: 8px 15px; background-color: var(--primary-green); color: white; border: none; border-radius: 8px; cursor: pointer;">
                                Edit
                            </button>

                            <!-- Delete Button -->
                            <button onclick="window.location.href='delete_user.php?id=<?php echo $user['id']; ?>&role=<?php echo strtolower($user['role']); ?>'"
                                    style="padding: 8px 15px; background-color: #d9534f; color: white; border: none; border-radius: 8px; cursor: pointer; margin-left: 5px;">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>

</html>

<?php
// Close database connection
mysqli_close($conn);
?>
