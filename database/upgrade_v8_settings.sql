USE learnloop;

/* Student registration number */
ALTER TABLE tbl_student
ADD COLUMN reg_number VARCHAR(50) NULL AFTER username;

/* Per-user portal settings */
CREATE TABLE IF NOT EXISTS tbl_user_settings (
    username VARCHAR(100) NOT NULL,
    role ENUM('student','teacher','admin') NOT NULL,
    theme ENUM('light','dark') NOT NULL DEFAULT 'light',
    profile_pic VARCHAR(255) NOT NULL DEFAULT '',
    email_notifications TINYINT(1) NOT NULL DEFAULT 1,
    assignment_notifications TINYINT(1) NOT NULL DEFAULT 1,
    attendance_notifications TINYINT(1) NOT NULL DEFAULT 1,
    show_academic_info TINYINT(1) NOT NULL DEFAULT 1,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (username, role)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
