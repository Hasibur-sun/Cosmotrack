<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}
include "../includes/db.php";

$user_id     = $_SESSION['user_id'];
$movie_id    = (int)($_POST['movie_id']    ?? 0);
$user_rating = (int)($_POST['user_rating'] ?? 0);
$review      = $_POST['review'] ?? '';

if ($movie_id == 0 || $user_rating < 1 || $user_rating > 5) {
    header("Location: ../movies.php?msg=error");
    exit();
}

// Check if already rated
$existing = mysqli_fetch_row(mysqli_query($conn,
    "SELECT rating_id FROM movie_ratings
     WHERE user_id = $user_id AND movie_id = $movie_id"
));

if ($existing) {
    // UPDATE existing rating
    $stmt = mysqli_prepare($conn,
        "UPDATE movie_ratings SET user_rating = ?, review = ?
         WHERE user_id = ? AND movie_id = ?"
    );
    mysqli_stmt_bind_param($stmt, "isii",
        $user_rating, $review, $user_id, $movie_id);
    mysqli_stmt_execute($stmt);
    header("Location: ../movies.php?msg=updated");
} else {
    // INSERT new rating
    $stmt = mysqli_prepare($conn,
        "INSERT INTO movie_ratings (user_id, movie_id, user_rating, review)
         VALUES (?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "iiis",
        $user_id, $movie_id, $user_rating, $review);
    mysqli_stmt_execute($stmt);
    header("Location: ../movies.php?msg=rated");
}
exit();
?>