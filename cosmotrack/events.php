<?php
include "includes/db.php";

$events = mysqli_query(
    $conn,
    "SELECT * FROM public_events 
     WHERE event_date >= CURDATE()
     ORDER BY event_date ASC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Upcoming Astronomy Events | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>

<div class="app-shell">
<?php include 'includes/sidebar.php'; ?>

  <main class="main-panel">

    <section class="page-head">
      <div>
        <p class="eyebrow">Public Event System</p>
        <h1>Upcoming Astronomy Events</h1>
      </div>
    </section>

    <section class="glass panel">

      <div class="table-wrap">
        <table>

          <thead>
            <tr>
              <th>ID</th>
              <th>Event</th>
              <th>Type</th>
              <th>Date</th>
              <th>Time</th>
              <th>Region</th>
              <th>Description</th>
              <th>Reminder</th>
            </tr>
          </thead>

          <tbody>

          <?php while($event = mysqli_fetch_assoc($events)) { ?>

            <tr>

              <td><?php echo $event['event_id']; ?></td>

              <td><?php echo $event['event_title']; ?></td>

              <td><?php echo $event['event_type']; ?></td>

              <td><?php echo $event['event_date']; ?></td>

              <td><?php echo $event['event_time']; ?></td>

              <td><?php echo $event['visibility_region']; ?></td>

              <td><?php echo $event['description']; ?></td>

              <td>
                <a class="btn tiny primary"
                   href="add_reminder.php?event_id=<?php echo $event['event_id']; ?>">
                   Remind Me
                </a>
              </td>

            </tr>

          <?php } ?>

          </tbody>

        </table>
      </div>

    </section>

  </main>

</div>

</body>
</html>