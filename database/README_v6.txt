LEARNLOOP V9 TEACHER SUBJECT CONTEXT UPDATE

This update fixes teacher/subject/semester handling.

A teacher is NOT assigned one permanent subject or semester.
The admin creates teaching assignments:
  Teacher + Batch + Semester + Subject

Example:
  Jiby + BCA + Semester 1 + Data Structures
  Jiby + BCA + Semester 2 + Database Management Systems
  Jiby + BCA + Semester 3 + Operating Systems

The timetable uses these teaching assignments, so each hour carries the correct teacher and subject.

For an existing v8/v7 database:
1. Back up the learnloop database.
2. Open phpMyAdmin -> learnloop -> SQL.
3. Import/run upgrade_v6_teacher_subject_context.sql ONCE.
4. Do not run database/learnloop.sql on an existing database.

After the migration:
Admin -> Teachers: add teachers without subject/semester.
Admin -> Timetable -> Teaching Assignments: assign the teacher to a specific batch/semester/subject.
Admin -> Timetable -> Place Teaching Assignment: put that exact teaching assignment into an hour.

Teacher Dashboard then allows the teacher to choose the batch, semester and subject they are currently teaching.
Notes and assignments use that exact context.
Hourly attendance uses the exact timetable slot and its teaching assignment.
