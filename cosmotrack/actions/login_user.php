<?php
session_start();
include "../includes/db.php";

// Get form data
$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

// Basic validation
if (empty($email) || empty($password)) {
    header("Location: ../login.php?error=empty_fields");
    exit();
}

// Check if email exists in database
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);

    // Verify password against hashed password stored in database
    if (password_verify($password, $row['password'])) {

        // Password correct — save user info in session
        $_SESSION['user_id']   = $row['user_id'];
        $_SESSION['user_name'] = $row['name'];
        $_SESSION['user_email']= $row['email'];
        $_SESSION['user_role'] = $row['role'];

        // Redirect to dashboard
        header("Location: ../dashboard.php");
        exit();

    } else {
        // Wrong password
        header("Location: ../login.php?error=wrong_password");
        exit();
    }

} else {
    // Email not found in database
    header("Location: ../login.php?error=not_found");
    exit();
}
?>