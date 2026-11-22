<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include "../includes/db.php";

$id           = (int)($_POST['location_id'] ?? 0);
$location_name = $_POST['location_name'] ?? '';
$city         = $_POST['city']         ?? '';
$country      = $_POST['country']      ?? '';
$latitude     = $_POST['latitude']     ?? '';
$longitude    = $_POST['longitude']    ?? '';
$bortle_scale = (int)($_POST['bortle_scale'] ?? 0);

$stmt = mysqli_prepare($conn,
    "UPDATE locations SET
        location_name = ?,
        city          = ?,
        country       = ?,
        latitude      = ?,
        longitude     = ?,
        bortle_scale  = ?
     WHERE location_id = ?"
);

mysqli_stmt_bind_param($stmt, "sssssii",
    $location_name,
    $city,
    $country,
    $latitude,
    $longitude,
    $bortle_scale,
    $id
);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../view_locations.php?msg=updated");
    exit();
} else {
    echo "Error: " . mysqli_stmt_error($stmt);
}
?>