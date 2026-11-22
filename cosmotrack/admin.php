<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
// Only Admin can access this page
if ($_SESSION['user_role'] !== 'Admin') {
    header("Location: dashboard.php?error=unauthorized");
    exit();
}
include "includes/db.php";

// System wide stats
$totalUsers  = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM users"))[0];
$totalObs    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM observations"))[0];
$totalLoc    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM locations"))[0];
$totalTel    = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM telescopes"))[0];
$totalEvents = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM public_events"))[0];
$totalPasses = mysqli_fetch_row(mysqli_query($conn, "SELECT COUNT(*) FROM satellite_passes"))[0];

// All users with observation count
$users = mysqli_query($conn,
    "SELECT users.user_id, users.name, users.email,
            users.role, users.created_at,
            COUNT(observations.observation_id) AS total_obs
     FROM users
     LEFT JOIN observations ON users.user_id = observations.user_id
     GROUP BY users.user_id
     ORDER BY users.created_at DESC"
);

// All observations from all users
$allObs = mysqli_query($conn,
    "SELECT observations.observation_id, observations.object_name,
            observations.object_type, observations.observation_date,
            observations.visibility, observations.quality_score,
            users.name AS username,
            locations.location_name
     FROM observations
     LEFT JOIN users ON observations.user_id = users.user_id
     LEFT JOIN locations ON observations.location_id = locations.location_id
     ORDER BY observations.observation_id DESC
     LIMIT 20"
);

// Observations per user report
$obsPerUser = mysqli_query($conn,
    "SELECT users.name, COUNT(observations.observation_id) AS total
     FROM users
     LEFT JOIN observations ON users.user_id = observations.user_id
     GROUP BY users.user_id
     ORDER BY total DESC"
);

