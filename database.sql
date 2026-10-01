CREATE DATABASE IF NOT EXISTS student_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE student_db;

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  full_name VARCHAR(100) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS classes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(60) NOT NULL,
  section VARCHAR(10) DEFAULT NULL,
  teacher VARCHAR(100) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS students (
  id INT AUTO_INCREMENT PRIMARY KEY,
  roll_no VARCHAR(30) NOT NULL UNIQUE,
  first_name VARCHAR(60) NOT NULL,
  last_name VARCHAR(60) NOT NULL,
  gender ENUM('Male','Female','Other') NOT NULL,
  dob DATE DEFAULT NULL,
  email VARCHAR(120) DEFAULT NULL,
  phone VARCHAR(30) DEFAULT NULL,
  address TEXT,
  guardian_name VARCHAR(100) DEFAULT NULL,
  guardian_phone VARCHAR(30) DEFAULT NULL,
  class_id INT DEFAULT NULL,
  admission_date DATE DEFAULT NULL,
  status ENUM('active','inactive','graduated') NOT NULL DEFAULT 'active',
  photo VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (class_id), INDEX (status), INDEX (last_name),
  CONSTRAINT fk_student_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  student_id INT NOT NULL,
  att_date DATE NOT NULL,
  status ENUM('present','absent','late') NOT NULL,
  UNIQUE KEY uniq_day (student_id, att_date),
  CONSTRAINT fk_att_student FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO classes (name, section, teacher) VALUES
 ('Grade 6','A','Ms. Hina Raza'),('Grade 7','A','Mr. Usman Ali'),('Grade 8','B','Ms. Sana Iqbal');
-- The admin account (admin / Admin@123) is created automatically on first visit to login.php
