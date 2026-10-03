<?php
/* LearnLoop academic progression helpers.
   Academic year starts in June and has two semesters:
   Jun-Nov = odd semester, Dec-May = even semester.
*/
function course_duration_years(string $course): int {
    $map = [
        'BCA' => 3,
        'BBA' => 3,
        'BCom' => 3,
        'BSC Computer Science' => 4,
    ];
    return $map[$course] ?? 3;
}

function semester_for_admission_year(int $admissionYear, ?string $date = null): int {
    if ($admissionYear <= 0) return 0;
    $date = $date ?: date('Y-m-d');
    $ts = strtotime($date);
    $year = (int)date('Y', $ts);
    $month = (int)date('n', $ts);
    $academicStartYear = ($month >= 6) ? $year : ($year - 1);
    $yearOffset = $academicStartYear - $admissionYear;
    if ($yearOffset < 0) return 0;
    $semesterInYear = ($month >= 6 && $month <= 11) ? 1 : 2;
    return ($yearOffset * 2) + $semesterInYear;
}

function academic_year_for_semester(int $semester): int {
    return $semester > 0 ? (int)ceil($semester / 2) : 0;
}

function academic_context(mysqli $conn, string $username): ?array {
    $st = $conn->prepare("SELECT s.username,s.name,s.course,s.batch_id,b.batch_name,b.course batch_course,b.admission_year,b.duration_years
                          FROM tbl_student s
                          LEFT JOIN tbl_batches b ON b.id=s.batch_id
                          WHERE s.username=? AND s.approved=1");
    $st->bind_param('s', $username);
    $st->execute();
    $row = $st->get_result()->fetch_assoc();
    if (!$row) return null;
    $semester = semester_for_admission_year((int)($row['admission_year'] ?? 0));
    $duration = (int)($row['duration_years'] ?? course_duration_years($row['course'] ?? ''));
    $maxSemester = $duration * 2;
    if ($semester > $maxSemester) $semester = $maxSemester;
    $row['current_semester'] = $semester;
    $row['current_year'] = academic_year_for_semester($semester);
    $row['max_semester'] = $maxSemester;
    return $row;
}

function batch_current_semester(array $batch): int {
    $semester = semester_for_admission_year((int)($batch['admission_year'] ?? 0));
    $duration = (int)($batch['duration_years'] ?? course_duration_years($batch['course'] ?? ''));
    $maxSemester = $duration * 2;
    if ($semester > $maxSemester) $semester = $maxSemester;
    return $semester;
}
