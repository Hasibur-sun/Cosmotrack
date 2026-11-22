<?php
session_start();
include "../includes/db.php";

$telescope_name = $_POST['telescope_name'] ?? '';
$telescope_type = $_POST['telescope_type'] ?? '';
$aperture       = $_POST['aperture']       ?? '';
$focal_length   = $_POST['focal_length']   ?? '';
$mount_type     = $_POST['mount_type']     ?? '';
$description    = $_POST['description']    ?? '';
$user_id        = $_SESSION['user_id']     ?? NULL;

if (empty($telescope_name)) {
    header("Location: ../telescopes.php?error=empty");
    exit();
}

$stmt = mysqli_prepare($conn,
    "INSERT INTO telescopes
     (telescope_name, telescope_type, aperture, focal_length, mount_type, description, user_id)
     VALUES (?, ?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param($stmt, "ssssssi",
    $telescope_name,
    $telescope_type,
    $aperture,
    $focal_length,
    $mount_type,
    $description,
    $user_id
);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../view_telescopes.php");
    exit();
} else {
    echo "Error: " . mysqli_stmt_error($stmt);
}
?>