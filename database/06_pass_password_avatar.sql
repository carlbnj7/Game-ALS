-- Game ALS v3.6: starter password printed on QR passes, and a profile picture for every account.
-- Run ONCE after 05. New installs: 01_schema.sql already has these columns.
ALTER TABLE users
  ADD COLUMN starter_pw VARCHAR(40) NULL AFTER password_hash,
  ADD COLUMN avatar VARCHAR(120) NULL AFTER starter_pw;
