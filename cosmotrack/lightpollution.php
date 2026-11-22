<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
include "includes/db.php";

$user_id = $_SESSION['user_id'];

// All locations with bortle scale ordered best first
$locations = mysqli_query($conn,
    "SELECT * FROM locations
     WHERE bortle_scale IS NOT NULL
     AND bortle_scale > 0
     ORDER BY bortle_scale ASC"
);

// Best locations (Bortle 1-3)
$bestLocations = mysqli_query($conn,
    "SELECT * FROM locations
     WHERE bortle_scale BETWEEN 1 AND 3
     ORDER BY bortle_scale ASC"
);

// Worst locations (Bortle 7-9)
$worstLocations = mysqli_query($conn,
    "SELECT * FROM locations
     WHERE bortle_scale BETWEEN 7 AND 9
     ORDER BY bortle_scale DESC"
);

// Average bortle scale
$avgBortle = mysqli_fetch_row(mysqli_query($conn,
    "SELECT ROUND(AVG(bortle_scale), 1) FROM locations
     WHERE bortle_scale > 0"))[0];

// Count by bortle scale GROUP BY
$byBortle = mysqli_query($conn,
    "SELECT bortle_scale, COUNT(*) AS total,
            GROUP_CONCAT(location_name SEPARATOR ', ') AS location_names
     FROM locations
     WHERE bortle_scale > 0
     GROUP BY bortle_scale
     ORDER BY bortle_scale ASC"
);

// Most active dark sky location (JOIN with observations)
$bestObsLocation = mysqli_fetch_assoc(mysqli_query($conn,
    "SELECT locations.location_name, locations.bortle_scale,
            COUNT(observations.observation_id) AS total_obs
     FROM locations
     LEFT JOIN observations ON locations.location_id = observations.location_id
     WHERE locations.bortle_scale BETWEEN 1 AND 4
     GROUP BY locations.location_id
     ORDER BY total_obs DESC, locations.bortle_scale ASC
     LIMIT 1"
));

$totalLocations = mysqli_num_rows($locations);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Light Pollution Map | CosmoTrack</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .bortle-bar {
      display: flex;
      height: 36px;
      border-radius: 12px;
      overflow: hidden;
      margin: 16px 0;
      box-shadow: 0 4px 20px rgba(0,0,0,.4);
    }
    .bortle-segment {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
      font-weight: 800;
      color: rgba(255,255,255,.9);
      cursor: pointer;
      transition: filter .2s;
    }
    .bortle-segment:hover { filter: brightness(1.3); }
    .b1 { background: #000428; }
    .b2 { background: #001a40; }
    .b3 { background: #003060; }
    .b4 { background: #004080; }
    .b5 { background: #2d5a8e; }
    .b6 { background: #5a7fa0; }
    .b7 { background: #8a9fb0; }
    .b8 { background: #b8c8d8; color: #333; }
    .b9 { background: #e8f0f8; color: #333; }

    .bortle-card {
      border-radius: 14px;
      padding: 18px;
      margin-bottom: 10px;
      border-left: 4px solid;
      transition: var(--trans);
    }
    .bortle-card:hover { transform: translateX(4px); }
    .bortle-card.excellent {
      background: rgba(0,4,40,.6);
      border-color: #4fc3f7;
    }
    .bortle-card.good {
      background: rgba(0,20,50,.5);
      border-color: #81d4fa;
    }
    .bortle-card.rural {
      background: rgba(0,40,80,.4);
      border-color: #a5d6a7;
    }
    .bortle-card.suburban {
      background: rgba(45,90,142,.3);
      border-color: #fff176;
    }
    .bortle-card.urban {
      background: rgba(100,80,60,.3);
      border-color: #ff8a65;
    }
    .bortle-card.city {
      background: rgba(150,50,50,.3);
      border-color: #ef5350;
    }

    .location-rank {
      display: flex;
      align-items: center;
      gap: 14px;
      padding: 14px;
      border-radius: 12px;
      background: rgba(255,255,255,.04);
      border: 1px solid var(--line2);
      margin-bottom: 10px;
      transition: var(--trans);
    }
    .location-rank:hover {
      background: rgba(93,232,255,.05);
      border-color: rgba(93,232,255,.2);
    }
    .rank-number {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--cyan), var(--violet));
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: 800;
      font-size: 13px;
      color: #020310;
      flex-shrink: 0;
    }
    .bortle-dot {
      width: 12px;
      height: 12px;
      border-radius: 50%;
      flex-shrink: 0;
    }
  </style>
</head>

<body data-page="lightpollution">
  <div class="stars"></div>
  <div class="nebula nebula-one"></div>
  <div class="nebula nebula-two"></div>

  <div class="app-shell">
    <?php include 'includes/sidebar.php'; ?>

    <main class="main-panel">
      <button class="mobile-menu" type="button">☰ Menu</button>

      <section class="page-head">
        <div>
          <p class="eyebrow">Light Pollution Module</p>
          <h1>🌃 Light Pollution & Bortle Scale</h1>
          <p class="muted">
            Analyze sky darkness at your saved locations using the Bortle scale.
          </p>
        </div>
        <a class="btn primary" href="locations.php">+ Add Location</a>
      </section>

      <!-- STATS -->
      <div class="stats-grid"
           style="grid-template-columns:repeat(4,1fr); margin-bottom:20px;">
        <div class="stat glass">
          <span>Locations Tracked</span>
          <strong><?php echo $totalLocations; ?></strong>
        </div>
        <div class="stat glass">
          <span>Average Bortle</span>
          <strong style="color:var(--gold);">
            <?php echo $avgBortle ?? '—'; ?>
          </strong>
        </div>
        <div class="stat glass">
          <span>Dark Sky Sites</span>
          <strong style="color:var(--cyan);">
            <?php echo mysqli_num_rows($bestLocations); ?>
          </strong>
        </div>
        <div class="stat glass">
          <span>Best Obs Location</span>
          <strong style="font-size:13px; margin-top:4px;">
            <?php echo $bestObsLocation
                  ? htmlspecialchars($bestObsLocation['location_name'])
                  : '—'; ?>
          </strong>
        </div>
      </div>

      <!-- BORTLE SCALE VISUAL -->
      <section class="glass panel" style="margin-bottom:20px;">
        <h2>🎨 Bortle Scale Visual Guide</h2>
        <p class="muted" style="font-size:13px; margin-bottom:16px;">
          The Bortle scale measures sky darkness from 1 (darkest) to 9 (brightest city sky).
          Lower = better for astronomy.
        </p>

        <!-- Color bar -->
        <div class="bortle-bar">
          <?php for ($i = 1; $i <= 9; $i++): ?>
            <div class="bortle-segment b<?php echo $i; ?>"><?php echo $i; ?></div>
          <?php endfor; ?>
        </div>

        <!-- Bortle level cards -->
        <?php
          $bortleLevels = [
            1 => ['class'=>'excellent','label'=>'Excellent Dark Sky',
                  'stars'=>'Zodiacal light, gegenschein visible. Milky Way casts shadows.',
                  'objects'=>'All deep sky objects visible. M33 seen with naked eye.'],
            2 => ['class'=>'excellent','label'=>'Typical Truly Dark Site',
                  'stars'=>'Airglow visible on horizon. Milky Way highly structured.',
                  'objects'=>'Faint globular clusters, galaxies easily visible.'],
            3 => ['class'=>'good','label'=>'Rural Sky',
                  'stars'=>'Some light pollution on horizon. Milky Way still impressive.',
                  'objects'=>'Most Messier objects visible with naked eye.'],
            4 => ['class'=>'rural','label'=>'Rural/Suburban Transition',
                  'stars'=>'Light domes visible. Milky Way complex structure visible.',
                  'objects'=>'Globular clusters obvious. Faint galaxies with telescope.'],
            5 => ['class'=>'suburban','label'=>'Suburban Sky',
                  'stars'=>'Milky Way washed out. Light pollution obvious.',
                  'objects'=>'Bright Messier objects visible. Deep sky needs telescope.'],
            6 => ['class'=>'suburban','label'=>'Bright Suburban Sky',
                  'stars'=>'Milky Way only in zenith. Strong light domes.',
                  'objects'=>'Only brightest clusters and nebulae visible.'],
            7 => ['class'=>'urban','label'=>'Suburban/Urban Transition',
                  'stars'=>'Milky Way invisible. Sky background grey not black.',
                  'objects'=>'Moon and planets good. Deep sky very difficult.'],
            8 => ['class'=>'city','label'=>'City Sky',
                  'stars'=>'Only 20-30 stars visible. Sky orange-grey.',
                  'objects'=>'Moon, planets, brightest stars only.'],
            9 => ['class'=>'city','label'=>'Inner City Sky',
                  'stars'=>'Less than 10 stars visible. Very bright sky.',
                  'objects'=>'Moon and Venus only worthwhile targets.'],
          ];
        ?>

        <div style="margin-top:16px;">
          <?php foreach ($bortleLevels as $level => $info): ?>
            <div class="bortle-card <?php echo $info['class']; ?>">
              <div style="display:flex; align-items:center; gap:12px; margin-bottom:6px;">
                <span style="font-size:1.3rem; font-weight:900;
                             color:var(--cyan); min-width:24px;">
                  <?php echo $level; ?>
                </span>
                <strong style="color:var(--text); font-size:14px;">
                  <?php echo $info['label']; ?>
                </strong>
              </div>
              <p style="font-size:12px; color:var(--muted2); margin-left:36px;">
                <?php echo $info['stars']; ?>
              </p>
              <p style="font-size:12px; color:var(--cyan); margin-left:36px; margin-top:4px;">
                ✦ <?php echo $info['objects']; ?>
              </p>
            </div>
          <?php endforeach; ?>
        </div>
      </section>

      <div class="dashboard-grid" style="margin-bottom:20px;">

        <!-- YOUR LOCATIONS RANKED -->
        <section class="glass panel">
          <h2>📍 Your Locations Ranked</h2>
          <p class="muted" style="font-size:12px; margin-bottom:16px;">
            SELECT * FROM locations ORDER BY bortle_scale ASC
          </p>

          <?php if ($totalLocations == 0): ?>
            <p class="muted">
              No locations with Bortle data yet.
              <a href="locations.php" style="color:var(--cyan);">
                Add locations →
              </a>
            </p>
          <?php else: ?>
            <?php
              // Reset pointer
              mysqli_data_seek($locations, 0);
              $rank = 1;
              while ($loc = mysqli_fetch_assoc($locations)):
                $b = (int)$loc['bortle_scale'];
                $colors = [
                  1=>'#4fc3f7', 2=>'#4fc3f7', 3=>'#81d4fa',
                  4=>'#a5d6a7', 5=>'#fff176', 6=>'#ffb74d',
                  7=>'#ff8a65', 8=>'#ef5350', 9=>'#b71c1c'
                ];
                $dotColor = $colors[$b] ?? '#aaa';
            ?>
              <div class="location-rank">
                <div class="rank-number"><?php echo $rank; ?></div>
                <div class="bortle-dot"
                     style="background:<?php echo $dotColor; ?>;
                            box-shadow: 0 0 8px <?php echo $dotColor; ?>;">
                </div>
                <div style="flex:1;">
                  <strong style="color:var(--text); font-size:14px;">
                    <?php echo htmlspecialchars($loc['location_name']); ?>
                  </strong>
                  <p class="muted" style="font-size:12px; margin-top:2px;">
                    <?php echo htmlspecialchars($loc['city'] ?? ''); ?>
                    <?php echo $loc['city'] && $loc['country'] ? ', ' : ''; ?>
                    <?php echo htmlspecialchars($loc['country'] ?? ''); ?>
                  </p>
                </div>
                <span class="badge <?php echo $b <= 3 ? 'badge-cyan'
                                          : ($b <= 5 ? 'badge-gold'
                                          : 'badge-red'); ?>">
                  Bortle <?php echo $b; ?>
                </span>
              </div>
            <?php
                $rank++;
              endwhile;
            ?>
          <?php endif; ?>
        </section>

        <!-- GROUP BY REPORT -->
        <section class="glass panel">
          <h2>📊 Locations by Bortle Scale</h2>
          <p class="muted" style="font-size:12px; margin-bottom:16px;">
            SELECT bortle_scale, COUNT(*), GROUP_CONCAT(location_name)
            FROM locations GROUP BY bortle_scale
          </p>

          <?php
            mysqli_data_seek($byBortle, 0);
            if (mysqli_num_rows($byBortle) == 0):
          ?>
            <p class="muted">No Bortle data yet.</p>
          <?php else: ?>
            <table>
              <thead>
                <tr>
                  <th>Bortle</th>
                  <th>Count</th>
                  <th>Locations</th>
                </tr>
              </thead>
              <tbody>
                <?php while ($row = mysqli_fetch_assoc($byBortle)): ?>
                  <tr>
                    <td>
                      <span class="badge <?php
                        $b = (int)$row['bortle_scale'];
                        echo $b <= 3 ? 'badge-cyan'
                           : ($b <= 5 ? 'badge-gold' : 'badge-red');
                      ?>">
                        Bortle <?php echo $row['bortle_scale']; ?>
                      </span>
                    </td>
                    <td>
                      <span class="badge badge-violet">
                        <?php echo $row['total']; ?>
                      </span>
                    </td>
                    <td style="font-size:12px; color:var(--muted);">
                      <?php echo htmlspecialchars($row['location_names']); ?>
                    </td>
                  </tr>
                <?php endwhile; ?>
              </tbody>
            </table>
          <?php endif; ?>

          <!-- Live map link -->
          <div style="margin-top:20px; padding-top:16px;
                      border-top:1px solid var(--line2);">
            <p class="eyebrow" style="margin-bottom:10px;">
              Live Light Pollution Map
            </p>
            <p class="muted" style="font-size:12px; margin-bottom:12px;">
              View real-time global light pollution data:
            </p>
            <div style="display:grid; gap:8px;">
              <a href="https://www.lightpollutionmap.info"
                 target="_blank" class="btn ghost"
                 style="border-color:rgba(93,232,255,.3); font-size:13px;">
                🗺️ Light Pollution Map →
              </a>
              <a href="https://darksitefinder.com/maps/world.html"
                 target="_blank" class="btn ghost"
                 style="border-color:rgba(139,111,255,.3); font-size:13px;">
                🌑 Dark Site Finder →
              </a>
              <a href="https://www.cleardarksky.com"
                 target="_blank" class="btn ghost"
                 style="border-color:rgba(255,200,71,.3); font-size:13px;">
                🌤️ Clear Dark Sky →
              </a>
            </div>
          </div>
        </section>

      </div>

      <!-- BEST AND WORST -->
      <div class="dashboard-grid" style="margin-bottom:20px;">

        <!-- BEST DARK SKY LOCATIONS -->
        <section class="glass panel">
          <h2 style="color:var(--cyan);">
            ✦ Best Dark Sky Locations (Bortle 1-3)
          </h2>
          <p class="muted" style="font-size:12px; margin-bottom:14px;">
            WHERE bortle_scale BETWEEN 1 AND 3 ORDER BY bortle_scale ASC
          </p>

          <?php if (mysqli_num_rows($bestLocations) == 0): ?>
            <p class="muted">
              No dark sky locations yet.
              Add locations with Bortle scale 1-3.
            </p>
          <?php else: ?>
            <?php while ($loc = mysqli_fetch_assoc($bestLocations)): ?>
              <div style="padding:12px; border-radius:10px;
                          background:rgba(77,255,195,.05);
                          border:1px solid rgba(77,255,195,.15);
                          margin-bottom:8px;">
                <div style="display:flex;
                            justify-content:space-between;
                            align-items:center;">
                  <div>
                    <strong style="color:var(--text);">
                      <?php echo htmlspecialchars($loc['location_name']); ?>
                    </strong>
                    <p class="muted" style="font-size:12px;">
                      <?php echo htmlspecialchars($loc['city'] ?? ''); ?>
                      <?php echo $loc['country'] ? ', '.$loc['country'] : ''; ?>
                    </p>
                  </div>
                  <span class="badge badge-green">
                    Bortle <?php echo $loc['bortle_scale']; ?>
                  </span>
                </div>
              </div>
            <?php endwhile; ?>
          <?php endif; ?>
        </section>

        <!-- WORST LIGHT POLLUTED -->
        <section class="glass panel">
          <h2 style="color:var(--danger);">
            ⚠️ Most Light Polluted (Bortle 7-9)
          </h2>
          <p class="muted" style="font-size:12px; margin-bottom:14px;">
            WHERE bortle_scale BETWEEN 7 AND 9 ORDER BY bortle_scale DESC
          </p>

          <?php if (mysqli_num_rows($worstLocations) == 0): ?>
            <p class="muted">
              No highly polluted locations recorded.
            </p>
          <?php else: ?>
            <?php while ($loc = mysqli_fetch_assoc($worstLocations)): ?>
              <div style="padding:12px; border-radius:10px;
                          background:rgba(255,95,126,.05);
                          border:1px solid rgba(255,95,126,.15);
                          margin-bottom:8px;">
                <div style="display:flex;
                            justify-content:space-between;
                            align-items:center;">
                  <div>
                    <strong style="color:var(--text);">
                      <?php echo htmlspecialchars($loc['location_name']); ?>
                    </strong>
                    <p class="muted" style="font-size:12px;">
                      <?php echo htmlspecialchars($loc['city'] ?? ''); ?>
                      <?php echo $loc['country'] ? ', '.$loc['country'] : ''; ?>
                    </p>
                  </div>
                  <span class="badge badge-red">
                    Bortle <?php echo $loc['bortle_scale']; ?>
                  </span>
                </div>
              </div>
            <?php endwhile; ?>
          <?php endif; ?>
        </section>

      </div>

      <!-- TIPS SECTION -->
      <section class="glass panel">
        <h2>💡 Tips to Find Dark Skies</h2>
        <div style="display:grid; grid-template-columns:repeat(3,1fr);
                    gap:16px; margin-top:16px;">
          <div class="mini-card">
            <p style="font-size:20px; margin-bottom:8px;">🌑</p>
            <strong style="color:var(--text);">Observe on New Moon</strong>
            <p class="muted" style="font-size:12px; margin-top:6px;">
              The moon is the biggest source of natural light pollution.
              Plan observations around new moon phase.
            </p>
          </div>
          <div class="mini-card">
            <p style="font-size:20px; margin-bottom:8px;">🏔️</p>
            <strong style="color:var(--text);">Go to Higher Altitude</strong>
            <p class="muted" style="font-size:12px; margin-top:6px;">
              Mountains and hills reduce atmosphere thickness and
              put you above low-lying light pollution haze.
            </p>
          </div>
          <div class="mini-card">
            <p style="font-size:20px; margin-bottom:8px;">🧭</p>
            <strong style="color:var(--text);">Face Away from Cities</strong>
            <p class="muted" style="font-size:12px; margin-top:6px;">
              Even in suburban areas, facing away from city centers
              can improve your view by 1-2 Bortle levels.
            </p>
          </div>
          <div class="mini-card">
            <p style="font-size:20px; margin-bottom:8px;">⏰</p>
            <strong style="color:var(--text);">Observe After Midnight</strong>
            <p class="muted" style="font-size:12px; margin-top:6px;">
              Business and street lighting reduces after midnight.
              Sky conditions often improve by 1 Bortle level.
            </p>
          </div>
          <div class="mini-card">
            <p style="font-size:20px; margin-bottom:8px;">👁️</p>
            <strong style="color:var(--text);">Dark Adapt Your Eyes</strong>
            <p class="muted" style="font-size:12px; margin-top:6px;">
              Takes 20-30 minutes for eyes to fully dark adapt.
              Use red light only — it preserves night vision.
            </p>
          </div>
          <div class="mini-card">
            <p style="font-size:20px; margin-bottom:8px;">📱</p>
            <strong style="color:var(--text);">Check Weather Apps</strong>
            <p class="muted" style="font-size:12px; margin-top:6px;">
              Use Clear Outside or Clear Dark Sky apps to check
              cloud cover, humidity, and seeing conditions before going out.
            </p>
          </div>
        </div>
      </section>

    </main>
  </div>
  <script src="assets/js/app.js"></script>
</body>
</html>