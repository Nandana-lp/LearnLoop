USE learnloop;

/* ---------------------------------------------------------
   Cohort/batch becomes the student's permanent programme cohort.
   Semester is no longer stored on the batch itself.
   --------------------------------------------------------- */
ALTER TABLE tbl_batches ADD COLUMN admission_year INT NULL AFTER course;
ALTER TABLE tbl_batches ADD COLUMN duration_years INT NOT NULL DEFAULT 3 AFTER admission_year;

/* Existing batches: use the current year as the cohort start if unknown. */
UPDATE tbl_batches
SET admission_year = COALESCE(admission_year, YEAR(CURDATE())),
    duration_years = CASE
        WHEN course = 'BSC Computer Science' THEN 4
        ELSE 3
    END
WHERE admission_year IS NULL OR admission_year = 0;

/* Timetable must be semester-specific for the same cohort. */
ALTER TABLE tbl_timetable ADD COLUMN semester INT NOT NULL DEFAULT 1 AFTER batch_id;
UPDATE tbl_timetable tt
LEFT JOIN tbl_teacher_teaching ta ON ta.id = tt.teaching_assignment_id
SET tt.semester = COALESCE(ta.semester, tt.semester, 1);
ALTER TABLE tbl_timetable DROP INDEX unique_batch_day_hour;
ALTER TABLE tbl_timetable ADD UNIQUE KEY unique_batch_sem_day_hour(batch_id,semester,day_of_week,hour_no);
ALTER TABLE tbl_timetable ADD INDEX idx_tt_batch_semester(batch_id,semester);

/* Keep the old batch semester field only for backward compatibility.
   New code does not use it for student progression. */

/* Ensure the default BCA cohort is usable. */
UPDATE tbl_batches
SET batch_name = CASE WHEN batch_name='' THEN 'BCA' ELSE batch_name END,
    course = CASE WHEN course='' THEN 'BCA' ELSE course END,
    admission_year = COALESCE(admission_year,YEAR(CURDATE())),
    duration_years = CASE WHEN course='BSC Computer Science' THEN 4 ELSE 3 END
WHERE active=1;
