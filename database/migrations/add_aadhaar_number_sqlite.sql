-- Add aadhaar_number to class_payments and donations (SQLite)
ALTER TABLE class_payments ADD COLUMN aadhaar_number TEXT NULL;
UPDATE class_payments SET aadhaar_number = mobile WHERE aadhaar_number IS NULL;
-- SQLite does not support ALTER COLUMN NOT NULL easily; leave nullable for old rows
CREATE INDEX IF NOT EXISTS idx_class_payments_aadhaar_class ON class_payments (aadhaar_number, class_id);

ALTER TABLE donations ADD COLUMN aadhaar_number TEXT NULL;
UPDATE donations SET aadhaar_number = mobile WHERE aadhaar_number IS NULL;
CREATE INDEX IF NOT EXISTS idx_donations_aadhaar ON donations (aadhaar_number);
