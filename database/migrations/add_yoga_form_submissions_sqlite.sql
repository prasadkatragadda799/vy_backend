CREATE TABLE IF NOT EXISTS yoga_form_submissions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    author_name TEXT NOT NULL,
    father_or_mother_name TEXT NOT NULL,
    course_name TEXT NOT NULL,
    year_of_learning TEXT NULL,
    qualification TEXT NOT NULL,
    previous_course TEXT NULL,
    sibling_details TEXT NULL,
    age_or_birth_date TEXT NOT NULL,
    location TEXT NOT NULL,
    aadhaar_front_path TEXT NOT NULL,
    aadhaar_back_path TEXT NOT NULL,
    mentor_name TEXT NULL,
    mentor_occupation TEXT NULL,
    mentor_phone TEXT NULL,
    referrer_name TEXT NULL,
    referrer_phone TEXT NULL,
    referrer_occupation TEXT NULL,
    another_referrer_name TEXT NULL,
    another_referrer_phone TEXT NULL,
    another_referrer_occupation TEXT NULL,
    amount_paid NUMERIC NOT NULL CHECK(amount_paid > 0),
    transaction_id TEXT NULL,
    transaction_receipt_path TEXT NOT NULL,
    additional_message TEXT NULL,
    mobile TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_yoga_form_submissions_mobile
ON yoga_form_submissions (mobile);
