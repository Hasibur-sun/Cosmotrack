<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "includes/db.php";

$user_id = $_SESSION['user_id'];

// Fetch user data
$user = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT * FROM users WHERE user_id = $user_id"
));

// Fetch user stats
$totalObs  = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM observations WHERE user_id = $user_id"))[0];
$totalTel  = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM telescopes WHERE user_id = $user_id"))[0];
$totalRem  = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM reminders WHERE user_id = $user_id"))[0];

// Most observed object
$topObject = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT object_name, COUNT(*) AS total
     FROM observations
     WHERE user_id = $user_id
     GROUP BY object_name
     ORDER BY total DESC
     LIMIT 1"
));

// Most used sky condition
$topSky = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT sky_condition, COUNT(*) AS total
     FROM observations
     WHERE user_id = $user_id
     GROUP BY sky_condition
     ORDER BY total DESC
     LIMIT 1"
));

// Success message
$msg = $_GET['msg'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Profile | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body data-page="profile">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-panel">
      <button class="mobile-menu" type="button">☰ Menu</button>

      <section class="page-head">
        <div>
          <p class="eyebrow">Account</p>
          <h1>My Profile</h1>
          <p class="muted">View and update your CosmoTrack account details.</p>
        </div>
      </section>

      <?php if ($msg == 'updated'): ?>
  <div style="background:rgba(77,255,195,.1); border:1px solid rgba(77,255,195,.3);
              border-radius:12px; padding:14px 20px; margin-bottom:18px; color:var(--green);">
    ✅ Profile updated successfully.
  </div>
<?php elseif ($msg == 'password_changed'): ?>
  <div style="background:rgba(77,255,195,.1); border:1px solid rgba(77,255,195,.3);
              border-radius:12px; padding:14px 20px; margin-bottom:18px; color:var(--green);">
    ✅ Password changed successfully.
  </div>
<?php elseif ($msg == 'wrong_password'): ?>
  <div style="background:rgba(255,95,126,.1); border:1px solid rgba(255,95,126,.3);
              border-radius:12px; padding:14px 20px; margin-bottom:18px; color:var(--danger);">
    ❌ Current password is incorrect.
  </div>
<?php elseif ($msg == 'mismatch'): ?>
  <div style="background:rgba(255,95,126,.1); border:1px solid rgba(255,95,126,.3);
              border-radius:12px; padding:14px 20px; margin-bottom:18px; color:var(--danger);">
    ❌ New passwords do not match.
  </div>
<?php elseif ($msg == 'too_short'): ?>
  <div style="background:rgba(255,200,71,.1); border:1px solid rgba(255,200,71,.3);
              border-radius:12px; padding:14px 20px; margin-bottom:18px; color:var(--gold);">
    ⚠️ Password must be at least 6 characters.
  </div>
<?php endif; ?>

      <!-- PROFILE CARD -->
      <section class="glass panel" style="margin-bottom:18px;">
        <div class="profile-card">
          <div class="avatar">
            <?php echo strtoupper(substr($user['name'], 0, 1)); ?>
          </div>
          <div>
            <h2 style="font-size:22px; margin-bottom:4px;">
              <?php echo htmlspecialchars($user['name']); ?>
            </h2>
            <p class="muted" style="margin-bottom:6px;">
              <?php echo htmlspecialchars($user['email']); ?>
            </p>
            <span class="badge <?php echo $user['role'] == 'Admin' ? 'badge-gold' : 'badge-cyan'; ?>">
              <?php echo $user['role']; ?>
            </span>
            <p class="muted" style="font-size:12px; margin-top:8px;">
              Member since <?php echo date('F Y', strtotime($user['created_at'])); ?>
            </p>
          </div>
        </div>
      </section>

      <!-- STATS ROW -->
      <div class="stats-grid" style="grid-template-columns:repeat(4,1fr); margin-bottom:18px;">
        <div class="stat glass">
          <span>My Observations</span>
          <strong><?php echo $totalObs; ?></strong>
        </div>
        <div class="stat glass">
          <span>My Telescopes</span>
          <strong><?php echo $totalTel; ?></strong>
        </div>
        <div class="stat glass">
          <span>My Reminders</span>
          <strong><?php echo $totalRem; ?></strong>
        </div>
        <div class="stat glass">
          <span>Top Object</span>
          <strong style="font-size:16px; margin-top:8px;">
            <?php echo $topObject ? htmlspecialchars($topObject['object_name']) : '—'; ?>
          </strong>
        </div>
      </div>

      <div class="dashboard-grid">

        <!-- EDIT PROFILE FORM -->
        <section class="glass panel">
          <h2>✏️ Edit Profile</h2>
          <form method="POST" action="actions/update_profile.php"
                style="display:grid; gap:16px;">

            <label>Full Name
              <input type="text" name="name" required
                     value="<?php echo htmlspecialchars($user['name']); ?>">
            </label>

            <label>Phone Number
              <input type="text" name="phone"
                     value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>"
                     placeholder="e.g. +880 1234 567890">
            </label>

            <label>Email Address
              <input type="email" value="<?php echo htmlspecialchars($user['email']); ?>"
                     disabled
                     style="opacity:.5; cursor:not-allowed;">
              <small style="color:var(--muted); font-size:11px; font-weight:400;">
                Email cannot be changed.
              </small>
            </label>

            <button class="btn primary" type="submit">💾 Save Changes</button>
          </form>
        </section>

        <!-- CHANGE PASSWORD FORM -->
        <section class="glass panel">
          <h2>🔒 Change Password</h2>
          <form method="POST" action="actions/change_password.php"
                style="display:grid; gap:16px;">

            <label>Current Password
              <input type="password" name="current_password"
                     placeholder="Enter current password">
            </label>

            <label>New Password
              <input type="password" name="new_password"
                     placeholder="Enter new password">
            </label>

            <label>Confirm New Password
              <input type="password" name="confirm_password"
                     placeholder="Repeat new password">
            </label>

            <button class="btn ghost" type="submit">🔑 Update Password</button>
          </form>
        </section>

        <!-- OBSERVATION INSIGHTS -->
        <section class="glass panel wide">
          <h2>📊 My Observation Insights</h2>
          <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-top:8px;">

            <div class="mini-card">
              <p class="eyebrow">Most Observed</p>
              <p style="font-size:1.2rem; font-weight:700; margin-top:8px; color:var(--text);">
                <?php echo $topObject ? htmlspecialchars($topObject['object_name']) : 'No data yet'; ?>
              </p>
              <?php if ($topObject): ?>
                <p class="muted" style="font-size:12px;">
                  <?php echo $topObject['total']; ?> times observed
                </p>
              <?php endif; ?>
            </div>

            <div class="mini-card">
              <p class="eyebrow">Favourite Sky</p>
              <p style="font-size:1.2rem; font-weight:700; margin-top:8px; color:var(--text);">
                <?php echo $topSky ? htmlspecialchars($topSky['sky_condition']) : 'No data yet'; ?>
              </p>
              <?php if ($topSky): ?>
                <p class="muted" style="font-size:12px;">
                  <?php echo $topSky['total']; ?> observations
                </p>
              <?php endif; ?>
            </div>

            <div class="mini-card">
              <p class="eyebrow">Account Role</p>
              <p style="font-size:1.2rem; font-weight:700; margin-top:8px; color:var(--cyan);">
                <?php echo $user['role']; ?>
              </p>
              <p class="muted" style="font-size:12px;">
                <?php echo $user['role'] == 'Admin' ? 'Full access' : 'Standard access'; ?>
              </p>
            </div>

          </div>
        </section>

      </div>
    </main>
  </div>
  <script src="assets/js/app.js"></script>
</body>
</html>