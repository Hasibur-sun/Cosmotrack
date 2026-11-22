<?php
include "../includes/db.php";

$id = $_GET['id'] ?? 0;

$sql = "DELETE FROM observations WHERE observation_id = $id";

if (mysqli_query($conn, $sql)) {
    header("Location: ../view_observations.php");
    exit();
} else {
    echo "Delete failed: " . mysqli_error($conn);
}
?>