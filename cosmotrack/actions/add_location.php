<?php
include "../includes/db.php";

$location_name = $_POST['location_name'] ?? '';
$city = $_POST['city'] ?? '';
$country = $_POST['country'] ?? '';
$latitude = $_POST['latitude'] ?? '';
$longitude = $_POST['longitude'] ?? '';
$bortle_scale = $_POST['bortle_scale'] ?? '';

$sql = "INSERT INTO locations 
        (location_name, city, country, latitude, longitude, bortle_scale)
        VALUES 
        ('$location_name', '$city', '$country', '$latitude', '$longitude', '$bortle_scale')";

if (mysqli_query($conn, $sql)) {
    header("Location: ../view_locations.php");
    exit();
} else {
    echo "Location insert failed: " . mysqli_error($conn);
}
?>