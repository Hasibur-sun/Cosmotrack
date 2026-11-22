<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include "../includes/db.php";

$user_id              = $_SESSION['user_id'];
$calc_type            = $_POST['calc_type']             ?? '';
$scenario_name        = $_POST['scenario_name']         ?? '';
$input_value          = $_POST['input_value']           ?? 0;
$input_unit           = $_POST['input_unit']            ?? '';
$earth_time_years     = $_POST['earth_time_years']      ?? 0;
$traveler_time_years  = $_POST['traveler_time_years']   ?? 0;
$dilation_factor      = $_POST['dilation_factor']       ?? 0;
$time_difference_years= $_POST['time_difference_years'] ?? 0;

$stmt = mysqli_prepare($conn,
    "INSERT INTO dilation_calculations
     (user_id, calc_type, scenario_name, input_value, input_unit,
      earth_time_years, traveler_time_years, dilation_factor, time_difference_years)
     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
);

mysqli_stmt_bind_param($stmt, "issdsddddd",
    $user_id,
    $calc_type,
    $scenario_name,
    $input_value,
    $input_unit,
    $earth_time_years,
    $traveler_time_years,
    $dilation_factor,
    $time_difference_years
);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../timedilation.php");
    exit();
} else {
    echo "Error: " . mysqli_stmt_error($stmt);
}
?>