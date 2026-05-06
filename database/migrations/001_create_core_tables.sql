CREATE TABLE IF NOT EXISTS paper_references (
  id VARCHAR(64) PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  authors VARCHAR(512) NOT NULL,
  publication_year SMALLINT UNSIGNED NOT NULL,
  journal VARCHAR(255) NOT NULL,
  volume_number VARCHAR(32) NOT NULL,
  issue_number VARCHAR(32) NOT NULL,
  pages VARCHAR(32) NOT NULL,
  doi VARCHAR(120) NOT NULL,
  doi_url VARCHAR(255) NOT NULL,
  citation TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS technology_catalog (
  id VARCHAR(64) PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  frequency VARCHAR(180) NOT NULL,
  range_meters INT UNSIGNED NOT NULL,
  network_size INT UNSIGNED NOT NULL,
  power_profile VARCHAR(80) NOT NULL,
  installation_profile TEXT NOT NULL,
  strengths JSON NOT NULL,
  tradeoffs JSON NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CHECK (JSON_VALID(strengths)),
  CHECK (JSON_VALID(tradeoffs))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS application_catalog (
  id VARCHAR(64) PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  paper_signal TEXT NOT NULL,
  design_focus TEXT NOT NULL,
  key_devices JSON NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CHECK (JSON_VALID(key_devices))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS challenge_catalog (
  id VARCHAR(64) PRIMARY KEY,
  name VARCHAR(180) NOT NULL,
  severity ENUM('Low', 'Medium', 'High') NOT NULL,
  paper_signal TEXT NOT NULL,
  mitigation TEXT NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_challenge_severity (severity)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS control_catalog (
  id VARCHAR(64) PRIMARY KEY,
  name VARCHAR(200) NOT NULL,
  category VARCHAR(120) NOT NULL,
  weight TINYINT UNSIGNED NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_control_category (category),
  INDEX idx_control_weight (weight)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS challenge_control_map (
  challenge_id VARCHAR(64) NOT NULL,
  control_id VARCHAR(64) NOT NULL,
  PRIMARY KEY (challenge_id, control_id),
  CONSTRAINT fk_map_challenge
    FOREIGN KEY (challenge_id) REFERENCES challenge_catalog(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_map_control
    FOREIGN KEY (control_id) REFERENCES control_catalog(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS design_assessments (
  id CHAR(36) PRIMARY KEY,
  project_name VARCHAR(180) NOT NULL,
  primary_application VARCHAR(80) NOT NULL,
  home_size VARCHAR(40) NOT NULL,
  node_count INT UNSIGNED NOT NULL,
  topology VARCHAR(40) NOT NULL,
  score TINYINT UNSIGNED NOT NULL,
  readiness VARCHAR(80) NOT NULL,
  risk_tier VARCHAR(40) NOT NULL,
  selected_controls JSON NOT NULL,
  result_payload JSON NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_design_created (created_at),
  INDEX idx_design_risk (risk_tier),
  CHECK (JSON_VALID(selected_controls)),
  CHECK (JSON_VALID(result_payload))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS audit_events (
  id CHAR(36) PRIMARY KEY,
  event_name VARCHAR(120) NOT NULL,
  payload JSON NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_audit_event_name (event_name),
  INDEX idx_audit_created (created_at),
  CHECK (JSON_VALID(payload))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

