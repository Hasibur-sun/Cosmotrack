<?php
include "../includes/db.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    mysqli_query($conn, "DELETE FROM satellite_passes WHERE pass_id = $id");
}

header("Location: ../satellite.php");
exit();
?>