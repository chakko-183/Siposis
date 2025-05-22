-- Create database
CREATE DATABASE IF NOT EXISTS osis_registration;
USE osis_registration;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    nis VARCHAR(20) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'student') NOT NULL DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Registrations table
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    nis VARCHAR(20) NOT NULL,
    class VARCHAR(20) NOT NULL,
    gender ENUM('male', 'female') NOT NULL,
    birth_place VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    last_semester_grade DECIMAL(5,2) NOT NULL,
    achievements TEXT,
    has_organization_experience BOOLEAN DEFAULT FALSE,
    organization_name VARCHAR(100),
    organization_position VARCHAR(100),
    organization_year VARCHAR(20),
    organization_description TEXT,
    reason TEXT NOT NULL,
    vision TEXT NOT NULL,
    mission TEXT NOT NULL,
    status ENUM('pending', 'verified', 'interview_scheduled', 'interview_completed', 'selected', 'not_selected') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Documents table
CREATE TABLE IF NOT EXISTS documents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,
    type ENUM('parent_permission', 'photo', 'report_card', 'certificate') NOT NULL,
    filename VARCHAR(255) NOT NULL,
    original_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    file_type VARCHAR(100) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE
);

-- Schedules table
CREATE TABLE IF NOT EXISTS schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    description TEXT,
    date DATE NOT NULL,
    time VARCHAR(50) NOT NULL,
    location VARCHAR(100) NOT NULL,
    type ENUM('general', 'interview', 'presentation') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Interview schedules table
CREATE TABLE IF NOT EXISTS interview_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    registration_id INT NOT NULL,
    user_id INT NOT NULL,
    time_slot VARCHAR(50) NOT NULL,
    status ENUM('scheduled', 'completed', 'cancelled') DEFAULT 'scheduled',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (schedule_id) REFERENCES schedules(id) ON DELETE CASCADE,
    FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Evaluations table
CREATE TABLE IF NOT EXISTS evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    registration_id INT NOT NULL,
    evaluator_id INT NOT NULL,
    leadership_score INT NOT NULL,
    communication_score INT NOT NULL,
    creativity_score INT NOT NULL,
    teamwork_score INT NOT NULL,
    knowledge_score INT NOT NULL,
    total_score INT NOT NULL,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (registration_id) REFERENCES registrations(id) ON DELETE CASCADE,
    FOREIGN KEY (evaluator_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Insert default admin user
INSERT INTO users (name, nis, email, password, role) VALUES 
('Admin OSIS', 'ADMIN001', 'admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Insert demo student user
INSERT INTO users (name, nis, email, password, role) VALUES 
('Siswa Demo', '2021001234', 'siswa@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

-- Insert sample schedules
INSERT INTO schedules (title, description, date, time, location, type) VALUES
('Tes Tulis', 'Tes pengetahuan umum dan keorganisasian', '2024-05-24', '08:00 - 10:00', 'Ruang Multimedia', 'general'),
('Wawancara - Hari 1', 'Wawancara calon pengurus OSIS', '2024-05-25', '09:00 - 12:00', 'Ruang OSIS', 'interview'),
('Wawancara - Hari 2', 'Wawancara calon pengurus OSIS', '2024-05-26', '09:00 - 12:00', 'Ruang OSIS', 'interview'),
('Presentasi', 'Presentasi visi dan misi calon pengurus OSIS', '2024-05-27', '13:00 - 15:00', 'Aula Sekolah', 'presentation');

-- Insert sample notifications
INSERT INTO notifications (user_id, title, message, is_read) VALUES
(NULL, 'Pendaftaran OSIS Dibuka', 'Pendaftaran calon pengurus OSIS SMKN 2 Sampang telah dibuka. Silakan daftar melalui sistem.', FALSE),
(NULL, 'Jadwal Tes Tulis', 'Tes tulis akan dilaksanakan pada tanggal 24 Mei 2024 pukul 08:00 - 10:00 di Ruang Multimedia.', FALSE);
