-- Run if class_payments already exists without new registration fields (MySQL)
ALTER TABLE class_payments
  ADD COLUMN location VARCHAR(255) NULL AFTER preferred_time,
  ADD COLUMN siblings_name VARCHAR(255) NULL AFTER location,
  ADD COLUMN transaction_msg VARCHAR(500) NULL AFTER transaction_id,
  ADD COLUMN aadhaar_doc_path VARCHAR(500) NULL AFTER transaction_msg,
  ADD COLUMN aadhaar_doc_back_path VARCHAR(500) NULL AFTER aadhaar_doc_path;
