USE game_dev_portfolio;

ALTER TABLE projects
  ADD COLUMN metadata_title VARCHAR(160) NULL AFTER description,
  ADD COLUMN metadata_thumbnail_url VARCHAR(2048) NULL AFTER metadata_description;
