<link rel="stylesheet" href="../../includes/style.css">
<?php
if (isset($_SESSION['error'])) {
    echo '<div class="error">' . $_SESSION['error'] . '</div>';
    unset($_SESSION['error']); // Clear the error message after displaying it
}
if (isset($_SESSION['success'])) {
    echo '<div class="success">' . $_SESSION['success'] . '</div>';
    unset($_SESSION['success']); // Clear the success message after displaying it
}
?>
