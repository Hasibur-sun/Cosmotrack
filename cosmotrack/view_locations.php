<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "includes/db.php";

$result = mysqli_query($conn, "SELECT * FROM locations ORDER BY location_id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>View Locations | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-page="locations">

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
        <h1>Observation Locations</h1>
        <p class="muted">Locations stored in MySQL. Select one when adding an observation.</p>
      </div>
      <a class="btn primary" href="locations.php">+ Add New Location</a>
    </section>

    <section class="glass panel">
      <div class="panel-head">
        <h2>Saved Locations
          <span class="badge badge-cyan"><?php echo mysqli_num_rows($result); ?> records</span>
        </h2>
      </div>

      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>ID</th>
              <th>Location Name</th>
              <th>City</th>
              <th>Country</th>
              <th>Latitude</th>
              <th>Longitude</th>
              <th>Bortle Scale</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (mysqli_num_rows($result) == 0): ?>
              <tr>
                <td colspan="8" style="text-align:center; color:var(--muted); padding:30px;">
                  No locations yet.
                  <a href="locations.php" style="color:var(--cyan);">Add your first one →</a>
                </td>
              </tr>
            <?php endif; ?>

            <?php while ($row = mysqli_fetch_assoc($result)): ?>
              <tr>
                <td><?php echo $row['location_id']; ?></td>
                <td><strong style="color:var(--text);">
                  <?php echo htmlspecialchars($row['location_name']); ?>
                </strong></td>
                <td><?php echo htmlspecialchars($row['city'] ?: '—'); ?></td>
                <td><?php echo htmlspecialchars($row['country'] ?: '—'); ?></td>
                <td><?php echo $row['latitude'] ?: '—'; ?></td>
                <td><?php echo $row['longitude'] ?: '—'; ?></td>
                <td>
                  <?php if ($row['bortle_scale']): ?>
                    <span class="badge badge-cyan">Bortle <?php echo $row['bortle_scale']; ?></span>
                  <?php else: ?>
                    <span style="color:var(--muted)">—</span>
                  <?php endif; ?>
                </td>
                <td>
                  <a class="edit-link"
                     href="edit_location.php?id=<?php echo $row['location_id']; ?>">
                    Edit
                  </a>
                  <a class="danger"
                     href="actions/delete_location.php?id=<?php echo $row['location_id']; ?>"
                     onclick="return confirm('Delete this location?')">
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