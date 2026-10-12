-- Rental Evidence: database schema (MySQL 8 / MariaDB 10.4+)
CREATE DATABASE IF NOT EXISTS rental_evidence
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rental_evidence;

CREATE TABLE users (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(100)  NOT NULL,
    email         VARCHAR(190)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE properties (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id       INT UNSIGNED  NOT NULL,
    nickname      VARCHAR(100)  NOT NULL,
    address       VARCHAR(255)  NOT NULL,
    landlord_name VARCHAR(100)  NULL,
    move_in_date  DATE          NULL,
    move_out_date DATE          NULL,
    notes         TEXT          NULL,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_properties_user FOREIGN KEY (user_id)
        REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE photos (
    id            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    property_id   INT UNSIGNED  NOT NULL,
    room          VARCHAR(60)   NOT NULL,
    stage         ENUM('before','after') NOT NULL,
    description   TEXT          NULL,
    stored_name   VARCHAR(80)   NOT NULL UNIQUE,  -- random file name on disk
    original_name VARCHAR(255)  NOT NULL,
    mime_type     VARCHAR(40)   NOT NULL,
    size_bytes    INT UNSIGNED  NOT NULL,
    sha256        CHAR(64)      NOT NULL,         -- fingerprint: shows the file wasn't altered later
    taken_at      DATETIME      NULL,             -- from the photo's EXIF data when available
    uploaded_at   DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_photos_property FOREIGN KEY (property_id)
        REFERENCES properties(id) ON DELETE CASCADE,
    INDEX idx_photos_property_room (property_id, room, stage)
) ENGINE=InnoDB;
