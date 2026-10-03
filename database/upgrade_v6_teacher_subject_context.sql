USE learnloop;

/* Teacher can teach different subjects in different batches/semesters. */
CREATE TABLE IF NOT EXISTS tbl_teacher_teaching (
    id INT NOT NULL AUTO_INCREMENT,
    teacher_username VARCHAR(100) NOT NULL,
    batch_id INT NOT NULL,
    semester INT NOT NULL,
    subject VARCHAR(100) NOT NULL,
    active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_teacher_batch_sem_subject (teacher_username,batch_id,semester,subject),
    INDEX idx_teaching_teacher (teacher_username),
    INDEX idx_teaching_context (batch_id,semester),
    INDEX idx_teaching_subject (subject)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE tbl_trs MODIFY subject VARCHAR(100) NOT NULL DEFAULT '';
ALTER TABLE tbl_timetable ADD COLUMN teaching_assignment_id INT NULL AFTER batch_id;
CREATE INDEX idx_tt_teaching_assignment ON tbl_timetable(teaching_assignment_id);

/* Backfill teacher/subject combinations already used by the timetable. */
INSERT IGNORE INTO tbl_teacher_teaching(teacher_username,batch_id,semester,subject)
SELECT tt.teacher_username,tt.batch_id,b.semester,tt.subject
FROM tbl_timetable tt
JOIN tbl_batches b ON b.id=tt.batch_id;

/* Connect old timetable rows to their teaching assignment. */
UPDATE tbl_timetable tt
JOIN tbl_teacher_teaching ta
  ON ta.teacher_username=tt.teacher_username
 AND ta.batch_id=tt.batch_id
 AND ta.subject=tt.subject
JOIN tbl_batches b ON b.id=tt.batch_id
SET tt.teaching_assignment_id=ta.id
WHERE tt.teaching_assignment_id IS NULL;
