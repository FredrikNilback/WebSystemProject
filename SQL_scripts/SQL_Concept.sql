CREATE DATABASE incident_report_DB;
USE incident_report_DB;
CREATE TABLE users (
	user_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(128) NOT NULL,
    email VARCHAR(256) NOT NULL UNIQUE,
    password_hash VARCHAR(512) NOT NULL,
    salt VARCHAR(16) NOT NULL,
    admin BOOLEAN DEFAULT False
);

CREATE TABLE incidents (
	incident_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(128) NOT NULL,
    description TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('Pending', 'In Progress', 'Done') NOT NULL DEFAULT 'Pending',
    priority ENUM('Low', 'Medium', 'High', 'Urgent') NOT NULL DEFAULT 'Medium'
    
);

CREATE TABLE incident_users (
    incident_id INT NOT NULL,
    user_id INT NOT NULL,
    role ENUM('reporter', 'assignee', 'watcher') NOT NULL,
    assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (incident_id, user_id, role),

    FOREIGN KEY (incident_id) REFERENCES incidents(incident_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);