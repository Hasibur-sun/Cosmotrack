<aside class="sidebar">
  <a class="brand mini" href="dashboard.php">
    <span class="planet-dot"></span>
    <span>CosmoTrack</span>
  </a>

  <nav>
    <span class="nav-label">Main</span>
    <a href="dashboard.php">⌂ Dashboard</a>

    <span class="nav-label">Observations</span>
    <a href="observation.php">+ Add Observation</a>
    <a href="view_observations.php">≡ View Observations</a>
    <a href="stats.php">◈ My Statistics</a>
    <a href="search.php">⌕ Search Observations</a>

    <span class="nav-label">Equipment</span>
    <a href="locations.php">◎ Add Location</a>
    <a href="view_locations.php">◉ View Locations</a>
    <a href="telescopes.php">⌖ Add Telescope</a>
    <a href="view_telescopes.php">◈ View Telescopes</a>

    <span class="nav-label">Explore</span>
    <a href="events.php">◆ Astronomy Events</a>
    <a href="reminders.php">◷ My Reminders</a>
    <a href="fact.php">✦ Cosmic Fact</a>
    <a href="movies.php">🎬 Space Movies</a>
    <a href="reports.php">◫ SQL Reports</a>
    <a href="lightpollution.php">🌃 Light Pollution</a>
    <a href="satellite.php">◉ Satellite Tracker</a>
    <a href="timedilation.php">⏱ Time Dilation</a>
    <a href="exoplanets.php">🌍 Exoplanet Explorer</a>

    <span class="nav-label">Account</span>
    <a href="profile.php">◑ My Profile</a>
    <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'Admin'): ?>
  <a href="admin.php">⚙ Admin Panel</a>
<?php endif; ?>

  </nav>

  <a class="logout" href="logout.php">→ Logout</a>
</aside>