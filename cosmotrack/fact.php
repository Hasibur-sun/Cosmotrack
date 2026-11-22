<?php
include "includes/db.php";

$result = mysqli_query($conn, "SELECT * FROM space_facts ORDER BY RAND() LIMIT 1");
$fact = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cosmic Fact | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body data-page="facts">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <div class="app-shell">

    <?php include 'includes/sidebar.php'; ?>

    <main class="main-panel">
      <button class="mobile-menu" type="button">☰ Menu</button>

      <section class="page-head">
        <div>
          <p class="eyebrow">Cosmic Fact</p>
          <h1>Random Space Fact</h1>
          <p class="muted">A random fact fetched from the space_facts MySQL table.</p>
        </div>
      </section>

      <section class="glass panel" style="text-align:center; padding: 40px;">
        <p class="eyebrow"><?php echo htmlspecialchars($fact['category']); ?></p>
        <h2 style="color:#fff; font-size:1.4rem; margin: 20px 0;">
          <?php echo htmlspecialchars($fact['fact_text']); ?>
        </h2>
        <p class="muted">Source: <?php echo htmlspecialchars($fact['source_note']); ?></p>

        <br>
        <a class="btn primary" href="fact.php">🔄 Show Another Fact</a>
        &nbsp;
        <a class="btn ghost" href="dashboard.php">← Back to Dashboard</a>
      </section>

    </main>
  </div>

  <script src="assets/js/app.js"></script>
</body>
</html>