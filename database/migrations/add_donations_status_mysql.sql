-- Add status to donations (MySQL)
ALTER TABLE donations ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'pending' AFTER transaction_id;
CREATE INDEX idx_donations_status ON donations (status);
