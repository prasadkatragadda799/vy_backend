CREATE TABLE IF NOT EXISTS classes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    class_name TEXT NOT NULL,
    total_fee NUMERIC NOT NULL CHECK(total_fee > 0),
    is_active INTEGER NOT NULL DEFAULT 1,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS class_payments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    mobile TEXT NOT NULL,
    name TEXT NOT NULL,
    email TEXT NULL,
    class_id INTEGER NOT NULL,
    preferred_time TEXT NULL,
    location TEXT NULL,
    siblings_name TEXT NULL,
    message TEXT NULL,
    amount_paid NUMERIC NOT NULL CHECK(amount_paid > 0),
    transaction_id TEXT NULL,
    transaction_msg TEXT NULL,
    aadhaar_doc_path TEXT NULL,
    aadhaar_doc_back_path TEXT NULL,
    transaction_receipt_path TEXT NULL,
    payment_status TEXT NOT NULL CHECK(payment_status IN ('partial', 'paid')),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id)
);

CREATE INDEX IF NOT EXISTS idx_class_payments_mobile_class
ON class_payments (mobile, class_id);

CREATE TABLE IF NOT EXISTS donations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    mobile TEXT NOT NULL,
    amount_paid NUMERIC NOT NULL CHECK(amount_paid > 0),
    transaction_id TEXT NULL,
    aadhaar_front_path TEXT NULL,
    aadhaar_back_path TEXT NULL,
    transaction_rep_path TEXT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_donations_mobile
ON donations (mobile);
