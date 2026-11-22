<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "includes/db.php";

$user_id = $_SESSION['user_id'];

// Only show this user's own telescopes
$result = mysqli_query($conn,
    "SELECT * FROM telescopes
     WHERE user_id = $user_id
     ORDER BY telescope_id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Telescopes | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-page="telescopes">

<div class="stars"></div>
<div class="nebula nebula-one"></div>
<div class="nebula nebula-two"></div>

<div class="app-shell">

  <?php include 'includes/sidebar.php'; ?>

  <main class="main-panel">
    <button class="mobile-menu" type="button">☰ Menu</button>

    <section class="page-head">
      <div>
        <p class="eyebrow">Database Records</p>
        <h1>My Telescopes</h1>
        <p class="muted">Your personal telescope records stored in MySQL.</p>
      </div>
      <a class="btn primary" href="telescopes.php">+ Add New Telescope</a>
    </section>

    <section class="glass panel">
      <div class="panel-head">
        <h2>Saved Telescopes
          <span class="badge badge-cyan"><?php echo mysqli_num_rows($result); ?> records</span>
        </h2>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Telescope Name</th>
              <th>Type</th>
              <th>Aperture</th>
              <th>Focal Length</th>
              <th>Mount Type</th>
              <th>Description</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>

            <?php if (mysqli_num_rows($result) == 0): ?>
              <tr>
                <td colspan="8" style="text-align:center; color:var(--muted); padding:30px;">
                  No telescopes yet.
                  <a href="telescopes.php" style="color:var(--cyan);">Add your first one →</a>
                </td>
              </tr>
            <?php endif; ?>

            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?php echo $row['telescope_id']; ?></td>

                <td><strong style="color:var(--text);">
                  <?php echo htmlspecialchars($row['telescope_name']); ?>
                </strong></td>

                <td>
                  <span class="badge badge-violet">
                    <?php echo htmlspecialchars($row['telescope_type'] ?: '—'); ?>
                  </span>
                </td>

                <td><?php echo htmlspecialchars($row['aperture'] ?: '—'); ?></td>
                <td><?php echo htmlspecialchars($row['focal_length'] ?: '—'); ?></td>
                <td><?php echo htmlspecialchars($row['mount_type'] ?: '—'); ?></td>

                <td style="max-width:160px; word-wrap:break-word; font-size:12px; color:var(--muted);">
                  <?php echo htmlspecialchars($row['description'] ?: '—'); ?>
                </td>

                <td>
                  <a class="edit-link"
                     href="edit_telescope.php?id=<?php echo $row['telescope_id']; ?>">
                    Edit
                  </a>
                  <a class="danger"
                     href="actions/delete_telescope.php?id=<?php echo $row['telescope_id']; ?>"
                     onclick="return confirm('Delete this telescope?')">
                    Delete
                  </a>
                </td>
              </tr>
            <?php endwhile; ?>

          </tbody>
        </table>
      </div>
    </section>

  </main>
</div>

<script src="assets/js/app.js"></script>
</body>
</html>