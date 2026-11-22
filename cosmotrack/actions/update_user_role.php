<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
include "../includes/db.php";

$user_id = (int)($_POST['user_id'] ?? 0);
$role    = $_POST['role'] ?? 'User';

// Only allow valid roles
if (!in_array($role, ['User', 'Admin'])) {
    header("Location: ../admin.php");
    exit();
}

// Prevent changing own role
if ($user_id > 0 && $user_id != $_SESSION['user_id']) {
    $stmt = mysqli_prepare($conn,
        "UPDATE users SET role = ? WHERE user_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "si", $role, $user_id);
    mysqli_stmt_execute($stmt);
}

header("Location: ../admin.php?msg=role_updated");
exit();
?>