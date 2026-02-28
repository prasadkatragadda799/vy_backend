-- Run this if donations table already exists without doc columns (SQLite)
-- Run each statement separately if your SQLite version requires it.
ALTER TABLE donations ADD COLUMN aadhaar_front_path TEXT NULL;
ALTER TABLE donations ADD COLUMN aadhaar_back_path TEXT NULL;
ALTER TABLE donations ADD COLUMN transaction_rep_path TEXT NULL;
