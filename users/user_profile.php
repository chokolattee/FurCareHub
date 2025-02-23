<?php
include('../includes/config.php');
session_start();

if (!isset($_SESSION['owner_id'])) {
    header("Location: /FurCareHub/users/login.php");
    exit();
}
$userId = $_SESSION['owner_id'];

// Fetch Owner Data
$query = "SELECT owner_id, fname, m_i, lname, contact, email, password, img_path FROM owner WHERE owner_id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo "User not found.";
    exit();
}

// Handle Profile Update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $fname = trim($_POST['fname']);
    $m_i = trim($_POST['m_i']);
    $lname = trim($_POST['lname']);
    $contact = trim($_POST['contact']);
    $email = trim($_POST['email']);
    $oldPassword = trim($_POST['old_password']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);
    $imgPath = $user['img_path'];

    if (isset($_FILES['img_path']) && $_FILES['img_path']['error'] == 0) {
        $targetDir = '../uploads/user/';
        $fileName = basename($_FILES['img_path']['name']);
        $targetFilePath = $targetDir . $fileName;

        if (move_uploaded_file($_FILES['img_path']['tmp_name'], $targetFilePath)) {
            $imgPath = 'uploads/user/' . $fileName;
        } else {
            echo "Error uploading image.";
        }
    }

    // Check if old password matches
    if (!empty($oldPassword) && !password_verify($oldPassword, $user['password'])) {
        echo "Old password is incorrect.";
        exit();
    }

    // Update password if new password is provided and matches confirmation
    $password = $user['password'];
    if (!empty($newPassword)) {
        if ($newPassword === $confirmPassword) {
            $password = password_hash($newPassword, PASSWORD_BCRYPT);
        } else {
            echo "New password and confirmation do not match.";
            exit();
        }
    }

    // Update Owner Data
    $updateQuery = "UPDATE owner SET fname = ?, m_i = ?, lname = ?, contact = ?, email = ?, password = ?, img_path = ? WHERE owner_id = ?";
    $updateStmt = mysqli_prepare($conn, $updateQuery);
    mysqli_stmt_bind_param($updateStmt, 'sssssssi', $fname, $m_i, $lname, $contact, $email, $password, $imgPath, $userId);

    if (mysqli_stmt_execute($updateStmt)) {
        header("Location: user_profile.php"); // Redirect to refresh profile
        exit();
    } else {
        echo "Error updating profile.";
    }
}

