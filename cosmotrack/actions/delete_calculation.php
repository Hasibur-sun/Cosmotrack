<?php
include "../includes/db.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    mysqli_query($conn, "DELETE FROM dilation_calculations WHERE calc_id = $id");
}

header("Location: ../timedilation.php");
exit();
?>