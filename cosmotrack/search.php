<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "includes/db.php";

$user_id = $_SESSION['user_id'];

// Get filter values from URL
$search      = $_GET['search']      ?? '';
$object_type = $_GET['object_type'] ?? '';
$sky         = $_GET['sky']         ?? '';
$visibility  = $_GET['visibility']  ?? '';
$date_from   = $_GET['date_from']   ?? '';
$date_to     = $_GET['date_to']     ?? '';

// Build dynamic WHERE clause
$where = "WHERE observations.user_id = $user_id";

if (!empty($search)) {
    $safe = mysqli_real_escape_string($conn, $search);
    $where .= " AND (observations.object_name LIKE '%$safe%'
                OR observations.notes LIKE '%$safe%'
                OR locations.location_name LIKE '%$safe%')";
}

if (!empty($object_type)) {
    $safe = mysqli_real_escape_string($conn, $object_type);
    $where .= " AND observations.object_type = '$safe'";
}

if (!empty($sky)) {
    $safe = mysqli_real_escape_string($conn, $sky);
    $where .= " AND observations.sky_condition = '$safe'";
}

if (!empty($visibility)) {
    $safe = mysqli_real_escape_string($conn, $visibility);
    $where .= " AND observations.visibility = '$safe'";
}

if (!empty($date_from) && !empty($date_to)) {
    $where .= " AND observations.observation_date
                BETWEEN '$date_from' AND '$date_to'";
} elseif (!empty($date_from)) {
    $where .= " AND observations.observation_date >= '$date_from'";
} elseif (!empty($date_to)) {
    $where .= " AND observations.observation_date <= '$date_to'";
}

// Final query
$sql = "SELECT
            observations.observation_id,
            observations.object_name,
            observations.object_type,
            observations.observation_date,
            observations.observation_time,
            observations.sky_condition,
            observations.visibility,
            observations.quality_score,
            observations.notes,
            locations.location_name,
            telescopes.telescope_name
        FROM observations
        LEFT JOIN locations  ON observations.location_id  = locations.location_id
        LEFT JOIN telescopes ON observations.telescope_id = telescopes.telescope_id
        $where
        ORDER BY observations.observation_date DESC";