// Handle Account Deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $deleteQuery = "DELETE FROM owner WHERE owner_id = ?";
    $deleteStmt = mysqli_prepare($conn, $deleteQuery);
    mysqli_stmt_bind_param($deleteStmt, 'i', $userId);

    if (mysqli_stmt_execute($deleteStmt)) {
        session_destroy();
        header("Location: ../index.php"); // Redirect to homepage after deletion
        exit();
    } else {
        echo "Error deleting account.";
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Profile - Pet a' Pat</title>
    <link rel="stylesheet" href="../includes/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="icon" href="../images/pet3.png">
    <style>
        :root {
            --primary-green: #4CAF50;
            --danger-red: #d9534f;
            --background-color: #f7f7f7;
            --form-background: #543306;
            --button-hover: #45a049;
            --text-color: white;
            --label-color: #ddd;
            --input-border: #bbb;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--background-color);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            position: relative;
            display: flex;
            flex-direction: row;
            background-color: var(--form-background);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.3);
            color: var(--text-color);
            width: 1000px;
            max-width: 90%;
        }

        .profile-section {
            width: 25%;
            text-align: center;
            padding-right: 20px;
            border-right: 2px solid var(--label-color);
        }

        .profile-section img {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 10px;
        }

        .form-section {
            width: 75%;
            padding-left: 20px;
        }

        .user-info, .security-info {
            margin-bottom: 20px;
        }

        h2 {
            text-align: left;
            font-family: 'Montserrat', sans-serif;
            margin-bottom: 20px;
        }

        label {
            font-size: 14px;
            color: var(--label-color);
            font-weight: bold;
        }

        input[type="text"], input[type="email"], input[type="password"], input[type="file"] {
            padding: 8px;
            font-size: 14px;
            margin-top: 5px;
            border: 2px solid var(--input-border);
            border-radius: 8px;
            width: 100%;
            background-color: #fff;
            color: #000;
        }

        .password-note {
            font-size: 12px;
            color: #ccc;
            margin-top: 5px;
        }

        button {
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            width: 100%;
            margin-top: 10px;
        }

        .update-btn {
            background-color: var(--primary-green);
            color: white;
        }

        .update-btn:hover {
            background-color: var(--button-hover);
        }

        .delete-btn {
            background-color: var(--danger-red);
            color: white;
        }

        .delete-btn:hover {
            background-color: #c9302c;
        }

        /* Home Icon Button */
        .home-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 24px;
            color: var(--text-color);
            background: none;
            border: none;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .home-btn:hover {
            color: var(--primary-green);
        }

        .toggle-buttons {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .toggle-buttons button {
            flex: 1;
            margin: 0 5px;
            padding: 10px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s ease;
            background-color: var(--primary-green);
            color: white;
        }

        .toggle-buttons button:hover {
            background-color: var(--button-hover);
        }

        .toggle-buttons button.active {
            background-color: var(--danger-red);
        }

        .toggle-buttons button.active:hover {
            background-color: #c9302c;
        }

        .hidden {
            display: none;
        }
    </style>
    <script>
        function confirmDelete() {
            return confirm("Are you sure you want to delete your account? This action cannot be undone.");
        }

        function toggleSection(section) {
            const userInfo = document.getElementById('user-info');
            const securityInfo = document.getElementById('security-info');
            const userButton = document.getElementById('toggle-user');
            const securityButton = document.getElementById('toggle-security');

            if (section === 'user') {
                userInfo.classList.remove('hidden');
                securityInfo.classList.add('hidden');
                userButton.classList.add('active');
                securityButton.classList.remove('active');
            } else if (section === 'security') {
                userInfo.classList.add('hidden');
                securityInfo.classList.remove('hidden');
                userButton.classList.remove('active');
                securityButton.classList.add('active');
            }
        }

        // Initialize the page with User Info visible
        document.addEventListener('DOMContentLoaded', function () {
            toggleSection('user');
        });
    </script>
</head>
<body>
    <div class="container">
        <!-- Home Button -->
        <a href="/FurCareHub/index.php" class="home-btn"><i class="fa fa-home"></i></a>

        <div class="profile-section">
            <img src="<?php echo !empty($user['img_path']) ? '../' . htmlspecialchars($user['img_path']) : '../images/default.png'; ?>" alt="User Image">
            <h3><?php echo htmlspecialchars($user['fname'] . ' ' . $user['m_i'] . ' ' . $user['lname']); ?></h3>
        </div>
        <div class="form-section">
            <!-- Toggle Buttons -->
            <div class="toggle-buttons">
                <button id="toggle-user" onclick="toggleSection('user')">User Information</button>
                <button id="toggle-security" onclick="toggleSection('security')">Security Information</button>
            </div>

            <!-- User Information Section -->
            <div id="user-info">
                <h3>User Information</h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <label for="fname">First Name</label>
                    <input type="text" id="fname" name="fname" value="<?php echo htmlspecialchars($user['fname']); ?>" required>

                    <label for="m_i">Middle Initial</label>
                    <input type="text" id="m_i" name="m_i" value="<?php echo htmlspecialchars($user['m_i']); ?>" required>

                    <label for="lname">Surname</label>
                    <input type="text" id="lname" name="lname" value="<?php echo htmlspecialchars($user['lname']); ?>" required>

                    <label for="contact">Phone</label>
                    <input type="text" id="contact" name="contact" value="<?php echo htmlspecialchars($user['contact']); ?>" required>

                    <label for="img_path">Image</label>
                    <input type="file" id="img_path" name="img_path">

                    <button type="submit" name="update" class="update-btn">Update</button>
                </form>
            </div>

            <!-- Security Information Section -->
            <div id="security-info" class="hidden">
                <h3>Security Information</h3>
                <form method="POST" action="" enctype="multipart/form-data">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>

                    <label for="old_password">Old Password</label>
                    <input type="password" id="old_password" name="old_password" placeholder="Enter old password">

                    <label for="new_password">New Password</label>
                    <input type="password" id="new_password" name="new_password" placeholder="Enter new password">

                    <label for="confirm_password">Confirm New Password</label>
                    <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm new password">
                    <p class="password-note">Leave password fields blank if you don't want to change the password.</p>

                    <button type="submit" name="update" class="update-btn">Update</button>
                </form>
            </div>

            <!-- Delete Account Form -->
            <form method="POST" action="" onsubmit="return confirmDelete();">
                <button type="submit" name="delete" class="delete-btn">Delete Account</button>
            </form>
        </div>
    </div>
</body>
</html>