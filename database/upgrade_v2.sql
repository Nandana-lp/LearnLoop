USE learnloop;

CREATE TABLE IF NOT EXISTS tbl_assignments (
  id INT(11) NOT NULL AUTO_INCREMENT,
  teacher_username VARCHAR(100) NOT NULL,
  title VARCHAR(150) NOT NULL,
  subject VARCHAR(50) NOT NULL,
  description TEXT NOT NULL,
  due_date DATE NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  INDEX(teacher_username)
);

ALTER TABLE tbl_student_assignments ADD COLUMN assignment_id INT(11) NULL AFTER username;
ALTER TABLE tbl_student_assignments ADD INDEX(assignment_id);

CREATE TABLE IF NOT EXISTS tbl_attendance (
  id INT(11) NOT NULL AUTO_INCREMENT,
  student_username VARCHAR(100) NOT NULL,
  attendance_date DATE NOT NULL,
  status ENUM('Present','Absent') NOT NULL DEFAULT 'Absent',
  marked_by VARCHAR(100) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY unique_student_date (student_username, attendance_date),
  INDEX(marked_by)
);
