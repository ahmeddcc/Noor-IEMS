-- 001_create_login_attempts.sql

CREATE TABLE IF NOT EXISTS login_attempts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    ip_address TEXT NOT NULL,
    attempts INTEGER DEFAULT 1,
    last_attempt_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    locked_until DATETIME DEFAULT NULL
);

CREATE INDEX IF NOT EXISTS idx_ip_address ON login_attempts(ip_address);
