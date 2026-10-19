USE hms;
CREATE TABLE facilities(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL UNIQUE,code VARCHAR(20),level VARCHAR(10));
ALTER TABLE users ADD facility_id INT NULL;
ALTER TABLE users MODIFY role ENUM('admin','receptionist','nurse','doctor','lab','pharmacist','cashier','records','chp') NOT NULL;
CREATE TABLE telemedicine_requests(id INT AUTO_INCREMENT PRIMARY KEY,facility_id INT NULL,patient_id INT NOT NULL,specialty VARCHAR(80) NOT NULL,summary TEXT NOT NULL,status ENUM('requested','accepted','completed','cancelled') DEFAULT 'requested',specialist_id INT NULL,scheduled_at DATETIME NULL,notes TEXT,created_by INT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(patient_id) REFERENCES patients(id));
CREATE TABLE chp_reports(id INT AUTO_INCREMENT PRIMARY KEY,facility_id INT NULL,chp_id INT NOT NULL,household_head VARCHAR(100) NOT NULL,village VARCHAR(80),visit_date DATE NOT NULL,members INT NOT NULL,pregnant INT DEFAULT 0,under5 INT DEFAULT 0,immunized INT DEFAULT 0,danger_signs TEXT,referral_needed TINYINT DEFAULT 0,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
CREATE TABLE messages(id INT AUTO_INCREMENT PRIMARY KEY,from_id INT NOT NULL,to_id INT NOT NULL,subject VARCHAR(150) NOT NULL,body TEXT NOT NULL,read_at DATETIME NULL,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP);
