CREATE DATABASE IF NOT EXISTS bcc_schedule DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bcc_schedule;

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- WORKING TABLES (Admin Workspace)
CREATE TABLE IF NOT EXISTS schedule_headers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    header_name VARCHAR(100) NOT NULL,
    position INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS schedule_rows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    row_order INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS schedule_cells (
    id INT AUTO_INCREMENT PRIMARY KEY,
    row_id INT NOT NULL,
    header_id INT NOT NULL,
    content TEXT,
    bg_color VARCHAR(20) DEFAULT NULL,
    font_weight VARCHAR(20) DEFAULT 'normal',
    text_align VARCHAR(20) DEFAULT 'left',
    row_span INT NOT NULL DEFAULT 1,
    col_span INT NOT NULL DEFAULT 1,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (row_id) REFERENCES schedule_rows(id) ON DELETE CASCADE,
    FOREIGN KEY (header_id) REFERENCES schedule_headers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_cell (row_id, header_id)
) ENGINE=InnoDB;

-- PUBLISHED TABLES (User View Portal)
CREATE TABLE IF NOT EXISTS published_headers (
    id INT,
    header_name VARCHAR(100) NOT NULL,
    position INT NOT NULL,
    version_id INT NOT NULL,
    PRIMARY KEY (id, version_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS published_rows (
    id INT,
    row_order INT NOT NULL,
    version_id INT NOT NULL,
    PRIMARY KEY (id, version_id)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS published_cells (
    id INT AUTO_INCREMENT PRIMARY KEY,
    row_id INT NOT NULL,
    header_id INT NOT NULL,
    content TEXT,
    bg_color VARCHAR(20),
    font_weight VARCHAR(20),
    text_align VARCHAR(20),
    row_span INT,
    col_span INT,
    version_id INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS published_schedule (
    id INT AUTO_INCREMENT PRIMARY KEY,
    published_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    version INT NOT NULL DEFAULT 1
) ENGINE=InnoDB;

-- Default Admin (password: admin123)
INSERT IGNORE INTO admins (username, password) VALUES
('admin', '$2y$10$XjsRm86wo1f/72GYgVb3rux18vu9sa.C//qbum8S/jJEKRp5hjPRy');

-- Sample Data (Admin Workspace)
INSERT IGNORE INTO schedule_headers (id, header_name, position) VALUES
(1, 'Time / Day', 1), (2, 'Monday', 2), (3, 'Tuesday', 3), (4, 'Wednesday', 4), (5, 'Thursday', 5), (6, 'Friday', 6);

INSERT IGNORE INTO schedule_rows (id, row_order) VALUES (1, 1), (2, 2), (3, 3);

INSERT IGNORE INTO schedule_cells (row_id, header_id, content) VALUES
(1, 1, '07:30 AM - 09:00 AM'),
(2, 1, '09:00 AM - 10:30 AM'),
(3, 1, '10:30 AM - 12:00 PM'),
(1, 2, 'BSIT-3A\nNetwork Security\nRm 204'),
(2, 3, 'BSIT-2B\nWeb Dev 1\nRm 301');

-- Seed Published (Version 1)
INSERT IGNORE INTO published_schedule (id, version) VALUES (1, 1);
INSERT INTO published_headers (id, header_name, position, version_id) SELECT id, header_name, position, 1 FROM schedule_headers;
INSERT INTO published_rows (id, row_order, version_id) SELECT id, row_order, 1 FROM schedule_rows;
INSERT INTO published_cells (row_id, header_id, content, bg_color, font_weight, text_align, row_span, col_span, version_id) 
SELECT row_id, header_id, content, bg_color, font_weight, text_align, row_span, col_span, 1 FROM schedule_cells;

-- LDRRMO Crisis Reports Table
CREATE TABLE IF NOT EXISTS ldrrmo_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    name VARCHAR(255),
    phone VARCHAR(20) NOT NULL,
    category VARCHAR(100) NOT NULL,
    message TEXT NOT NULL,
    status VARCHAR(50) DEFAULT 'Pending'
) ENGINE=InnoDB;
