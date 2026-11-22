<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "includes/db.php";

$user_id = $_SESSION['user_id'];

$bookmarks = mysqli_query($conn,
    "SELECT exoplanets.*, exoplanet_bookmarks.created_at AS bookmarked_at
     FROM exoplanet_bookmarks
     JOIN exoplanets ON exoplanet_bookmarks.planet_id = exoplanets.planet_id
     WHERE exoplanet_bookmarks.user_id = $user_id
     ORDER BY exoplanet_bookmarks.created_at DESC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Bookmarks | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-page="exoplanets">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-panel">
      <button class="mobile-menu" type="button">☰ Menu</button>

      <section class="page-head">
        <div>
          <p class="eyebrow">My Collection</p>
          <h1>★ Bookmarked Exoplanets</h1>
          <p class="muted">
            Your saved exoplanets — fetched using JOIN on
            exoplanet_bookmarks and exoplanets tables.
          </p>
        </div>
        <a class="btn ghost" href="exoplanets.php">← All Exoplanets</a>
      </section>

      <section class="glass panel">
        <div class="panel-head">
          <h2>My Bookmarks
            <span class="badge badge-cyan">
              <?php echo mysqli_num_rows($bookmarks); ?> saved
            </span>
          </h2>
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Planet</th>
                <th>Host Star</th>
                <th>Habitability</th>
                <th>Distance</th>
                <th>Mass</th>
                <th>Discovery</th>
                <th>Bookmarked</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($bookmarks) == 0): ?>
                <tr>
                  <td colspan="8"
                      style="text-align:center; color:var(--muted); padding:30px;">
                    No bookmarks yet.
                    <a href="exoplanets.php" style="color:var(--cyan);">
                      Browse exoplanets →
                    </a>
                  </td>
                </tr>
              <?php endif; ?>

              <?php while ($row = mysqli_fetch_assoc($bookmarks)): ?>
                <tr>
                  <td><strong style="color:var(--text);">
                    <?php echo htmlspecialchars($row['planet_name']); ?>
                  </strong></td>
                  <td><?php echo htmlspecialchars($row['host_star']); ?></td>
                  <td>
                    <?php
                      $badge = $row['habitability_zone'] == 'Habitable Zone'
                             ? 'badge-green'
                             : ($row['habitability_zone'] == 'Too Hot'
                                ? 'badge-red' : 'badge-cyan');
                    ?>
                    <span class="badge <?php echo $badge; ?>">
                      <?php echo $row['habitability_zone']; ?>
                    </span>
                  </td>
                  <td><?php echo $row['distance_ly']; ?> ly</td>
                  <td><?php echo $row['planet_mass_earth']; ?>× Earth</td>
                  <td>
                    <span class="badge badge-violet">
                      <?php echo $row['discovery_method']; ?>
                    </span>
                  </td>
                  <td style="font-size:12px; color:var(--muted);">
                    <?php echo date('d M Y', strtotime($row['bookmarked_at'])); ?>
                  </td>
                  <td>
                    <form method="POST" action="actions/toggle_bookmark.php">
                      <input type="hidden" name="planet_id"
                             value="<?php echo $row['planet_id']; ?>">
                      <input type="hidden" name="is_bookmarked" value="1">
                      <button type="submit" class="danger"
                              onclick="return confirm('Remove bookmark?')">
                        Remove
                      </button>
                    </form>
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