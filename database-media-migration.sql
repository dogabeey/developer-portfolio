USE game_dev_portfolio;

ALTER TABLE projects
  ADD COLUMN thumbnail_url VARCHAR(2048) NULL AFTER project_url;

CREATE TABLE project_screenshots (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  project_id INT UNSIGNED NOT NULL,
  image_url VARCHAR(2048) NOT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT screenshots_project_fk
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
  INDEX screenshots_project_order (project_id, sort_order)
) ENGINE=InnoDB;
