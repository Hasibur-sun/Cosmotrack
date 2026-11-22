<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "includes/db.php";

$user_id = $_SESSION['user_id'];

$sql = "SELECT 
            observations.observation_id,
            observations.object_name,
            observations.object_type,
            observations.observation_date,
            observations.observation_time,
            observations.sky_condition,
            observations.notes,
            observations.captured_latitude,
            observations.captured_longitude,
            observations.visibility,
            observations.quality_score,
            locations.location_name,
            telescopes.telescope_name
        FROM observations
        LEFT JOIN locations  ON observations.location_id  = locations.location_id
        LEFT JOIN telescopes ON observations.telescope_id = telescopes.telescope_id
        WHERE observations.user_id = $user_id
        ORDER BY observations.observation_id DESC";

$result = mysqli_query($conn, $sql);
if (!$result) die("Query failed: " . mysqli_error($conn));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Observations | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body data-page="observations">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-panel">
      <button class="mobile-menu" type="button">☰ Menu</button>

      <section class="page-head">
        <div>
          <p class="eyebrow">My Records</p>
          <h1>My Observations</h1>
          <p class="muted">
            Your personal observations — fetched using
            LEFT JOIN with WHERE user_id filter.
          </p>
        </div>
        <a class="btn primary" href="observation.php">+ Add New</a>
      </section>

      <section class="glass panel">
        <div class="panel-head">
          <h2>Saved Observations
            <span class="badge badge-cyan"><?php echo mysqli_num_rows($result); ?> records</span>
          </h2>
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Object</th>
                <th>Type</th>
                <th>Date</th>
                <th>Time</th>
                <th>Location</th>
                <th>Telescope</th>
                <th>Sky</th>
                <th>Visibility</th>
                <th>Quality</th>
                <th>Notes</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($result) == 0): ?>
                <tr>
                  <td colspan="12" style="text-align:center; color:var(--muted); padding:30px;">
                    No observations yet.
                    <a href="observation.php" style="color:var(--cyan);">Add your first one →</a>
                  </td>
                </tr>
              <?php endif; ?>

              <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                  <td><?php echo $row['observation_id']; ?></td>

                  <td><strong style="color:var(--text);">
                    <?php echo htmlspecialchars($row['object_name']); ?>
                  </strong></td>

                  <td>
                    <span class="badge badge-violet">
                      <?php echo htmlspecialchars($row['object_type'] ?: '—'); ?>
                    </span>
                  </td>

                  <td><?php echo $row['observation_date'] ?: '—'; ?></td>
                  <td><?php echo $row['observation_time'] ?: '—'; ?></td>

                  <td><?php echo $row['location_name']
                        ? htmlspecialchars($row['location_name'])
                        : '<span style="color:var(--muted)">—</span>'; ?></td>

                  <td><?php echo $row['telescope_name']
                        ? htmlspecialchars($row['telescope_name'])
                        : '<span style="color:var(--muted)">—</span>'; ?></td>

                  <td><?php echo htmlspecialchars($row['sky_condition'] ?: '—'); ?></td>

                  <td>
                    <span class="<?php echo $row['visibility'] == 'Public' ? 'vis-public' : 'vis-private'; ?>">
                      <?php echo $row['visibility']; ?>
                    </span>
                  </td>

                  <td>
                    <?php
                      $q   = (int)$row['quality_score'];
                      $cls = $q >= 70 ? 'q-high' : ($q >= 40 ? 'q-mid' : 'q-low');
                    ?>
                    <span class="quality-badge <?php echo $cls; ?>"><?php echo $q; ?></span>
                  </td>

                  <td style="max-width:160px; word-wrap:break-word; font-size:12px; color:var(--muted);">
                    <?php echo htmlspecialchars($row['notes'] ?: '—'); ?>
                  </td>

                  <td>
                    <a class="edit-link"
                       href="edit_observation.php?id=<?php echo $row['observation_id']; ?>">
                      Edit
                    </a>
                    <a class="danger"
                       href="actions/delete_observation.php?id=<?php echo $row['observation_id']; ?>"
                       onclick="return confirm('Delete this observation?')">
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