-- University Past Papers Portal Database
-- Drop existing database if it exists
DROP DATABASE IF EXISTS past_papers_portal;
CREATE DATABASE past_papers_portal;
USE past_papers_portal;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Faculties table
CREATE TABLE faculties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(10) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_code (code)
);

-- Courses table
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    faculty_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (faculty_id) REFERENCES faculties(id) ON DELETE CASCADE,
    INDEX idx_faculty (faculty_id),
    INDEX idx_code (code)
);

-- Course Units table
CREATE TABLE course_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    INDEX idx_course (course_id),
    INDEX idx_code (code)
);

-- Past Papers table
CREATE TABLE past_papers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_unit_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    year INT NOT NULL,
    semester ENUM('1', '2', '3', 'recess', 'supplementary') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(500) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(50) DEFAULT 'pdf',
    uploaded_by INT NOT NULL,
    downloads INT DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (course_unit_id) REFERENCES course_units(id) ON DELETE CASCADE,
    FOREIGN KEY (uploaded_by) REFERENCES users(id),
    INDEX idx_course_unit (course_unit_id),
    INDEX idx_year (year),
    INDEX idx_semester (semester),
    INDEX idx_uploaded_by (uploaded_by)
);

-- Download History table
CREATE TABLE download_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    paper_id INT NOT NULL,
    downloaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (paper_id) REFERENCES past_papers(id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_paper (paper_id),
    INDEX idx_downloaded_at (downloaded_at)
);

-- System Settings table
CREATE TABLE system_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    description TEXT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin account (password: admin123)
INSERT INTO users (full_name, email, username, password, role) 
VALUES (
    'System Administrator', 
    'admin@university.edu', 
    'admin', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- admin123
    'admin'
);

-- Insert sample user account (password: user123)
INSERT INTO users (full_name, email, username, password, role) 
VALUES (
    'Demo Student', 
    'student@university.edu', 
    'student', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- user123
    'user'
);

-- Insert sample faculties
INSERT INTO faculties (name, code, description) VALUES
('Faculty of Computing and Information Technology', 'FCIT', 'Computer Science, IT, and related programs'),
('Faculty of Engineering', 'FOE', 'Civil, Mechanical, Electrical, and other engineering programs'),
('Faculty of Science', 'FOS', 'Mathematics, Physics, Chemistry, Biology, and natural sciences'),
('Faculty of Business and Economics', 'FBE', 'Business administration, economics, and commerce programs'),
('Faculty of Arts and Social Sciences', 'FASS', 'Literature, history, sociology, and social science programs');

-- Insert sample courses
INSERT INTO courses (faculty_id, name, code, description) VALUES
(1, 'Bachelor of Computer Science', 'BCS', 'Comprehensive computer science program covering programming, algorithms, and software engineering'),
(1, 'Bachelor of Information Technology', 'BIT', 'IT-focused program covering systems administration, networking, and database management'),
(1, 'Bachelor of Software Engineering', 'BSE', 'Software development and engineering practices'),
(2, 'Bachelor of Civil Engineering', 'BCE', 'Civil engineering with focus on infrastructure and construction'),
(2, 'Bachelor of Mechanical Engineering', 'BME', 'Mechanical systems and manufacturing engineering'),
(3, 'Bachelor of Science in Mathematics', 'BSM', 'Pure and applied mathematics program'),
(4, 'Bachelor of Business Administration', 'BBA', 'General business administration and management'),
(5, 'Bachelor of Arts in English', 'BAE', 'English literature and language studies');

-- Insert sample course units
INSERT INTO course_units (course_id, name, code, description) VALUES
(1, 'Data Structures and Algorithms', 'CSC2101', 'Introduction to fundamental data structures and algorithmic problem solving'),
(1, 'Database Systems', 'CSC2102', 'Database design, SQL, and database management systems'),
(1, 'Software Engineering', 'CSC3101', 'Software development lifecycle and engineering practices'),
(1, 'Computer Networks', 'CSC3102', 'Network protocols, architecture, and security'),
(2, 'Web Development', 'BIT2201', 'HTML, CSS, JavaScript, and web application development'),
(2, 'System Administration', 'BIT2202', 'Operating systems, server management, and IT infrastructure'),
(3, 'Software Design Patterns', 'BSE3201', 'Design patterns and software architecture principles'),
(4, 'Structural Analysis', 'BCE2101', 'Analysis of structural systems and load calculations'),
(5, 'Thermodynamics', 'BME2101', 'Heat transfer and thermodynamic principles'),
(6, 'Calculus I', 'MTH1101', 'Differential and integral calculus'),
(7, 'Principles of Management', 'MGT2101', 'Management theory and organizational behavior'),
(8, 'Introduction to Literature', 'ENG1101', 'Literary analysis and critical thinking');

-- Insert system settings
INSERT INTO system_settings (setting_key, setting_value, description) VALUES
('site_name', 'University Past Papers Portal', 'Name of the portal'),
('max_file_size', '10485760', 'Maximum file upload size in bytes (10MB)'),
('allowed_file_types', 'pdf', 'Comma-separated list of allowed file extensions'),
('download_tracking', '1', 'Enable download tracking (1=yes, 0=no)'),
('registration_enabled', '1', 'Allow new user registration (1=yes, 0=no)');