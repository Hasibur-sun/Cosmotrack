<?php
include "../includes/db.php";

$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$phone = $_POST['phone'] ?? '';
$password_input = $_POST['password'] ?? '';
$role = $_POST['role'] ?? 'User';

$message = "";
$button_text = "";
$button_link = "";
$page_title = "";
$auto_redirect = "";

if (empty($name) || empty($email) || empty($password_input)) {
    $page_title = "Registration Failed";
    $message = "Name, email, and password are required.";
    $button_text = "Go Back";
    $button_link = "../register.html";
} else {
    // Check duplicate email
    $check_sql = "SELECT * FROM users WHERE email = '$email'";
    $check_result = mysqli_query($conn, $check_sql);

    if (mysqli_num_rows($check_result) > 0) {
        $page_title = "Email Already Exists";
        $message = "This email is already registered. Please use another email.";
        $button_text = "Back to Register";
        $button_link = "../register.html";
    } else {
        
        $password = password_hash($password_input, PASSWORD_DEFAULT);

        
        $sql = "INSERT INTO users (name, email, phone, password, role)
                VALUES ('$name', '$email', '$phone', '$password', '$role')";

        if (mysqli_query($conn, $sql)) {
            $page_title = "Registration Successful";
            $message = "Your account has been created successfully. You can now login to CosmoTrack.";
            $button_text = "Login Now";
            $button_link = "../login.php";

            // optional auto redirect after 3 seconds
            $auto_redirect = '<meta http-equiv="refresh" content="3;url=../login.php">';
        } else {
            $page_title = "Registration Failed";
            $message = "Something went wrong: " . mysqli_error($conn);
            $button_text = "Try Again";
            $button_link = "../register.php";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title; ?> | CosmoTrack</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <?php echo $auto_redirect; ?>
</head>
<body>
    <div class="stars"></div>
    <div class="nebula nebula-one"></div>
    <div class="nebula nebula-two"></div>

    <main class="auth-wrap">
        <a class="brand auth-brand" href="../index.html">
            <span class="planet-dot"></span>
            <span>CosmoTrack</span>
        </a>

        <section class="auth-card glass" style="text-align: center;">
            <p class="eyebrow">Account Status</p>
            <h1><?php echo $page_title; ?></h1>
            <p class="muted"><?php echo $message; ?></p>

            <div style="margin-top: 24px;">
                <a class="btn primary full" href="<?php echo $button_link; ?>">
                    <?php echo $button_text; ?>
                </a>
            </div>

            <?php if ($button_link == "../login.html") { ?>
                <p class="muted" style="margin-top: 16px;">
                    Redirecting to login page in 3 seconds...
                </p>
            <?php } ?>
        </section>
    </main>
</body>
</html>