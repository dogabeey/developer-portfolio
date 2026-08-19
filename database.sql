CREATE DATABASE IF NOT EXISTS game_dev_portfolio
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE game_dev_portfolio;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(80) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE projects (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(160) NOT NULL,
  role VARCHAR(160) NOT NULL,
  description TEXT NOT NULL,
  metadata_title VARCHAR(160) NULL,
  metadata_description TEXT NULL,
  metadata_thumbnail_url VARCHAR(2048) NULL,
  use_metadata_description TINYINT(1) NOT NULL DEFAULT 0,
  use_metadata_screenshots TINYINT(1) NOT NULL DEFAULT 0,
  project_url VARCHAR(2048) NULL,
  thumbnail_url VARCHAR(2048) NULL,
  cover_image_url VARCHAR(2048) NULL,
  sort_order INT NOT NULL DEFAULT 0,
  is_published TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX projects_public_order (is_published, sort_order, created_at)
) ENGINE=InnoDB;

CREATE TABLE project_screenshots (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  image_url VARCHAR(2048) NOT NULL,
  source ENUM('manual', 'metadata') NOT NULL DEFAULT 'manual',
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT screenshots_project_fk
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  INDEX screenshots_project_order (project_id, sort_order)
) ENGINE=InnoDB;
