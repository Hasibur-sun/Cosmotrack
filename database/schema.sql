-- CosmoTrack database schema (MySQL / MariaDB)
-- Reconstructed from the queries used in the application code.
-- Usage: import in phpMyAdmin, or run:  mysql -u root -p < database/schema.sql

CREATE DATABASE IF NOT EXISTS cosmotrack_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE cosmotrack_db;

CREATE TABLE users (
  user_id    INT AUTO_INCREMENT PRIMARY KEY,
  name       VARCHAR(100) NOT NULL,
  email      VARCHAR(150) NOT NULL UNIQUE,
  phone      VARCHAR(30),
  password   VARCHAR(255) NOT NULL,            -- password_hash() output
  role       VARCHAR(10)  NOT NULL DEFAULT 'User',   -- 'User' or 'Admin'
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE locations (
  location_id   INT AUTO_INCREMENT PRIMARY KEY,
  location_name VARCHAR(150) NOT NULL,
  city          VARCHAR(100),
  country       VARCHAR(100),
  latitude      DECIMAL(9,6),
  longitude     DECIMAL(9,6),
  bortle_scale  TINYINT                          -- 1 (darkest) to 9 (brightest)
);

CREATE TABLE telescopes (
  telescope_id   INT AUTO_INCREMENT PRIMARY KEY,
  telescope_name VARCHAR(150) NOT NULL,
  telescope_type VARCHAR(50),
  aperture       DECIMAL(8,2),                   -- mm
  focal_length   DECIMAL(8,2),                   -- mm
  mount_type     VARCHAR(50),
  description    TEXT,
  user_id        INT,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);

CREATE TABLE observations (
  observation_id    INT AUTO_INCREMENT PRIMARY KEY,
  object_name       VARCHAR(150) NOT NULL,
  object_type       VARCHAR(50),
  observation_date  DATE,
  observation_time  TIME,
  location_id       INT,
  telescope_id      INT,
  sky_condition     VARCHAR(50),
  notes             TEXT,
  captured_latitude  DECIMAL(9,6),
  captured_longitude DECIMAL(9,6),
  visibility        VARCHAR(10) DEFAULT 'Private',   -- 'Public' or 'Private'
  quality_score     TINYINT,
  user_id           INT,
  created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (location_id)  REFERENCES locations(location_id)  ON DELETE SET NULL,
  FOREIGN KEY (telescope_id) REFERENCES telescopes(telescope_id) ON DELETE SET NULL,
  FOREIGN KEY (user_id)      REFERENCES users(user_id)          ON DELETE CASCADE
);

CREATE TABLE satellite_passes (
  pass_id          INT AUTO_INCREMENT PRIMARY KEY,
  user_id          INT,
  location_id      INT,
  satellite_name   VARCHAR(100) NOT NULL,
  satellite_type   VARCHAR(50),
  pass_date        DATE,
  pass_time        TIME,
  max_elevation    DECIMAL(5,1),
  duration_seconds INT,
  direction_start  VARCHAR(10),
  direction_end    VARCHAR(10),
  brightness       VARCHAR(50),
  notes            TEXT,
  FOREIGN KEY (user_id)     REFERENCES users(user_id)         ON DELETE CASCADE,
  FOREIGN KEY (location_id) REFERENCES locations(location_id) ON DELETE SET NULL
);

CREATE TABLE exoplanets (
  planet_id            INT AUTO_INCREMENT PRIMARY KEY,
  planet_name          VARCHAR(100) NOT NULL,
  host_star            VARCHAR(100),
  discovery_method     VARCHAR(50),
  discovery_year       SMALLINT,
  distance_ly          DECIMAL(12,2),
  orbital_period_days  DECIMAL(14,4),
  planet_mass_earth    DECIMAL(12,4),
  planet_radius_earth  DECIMAL(12,4),
  surface_temp_k       DECIMAL(10,2),
  habitability_zone    VARCHAR(50),
  description          TEXT,
  nasa_link            VARCHAR(255)
);

CREATE TABLE exoplanet_bookmarks (
  bookmark_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  planet_id   INT NOT NULL,
  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uq_user_planet (user_id, planet_id),
  FOREIGN KEY (user_id)   REFERENCES users(user_id)       ON DELETE CASCADE,
  FOREIGN KEY (planet_id) REFERENCES exoplanets(planet_id) ON DELETE CASCADE
);

CREATE TABLE public_events (
  event_id          INT AUTO_INCREMENT PRIMARY KEY,
  event_title       VARCHAR(200) NOT NULL,
  event_type        VARCHAR(50),
  event_date        DATE,
  event_time        TIME,
  visibility_region VARCHAR(150),
  description       TEXT
);

CREATE TABLE reminders (
  reminder_id     INT AUTO_INCREMENT PRIMARY KEY,
  user_id         INT NOT NULL,
  event_id        INT NOT NULL,
  reminder_date   DATE,
  reminder_time   TIME,
  reminder_method VARCHAR(30),
  status          VARCHAR(20) DEFAULT 'Pending',
  FOREIGN KEY (user_id)  REFERENCES users(user_id)          ON DELETE CASCADE,
  FOREIGN KEY (event_id) REFERENCES public_events(event_id) ON DELETE CASCADE
);

CREATE TABLE space_facts (
  fact_id     INT AUTO_INCREMENT PRIMARY KEY,
  category    VARCHAR(50),
  fact_text   TEXT NOT NULL,
  source_note VARCHAR(255)
);

CREATE TABLE space_movies (
  movie_id            INT AUTO_INCREMENT PRIMARY KEY,
  title               VARCHAR(200) NOT NULL,
  director            VARCHAR(150),
  release_year        SMALLINT,
  genre               VARCHAR(80),
  description         TEXT,
  scientific_accuracy TINYINT,
  educational_value   TINYINT,
  imdb_link           VARCHAR(255)
);

CREATE TABLE movie_ratings (
  rating_id   INT AUTO_INCREMENT PRIMARY KEY,
  user_id     INT NOT NULL,
  movie_id    INT NOT NULL,
  user_rating TINYINT,
  review      TEXT,
  UNIQUE KEY uq_user_movie (user_id, movie_id),
  FOREIGN KEY (user_id)  REFERENCES users(user_id)        ON DELETE CASCADE,
  FOREIGN KEY (movie_id) REFERENCES space_movies(movie_id) ON DELETE CASCADE
);

CREATE TABLE dilation_calculations (
  calc_id               INT AUTO_INCREMENT PRIMARY KEY,
  user_id               INT NOT NULL,
  calc_type             VARCHAR(50),
  scenario_name         VARCHAR(150),
  input_value           DECIMAL(20,6),
  input_unit            VARCHAR(30),
  earth_time_years      DECIMAL(20,6),
  traveler_time_years   DECIMAL(20,6),
  dilation_factor       DECIMAL(20,6),
  time_difference_years DECIMAL(20,6),
  created_at            TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
