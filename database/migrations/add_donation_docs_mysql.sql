-- Run this if donations table already exists without doc columns (MySQL)
ALTER TABLE donations
  ADD COLUMN aadhaar_front_path VARCHAR(500) NULL AFTER transaction_id,
  ADD COLUMN aadhaar_back_path VARCHAR(500) NULL AFTER aadhaar_front_path,
  ADD COLUMN transaction_rep_path VARCHAR(500) NULL AFTER aadhaar_back_path;
