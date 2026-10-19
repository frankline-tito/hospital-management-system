USE hms;
CREATE TABLE IF NOT EXISTS bookings(id INT AUTO_INCREMENT PRIMARY KEY,patient_id INT NOT NULL,dept_id INT NULL,service VARCHAR(120),booked_for DATETIME NOT NULL,status ENUM('booked','arrived','missed','cancelled') DEFAULT 'booked',notes VARCHAR(255),created_by INT,created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,FOREIGN KEY(patient_id) REFERENCES patients(id)) DEFAULT CHARSET=utf8mb4;
