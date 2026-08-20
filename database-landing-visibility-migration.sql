USE game_dev_portfolio;

ALTER TABLE landing_content
  ADD COLUMN is_visible TINYINT(1) NOT NULL DEFAULT 1 AFTER content_json;
