<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include "../includes/db.php";

$id             = (int)($_POST['telescope_id'] ?? 0);
$telescope_name = $_POST['telescope_name'] ?? '';
$telescope_type = $_POST['telescope_type'] ?? '';
$aperture       = $_POST['aperture']       ?? '';
$focal_length   = $_POST['focal_length']   ?? '';
$mount_type     = $_POST['mount_type']     ?? '';
$description    = $_POST['description']    ?? '';

$stmt = mysqli_prepare($conn,
    "UPDATE telescopes SET
        telescope_name = ?,
        telescope_type = ?,
        aperture       = ?,
        focal_length   = ?,
        mount_type     = ?,
        description    = ?
     WHERE telescope_id = ?"
);

mysqli_stmt_bind_param($stmt, "ssssssi",
    $telescope_name,
    $telescope_type,
    $aperture,
    $focal_length,
    $mount_type,
    $description,
    $id
);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../view_telescopes.php?msg=updated");
    exit();
} else {
    echo "Error: " . mysqli_stmt_error($stmt);
}
?>