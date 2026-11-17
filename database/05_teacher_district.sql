-- Game ALS v3.2: each teacher belongs to a district (chosen by the admin). Run ONCE after 04.
ALTER TABLE users ADD COLUMN district_id INT UNSIGNED NULL AFTER clc_id, ADD KEY (district_id);
