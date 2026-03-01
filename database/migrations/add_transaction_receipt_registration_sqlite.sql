-- Add transaction receipt image path to class_payments (SQLite)
ALTER TABLE class_payments ADD COLUMN transaction_receipt_path TEXT NULL;
