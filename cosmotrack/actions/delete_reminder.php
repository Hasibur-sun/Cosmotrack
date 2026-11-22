<?php
include "../includes/db.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    mysqli_query($conn, "DELETE FROM reminders WHERE reminder_id = $id");
}

header("Location: ../reminders.php");
exit();
?>