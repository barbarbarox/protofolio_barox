-- ============================================
-- Database Portfolio - Setup Script (SECURED)
-- ============================================

CREATE DATABASE IF NOT EXISTS portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE portfolio_db;

-- Tabel Admin
CREATE TABLE IF NOT EXISTS admin (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel Login Attempts (Rate Limiting)
CREATE TABLE IF NOT EXISTS login_attempts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    username VARCHAR(50),
    attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_successful TINYINT(1) DEFAULT 0,
    INDEX idx_ip_time (ip_address, attempted_at),
    INDEX idx_username_time (username, attempted_at)
) ENGINE=InnoDB;

-- Tabel Contact Rate Limit
CREATE TABLE IF NOT EXISTS contact_rate_limit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ip_address VARCHAR(45) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ip_time (ip_address, created_at)
) ENGINE=InnoDB;

-- Tabel Profile
CREATE TABLE IF NOT EXISTS profile (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    title VARCHAR(150),
    bio TEXT,
    avatar VARCHAR(255) DEFAULT 'default-avatar.png',
    email VARCHAR(100),
    phone VARCHAR(20),
    location VARCHAR(100),
    github VARCHAR(255),
    linkedin VARCHAR(255),
    instagram VARCHAR(255),
    cv_link VARCHAR(255),
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel Projects
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    image VARCHAR(255),
    category VARCHAR(50),
    tech_stack VARCHAR(255),
    link VARCHAR(255),
    github_link VARCHAR(255),
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabel Skills
CREATE TABLE IF NOT EXISTS skills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    percentage INT DEFAULT 0,
    category VARCHAR(50) DEFAULT 'Technical',
    icon VARCHAR(50),
    sort_order INT DEFAULT 0
) ENGINE=InnoDB;

-- Tabel Messages
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================
-- Seed Data
-- ============================================

-- Secure Admin (username: portfolioadmin / password: S3cur3P@ss!2026)
INSERT INTO admin (username, password, email) VALUES 
('portfolioadmin', '$2y$10$IzPqrUFXsKCFZRlRHCaiZe15lGOx3.DCc0ZQ0awPpRl7MZuAANTL.', 'admin@portfolio.com');

-- Default Profile
INSERT INTO profile (name, title, bio, email, github, linkedin, instagram) VALUES 
('John Doe', 'Full Stack Developer & UI/UX Designer', 
'Saya adalah seorang developer yang passionate dalam membangun aplikasi web modern dan interaktif. Dengan pengalaman lebih dari 5 tahun, saya spesialisasi dalam mengembangkan solusi digital yang elegan dan efisien.',
'john@example.com', 'https://github.com', 'https://linkedin.com', 'https://instagram.com');

-- Default Skills
INSERT INTO skills (name, percentage, category, icon, sort_order) VALUES 
('HTML & CSS', 95, 'Frontend', 'fa-html5', 1),
('JavaScript', 90, 'Frontend', 'fa-js', 2),
('PHP', 85, 'Backend', 'fa-php', 3),
('MySQL', 80, 'Backend', 'fa-database', 4),
('React', 75, 'Frontend', 'fa-react', 5),
('Node.js', 70, 'Backend', 'fa-node-js', 6),
('Python', 65, 'Backend', 'fa-python', 7),
('Git', 85, 'Tools', 'fa-git-alt', 8);

-- Default Projects
INSERT INTO projects (title, description, image, category, tech_stack, link, github_link, is_featured) VALUES 
('E-Commerce Platform', 'Platform e-commerce modern dengan fitur keranjang belanja, pembayaran, dan manajemen produk.', 'project1.jpg', 'Web App', 'PHP, MySQL, JavaScript', '#', '#', 1),
('Task Management App', 'Aplikasi manajemen tugas real-time dengan fitur drag & drop dan kolaborasi tim.', 'project2.jpg', 'Web App', 'React, Node.js, MongoDB', '#', '#', 1),
('Portfolio Website', 'Website portfolio responsif dengan tema gelap dan animasi modern.', 'project3.jpg', 'Website', 'HTML, CSS, JavaScript', '#', '#', 1),
('Weather Dashboard', 'Dashboard cuaca interaktif dengan visualisasi data dan prediksi 7 hari.', 'project4.jpg', 'Web App', 'Vue.js, API, Chart.js', '#', '#', 0),
('Blog CMS', 'Content Management System untuk blog dengan editor WYSIWYG dan sistem komentar.', 'project5.jpg', 'Web App', 'Laravel, MySQL, Vue.js', '#', '#', 0),
('Mobile Banking UI', 'Desain UI/UX untuk aplikasi mobile banking dengan dark mode.', 'project6.jpg', 'UI/UX', 'Figma, Adobe XD', '#', '#', 0);
