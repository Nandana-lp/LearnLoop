USE learnloop;

/* Teachers no longer belong to one fixed semester. */
ALTER TABLE tbl_trs MODIFY semester INT NULL DEFAULT NULL;

/* Notes and assignments are published to a selected batch/semester. */
ALTER TABLE tbl_notes ADD COLUMN batch_id INT NULL AFTER teacher_username;
ALTER TABLE tbl_notes ADD COLUMN semester INT NULL AFTER batch_id;
ALTER TABLE tbl_notes ADD INDEX idx_notes_batch_semester (batch_id, semester);

ALTER TABLE tbl_assignments ADD COLUMN batch_id INT NULL AFTER teacher_username;
ALTER TABLE tbl_assignments ADD COLUMN semester INT NULL AFTER batch_id;
ALTER TABLE tbl_assignments ADD INDEX idx_assignments_batch_semester (batch_id, semester);

/* Optional course names used by the admin batch selector. */
/* Existing batch data is preserved. */


/* Keep one active BCA batch as the default structure while preserving old rows for history. */
INSERT IGNORE INTO tbl_batches(batch_name,course,semester) VALUES('BCA','BCA',4);
UPDATE tbl_batches SET active=0 WHERE NOT (batch_name='BCA' AND course='BCA' AND semester=4);

