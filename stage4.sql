USE hms;
ALTER TABLE lab_tests ADD price DECIMAL(10,2) DEFAULT 0;
ALTER TABLE drugs ADD price DECIMAL(10,2) DEFAULT 0;
ALTER TABLE invoice_items ADD source VARCHAR(10) NULL, ADD ref_id INT NULL;
ALTER TABLE visits MODIFY status ENUM('registered','triage','consultation','lab','pharmacy','billing','admitted','done') DEFAULT 'registered';
CREATE TABLE wards(id INT AUTO_INCREMENT PRIMARY KEY,name VARCHAR(60) NOT NULL UNIQUE,daily_rate DECIMAL(10,2) DEFAULT 0);
CREATE TABLE beds(id INT AUTO_INCREMENT PRIMARY KEY,ward_id INT NOT NULL,bed_no VARCHAR(10) NOT NULL,UNIQUE(ward_id,bed_no),FOREIGN KEY(ward_id) REFERENCES wards(id));
CREATE TABLE admissions(id INT AUTO_INCREMENT PRIMARY KEY,patient_id INT NOT NULL,visit_id INT NOT NULL,bed_id INT NOT NULL,reason VARCHAR(255),admitted_at DATETIME DEFAULT CURRENT_TIMESTAMP,discharged_at DATETIME NULL,discharge_note TEXT,admitted_by INT,FOREIGN KEY(patient_id) REFERENCES patients(id),FOREIGN KEY(visit_id) REFERENCES visits(id),FOREIGN KEY(bed_id) REFERENCES beds(id));
CREATE TABLE referrals(id INT AUTO_INCREMENT PRIMARY KEY,patient_id INT NOT NULL,direction ENUM('out','in') NOT NULL,facility VARCHAR(120) NOT NULL,reason TEXT NOT NULL,urgency ENUM('routine','urgent','emergency') DEFAULT 'routine',status ENUM('sent','received','closed') DEFAULT 'sent',created_by INT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(patient_id) REFERENCES patients(id));
