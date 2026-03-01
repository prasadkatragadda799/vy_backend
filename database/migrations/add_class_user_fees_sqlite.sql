-- Agreed/custom fee per user (aadhaar) per class (SQLite)
CREATE TABLE IF NOT EXISTS class_user_fees (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    aadhaar_number TEXT NOT NULL,
    class_id INTEGER NOT NULL,
    agreed_fee NUMERIC NOT NULL CHECK(agreed_fee > 0),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(aadhaar_number, class_id),
    FOREIGN KEY (class_id) REFERENCES classes(id)
);
CREATE INDEX IF NOT EXISTS idx_class_user_fees_aadhaar_class ON class_user_fees (aadhaar_number, class_id);
