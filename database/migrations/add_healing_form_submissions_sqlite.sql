CREATE TABLE IF NOT EXISTS healing_form_submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    full_name TEXT NOT NULL,
    date_of_birth TEXT NOT NULL,
    time_of_birth TEXT NULL,
    place_of_birth TEXT NULL,
    current_location TEXT NULL,
    mobile TEXT NOT NULL,
    email TEXT NULL,
    address TEXT NULL,
    aadhaar_number TEXT NOT NULL,
    aadhaar_front_path TEXT NOT NULL,
    aadhaar_back_path TEXT NOT NULL,
    issue_type TEXT NULL,
    issue_description TEXT NULL,
    current_picture_path TEXT NULL,
    declaration_accepted INTEGER NOT NULL DEFAULT 0,
    amount_paid NUMERIC NOT NULL CHECK(amount_paid > 0),
    transaction_id TEXT NULL,
    transaction_receipt_path TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_healing_form_submissions_mobile
ON healing_form_submissions (mobile);
