CREATE DATABASE IF NOT EXISTS farmer_loan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE farmer_loan;

CREATE TABLE IF NOT EXISTS farmers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  nid VARCHAR(100) NOT NULL,
  phone VARCHAR(100) NOT NULL,
  address VARCHAR(255) NOT NULL,
  farm_size DECIMAL(10,2) NOT NULL DEFAULT 0,
  crops VARCHAR(255),
  nid_file VARCHAR(255) DEFAULT NULL,
  created_at DATETIME NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS loans (
  id INT AUTO_INCREMENT PRIMARY KEY,
  farmer_id INT NOT NULL,
  amount DECIMAL(12,2) NOT NULL,
  purpose VARCHAR(255),
  status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  created_at DATETIME NOT NULL,
  updated_at DATETIME NULL,
  FOREIGN KEY (farmer_id) REFERENCES farmers(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
