USE game_dev_portfolio;

ALTER TABLE projects
  ADD COLUMN metadata_description TEXT NULL AFTER description,
  ADD COLUMN use_metadata_description TINYINT(1) NOT NULL DEFAULT 0 AFTER metadata_description,
  ADD COLUMN use_metadata_screenshots TINYINT(1) NOT NULL DEFAULT 0 AFTER use_metadata_description;

ALTER TABLE project_screenshots
  ADD COLUMN source ENUM('manual', 'metadata') NOT NULL DEFAULT 'manual' AFTER image_url;
