-- Game ALS v3: ALS IDs, own username and 4-digit PIN, QR sign-in passes, municipalities and districts.
-- UPGRADE ONLY: run this ONCE on a database made with Game ALS v1 or v2.
-- New installs do NOT need it: 01_schema.sql already has all of this.

ALTER TABLE users
  ADD COLUMN als_id VARCHAR(20) NULL AFTER role,
  ADD COLUMN pin_hash VARCHAR(255) NULL AFTER password_hash,
  ADD COLUMN pin_fails TINYINT NOT NULL DEFAULT 0 AFTER pin_hash,
  ADD COLUMN setup_done TINYINT(1) NOT NULL DEFAULT 0 AFTER must_change,
  ADD COLUMN qr_token CHAR(40) NULL AFTER setup_done,
  ADD UNIQUE KEY uq_als_id (als_id),
  ADD UNIQUE KEY uq_qr_token (qr_token);

-- Give every account an ALS ID: ALS for learners, TCH for teachers, ADM for admins (e.g. ALS202600012).
UPDATE users SET als_id = CONCAT(CASE role WHEN 'learner' THEN 'ALS' WHEN 'teacher' THEN 'TCH' ELSE 'ADM' END, YEAR(created_at), LPAD(id, 5, '0'))
WHERE als_id IS NULL;

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

ALTER TABLE clcs ADD COLUMN district_id INT UNSIGNED NULL AFTER district, ADD KEY (district_id);

-- Move the municipality and district names already typed on CLCs into the new lists.
INSERT IGNORE INTO municipalities (name) SELECT DISTINCT TRIM(municipality) FROM clcs WHERE TRIM(municipality) <> '';
INSERT IGNORE INTO districts (municipality_id, name)
  SELECT DISTINCT m.id, IF(TRIM(c.district) = '', 'Main district', TRIM(c.district)) FROM clcs c JOIN municipalities m ON BINARY m.name = BINARY TRIM(c.municipality);
UPDATE clcs c JOIN municipalities m ON BINARY m.name = BINARY TRIM(c.municipality)
  JOIN districts d ON d.municipality_id = m.id AND BINARY d.name = BINARY IF(TRIM(c.district) = '', 'Main district', TRIM(c.district))
  SET c.district_id = d.id;
