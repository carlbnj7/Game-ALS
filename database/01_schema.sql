-- Game ALS database schema (MySQL 5.7+ / MariaDB 10.3+)
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS clcs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  municipality VARCHAR(100) NOT NULL DEFAULT '',
  district VARCHAR(100) NOT NULL DEFAULT '',
  district_id INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  role ENUM('admin','teacher','learner') NOT NULL,
  als_id VARCHAR(20) NULL UNIQUE,
  username VARCHAR(60) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  pin_hash VARCHAR(255) NULL,
  pin_fails TINYINT NOT NULL DEFAULT 0,
  first_name VARCHAR(80) NOT NULL DEFAULT '',
  middle_name VARCHAR(80) NOT NULL DEFAULT '',
  last_name VARCHAR(80) NOT NULL DEFAULT '',
  sex ENUM('M','F','') NOT NULL DEFAULT '',
  birthdate DATE NULL,
  lrn VARCHAR(20) NOT NULL DEFAULT '',
  grade ENUM('ELEM','JHS') NULL,
  clc_id INT UNSIGNED NULL,
  district_id INT UNSIGNED NULL,
  email VARCHAR(150) NOT NULL DEFAULT '',
  active TINYINT(1) NOT NULL DEFAULT 1,
  must_change TINYINT(1) NOT NULL DEFAULT 1,
  setup_done TINYINT(1) NOT NULL DEFAULT 0,
  qr_token CHAR(40) NULL UNIQUE,
  created_by INT UNSIGNED NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  last_login DATETIME NULL,
  KEY (role), KEY (clc_id), KEY (grade)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS municipalities (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS districts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  municipality_id INT UNSIGNED NOT NULL,
  name VARCHAR(100) NOT NULL,
  UNIQUE KEY one_name (municipality_id, name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS teacher_clc (
  teacher_id INT UNSIGNED NOT NULL,
  clc_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (teacher_id, clc_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS items (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  grade ENUM('ELEM','JHS') NOT NULL,
  strand VARCHAR(10) NOT NULL,
  level TINYINT NOT NULL,
  activity TINYINT NOT NULL,
  num SMALLINT NOT NULL,
  passage MEDIUMTEXT NULL,
  question TEXT NOT NULL,
  opt_a TEXT NULL, opt_b TEXT NULL, opt_c TEXT NULL, opt_d TEXT NULL,
  answer CHAR(1) NULL,
  explanation TEXT NULL,
  image VARCHAR(255) NULL,
  image_only TINYINT(1) NOT NULL DEFAULT 0,
  active TINYINT(1) NOT NULL DEFAULT 1,
  updated_by INT UNSIGNED NULL,
  updated_at DATETIME NULL,
  KEY k_act (grade, strand, level, activity, num)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS attempts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  strand VARCHAR(10) NOT NULL,
  level TINYINT NOT NULL,
  activity TINYINT NOT NULL,
  status ENUM('playing','passed','failed','lost','quit') NOT NULL DEFAULT 'playing',
  item_ids TEXT NOT NULL,
  pos SMALLINT NOT NULL DEFAULT 0,
  correct SMALLINT NOT NULL DEFAULT 0,
  lives TINYINT NOT NULL DEFAULT 3,
  hints TINYINT NOT NULL DEFAULT 3,
  hinted_pos SMALLINT NOT NULL DEFAULT -1,
  streak TINYINT NOT NULL DEFAULT 0,
  best_streak TINYINT NOT NULL DEFAULT 0,
  started_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  finished_at DATETIME NULL,
  KEY (user_id, strand, level, activity, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS answers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  attempt_id INT UNSIGNED NOT NULL,
  user_id INT UNSIGNED NOT NULL,
  item_id INT UNSIGNED NOT NULL,
  choice CHAR(1) NOT NULL,
  correct TINYINT(1) NOT NULL,
  answered_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (attempt_id), KEY (item_id), KEY (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS progress (
  user_id INT UNSIGNED NOT NULL,
  strand VARCHAR(10) NOT NULL,
  level TINYINT NOT NULL,
  activity TINYINT NOT NULL,
  best_score DECIMAL(5,2) NOT NULL DEFAULT 0,
  best_correct SMALLINT NOT NULL DEFAULT 0,
  total SMALLINT NOT NULL DEFAULT 0,
  tries SMALLINT NOT NULL DEFAULT 0,
  passed TINYINT(1) NOT NULL DEFAULT 0,
  passed_at DATETIME NULL,
  PRIMARY KEY (user_id, strand, level, activity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS certificates (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  type ENUM('level','readiness') NOT NULL,
  strand VARCHAR(10) NOT NULL DEFAULT '',
  level TINYINT NOT NULL DEFAULT 0,
  code VARCHAR(20) NOT NULL UNIQUE,
  issued_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY one_cert (user_id, type, strand, level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_tries (
  ip VARCHAR(45) NOT NULL,
  tried_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY (ip, tried_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS settings (
  k VARCHAR(60) PRIMARY KEY,
  v TEXT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- First admin account. Sign in with ALS ID ADM0001 (or username admin) and password ChangeMe123!
-- On the first sign-in the app asks for a new password, their own username and a 4-digit PIN.
INSERT IGNORE INTO users (role, als_id, username, password_hash, first_name, last_name, must_change)
VALUES ('admin', 'ADM0001', 'admin', '$2y$10$5ibaweDNw992pdkNA.J5IOGslbLCpwi6k08hUvbr6D0pcFgJvOTI.', 'System', 'Administrator', 1);
INSERT IGNORE INTO settings (k, v) VALUES ('division_name', 'Division of Bukidnon'), ('region_name', 'Region X - Northern Mindanao'), ('sds_name', ''), ('sds_title', 'Schools Division Superintendent');
