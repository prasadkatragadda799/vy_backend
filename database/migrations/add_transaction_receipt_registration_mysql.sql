-- Add transaction receipt image path to class_payments (MySQL)
ALTER TABLE class_payments ADD COLUMN transaction_receipt_path VARCHAR(500) NULL AFTER aadhaar_doc_back_path;
