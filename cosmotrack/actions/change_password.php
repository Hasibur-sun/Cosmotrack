<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include "../includes/db.php";

$user_id          = $_SESSION['user_id'];
$current_password = $_POST['current_password'] ?? '';
$new_password     = $_POST['new_password']     ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';

// Fetch current hashed password
$user = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT password FROM users WHERE user_id = $user_id"
));

// Verify current password
if (!password_verify($current_password, $user['password'])) {
    header("Location: ../profile.php?msg=wrong_password");
    exit();
}

// Check new passwords match
if ($new_password !== $confirm_password) {
    header("Location: ../profile.php?msg=mismatch");
    exit();
}

// Check new password length
if (strlen($new_password) < 6) {
    header("Location: ../profile.php?msg=too_short");
    exit();
}

// Hash and update
$hashed = password_hash($new_password, PASSWORD_DEFAULT);
$stmt   = mysqli_prepare($conn,
    "UPDATE users SET password = ? WHERE user_id = ?"
);
mysqli_stmt_bind_param($stmt, "si", $hashed, $user_id);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../profile.php?msg=password_changed");
    exit();
} else {
    echo "Error: " . mysqli_stmt_error($stmt);
}
?>