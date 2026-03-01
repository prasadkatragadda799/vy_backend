-- Add aadhaar_number to class_payments and donations (MySQL)
ALTER TABLE class_payments ADD COLUMN aadhaar_number VARCHAR(20) NULL AFTER mobile;
UPDATE class_payments SET aadhaar_number = mobile WHERE aadhaar_number IS NULL;
ALTER TABLE class_payments MODIFY COLUMN aadhaar_number VARCHAR(20) NOT NULL;
CREATE INDEX idx_class_payments_aadhaar_class ON class_payments (aadhaar_number, class_id);

ALTER TABLE donations ADD COLUMN aadhaar_number VARCHAR(20) NULL AFTER mobile;
UPDATE donations SET aadhaar_number = mobile WHERE aadhaar_number IS NULL;
ALTER TABLE donations MODIFY COLUMN aadhaar_number VARCHAR(20) NOT NULL;
CREATE INDEX idx_donations_aadhaar ON donations (aadhaar_number);
