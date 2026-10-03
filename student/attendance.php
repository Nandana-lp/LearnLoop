<?php

require_once '../includes/auth.php';
require_role('student');

require_once '../config/db.php';
require_once '../includes/academic.php';

$page_title = 'Attendance Calendar';

$user = $_SESSION['username'];


/*
|--------------------------------------------------------------------------
| GET STUDENT INFORMATION
|--------------------------------------------------------------------------
*/

$st = $conn->prepare("
    SELECT
        s.batch_id,
        b.batch_name,
        b.course
    FROM tbl_student s
    LEFT JOIN tbl_batches b
        ON b.id = s.batch_id
    WHERE s.username = ?
");

$st->bind_param(
    's',
    $user
);

$st->execute();

$student = $st->get_result()->fetch_assoc();


/*
|--------------------------------------------------------------------------
| GET ACADEMIC CONTEXT
|--------------------------------------------------------------------------
*/

$academic = academic_context(
    $conn,
    $user
);

$batchId = (int)(
    $academic['batch_id'] ?? 0
);

$currentSemester = (int)(
    $academic['current_semester'] ?? 0
);

$currentYear = $academic['current_year'] ?? '-';


/*
|--------------------------------------------------------------------------
| ATTENDANCE DATA
|--------------------------------------------------------------------------
*/

$events = [];

$presentHours = 0;

$absentHours = 0;

$totalHours = 0;

$details = [];

$byDate = [];


if ($batchId) {

    $st = $conn->prepare("
        SELECT

            ha.attendance_date,

            ha.status,

            tt.hour_no,

            tt.subject,

            tt.start_time,

            tt.end_time,

            t.name AS teacher_name,

            b.batch_name

        FROM tbl_hourly_attendance ha

        JOIN tbl_timetable tt
            ON tt.id = ha.timetable_id

        JOIN tbl_trs t
            ON t.username = tt.teacher_username

        JOIN tbl_batches b
            ON b.id = tt.batch_id

        WHERE
            ha.student_username = ?

            AND tt.semester = ?

            AND ha.attendance_date >=
                DATE_SUB(
                    CURDATE(),
                    INTERVAL 365 DAY
                )

        ORDER BY
            ha.attendance_date DESC,
            tt.hour_no
    ");

    $st->bind_param(
        'si',
        $user,
        $currentSemester
    );

    $st->execute();

    $result = $st->get_result();


    while (
        $row = $result->fetch_assoc()
    ) {

        $date = $row['attendance_date'];


        /*
        | Count attendance by date
        */

        if (!isset($byDate[$date])) {

            $byDate[$date] = [
                'total' => 0,
                'absent' => 0
            ];

        }


        $byDate[$date]['total']++;


        if (
            $row['status'] === 'Absent'
        ) {

            $byDate[$date]['absent']++;

            $absentHours++;

        } else {

            $presentHours++;

        }


        $totalHours++;


        /*
        | Store detailed hourly attendance
        */

        $details[$date][] = $row;

    }


    /*
    |--------------------------------------------------------------------------
    | CREATE CALENDAR EVENTS
    |--------------------------------------------------------------------------
    */

    foreach (
        $byDate as $date => $data
    ) {

        $total = $data['total'];

        $absent = $data['absent'];

        $present = $total - $absent;


        if ($absent > 0) {

            $title =
                'Absent ' .
                $absent .
                '/' .
                $total;

            $className = 'absent-event';

            $description =
                $absent .
                ' absent hour(s) out of ' .
                $total;

        } else {

            $title =
                'Present ' .
                $present .
                '/' .
                $total;

            $className = 'present-event';

            $description =
                $present .
                ' present hour(s) out of ' .
                $total;

        }


        $events[] = [

            'title' => $title,

            'start' => $date,

            'allDay' => true,

            'classNames' => [
                $className
            ],

            'extendedProps' => [

                'type' =>
                    'Hourly Attendance',

                'description' =>
                    $description

            ]

        ];

    }

}


/*
|--------------------------------------------------------------------------
| ACADEMIC EVENTS
|
| IMPORTANT:
| Do NOT reuse $academic here.
|--------------------------------------------------------------------------
*/

$eventResult = $conn->query("
    SELECT
        title,
        date,
        description

    FROM tbl_events

    WHERE date >=
        DATE_SUB(
            CURDATE(),
            INTERVAL 365 DAY
        )

    ORDER BY date
");


if ($eventResult) {

    while (
        $event = $eventResult->fetch_assoc()
    ) {

        $events[] = [

            'title' =>
                $event['title'],

            'start' =>
                $event['date'],

            'allDay' =>
                true,

            'classNames' => [
                'academic-event'
            ],

            'extendedProps' => [

                'type' =>
                    'Academic Event',

                'description' =>
                    $event['description']

            ]

        ];

    }

}


/*
|--------------------------------------------------------------------------
| ATTENDANCE PERCENTAGE
|--------------------------------------------------------------------------
*/

$percentage = 0;

if ($totalHours > 0) {

    $percentage = round(
        ($presentHours / $totalHours) * 100
    );

}


/*
|--------------------------------------------------------------------------
| PREPARE CALENDAR DETAILS
|--------------------------------------------------------------------------
*/

$detailsJson = [];


foreach (
    $details as $date => $rows
) {

    $detailsJson[$date] = [];


    foreach (
        $rows as $row
    ) {

        $detailsJson[$date][] = [

            $row['hour_no'],

            $row['subject'],

            substr(
                $row['start_time'],
                0,
                5
            ),

            substr(
                $row['end_time'],
                0,
                5
            ),

            $row['teacher_name'],

            $row['status']

        ];

    }

}


require '../includes/header.php';

?>


<!-- =========================================================
     FULLCALENDAR
========================================================= -->

<link
    rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.css"
>


<!-- =========================================================
     PAGE HEADER
========================================================= -->

<div class="glass-hero mb-4">

    <div>

        <span class="eyebrow-dark">
            ATTENDANCE RECORD
        </span>

        <h2>
            My Attendance
        </h2>

        <p>

            <?php

            echo e(
                ($student['batch_name'] ?? 'Batch not assigned')
            );

            ?>

            ·

            <?php

            echo e(
                ($student['course'] ?? '')
            );

            ?>

            · Semester

            <?php
            echo $currentSemester;
            ?>

            · Year

            <?php
            echo e($currentYear);
            ?>

            · Every class hour is counted separately.

        </p>

    </div>


    <div class="hero-orb">

        <i class="fa-solid fa-calendar-check"></i>

    </div>

</div>


<!-- =========================================================
     BACK BUTTON
========================================================= -->

<?php include '../includes/back_button.php'; ?>


<!-- =========================================================
     MAIN CONTENT
========================================================= -->

<div class="row g-4">


    <!-- =====================================================
         CALENDAR
    ====================================================== -->

    <div class="col-xl-9">

        <div class="glass-card calendar-panel">

            <div
                class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-3"
            >

                <div>

                    <div class="section-kicker">
                        CALENDAR
                    </div>

                    <h5>
                        Attendance Calendar
                    </h5>

                    <span class="small-muted">

                        Month, week and list views.
                        Click a day to see each subject hour.

                    </span>

                </div>


                <div class="calendar-legend">

                    <span>

                        <i class="legend-dot present"></i>

                        Present

                    </span>


                    <span>

                        <i class="legend-dot absent"></i>

                        Absent

                    </span>


                    <span>

                        <i class="legend-dot academic"></i>

                        Academic Event

                    </span>

                </div>

            </div>


            <div id="studentCalendar"></div>

        </div>

    </div>


    <!-- =====================================================
         SUMMARY
    ====================================================== -->

    <div class="col-xl-3">

        <div class="glass-card mb-3">

            <div class="section-kicker">
                SUMMARY
            </div>

            <h5>
                Overall Attendance
            </h5>


            <div
                class="attendance-ring"
                style="--percentage:<?php
                    echo $percentage;
                ?>"
            >

                <?php
                echo $percentage;
                ?>%

            </div>


            <p class="small-muted text-center">

                Hourly attendance

            </p>


            <div class="mini-stat">

                <span>
                    Present hours
                </span>

                <strong>
                    <?php
                    echo $presentHours;
                    ?>
                </strong>

            </div>


            <div class="mini-stat">

                <span>
                    Absent hours
                </span>

                <strong
                    style="color:#dc3545"
                >

                    <?php
                    echo $absentHours;
                    ?>

                </strong>

            </div>


            <div class="mini-stat">

                <span>
                    Total marked
                </span>

                <strong>
                    <?php
                    echo $totalHours;
                    ?>
                </strong>

            </div>

        </div>


        <!-- =================================================
             TIMETABLE LINK
        ================================================== -->

        <div class="glass-card">

            <div class="section-kicker">
                QUICK LINK
            </div>

            <h5>
                Weekly Timetable
            </h5>

            <p class="small-muted">

                See the teacher, subject and fixed time
                for every hour.

            </p>


            <a
                href="timetable.php"
                class="btn btn-primary w-100"
            >

                View Timetable

            </a>

        </div>

    </div>

</div>


<!-- =========================================================
     ATTENDANCE DAY MODAL
========================================================= -->

<div
    class="modal fade"
    id="attendanceDayModal"
    tabindex="-1"
>

    <div class="modal-dialog modal-dialog-centered">

        <div class="modal-content glass-modal">


            <div class="modal-header">

                <div>

                    <div class="section-kicker">
                        DAY DETAILS
                    </div>

                    <h5
                        class="modal-title"
                        id="attendanceModalTitle"
                    >
                        Attendance
                    </h5>

                </div>


                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                ></button>

            </div>


            <div
                class="modal-body"
                id="attendanceModalBody"
            ></div>


        </div>

    </div>

</div>


<!-- =========================================================
     FULLCALENDAR JS
========================================================= -->

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.15/index.global.min.js"></script>


<script>

/*
|--------------------------------------------------------------------------
| PHP → JAVASCRIPT DATA
|--------------------------------------------------------------------------
*/

const attendanceDetails =
    <?php
    echo json_encode(
        $detailsJson,
        JSON_UNESCAPED_SLASHES
    );
    ?>;


const attendanceEvents =
    <?php
    echo json_encode(
        $events,
        JSON_UNESCAPED_SLASHES
    );
    ?>;


/*
|--------------------------------------------------------------------------
| SHOW ATTENDANCE FOR A DAY
|--------------------------------------------------------------------------
*/

function showAttendanceDay(date) {

    const rows =
        attendanceDetails[date] || [];


    const body =
        document.getElementById(
            'attendanceModalBody'
        );


    const title =
        document.getElementById(
            'attendanceModalTitle'
        );


    const dateObject =
        new Date(
            date + 'T00:00:00'
        );


    title.textContent =
        dateObject.toLocaleDateString(
            undefined,
            {
                weekday: 'long',
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            }
        );


    /*
    |--------------------------------------------------------------------------
    | No attendance
    |--------------------------------------------------------------------------
    */

    if (!rows.length) {

        body.innerHTML = `
            <div class="empty">

                No hourly attendance record
                was marked for this date.

            </div>
        `;

    }

    /*
    |--------------------------------------------------------------------------
    | Attendance records
    |--------------------------------------------------------------------------
    */

    else {

        body.innerHTML = rows
            .map(row => {

                return `

                    <div class="attendance-detail-row">

                        <div>

                            <span class="detail-hour">

                                Hour ${row[0]}

                            </span>

                            <strong>

                                ${escapeHtml(row[1])}

                            </strong>

                            <small>

                                ${row[2]}–${row[3]}
                                ·
                                ${escapeHtml(row[4])}

                            </small>

                        </div>


                        <span
                            class="
                                detail-status
                                ${row[5] === 'Absent'
                                    ? 'absent'
                                    : 'present'}
                            "
                        >

                            ${row[5]}

                        </span>

                    </div>

                `;

            })
            .join('');

    }


    /*
    |--------------------------------------------------------------------------
    | OPEN MODAL
    |--------------------------------------------------------------------------
    */

    bootstrap.Modal
        .getOrCreateInstance(
            document.getElementById(
                'attendanceDayModal'
            )
        )
        .show();

}


/*
|--------------------------------------------------------------------------
| ESCAPE HTML
|--------------------------------------------------------------------------
*/

function escapeHtml(value) {

    return String(value)
        .replace(
            /[&<>'"]/g,
            character => ({

                '&': '&amp;',

                '<': '&lt;',

                '>': '&gt;',

                "'": '&#39;',

                '"': '&quot;'

            }[character])
        );

}


/*
|--------------------------------------------------------------------------
| INITIALIZE CALENDAR
|--------------------------------------------------------------------------
*/

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const calendarElement =
            document.getElementById(
                'studentCalendar'
            );


        if (!calendarElement) {
            return;
        }


        const calendar =
            new FullCalendar.Calendar(
                calendarElement,
                {

                    initialView:
                        'dayGridMonth',

                    height:
                        'auto',

                    firstDay:
                        1,

                    dayMaxEvents:
                        3,

                    navLinks:
                        true,


                    headerToolbar: {

                        left:
                            'prev,next today',

                        center:
                            'title',

                        right:
                            'dayGridMonth,timeGridWeek,listMonth'

                    },


                    buttonText: {

                        today:
                            'Today',

                        month:
                            'Month',

                        week:
                            'Week',

                        list:
                            'List'

                    },


                    events:
                        attendanceEvents,


                    eventClick:
                        function (info) {

                            showAttendanceDay(
                                info.event.startStr
                            );

                        },


                    dateClick:
                        function (info) {

                            showAttendanceDay(
                                info.dateStr
                            );

                        }

                }
            );


        calendar.render();

    }
);

</script>


<?php

require '../includes/footer.php';

?>