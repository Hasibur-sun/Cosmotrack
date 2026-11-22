<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include "../includes/db.php";

$object_name        = $_POST['object_name']        ?? '';
$object_type        = $_POST['object_type']        ?? '';
$observation_date   = $_POST['observation_date']   ?? '';
$observation_time   = $_POST['observation_time']   ?? '';
$sky_condition      = $_POST['sky_condition']      ?? '';
$notes              = $_POST['notes']              ?? '';
$captured_latitude  = $_POST['captured_latitude']  ?? '';
$captured_longitude = $_POST['captured_longitude'] ?? '';
$visibility         = $_POST['visibility']         ?? 'Public';
$user_id            = $_SESSION['user_id'];

// Convert empty string to NULL for foreign keys
$location_id  = !empty($_POST['location_id'])  ? (int)$_POST['location_id']  : NULL;
$telescope_id = !empty($_POST['telescope_id']) ? (int)$_POST['telescope_id'] : NULL;

// Quality score calculation
$quality_score = 0;
if ($sky_condition == "Clear")             $quality_score += 40;
elseif ($sky_condition == "Partly Cloudy") $quality_score += 25;
elseif ($sky_condition == "Hazy")          $quality_score += 15;
else                                        $quality_score += 5;
if (!empty($captured_latitude)) $quality_score += 30;
if (strlen($notes) >= 20)       $quality_score += 30;

// Prepared statement — prevents SQL injection and handles NULL safely
$stmt = mysqli_prepare($conn,
    "INSERT INTO observations
     (object_name, object_type, observation_date, observation_time,
      location_id, telescope_id, sky_condition, notes,
      captured_latitude, captured_longitude, visibility, quality_score, user_id)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

// Type string explanation:
// s = object_name, object_type, observation_date, observation_time
// i = location_id, telescope_id
// s = sky_condition, notes, captured_latitude, captured_longitude, visibility
// i = quality_score, user_id
mysqli_stmt_bind_param($stmt, "ssssiisssssii",
    $object_name,
    $object_type,
    $observation_date,
    $observation_time,
    $location_id,
    $telescope_id,
    $sky_condition,
    $notes,
    $captured_latitude,
    $captured_longitude,
    $visibility,
    $quality_score,
    $user_id
);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../view_observations.php");
    exit();
} else {
    echo "Error: " . mysqli_stmt_error($stmt);
}
?>