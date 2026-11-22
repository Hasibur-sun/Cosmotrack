<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body data-page="login" class="">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <main class="auth-wrap">
    <a class="brand auth-brand" href="index.html">
      <span class="planet-dot"></span>
      <span>CosmoTrack</span>
    </a>

    <section class="auth-card glass">
      <p class="eyebrow">Welcome back</p>
      <h1>Login</h1>

      <?php if (isset($_GET['error'])): ?>
  <?php if ($_GET['error'] == 'empty_fields'): ?>
    <p style="color:#ff6b6b; margin-bottom:10px;">❌ Please enter both email and password.</p>
  <?php elseif ($_GET['error'] == 'wrong_password'): ?>
    <p style="color:#ff6b6b; margin-bottom:10px;">❌ Wrong password. Please try again.</p>
  <?php elseif ($_GET['error'] == 'not_found'): ?>
    <p style="color:#ff6b6b; margin-bottom:10px;">❌ No account found with that email.</p>
  <?php endif; ?>
<?php endif; ?>

      <form method="POST" action="actions/login_user.php">
        <label>Email
          <input type="email" name="email" required placeholder="you@example.com">
        </label>
        <label>Password
          <input type="password" name="password" required placeholder="••••••••">
        </label>
        <button class="btn primary full" type="submit">Login</button>
      </form>

      <p class="muted">New here? <a href="register.html">Create account</a></p>
    </section>
  </main>

  <script src="assets/js/app.js"></script>
</body>
</html>