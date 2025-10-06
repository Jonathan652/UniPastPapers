-- Create database
CREATE DATABASE IF NOT EXISTS user_system;
USE user_system;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Insert default admin account
-- Password: admin123
INSERT INTO users (full_name, email, username, password, role) 
VALUES (
    'System Administrator', 
    'admin@system.com', 
    'admin', 
    'admin123',
    'admin'
);

-- Insert sample user account
-- Password: user123
INSERT INTO users (full_name, email, username, password, role) 
VALUES (
    'Demo User', 
    'user@demo.com', 
    'user', 
    'user123',
    'user'
);