-- Add status to donations (SQLite)
ALTER TABLE donations ADD COLUMN status TEXT NOT NULL DEFAULT 'pending';
-- SQLite CHECK may not enforce; ensure values are valid
CREATE INDEX IF NOT EXISTS idx_donations_status ON donations (status);
