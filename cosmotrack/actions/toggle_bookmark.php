<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include "../includes/db.php";

$user_id      = $_SESSION['user_id'];
$planet_id    = (int)($_POST['planet_id']    ?? 0);
$is_bookmarked = (int)($_POST['is_bookmarked'] ?? 0);

if ($planet_id > 0) {
    if ($is_bookmarked) {
        // Remove bookmark
        mysqli_query($conn,
            "DELETE FROM exoplanet_bookmarks
             WHERE user_id = $user_id
             AND planet_id = $planet_id"
        );
        header("Location: ../exoplanets.php?msg=removed");
    } else {
        // Add bookmark
        $stmt = mysqli_prepare($conn,
            "INSERT IGNORE INTO exoplanet_bookmarks
             (user_id, planet_id) VALUES (?, ?)"
        );
        mysqli_stmt_bind_param($stmt, "ii", $user_id, $planet_id);
        mysqli_stmt_execute($stmt);
        header("Location: ../exoplanets.php?msg=bookmarked");
    }
}
exit();
?>