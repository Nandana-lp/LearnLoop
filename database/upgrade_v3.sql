
USE learnloop;

CREATE TABLE IF NOT EXISTS tbl_notes (
    id INT(11) NOT NULL AUTO_INCREMENT,
    teacher_username VARCHAR(100) NOT NULL,
    title VARCHAR(150) NOT NULL,
    subject VARCHAR(100) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    INDEX idx_notes_teacher (teacher_username),
    INDEX idx_notes_subject (subject)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- The existing attendance table already supports Present/Absent.
-- This index makes date-based calendar queries faster.
ALTER TABLE tbl_attendance ADD INDEX idx_attendance_student_date (student_username, attendance_date);
