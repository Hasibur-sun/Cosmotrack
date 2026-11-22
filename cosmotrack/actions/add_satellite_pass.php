<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include "../includes/db.php";

$user_id          = $_SESSION['user_id'];
$satellite_name   = $_POST['satellite_name']   ?? '';
$satellite_type   = $_POST['satellite_type']   ?? '';
$pass_date        = $_POST['pass_date']        ?? '';
$pass_time        = $_POST['pass_time']        ?? '';
$max_elevation    = !empty($_POST['max_elevation'])    ? (int)$_POST['max_elevation']    : NULL;
$duration_seconds = !empty($_POST['duration_seconds']) ? (int)$_POST['duration_seconds'] : NULL;
$direction_start  = $_POST['direction_start']  ?? '';
$direction_end    = $_POST['direction_end']    ?? '';
$brightness       = $_POST['brightness']       ?? '';
$notes            = $_POST['notes']            ?? '';

$stmt = mysqli_prepare($conn,
    "INSERT INTO satellite_passes
     (user_id, satellite_name, satellite_type, pass_date, pass_time,
      max_elevation, duration_seconds, direction_start, direction_end,
      brightness, notes)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param($stmt, "issssiisss s",
    $user_id,
    $satellite_name,
    $satellite_type,
    $pass_date,
    $pass_time,
    $max_elevation,
    $duration_seconds,
    $direction_start,
    $direction_end,
    $brightness,
    $notes
);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../satellite.php");
    exit();
} else {
    echo "Error: " . mysqli_stmt_error($stmt);
}
?>