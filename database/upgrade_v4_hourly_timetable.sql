USE learnloop;

ALTER TABLE tbl_student ADD COLUMN batch_id INT NULL AFTER course;
CREATE INDEX idx_student_batch ON tbl_student(batch_id);

CREATE TABLE IF NOT EXISTS tbl_batches (
 id INT NOT NULL AUTO_INCREMENT,
 batch_name VARCHAR(80) NOT NULL, course VARCHAR(80) NOT NULL, semester INT NOT NULL DEFAULT 1,
 active TINYINT(1) NOT NULL DEFAULT 1, created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY(id), UNIQUE KEY unique_batch(batch_name,course,semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tbl_timetable (
 id INT NOT NULL AUTO_INCREMENT, batch_id INT NOT NULL, day_of_week TINYINT NOT NULL, hour_no TINYINT NOT NULL,
 subject VARCHAR(100) NOT NULL, teacher_username VARCHAR(100) NOT NULL, start_time TIME NULL, end_time TIME NULL,
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, PRIMARY KEY(id),
 UNIQUE KEY unique_batch_day_hour(batch_id,day_of_week,hour_no), INDEX idx_tt_teacher(teacher_username), INDEX idx_tt_batch(batch_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS tbl_hourly_attendance (
 id INT NOT NULL AUTO_INCREMENT, timetable_id INT NOT NULL, student_username VARCHAR(100) NOT NULL, attendance_date DATE NOT NULL,
 status ENUM('Present','Absent') NOT NULL DEFAULT 'Present', marked_by VARCHAR(100) NOT NULL,
 marked_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP, PRIMARY KEY(id),
 UNIQUE KEY unique_hourly_attendance(timetable_id,student_username,attendance_date),
 INDEX idx_ha_student_date(student_username,attendance_date), INDEX idx_ha_teacher(marked_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO tbl_batches(batch_name,course,semester) VALUES
('BCA A','BCA',4),('BCA B','BCA',4),('BSC CS A','BSC Computer Science',4);
