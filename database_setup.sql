-- -- Create database
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
CREATE TABLE faculties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Courses table
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES faculties(id) ON DELETE CASCADE
);

-- Course Units table
CREATE TABLE course_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Past Papers table
CREATE TABLE past_papers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_unit_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    year INT NOT NULL,
    semester ENUM('1', '2', 'recess') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    uploaded_by INT NOT NULL,
    downloads INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (course_unit_id) REFERENCES course_units(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id)
);

-- Downloads tracking
CREATE TABLE download_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    paper_id INT NOT NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (paper_id) REFERENCES past_papers(id) ON DELETE CASCADE
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
    'user@',
    'user'
    'user123'
);
INSERT INTO faculties (name, code, description) VALUES
('Faculty of Computing', 'FOC', 'Computer Science and IT'),
('Faculty of Engineering', 'FOE', 'Engineering Programs'),
('Faculty of Science', 'FOS', 'Natural Sciences');

-- Sample courses
INSERT INTO courses (faculty_id, name, code) VALUES
(1, 'Bachelor of Computer Science', 'BCS'),
(1, 'Bachelor of Information Technology', 'BIT'),
(2, 'Bachelor of Civil Engineering', 'BCE');

-- Sample course units
INSERT INTO course_units (course_id, name, code) VALUES
(1, 'Data Structures', 'CSC2101'),
(1, 'Database Systems', 'CSC2102'),
(2, 'Web Development', 'BIT2201');