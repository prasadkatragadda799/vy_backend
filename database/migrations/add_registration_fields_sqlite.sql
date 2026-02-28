-- Run if class_payments already exists without new registration fields (SQLite)
ALTER TABLE class_payments ADD COLUMN location TEXT NULL;
ALTER TABLE class_payments ADD COLUMN siblings_name TEXT NULL;
ALTER TABLE class_payments ADD COLUMN transaction_msg TEXT NULL;
ALTER TABLE class_payments ADD COLUMN aadhaar_doc_path TEXT NULL;
ALTER TABLE class_payments ADD COLUMN aadhaar_doc_back_path TEXT NULL;
