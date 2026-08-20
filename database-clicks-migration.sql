USE game_dev_portfolio;

CREATE TABLE project_clicks (
  project_id INT UNSIGNED NOT NULL,
  ip_hash CHAR(64) NOT NULL,
  first_clicked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (project_id, ip_hash),
  CONSTRAINT clicks_project_fk
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE
) ENGINE=InnoDB;
