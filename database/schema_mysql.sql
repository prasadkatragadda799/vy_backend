CREATE TABLE IF NOT EXISTS classes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_name VARCHAR(255) NOT NULL,
    total_fee DECIMAL(10,2) NOT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS class_payments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    mobile VARCHAR(20) NOT NULL,
    aadhaar_number VARCHAR(20) NOT NULL,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NULL,
    class_id INT UNSIGNED NOT NULL,
    preferred_time VARCHAR(100) NULL,
    location VARCHAR(255) NULL,
    siblings_name VARCHAR(255) NULL,
    message TEXT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(150) NULL,
    transaction_msg VARCHAR(500) NULL,
    aadhaar_doc_path VARCHAR(500) NULL,
    aadhaar_doc_back_path VARCHAR(500) NULL,
    transaction_receipt_path VARCHAR(500) NULL,
    payment_status ENUM('partial', 'paid') NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_class_payments_class_id
        FOREIGN KEY (class_id) REFERENCES classes(id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE INDEX idx_class_payments_mobile_class ON class_payments (mobile, class_id);
CREATE INDEX idx_class_payments_aadhaar_class ON class_payments (aadhaar_number, class_id);

CREATE TABLE IF NOT EXISTS donations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    mobile VARCHAR(20) NOT NULL,
    aadhaar_number VARCHAR(20) NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL,
    transaction_id VARCHAR(150) NULL,
    aadhaar_front_path VARCHAR(500) NULL,
    aadhaar_back_path VARCHAR(500) NULL,
    transaction_rep_path VARCHAR(500) NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX idx_donations_mobile ON donations (mobile);
CREATE INDEX idx_donations_aadhaar ON donations (aadhaar_number);
