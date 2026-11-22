<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include "../includes/db.php";

$id                 = (int)($_POST['observation_id'] ?? 0);
$object_name        = $_POST['object_name']        ?? '';
$object_type        = $_POST['object_type']        ?? '';
$observation_date   = $_POST['observation_date']   ?? '';
$observation_time   = $_POST['observation_time']   ?? '';
$sky_condition      = $_POST['sky_condition']      ?? '';
$notes              = $_POST['notes']              ?? '';
$visibility         = $_POST['visibility']         ?? 'Public';
$location_id        = !empty($_POST['location_id'])  ? (int)$_POST['location_id']  : NULL;
$telescope_id       = !empty($_POST['telescope_id']) ? (int)$_POST['telescope_id'] : NULL;

// Recalculate quality score
$quality_score = 0;
if ($sky_condition == "Clear")             $quality_score += 40;
elseif ($sky_condition == "Partly Cloudy") $quality_score += 25;
elseif ($sky_condition == "Hazy")          $quality_score += 15;
else                                        $quality_score += 5;
if (strlen($notes) >= 20) $quality_score += 30;

$stmt = mysqli_prepare($conn,
    "UPDATE observations SET
        object_name      = ?,
        object_type      = ?,
        observation_date = ?,
        observation_time = ?,
        location_id      = ?,
        telescope_id     = ?,
        sky_condition    = ?,
        notes            = ?,
        visibility       = ?,
        quality_score    = ?
     WHERE observation_id = ?"
);

mysqli_stmt_bind_param($stmt, "ssssiisssi i",
    $object_name,
    $object_type,
    $observation_date,
    $observation_time,
    $location_id,
    $telescope_id,
    $sky_condition,
    $notes,
    $visibility,
    $quality_score,
    $id
);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../view_observations.php?msg=updated");
    exit();
} else {
    echo "Error: " . mysqli_stmt_error($stmt);
}
?>