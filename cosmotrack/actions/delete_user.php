<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'Admin') {
    header("Location: ../login.php");
    exit();
}
include "../includes/db.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Prevent admin from deleting themselves
if ($id > 0 && $id != $_SESSION['user_id']) {
    mysqli_query($conn, "DELETE FROM users WHERE user_id = $id");
}

header("Location: ../admin.php?msg=deleted");
exit();
?>