# 🔭 CosmoTrack

**Astronomy Observation and Research Data Management System**

CosmoTrack is a web-based database application that brings an astronomer's everyday tools into one place: logging observations, managing telescopes and observing sites, tracking satellite passes, exploring exoplanets, and turning the logged data into statistics and reports.

> Built as a database management and software engineering project, covering database design, requirements engineering, and system architecture.

![PHP](https://img.shields.io/badge/PHP-777BB4?logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?logo=mysql&logoColor=white)
![HTML5](https://img.shields.io/badge/HTML5-E34F26?logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/CSS3-1572B6?logo=css3&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?logo=javascript&logoColor=black)

---

## Features

| Module | What it does |
|---|---|
| **Observation logging** | Record, edit, delete and revisit observations (object, type, date/time, sky conditions, notes, quality score, public/private). Each observation links to a location and a telescope. |
| **Search** | Search your observations by keyword and filter by object type, date range, sky condition and visibility. |
| **Equipment management** | Keep a catalogue of telescopes and equipment (type, aperture, focal length, mount). |
| **Locations & light pollution** | Save observing sites with coordinates and a Bortle-scale rating. A Bortle guide ranks your sites and links out to external light-pollution maps. |
| **Satellite pass log** | Log satellite passes (name, type, max elevation, duration, direction, brightness) and see per-satellite statistics. Links out to N2YO and Heavens-Above for live pass predictions. |
| **Exoplanet explorer** | Browse and filter an exoplanet table (host star, discovery method and year, distance, habitability zone) and bookmark favourites. |
| **Events & reminders** | View upcoming astronomy events and set reminders for them. |
| **Time dilation calculator** | Special-relativity calculator; calculations can be saved and reviewed. |
| **Space movies** | Browse space films and rate and review them. |
| **Cosmic facts** | A random space fact on the dashboard. |
| **Statistics & reports** | Charts (Chart.js) and SQL reports built on `GROUP BY`, `HAVING` and joins: observations by type, by sky condition, by month, most-used telescope, most active location. |
| **Accounts & admin** | Registration and login (hashed passwords), user profile, change password, and an admin panel to manage users and roles and see activity across the system. |

## Tech stack

- **Frontend:** HTML5, CSS3, vanilla JavaScript, [Chart.js](https://www.chartjs.org/) (via CDN)
- **Backend:** PHP (procedural, `mysqli`)
- **Database:** MySQL / MariaDB
- **Local server:** XAMPP (Apache + MySQL)

## Getting started

### Requirements
- [XAMPP](https://www.apachefriends.org/) (or any Apache + PHP + MySQL/MariaDB stack)
- An internet connection for the Chart.js CDN

### Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/Hasibur-sun/cosmotrack.git
   ```
2. **Copy the app folder** `cosmotrack/` into your web root, e.g. `C:\xampp\htdocs\cosmotrack`.
3. **Start Apache and MySQL** from the XAMPP control panel.
4. **Create the database**: open phpMyAdmin (`http://localhost/phpmyadmin`), go to *Import*, and import [`database/schema.sql`](database/schema.sql). This creates `cosmotrack_db` with all tables.
5. **Check the connection settings** in `cosmotrack/includes/db.php` (defaults are XAMPP's: host `localhost`, user `root`, empty password, database `cosmotrack_db`).
6. **Open the app:** <http://localhost/cosmotrack/index.html>
7. Register an account, then log in.

The reference tables (`exoplanets`, `public_events`, `space_facts`, `space_movies`) start empty, so those pages stay blank until you add rows through phpMyAdmin.

To make an account an admin, change its `role` to `Admin` in the `users` table.

## Project structure

```
.
├── README.md
├── database/
│   └── schema.sql            # MySQL schema (13 tables)
└── cosmotrack/
    ├── index.html            # Landing page
    ├── register.html         # Sign-up form
    ├── login.php             # Login
    ├── dashboard.php         # Overview, next event, cosmic fact
    ├── observation.php       # Add an observation
    ├── view_observations.php # Browse / edit / delete observations
    ├── telescopes.php        # Add equipment  (view_telescopes.php to list)
    ├── locations.php         # Add a site     (view_locations.php to list)
    ├── satellite.php         # Satellite pass log
    ├── lightpollution.php    # Bortle scale guide and ranking
    ├── exoplanets.php        # Exoplanet explorer
    ├── events.php            # Astronomy events
    ├── reminders.php         # Event reminders
    ├── timedilation.php      # Time dilation calculator
    ├── movies.php            # Space movies and ratings
    ├── stats.php             # Charts
    ├── reports.php           # SQL reports
    ├── search.php            # Search observations
    ├── admin.php             # Admin panel
    ├── actions/              # Form handlers (add, edit, delete, login, etc.)
    ├── includes/             # db.php (connection), sidebar.php
    └── assets/               # css/style.css, js/app.js
```

The `*.html` pages other than `index.html` and `register.html` (for example `observations.html`, `reports.html`) are an earlier front-end prototype that stored data in browser `localStorage`. The working application is the set of `.php` pages.

## Database overview

13 tables:

- **Core:** `users`, `locations`, `telescopes`, `observations`
- **Satellites:** `satellite_passes`
- **Exoplanets:** `exoplanets`, `exoplanet_bookmarks`
- **Events:** `public_events`, `reminders`
- **Extras:** `space_facts`, `space_movies`, `movie_ratings`, `dilation_calculations`

`observations` references `users`, `locations` and `telescopes`. The `*_bookmarks`, `reminders` and `movie_ratings` tables are many-to-many link tables between users and the corresponding reference data.

## Known limitations

- **Local use only.** CosmoTrack is built for a local XAMPP setup and is not hardened for public hosting. In particular, a few handlers (such as registration and add-location) build SQL from raw input rather than prepared statements, the registration form lets the user choose their own role, and admin deletions use plain GET links without CSRF protection.
- **Satellite data is manual.** Passes are logged by the user. There is no live satellite or weather API integration yet.
- **No automated tests.**

## Roadmap

- Live satellite data through the [CelesTrak](https://celestrak.org/) API
- Weather and sky-condition lookup for observing sites
- An astronomy assistant for observing guidance
- Prepared statements everywhere, CSRF tokens, and server-side role assignment

## Author

**Hasibur Rahman Sun**: [github.com/Hasibur-sun](https://github.com/Hasibur-sun)

*From managing data on Earth to tracking objects beyond it. Another mission in the making.* 🚀
