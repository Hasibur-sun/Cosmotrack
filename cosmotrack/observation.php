<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "includes/db.php";

$user_id = $_SESSION['user_id'];

// All locations are public/shared
$locations = mysqli_query($conn,
    "SELECT location_id, location_name, city FROM locations ORDER BY location_name ASC"
);

// Only show THIS user's own telescopes
$telescopes = mysqli_query($conn,
    "SELECT telescope_id, telescope_name, telescope_type
     FROM telescopes
     WHERE user_id = $user_id
     ORDER BY telescope_name ASC"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Observation Records | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css" />
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
          <p class="eyebrow">Observation Module</p>
          <h1>Observation Records</h1>
          <p class="muted">
            Add astronomy observation records to the CosmoTrack MySQL database.
          </p>
        </div>
      </section>

      <section class="glass panel">
        <h2>Add New Observation</h2>

        <p class="muted">
          Before adding an observation, check your Location ID and Telescope ID from the view pages.
        </p>

        <div class="actions-row">
          <a class="btn tiny ghost" href="view_locations.php">View Location IDs</a>
          <a class="btn tiny ghost" href="view_telescopes.php">View Telescope IDs</a>
        </div>

        <form class="form-grid" method="POST" action="actions/add_observation.php">
          <label>
            Object Name
            <input 
              type="text" 
              name="object_name" 
              placeholder="Mars" 
              required
            />
          </label>

          <label>
            Object Type
            <select name="object_type" required>
              <option value="">Select Object Type</option>
              <option value="Planet">Planet</option>
              <option value="Star">Star</option>
              <option value="Galaxy">Galaxy</option>
              <option value="Nebula">Nebula</option>
              <option value="Moon">Moon</option>
              <option value="Comet">Comet</option>
              <option value="Other">Other</option>
            </select>
          </label>

          <label>
            Observation Date
            <input 
              type="date" 
              name="observation_date" 
              required
            />
          </label>

          <label>
            Observation Time
            <input 
              type="time" 
              name="observation_time" 
            />
          </label>

          <label>
  Observation Location
  <select name="location_id" required>
    <option value="">Select Location</option>

    <?php while ($loc = mysqli_fetch_assoc($locations)) { ?>
      <option value="<?php echo $loc['location_id']; ?>">
        <?php echo $loc['location_name']; ?> - <?php echo $loc['city']; ?>
      </option>
    <?php } ?>

  </select>
</label>

          <label>
  Telescope Used
  <select name="telescope_id" required>
    <option value="">Select Telescope</option>

    <?php while ($tel = mysqli_fetch_assoc($telescopes)) { ?>
      <option value="<?php echo $tel['telescope_id']; ?>">
        <?php echo $tel['telescope_name']; ?> - <?php echo $tel['telescope_type']; ?>
      </option>
    <?php } ?>

  </select>
</label>
<label>
  Captured Latitude
  <input type="text" name="captured_latitude" id="captured_latitude" placeholder="Click Use Current Location" readonly />
</label>

<label>
  Captured Longitude
  <input type="text" name="captured_longitude" id="captured_longitude" placeholder="Click Use Current Location" readonly />
</label>

<div class="form-actions">
  <button class="btn ghost" type="button" onclick="getCurrentLocation()">Use My Current Location</button>
</div>
<label>
  Visibility
  <select name="visibility">
    <option value="Public">Public</option>
    <option value="Private">Private</option>
  </select>
</label>

          <label>
            Sky Condition
            <select name="sky_condition" required>
              <option value="">Select Sky Condition</option>
              <option value="Clear">Clear</option>
              <option value="Partly Cloudy">Partly Cloudy</option>
              <option value="Cloudy">Cloudy</option>
              <option value="Hazy">Hazy</option>
            </select>
          </label>

          <label>
            Notes
            <textarea 
              name="notes" 
              placeholder="Write observation notes..."
            ></textarea>
          </label>

          <div class="form-actions">
            <button class="btn primary" type="submit">Save Observation</button>
            <button class="btn ghost" type="reset">Reset</button>
          </div>
        </form>
      </section>

      <section class="glass panel">
        <div class="panel-head">
          <h2>Saved Observation Records</h2>
          <a class="btn primary" href="view_observations.php">View Database Records</a>
        </div>

        <p class="muted">
          After saving an observation, open the database records page to view all submitted observations.
        </p>
      </section>
    </main>
  </div>

  <script src="assets/js/app.js"></script>
  <script>
function getCurrentLocation() {
  if (!navigator.geolocation) {
    alert("Geolocation is not supported by this browser.");
    return;
  }

  navigator.geolocation.getCurrentPosition(
    function(position) {
      document.getElementById("captured_latitude").value = position.coords.latitude;
      document.getElementById("captured_longitude").value = position.coords.longitude;
    },
    function(error) {
      alert("Unable to capture location. Please allow location permission.");
    }
  );
}
</script>
</body>
</html>