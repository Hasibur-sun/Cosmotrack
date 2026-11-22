<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "includes/db.php";

$user_id = $_SESSION['user_id'];

$sql = "SELECT 
    reminders.reminder_id,
    users.name,
    public_events.event_title,
    public_events.event_date,
    reminders.reminder_date,
    reminders.reminder_time,
    reminders.reminder_method,
    reminders.status
FROM reminders
JOIN users ON reminders.user_id = users.user_id
JOIN public_events ON reminders.event_id = public_events.event_id
WHERE reminders.user_id = $user_id
ORDER BY reminders.reminder_date ASC";

$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>My Reminders | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body data-page="reminders">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <div class="app-shell">

    <?php include 'includes/sidebar.php'; ?>

    <main class="main-panel">
      <button class="mobile-menu" type="button">☰ Menu</button>

      <section class="page-head">
        <div>
          <p class="eyebrow">Reminder System</p>
          <h1>Saved Event Reminders</h1>
          <p class="muted">Reminders fetched from MySQL using JOIN on users and public_events tables.</p>
        </div>
      </section>

      <section class="glass panel">
        <div class="panel-head">
          <h2>My Reminders</h2>
          <a class="btn ghost" href="events.php">+ Add Reminder</a>
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>ID</th>
                <th>User</th>
                <th>Event</th>
                <th>Event Date</th>
                <th>Reminder Date</th>
                <th>Time</th>
                <th>Method</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>

            <tbody>
              <?php if (mysqli_num_rows($result) == 0): ?>
                <tr>
                  <td colspan="9" style="text-align:center; color:#aaa;">
                    No reminders saved yet. Go to
                    <a href="events.php" style="color:#4fc3f7;">Astronomy Events</a>
                    and click Remind Me.
                  </td>
                </tr>
              <?php endif; ?>

              <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                  <td><?php echo $row['reminder_id']; ?></td>
                  <td><?php echo htmlspecialchars($row['name']); ?></td>
                  <td><?php echo htmlspecialchars($row['event_title']); ?></td>
                  <td><?php echo $row['event_date']; ?></td>
                  <td><?php echo $row['reminder_date']; ?></td>
                  <td><?php echo $row['reminder_time'] ?: '—'; ?></td>
                  <td><?php echo $row['reminder_method']; ?></td>
                  <td><?php echo $row['status']; ?></td>
                  <td>
                    <a class="danger"
                       href="actions/delete_reminder.php?id=<?php echo $row['reminder_id']; ?>"
                       onclick="return confirm('Delete this reminder?')">
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