$result = mysqli_query($conn, $sql);
if (!$result) die("Query error: " . mysqli_error($conn));
$total = mysqli_num_rows($result);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Search Observations | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body data-page="search">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-panel">
      <button class="mobile-menu" type="button">☰ Menu</button>

      <section class="page-head">
        <div>
          <p class="eyebrow">Search Module</p>
          <h1>Search Observations</h1>
          <p class="muted">
            Filter using SQL WHERE, LIKE, BETWEEN, AND conditions.
          </p>
        </div>
      </section>

      <!-- SEARCH FORM -->
      <section class="glass panel" style="margin-bottom:18px;">
        <h2 style="margin-bottom:16px;">🔍 Filter Observations</h2>

        <form method="GET" action="search.php">
          <div class="form-grid">

            <!-- Keyword search -->
            <label style="grid-column:1/-1">Search Keyword
              <input type="text" name="search"
                     value="<?php echo htmlspecialchars($search); ?>"
                     placeholder="Search by object name, notes, location...">
            </label>

            <!-- Object Type -->
            <label>Object Type
              <select name="object_type">
                <option value="">-- All Types --</option>
                <?php
                  $types = ['Planet','Moon','Star','Galaxy','Nebula',
                            'Comet','Asteroid','Meteor','Cluster','Other'];
                  foreach ($types as $t) {
                    $sel = $object_type == $t ? 'selected' : '';
                    echo "<option value='$t' $sel>$t</option>";
                  }
                ?>
              </select>
            </label>

            <!-- Sky Condition -->
            <label>Sky Condition
              <select name="sky">
                <option value="">-- All Conditions --</option>
                <?php
                  $skies = ['Clear','Partly Cloudy','Hazy','Cloudy','Windy'];
                  foreach ($skies as $s) {
                    $sel = $sky == $s ? 'selected' : '';
                    echo "<option value='$s' $sel>$s</option>";
                  }
                ?>
              </select>
            </label>

            <!-- Visibility -->
            <label>Visibility
              <select name="visibility">
                <option value="">-- All --</option>
                <option value="Public"  <?php echo $visibility == 'Public'  ? 'selected' : ''; ?>>Public</option>
                <option value="Private" <?php echo $visibility == 'Private' ? 'selected' : ''; ?>>Private</option>
              </select>
            </label>

            <!-- Date From -->
            <label>Date From
              <input type="date" name="date_from"
                     value="<?php echo $date_from; ?>">
            </label>

            <!-- Date To -->
            <label>Date To
              <input type="date" name="date_to"
                     value="<?php echo $date_to; ?>">
            </label>

            <!-- Buttons -->
            <div class="form-actions">
              <button class="btn primary" type="submit">
                🔍 Search
              </button>
              <a class="btn ghost" href="search.php">
                ✕ Clear Filters
              </a>
            </div>

          </div>
        </form>
      </section>

      <!-- RESULTS -->
      <section class="glass panel">
        <div class="panel-head">
          <h2>Search Results
            <span class="badge <?php echo $total > 0 ? 'badge-cyan' : 'badge-red'; ?>">
              <?php echo $total; ?> found
            </span>
          </h2>

          <?php if (!empty($search) || !empty($object_type) ||
                    !empty($sky) || !empty($date_from)): ?>
            <p class="muted" style="font-size:12px;">
              Filters active —
              <?php if ($search)      echo "keyword: <strong style='color:var(--cyan)'>$search</strong> "; ?>
              <?php if ($object_type) echo "type: <strong style='color:var(--cyan)'>$object_type</strong> "; ?>
              <?php if ($sky)         echo "sky: <strong style='color:var(--cyan)'>$sky</strong> "; ?>
              <?php if ($date_from)   echo "from: <strong style='color:var(--cyan)'>$date_from</strong> "; ?>
              <?php if ($date_to)     echo "to: <strong style='color:var(--cyan)'>$date_to</strong>"; ?>
            </p>
          <?php endif; ?>
        </div>

        

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Object</th>
                <th>Type</th>
                <th>Date</th>
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
              <?php if ($total == 0): ?>
                <tr>
                  <td colspan="11"
                      style="text-align:center; color:var(--muted); padding:30px;">
                    No observations found matching your filters.
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

                  <td><?php echo $row['location_name']
                        ? htmlspecialchars($row['location_name'])
                        : '<span style="color:var(--muted)">—</span>'; ?></td>

                  <td><?php echo $row['telescope_name']
                        ? htmlspecialchars($row['telescope_name'])
                        : '<span style="color:var(--muted)">—</span>'; ?></td>

                  <td><?php echo htmlspecialchars($row['sky_condition'] ?: '—'); ?></td>

                  <td>
                    <span class="<?php echo $row['visibility'] == 'Public'
                                          ? 'vis-public' : 'vis-private'; ?>">
                      <?php echo $row['visibility']; ?>
                    </span>
                  </td>

                  <td>
                    <?php
                      $q   = (int)$row['quality_score'];
                      $cls = $q >= 70 ? 'q-high' : ($q >= 40 ? 'q-mid' : 'q-low');
                    ?>
                    <span class="quality-badge <?php echo $cls; ?>">
                      <?php echo $q; ?>
                    </span>
                  </td>

                  <td style="max-width:140px; font-size:12px; color:var(--muted);">
                    <?php echo htmlspecialchars($row['notes'] ?: '—'); ?>
                  </td>

                  <td>
                    <a class="edit-link"
                       href="edit_observation.php?id=<?php echo $row['observation_id']; ?>">
                      Edit
                    </a>
                    <a class="danger"
                       href="actions/delete_observation.php?id=<?php echo $row['observation_id']; ?>"
                       onclick="return confirm('Delete?')">
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