<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "includes/db.php";

$user_id = $_SESSION['user_id'];

// Fetch saved calculations
$saved = mysqli_query($conn,
    "SELECT * FROM dilation_calculations
     WHERE user_id = $user_id
     ORDER BY created_at DESC"
);

// Stats
$totalCalcs = mysqli_fetch_row(mysqli_query($conn,
    "SELECT COUNT(*) FROM dilation_calculations
     WHERE user_id = $user_id"))[0];

$avgDilation = mysqli_fetch_row(mysqli_query($conn,
    "SELECT ROUND(AVG(dilation_factor), 4)
     FROM dilation_calculations
     WHERE user_id = $user_id"))[0];

// By type GROUP BY
$byType = mysqli_query($conn,
    "SELECT calc_type, COUNT(*) AS total,
            ROUND(AVG(dilation_factor), 4) AS avg_factor
     FROM dilation_calculations
     WHERE user_id = $user_id
     GROUP BY calc_type"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Time Dilation Calculator | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .calc-card {
      background: linear-gradient(135deg,
        rgba(93,232,255,.08) 0%,
        rgba(139,111,255,.06) 100%);
      border: 1px solid rgba(93,232,255,.2);
      border-radius: 20px;
      padding: 28px;
    }
    .result-box {
      background: rgba(0,0,0,.4);
      border: 1px solid rgba(93,232,255,.2);
      border-radius: 14px;
      padding: 20px;
      margin-top: 16px;
      display: none;
    }
    .result-box.show { display: block; }
    .result-value {
      font-size: 2rem;
      font-weight: 800;
      color: var(--cyan);
    }
    .formula-box {
      background: rgba(0,0,0,.4);
      border-left: 3px solid var(--violet);
      border-radius: 0 10px 10px 0;
      padding: 12px 16px;
      font-family: 'Consolas', monospace;
      font-size: 13px;
      color: #a0c4ff;
      margin: 12px 0;
    }
    .tab-btn {
      padding: 10px 22px;
      border-radius: 999px;
      border: 1px solid var(--line);
      background: rgba(255,255,255,.05);
      color: var(--muted);
      cursor: pointer;
      transition: var(--trans);
      font-weight: 700;
      font-size: 13px;
    }
    .tab-btn.active {
      background: linear-gradient(135deg, var(--cyan), var(--violet));
      color: #020310;
      border: none;
    }
  </style>
</head>

<body data-page="timedilation">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-panel">
      <button class="mobile-menu" type="button">☰ Menu</button>

      <section class="page-head">
        <div>
          <p class="eyebrow">Physics Module</p>
          <h1>⏱️ Time Dilation Calculator</h1>
          <p class="muted">
            Calculate relativistic time dilation using Einstein's
            Special and General Relativity equations.
          </p>
        </div>
      </section>

      <!-- STATS -->
      <div class="stats-grid"
           style="grid-template-columns:repeat(3,1fr); margin-bottom:20px;">
        <div class="stat glass">
          <span>Calculations Saved</span>
          <strong><?php echo $totalCalcs ?? 0; ?></strong>
        </div>
        <div class="stat glass">
          <span>Avg Dilation Factor</span>
          <strong style="font-size:20px; margin-top:6px;">
            <?php echo $avgDilation ?? '—'; ?>×
          </strong>
        </div>
        <div class="stat glass">
          <span>Physics Engine</span>
          <strong style="font-size:14px; margin-top:6px; color:var(--cyan);">
            Einstein GR + SR
          </strong>
        </div>
      </div>

      <div class="dashboard-grid" style="margin-bottom:20px;">

        <!-- CALCULATOR -->
        <section class="calc-card">
          <h2 style="margin-bottom:16px;">🧮 Calculate Time Dilation</h2>

          <!-- Tab buttons -->
          <div style="display:flex; gap:10px; margin-bottom:20px;">
            <button class="tab-btn active" onclick="switchTab('special')">
              ⚡ Special Relativity
            </button>
            <button class="tab-btn" onclick="switchTab('general')">
              🕳️ General Relativity
            </button>
          </div>

          <!-- Special Relativity Tab -->
          <div id="special-tab">
            <p class="muted" style="font-size:13px; margin-bottom:14px;">
              Time dilation due to <strong style="color:var(--cyan);">velocity</strong>
              — the faster you travel, the slower time passes for you.
            </p>

            <div class="formula-box">
              t' = t × √(1 - v²/c²)
              <br>
              t' = traveler time &nbsp;|&nbsp;
              t = Earth time &nbsp;|&nbsp;
              v = velocity &nbsp;|&nbsp;
              c = speed of light
            </div>

            <div style="display:grid; gap:14px; margin-top:16px;">
              <label>Velocity (% of speed of light)
                <input type="range" id="velocity" min="1" max="99.9999"
                       step="0.0001" value="50"
                       oninput="updateVelocityLabel(this.value); calcSpecial()"
                       style="width:100%; margin-top:8px; accent-color:var(--cyan);">
                <span id="velocity-label"
                      style="color:var(--cyan); font-weight:700; font-size:1.1rem;">
                  50% of c
                </span>
              </label>

              <label>Earth Observer Time (years)
                <input type="number" id="earth-time-special"
                       value="10" min="1" max="1000000"
                       oninput="calcSpecial()"
                       placeholder="e.g. 10">
              </label>

              <label>Scenario Name (optional)
                <input type="text" id="scenario-special"
                       placeholder="e.g. Trip to Alpha Centauri">
              </label>
            </div>

            <!-- Result -->
            <div class="result-box show" id="result-special">
              <p class="eyebrow" style="margin-bottom:10px;">Result</p>
              <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div>
                  <p class="muted" style="font-size:12px;">Traveler experiences</p>
                  <p class="result-value" id="traveler-time-special">—</p>
                  <p class="muted" style="font-size:12px;">years</p>
                </div>
                <div>
                  <p class="muted" style="font-size:12px;">Time difference</p>
                  <p class="result-value" id="time-diff-special"
                     style="color:var(--pink);">—</p>
                  <p class="muted" style="font-size:12px;">years lost</p>
                </div>
              </div>
              <div style="margin-top:14px; padding-top:14px;
                          border-top:1px solid var(--line);">
                <p class="muted" style="font-size:12px;">
                  Dilation Factor:
                  <strong style="color:var(--gold);" id="factor-special">—</strong>×
                  &nbsp;|&nbsp;
                  Lorentz Factor (γ):
                  <strong style="color:var(--violet);" id="lorentz">—</strong>
                </p>
              </div>
              <button class="btn primary"
                      style="margin-top:16px; width:100%;"
                      onclick="saveCalculation('special')">
                💾 Save This Calculation
              </button>
            </div>
          </div>

          <!-- General Relativity Tab -->
          <div id="general-tab" style="display:none;">
            <p class="muted" style="font-size:13px; margin-bottom:14px;">
              Time dilation due to
              <strong style="color:var(--violet);">gravity</strong>
              — the closer to a massive object, the slower time passes.
            </p>

            <div class="formula-box">
              t' = t × √(1 - rₛ/r)
              <br>
              t' = traveler time &nbsp;|&nbsp;
              t = distant observer time &nbsp;|&nbsp;
              rₛ = Schwarzschild radius &nbsp;|&nbsp;
              r = your distance
            </div>

            <div style="display:grid; gap:14px; margin-top:16px;">
              <label>Distance from Black Hole
                (× Schwarzschild radius, min 1.01)
                <input type="number" id="distance" value="2"
                       min="1.01" step="0.01"
                       oninput="calcGeneral()"
                       placeholder="e.g. 2">
              </label>

              <label>Distant Observer Time (years)
                <input type="number" id="earth-time-general"
                       value="10" min="1"
                       oninput="calcGeneral()"
                       placeholder="e.g. 10">
              </label>

              <label>Scenario Name (optional)
                <input type="text" id="scenario-general"
                       placeholder="e.g. Orbiting Sagittarius A*">
              </label>
            </div>

            <!-- Result -->
            <div class="result-box show" id="result-general">
              <p class="eyebrow" style="margin-bottom:10px;">Result</p>
              <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                <div>
                  <p class="muted" style="font-size:12px;">Traveler experiences</p>
                  <p class="result-value" id="traveler-time-general">—</p>
                  <p class="muted" style="font-size:12px;">years</p>
                </div>
                <div>
                  <p class="muted" style="font-size:12px;">Time difference</p>
                  <p class="result-value" id="time-diff-general"
                     style="color:var(--pink);">—</p>
                  <p class="muted" style="font-size:12px;">years lost</p>
                </div>
              </div>
              <div style="margin-top:14px; padding-top:14px;
                          border-top:1px solid var(--line);">
                <p class="muted" style="font-size:12px;">
                  Dilation Factor:
                  <strong style="color:var(--gold);"
                          id="factor-general">—</strong>×
                </p>
              </div>
              <button class="btn primary"
                      style="margin-top:16px; width:100%;"
                      onclick="saveCalculation('general')">
                💾 Save This Calculation
              </button>
            </div>
          </div>
        </section>

        <!-- BREAKDOWN BY TYPE -->
        <section class="glass panel">
          <h2>📊 Calculations by Type</h2>
          <p class="muted" style="font-size:12px; margin-bottom:14px;">
            SELECT calc_type, COUNT(*), AVG(dilation_factor)
            FROM dilation_calculations GROUP BY calc_type
          </p>

          <?php if (mysqli_num_rows($byType) == 0): ?>
            <p class="muted">No calculations saved yet.</p>
          <?php else: ?>
            <table>
              <thead>
                <tr>
                  <th>Type</th>
                  <th>Count</th>
                  <th>Avg Factor</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = mysqli_fetch_assoc($byType)): ?>
                  <tr>
                    <td><?php echo htmlspecialchars($row['calc_type']); ?></td>
                    <td>
                      <span class="badge badge-cyan">
                        <?php echo $row['total']; ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge badge-gold">
                        <?php echo $row['avg_factor']; ?>×
                      </span>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php endif; ?>

          <!-- Fun facts -->
          <div style="margin-top:20px; padding-top:16px;
                      border-top:1px solid var(--line2);">
            <p class="eyebrow" style="margin-bottom:12px;">Did You Know?</p>

            <div class="mini-card" style="margin-bottom:10px;">
              <p style="font-size:13px; color:var(--text);">
                🛸 At 99% speed of light, 1 year for traveler =
                <strong style="color:var(--cyan);">7.09 years</strong> on Earth
              </p>
            </div>

            <div class="mini-card" style="margin-bottom:10px;">
              <p style="font-size:13px; color:var(--text);">
                🕳️ At event horizon of black hole, time
                <strong style="color:var(--violet);">stops completely</strong>
                for outside observer
              </p>
            </div>

            <div class="mini-card">
              <p style="font-size:13px; color:var(--text);">
                🛰️ GPS satellites experience
                <strong style="color:var(--gold);">38 microseconds</strong>
                of time dilation per day — must be corrected
              </p>
            </div>
          </div>
        </section>

      </div>

      <!-- SAVED CALCULATIONS -->
      <section class="glass panel">
        <div class="panel-head">
          <h2>📋 Saved Calculations
            <span class="badge badge-cyan"><?php echo $totalCalcs; ?></span>
          </h2>
        </div>

        <div class="table-wrap">
          <table>
            <thead>
              <tr>
                <th>Type</th>
                <th>Scenario</th>
                <th>Input</th>
                <th>Earth Time</th>
                <th>Traveler Time</th>
                <th>Dilation Factor</th>
                <th>Time Lost</th>
                <th>Saved</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (mysqli_num_rows($saved) == 0): ?>
                <tr>
                  <td colspan="9"
                      style="text-align:center; color:var(--muted); padding:30px;">
                    No calculations saved yet. Use the calculator above.
                  </td>
                </tr>
              <?php endif; ?>

              <?php while ($row = mysqli_fetch_assoc($saved)): ?>
                <tr>
                  <td>
                    <span class="badge <?php echo $row['calc_type'] == 'Special Relativity'
                                              ? 'badge-cyan' : 'badge-violet'; ?>">
                      <?php echo $row['calc_type']; ?>
                    </span>
                  </td>
                  <td><?php echo htmlspecialchars($row['scenario_name'] ?: '—'); ?></td>
                  <td style="font-size:12px;">
                    <?php echo $row['input_value']; ?>
                    <?php echo htmlspecialchars($row['input_unit']); ?>
                  </td>
                  <td><?php echo $row['earth_time_years']; ?> yrs</td>
                  <td style="color:var(--cyan); font-weight:700;">
                    <?php echo $row['traveler_time_years']; ?> yrs
                  </td>
                  <td>
                    <span class="badge badge-gold">
                      <?php echo $row['dilation_factor']; ?>×
                    </span>
                  </td>
                  <td style="color:var(--pink); font-weight:700;">
                    <?php echo $row['time_difference_years']; ?> yrs
                  </td>
                  <td style="font-size:12px; color:var(--muted);">
                    <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                  </td>
                  <td>
                    <a class="danger"
                       href="actions/delete_calculation.php?id=<?php echo $row['calc_id']; ?>"
                       onclick="return confirm('Delete this calculation?')">
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

  <!-- SAVE FORM (hidden) -->
  <form id="save-form" method="POST"
        action="actions/save_calculation.php" style="display:none;">
    <input type="hidden" name="calc_type"             id="f-type">
    <input type="hidden" name="scenario_name"         id="f-scenario">
    <input type="hidden" name="input_value"           id="f-input">
    <input type="hidden" name="input_unit"            id="f-unit">
    <input type="hidden" name="earth_time_years"      id="f-earth">
    <input type="hidden" name="traveler_time_years"   id="f-traveler">
    <input type="hidden" name="dilation_factor"       id="f-factor">
    <input type="hidden" name="time_difference_years" id="f-diff">
  </form>

  <script>
  const C = 299792458; // speed of light m/s

  // Tab switching
  function switchTab(tab) {
    document.getElementById('special-tab').style.display =
      tab === 'special' ? 'block' : 'none';
    document.getElementById('general-tab').style.display =
      tab === 'general' ? 'block' : 'none';
    document.querySelectorAll('.tab-btn').forEach((btn, i) => {
      btn.classList.toggle('active', (i === 0) === (tab === 'special'));
    });
  }

  function updateVelocityLabel(val) {
    document.getElementById('velocity-label').textContent = val + '% of c';
  }

  // Special Relativity Calculation
  // Formula: t' = t * sqrt(1 - v²/c²)
  function calcSpecial() {
    const v    = parseFloat(document.getElementById('velocity').value) / 100;
    const t    = parseFloat(document.getElementById('earth-time-special').value) || 10;
    const gamma     = 1 / Math.sqrt(1 - v * v);
    const tPrime    = t / gamma;
    const diff      = t - tPrime;
    const factor    = gamma;

    document.getElementById('traveler-time-special').textContent =
      tPrime.toFixed(4) + ' yrs';
    document.getElementById('time-diff-special').textContent =
      diff.toFixed(4) + ' yrs';
    document.getElementById('factor-special').textContent =
      factor.toFixed(6);
    document.getElementById('lorentz').textContent =
      gamma.toFixed(6);
  }

  // General Relativity Calculation
  // Formula: t' = t * sqrt(1 - rs/r)
  // rs = Schwarzschild radius, r = distance in units of rs
  function calcGeneral() {
    const r  = parseFloat(document.getElementById('distance').value) || 2;
    const t  = parseFloat(document.getElementById('earth-time-general').value) || 10;

    if (r <= 1) {
      document.getElementById('traveler-time-general').textContent =
        'Inside event horizon!';
      return;
    }

    const tPrime = t * Math.sqrt(1 - 1/r);
    const diff   = t - tPrime;
    const factor = t / tPrime;

    document.getElementById('traveler-time-general').textContent =
      tPrime.toFixed(4) + ' yrs';
    document.getElementById('time-diff-general').textContent =
      diff.toFixed(4) + ' yrs';
    document.getElementById('factor-general').textContent =
      factor.toFixed(6);
  }

  // Save calculation to database
  function saveCalculation(type) {
    if (type === 'special') {
      const v       = parseFloat(document.getElementById('velocity').value);
      const t       = parseFloat(document.getElementById('earth-time-special').value);
      const gamma   = 1 / Math.sqrt(1 - (v/100) * (v/100));
      const tPrime  = t / gamma;

      document.getElementById('f-type').value     = 'Special Relativity';
      document.getElementById('f-scenario').value =
        document.getElementById('scenario-special').value;
      document.getElementById('f-input').value    = v;
      document.getElementById('f-unit').value     = '% of light speed';
      document.getElementById('f-earth').value    = t;
      document.getElementById('f-traveler').value = tPrime.toFixed(6);
      document.getElementById('f-factor').value   = gamma.toFixed(8);
      document.getElementById('f-diff').value     = (t - tPrime).toFixed(6);
    } else {
      const r      = parseFloat(document.getElementById('distance').value);
      const t      = parseFloat(document.getElementById('earth-time-general').value);
      const tPrime = t * Math.sqrt(1 - 1/r);

      document.getElementById('f-type').value     = 'General Relativity';
      document.getElementById('f-scenario').value =
        document.getElementById('scenario-general').value;
      document.getElementById('f-input').value    = r;
      document.getElementById('f-unit').value     = 'Schwarzschild radii';
      document.getElementById('f-earth').value    = t;
      document.getElementById('f-traveler').value = tPrime.toFixed(6);
      document.getElementById('f-factor').value   = (t/tPrime).toFixed(8);
      document.getElementById('f-diff').value     = (t - tPrime).toFixed(6);
    }
    document.getElementById('save-form').submit();
  }

  // Run on load
  calcSpecial();
  calcGeneral();
  </script>

  <script src="assets/js/app.js"></script>
</body>
</html>