$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Panel | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body data-page="admin">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-panel">
      <button class="mobile-menu" type="button">☰ Menu</button>

      <section class="page-head">
        <div>
          <p class="eyebrow">Admin Panel</p>
          <h1>⚙️ System Management</h1>
          <p class="muted">
            Full system access — visible to Admin role only.
          </p>
        </div>
        <span class="badge badge-gold" style="padding:8px 16px; font-size:13px;">
          Admin Mode
        </span>
      </section>

      <?php if ($msg == 'deleted'): ?>
        <div style="background:rgba(77,255,195,.1); border:1px solid rgba(77,255,195,.3);
                    border-radius:12px; padding:14px 20px; margin-bottom:18px; color:var(--green);">
          ✅ User deleted successfully.
        </div>
      <?php elseif ($msg == 'role_updated'): ?>
        <div style="background:rgba(77,255,195,.1); border:1px solid rgba(77,255,195,.3);
                    border-radius:12px; padding:14px 20px; margin-bottom:18px; color:var(--green);">
          ✅ User role updated successfully.
        </div>
      <?php endif; ?>

      <!-- SYSTEM STATS -->
      <div class="stats-grid" style="margin-bottom:20px;">
        <div class="stat glass">
          <span>Total Users</span>
          <strong><?php echo $totalUsers; ?></strong>
        </div>
        <div class="stat glass">
          <span>Total Observations</span>
          <strong><?php echo $totalObs; ?></strong>
        </div>
        <div class="stat glass">
          <span>Total Locations</span>
          <strong><?php echo $totalLoc; ?></strong>
        </div>
        <div class="stat glass">
          <span>Total Telescopes</span>
          <strong><?php echo $totalTel; ?></strong>
        </div>
        <div class="stat glass">
          <span>Public Events</span>
          <strong><?php echo $totalEvents; ?></strong>
        </div>
        <div class="stat glass">
          <span>Satellite Passes</span>
          <strong><?php echo $totalPasses; ?></strong>
        </div>
      </div>

      <div class="dashboard-grid" style="margin-bottom:20px;">

        <!-- Observations per user report -->
        <section class="glass panel">
          <h2>📊 Observations Per User</h2>
          <p class="muted" style="font-size:12px; margin-bottom:14px;">
            SELECT users.name, COUNT(*) FROM observations
            LEFT JOIN users GROUP BY users.user_id
          </p>
          <table>
            <thead>
              <tr>
                <th>User</th>
                <th>Observations</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = mysqli_fetch_assoc($obsPerUser)): ?>
                <tr>
                  <td><?php echo htmlspecialchars($row['name']); ?></td>
                  <td>
                    <span class="badge badge-cyan">
                      <?php echo $row['total']; ?>
                    </span>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </section>

        <!-- Quick admin actions -->
        <section class="glass panel">
          <h2>⚡ Quick Actions</h2>
          <div style="display:grid; gap:10px; margin-top:8px;">
            <a class="btn ghost" href="events.php"
               style="border-color:rgba(93,232,255,.3);">
              🌠 Manage Astronomy Events
            </a>
            <a class="btn ghost" href="view_locations.php"
               style="border-color:rgba(139,111,255,.3);">
              📍 Manage All Locations
            </a>
            <a class="btn ghost" href="reports.php"
               style="border-color:rgba(255,200,71,.3);">
              📊 View SQL Reports
            </a>
            <a class="btn ghost" href="view_observations.php"
               style="border-color:rgba(77,255,195,.3);">
              📋 My Observations
            </a>
          </div>
        </section>

      </div>

      <!-- ALL USERS TABLE -->
      <section class="glass panel" style="margin-bottom:20px;">
        <div class="panel-head">
          <h2>👥 All Users
            <span class="badge badge-gold"><?php echo $totalUsers; ?> users</span>
          </h2>
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Role</th>
                <th>Observations</th>
                <th>Joined</th>
                <th>Change Role</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = mysqli_fetch_assoc($users)): ?>
                <tr>
                  <td><?php echo $row['user_id']; ?></td>

                  <td><strong style="color:var(--text);">
                    <?php echo htmlspecialchars($row['name']); ?>
                  </strong></td>

                  <td style="color:var(--muted); font-size:13px;">
                    <?php echo htmlspecialchars($row['email']); ?>
                  </td>

                  <td>
                    <span class="badge <?php echo $row['role'] == 'Admin'
                                              ? 'badge-gold' : 'badge-cyan'; ?>">
                      <?php echo $row['role']; ?>
                    </span>
                  </td>

                  <td>
                    <span class="badge badge-violet">
                      <?php echo $row['total_obs']; ?>
                    </span>
                  </td>

                  <td style="font-size:12px; color:var(--muted);">
                    <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                  </td>

                  <td>
                    <?php if ($row['user_id'] != $_SESSION['user_id']): ?>
                      <form method="POST"
                            action="actions/update_user_role.php"
                            style="display:flex; gap:6px;">
                        <input type="hidden" name="user_id"
                               value="<?php echo $row['user_id']; ?>">
                        <select name="role"
                                style="padding:5px 8px; font-size:12px;
                                       border-radius:8px; width:auto;">
                          <option value="User"
                            <?php echo $row['role'] == 'User' ? 'selected' : ''; ?>>
                            User
                          </option>
                          <option value="Admin"
                            <?php echo $row['role'] == 'Admin' ? 'selected' : ''; ?>>
                            Admin
                          </option>
                        </select>
                        <button class="btn tiny ghost" type="submit">
                          Save
                        </button>
                      </form>
                    <?php else: ?>
                      <span style="color:var(--muted); font-size:12px;">
                        (You)
                      </span>
                    <?php endif; ?>
                  </td>

                  <td>
                    <?php if ($row['user_id'] != $_SESSION['user_id']): ?>
                      <a class="danger"
                         href="actions/delete_user.php?id=<?php echo $row['user_id']; ?>"
                         onclick="return confirm('Delete this user and all their data?')">
                        Delete
                      </a>
                    <?php else: ?>
                      <span style="color:var(--muted); font-size:12px;">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </section>

      <!-- RECENT OBSERVATIONS FROM ALL USERS -->
      <section class="glass panel">
        <div class="panel-head">
          <h2>📋 Recent Observations (All Users)</h2>
          <span style="color:var(--muted); font-size:13px;">Last 20 records</span>
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>User</th>
                <th>Object</th>
                <th>Type</th>
                <th>Date</th>
                <th>Location</th>
                <th>Visibility</th>
                <th>Quality</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = mysqli_fetch_assoc($allObs)): ?>
                <tr>
                  <td><?php echo $row['observation_id']; ?></td>

                  <td>
                    <span class="badge badge-gold">
                      <?php echo htmlspecialchars($row['username'] ?? 'Unknown'); ?>
                    </span>
                  </td>

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