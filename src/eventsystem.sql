-- ============================================
-- CREATE / USE DATABASE
-- ============================================
CREATE DATABASE IF NOT EXISTS eventsystem;
USE eventsystem;

-- ============================================
-- CLEAN UP OLD TABLES (optional but recommended)
-- ============================================
DROP TABLE IF EXISTS waitlist;
DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS admins;

-- ============================================
-- ADMINS TABLE (used by login.php)
-- ============================================
CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin: username = admin, password = admin123
INSERT INTO admins (username, password)
VALUES ('admin', SHA2('admin123', 256));


-- ============================================
-- EVENTS TABLE (matches event_create/save/edit/list/details)
-- ============================================
CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(255) NOT NULL,
  description TEXT,
  event_date  DATE         NOT NULL,

  -- CHANGED: TIME  -> VARCHAR(20)
  -- so '9:00am', '10:00 am', etc. are accepted
  event_time  VARCHAR(20)  NULL,

  location    VARCHAR(255) NULL,

  -- used in event_list.php + event_details.php
  capacity    INT NOT NULL DEFAULT 0,

  -- used in event_save.php (created_by from $_SESSION['user'])
  created_by  VARCHAR(100) NULL,

  -- used in event_save.php / event_update.php / event_edit.php
  image_path  VARCHAR(255) NULL,

  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ============================================
-- REGISTRATIONS TABLE (matches event_register.php, dashboard.php)
-- ============================================
CREATE TABLE registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,

  event_id      INT          NOT NULL,
  student_name  VARCHAR(100) NOT NULL,
  student_email VARCHAR(150) NOT NULL,

  -- event_register.php inserts 'confirmed'
  status        VARCHAR(20)  NOT NULL DEFAULT 'confirmed',

  registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_reg_event
    FOREIGN KEY (event_id) REFERENCES events(id)
    ON DELETE CASCADE
);

CREATE INDEX idx_reg_event_email
  ON registrations (event_id, student_email);


-- ============================================
-- WAITLIST TABLE (matches event_register.php, dashboard.php)
-- ============================================
CREATE TABLE waitlist (
  id INT AUTO_INCREMENT PRIMARY KEY,

  event_id      INT          NOT NULL,
  student_name  VARCHAR(100) NOT NULL,
  student_email VARCHAR(150) NOT NULL,

  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_wait_event
    FOREIGN KEY (event_id) REFERENCES events(id)
    ON DELETE CASCADE
);

CREATE INDEX idx_wait_event_email
  ON waitlist (event_id, student_email);


CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
=======
-- ============================================
-- CREATE / USE DATABASE
-- ============================================
CREATE DATABASE IF NOT EXISTS eventsystem;
USE eventsystem;

-- ============================================
-- CLEAN UP OLD TABLES (optional but recommended)
-- ============================================
DROP TABLE IF EXISTS waitlist;
DROP TABLE IF EXISTS registrations;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS admins;

-- ============================================
-- ADMINS TABLE (used by login.php)
-- ============================================
CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Default admin: username = admin, password = admin123
INSERT INTO admins (username, password)
VALUES ('admin', SHA2('admin123', 256));


-- ============================================
-- EVENTS TABLE (matches event_create/save/edit/list/details)
-- ============================================
CREATE TABLE events (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title       VARCHAR(255) NOT NULL,
  description TEXT,
  event_date  DATE         NOT NULL,

  -- CHANGED: TIME  -> VARCHAR(20)
  -- so '9:00am', '10:00 am', etc. are accepted
  event_time  VARCHAR(20)  NULL,

  location    VARCHAR(255) NULL,

  -- used in event_list.php + event_details.php
  capacity    INT NOT NULL DEFAULT 0,

  -- used in event_save.php (created_by from $_SESSION['user'])
  created_by  VARCHAR(100) NULL,

  -- used in event_save.php / event_update.php / event_edit.php
  image_path  VARCHAR(255) NULL,

  created_at  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ============================================
-- REGISTRATIONS TABLE (matches event_register.php, dashboard.php)
-- ============================================
CREATE TABLE registrations (
  id INT AUTO_INCREMENT PRIMARY KEY,

  event_id      INT          NOT NULL,
  student_name  VARCHAR(100) NOT NULL,
  student_email VARCHAR(150) NOT NULL,

  -- event_register.php inserts 'confirmed'
  status        VARCHAR(20)  NOT NULL DEFAULT 'confirmed',

  registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_reg_event
    FOREIGN KEY (event_id) REFERENCES events(id)
    ON DELETE CASCADE
);

CREATE INDEX idx_reg_event_email
  ON registrations (event_id, student_email);


-- ============================================
-- WAITLIST TABLE (matches event_register.php, dashboard.php)
-- ============================================
CREATE TABLE waitlist (
  id INT AUTO_INCREMENT PRIMARY KEY,

  event_id      INT          NOT NULL,
  student_name  VARCHAR(100) NOT NULL,
  student_email VARCHAR(150) NOT NULL,

  created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_wait_event
    FOREIGN KEY (event_id) REFERENCES events(id)
    ON DELETE CASCADE
);

CREATE INDEX idx_wait_event_email
  ON waitlist (event_id, student_email);


CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================
-- FEEDBACK TABLE (for GP-UC6)
-- ============================================
CREATE TABLE event_feedback (
  id INT AUTO_INCREMENT PRIMARY KEY,
  event_id INT NOT NULL,
  student_email VARCHAR(150) NOT NULL,
  rating TINYINT NOT NULL,        -- 1 to 5
  comments TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_fb_event
    FOREIGN KEY (event_id) REFERENCES events(id)
    ON DELETE CASCADE
);

CREATE UNIQUE INDEX idx_fb_unique
  ON event_feedback (event_id, student_email);

