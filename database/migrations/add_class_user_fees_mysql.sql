-- Agreed/custom fee per user (aadhaar) per class (MySQL)
CREATE TABLE IF NOT EXISTS class_user_fees (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    aadhaar_number VARCHAR(20) NOT NULL,
    class_id INT UNSIGNED NOT NULL,
    agreed_fee DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_aadhaar_class (aadhaar_number, class_id),
    CONSTRAINT fk_class_user_fees_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE RESTRICT ON UPDATE CASCADE
);
CREATE INDEX idx_class_user_fees_aadhaar_class ON class_user_fees (aadhaar_number, class_id);
