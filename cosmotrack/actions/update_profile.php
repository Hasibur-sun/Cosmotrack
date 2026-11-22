<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include "../includes/db.php";

$user_id = $_SESSION['user_id'];
$name    = trim($_POST['name']  ?? '');
$phone   = trim($_POST['phone'] ?? '');

if (empty($name)) {
    header("Location: ../profile.php?msg=error");
    exit();
}

$stmt = mysqli_prepare($conn,
    "UPDATE users SET name = ?, phone = ? WHERE user_id = ?"
);
mysqli_stmt_bind_param($stmt, "ssi", $name, $phone, $user_id);

if (mysqli_stmt_execute($stmt)) {
    // Update session name too
    $_SESSION['user_name'] = $name;
    header("Location: ../profile.php?msg=updated");
    exit();
} else {
    echo "Error: " . mysqli_stmt_error($stmt);
}
?>