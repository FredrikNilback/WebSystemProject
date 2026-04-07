USE nfv_irp_db;

CREATE TABLE asset (
	asset_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	asset_name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS `user` (
    user_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_role ENUM('administrator', 'responder', 'reporter') NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    user_email VARCHAR(70) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS browser_type (
browser_type_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
browser_name VARCHAR(24) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS page (
    page_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT, 
    page_name VARCHAR(24) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS incident_type (
incident_type_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
incident_type_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS incident (
incident_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
incident_severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
description TEXT NOT NULL,
occurance TIMESTAMP NOT NULL,
incident_type_id INT UNSIGNED NOT NULL,
FOREIGN KEY (incident_type_id)
	REFERENCES incident_type(incident_type_id)
);

CREATE TABLE IF NOT EXISTS current_event (
    event_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    event_date DATE NOT NULL,
    event_title VARCHAR(100) NOT NULL,
    event_text TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS incident (
	incident_id INT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	incident_severity ENUM('low', 'medium', 'high', 'critical') NOT NULL,
	description TEXT NOT NULL,
	occurance TIMESTAMP NOT NULL,
	incident_type_id INT UNSIGNED NOT NULL,
	FOREIGN KEY (incident_type_id)
	    REFERENCES incident_type(incident_type_id)
	    ON DELETE RESTRICT
	    ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS visit_tracking (
    tracking_id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    host_ip VARBINARY(16) NOT NULL, 
    visited_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    page_id INT UNSIGNED NOT NULL,
    browser_type_id INT UNSIGNED NOT NULL,
    FOREIGN KEY (page_id)
        REFERENCES page(page_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (browser_type_id)
        REFERENCES browser_type(browser_type_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS user_tracking (
	user_id INT UNSIGNED NOT NULL,
	tracking_id BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (user_id, tracking_id),
	FOREIGN KEY (user_id)
		REFERENCES `user`(user_id)
    	ON DELETE RESTRICT
		ON UPDATE CASCADE,
	FOREIGN KEY (tracking_id)
		REFERENCES visit_tracking(tracking_id)
    	ON DELETE CASCADE
    	ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS incident_update (
    incident_update_id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    status ENUM('pending', 'in progress', 'resolved') NOT NULL, 
    incident_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    update_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (incident_id)
        REFERENCES incident(incident_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (user_id)
        REFERENCES user(user_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS attachment (
	attachment_id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
	incident_update_id BIGINT UNSIGNED NOT NULL,
	attachment_file_path VARCHAR(255) NOT NULL,
    FOREIGN KEY(incident_update_id)
    	REFERENCES incident_update(incident_update_id)
    	ON DELETE CASCADE
    	ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS update_read (
	incident_update_id BIGINT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    read_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (incident_update_id, user_id),
    FOREIGN KEY (incident_update_id)
		REFERENCES incident_update(incident_update_id)
		ON DELETE CASCADE
		ON UPDATE CASCADE,
    FOREIGN KEY (user_id)
		REFERENCES `user`(user_id)
    	ON DELETE CASCADE
    	ON UPDATE RESTRICT
);

CREATE TABLE IF NOT EXISTS affected_assets (
    asset_id INT UNSIGNED NOT NULL,
    incident_id INT UNSIGNED NOT NULL,
	PRIMARY KEY (asset_id, incident_id),
    FOREIGN KEY (asset_id)
        REFERENCES asset(asset_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE,
    FOREIGN KEY (incident_id)
        REFERENCES incident(incident_id)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS comment (
    incident_update_id BIGINT UNSIGNED PRIMARY KEY,
    comment_text TEXT NOT NULL,
    FOREIGN KEY (incident_update_id)
    	REFERENCES incident_update(incident_update_id)
    	ON UPDATE CASCADE
    	ON DELETE CASCADE
);