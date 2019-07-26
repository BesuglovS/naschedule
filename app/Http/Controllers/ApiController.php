<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiController extends Controller
{
    public function index() {
        return view('api.index');
    }

    public function import(Request $request) {
        ini_set('max_execution_time', 300);



        $content = $request->getContent();
        $requestData = json_decode($content, true);

        $dbPrefix = $requestData['dbPrefix'];
        $dbPrefix = "nas_";

        $tableSelector = $requestData['tableSelector'];
        $append = $requestData['append'];
        $data = json_decode($requestData['data'], true);
        switch ($tableSelector) {
            case "auditoriums":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "auditoriums";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "auditoriums ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`building_id` bigint(20) UNSIGNED NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";



                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "auditoriums(id, name, building_id) VALUES ( ? , ? , ? )");
                foreach ($data as $auditorium) {
                    $statement->execute(array($auditorium["AuditoriumId"], $auditorium["Name"], $auditorium["BuildingId"]));
                }
                $pdo->commit();
                break;
            case "buildings":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "buildings";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "buildings ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "buildings(id, name) VALUES ( ? , ? )");
                foreach ($data as $building) {
                    $statement->execute(array($building["BuildingId"], $building["Name"]));
                }
                $pdo->commit();
                break;
            case "calendars":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "calendars";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "calendars ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`date` date NOT NULL, " .
                    "`state` int(11) NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "calendars(id, date, state) VALUES ( ? , ? , ? )");
                foreach ($data as $calendar) {
                    $statement->execute(array($calendar["CalendarId"], $calendar["Date"], $calendar["State"]));
                }
                $pdo->commit();
                break;
            case "rings":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "rings";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "rings ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`time` time NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "rings(id, `time`) VALUES ( ? , ? )");
                foreach ($data as $ring) {
                    $statement->execute(array($ring["RingId"], $ring["Time"]));
                }
                $pdo->commit();
                break;
            case "students":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "students";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "students ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`f` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`i` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`o` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`zach_number` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`birth_date` date NOT NULL, " .
                    "`address` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`phone` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`orders` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`starosta` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`n_factor` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`paid_edu` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`expelled` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "students(id, f, i, o, `zach_number`, `birth_date`, `address`, `phone`, `orders`, `starosta`, `n_factor`, `paid_edu`, `expelled` ) VALUES ( ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? )");
                foreach ($data as $student) {
                    $statement->execute(array($student["StudentId"], $student["F"], $student["I"], $student["O"],
                        $student["ZachNumber"], $student["BirthDate"], $student["Address"], $student["Phone"],
                        $student["Orders"], $student["Starosta"], $student["NFactor"], $student["PaidEdu"],
                        $student["Expelled"]));
                }
                $pdo->commit();
                break;
            case "studentGroups":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "student_groups";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "student_groups ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "student_groups(id, name) VALUES ( ? , ? )");
                foreach ($data as $studentGroup) {
                    $statement->execute(array($studentGroup["StudentGroupId"], $studentGroup["Name"]));
                }
                $pdo->commit();
                break;
            case "teachers":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "teachers";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "teachers ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`fio` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`phone` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "teachers(id, fio, phone) VALUES ( ? , ? , ? )");
                foreach ($data as $teacher) {
                    $statement->execute(array($teacher["TeacherId"], $teacher["FIO"], $teacher["Phone"]));
                }
                $pdo->commit();
                break;
            case "disciplines":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "disciplines";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "disciplines ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`attestation` int(11) NOT NULL, " .
                    "`auditorium_hours` int(11) NOT NULL, " .
                    "`auditorium_hours_per_week` int(11) NOT NULL, " .
                    "`lecture_hours` int(11) NOT NULL, " .
                    "`practical_hours` int(11) NOT NULL, " .
                    "`student_group_id` bigint(20) UNSIGNED NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "disciplines (id, name, attestation, `auditorium_hours`, `auditorium_hours_per_week`, `lecture_hours`, `practical_hours`, `student_group_id`) VALUES ( ? , ? , ? , ? , ? , ? , ? , ? )");
                foreach ($data as $discipline) {
                    $statement->execute(array(
                        $discipline["DisciplineId"], $discipline["Name"],
                        $discipline["Attestation"], $discipline["AuditoriumHours"],
                        $discipline["AuditoriumHoursPerWeek"], $discipline["LectureHours"],
                        $discipline["PracticalHours"], $discipline["StudentGroupId"]));
                }
                $pdo->commit();
                break;
            case "studentsInGroups":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "student_student_group";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "student_student_group ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`student_id` bigint(20) UNSIGNED NOT NULL, " .
                    "`student_group_id` bigint(20) UNSIGNED NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "student_student_group (id, student_id, student_group_id) VALUES ( ? , ? , ? )");
                foreach ($data as $sig) {
                    $statement->execute(array($sig["StudentsInGroupsId"], $sig["StudentId"], $sig["StudentGroupId"]));
                }
                $pdo->commit();
                break;
            case "teacherForDisciplines":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "discipline_teacher";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "discipline_teacher ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`discipline_id` bigint(20) UNSIGNED NOT NULL, " .
                    "`teacher_id` bigint(20) UNSIGNED NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "discipline_teacher (id, teacher_id, discipline_id) VALUES ( ? , ? , ? )");
                foreach ($data as $tfd) {
                    $statement->execute(array($tfd["TeacherForDisciplineId"], $tfd["TeacherId"], $tfd["DisciplineId"]));
                }
                $pdo->commit();
                break;
            case "lessons":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "lessons";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "lessons ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`state` int(11) NOT NULL, " .
                    "`discipline_teacher_id` bigint(20) UNSIGNED NOT NULL, " .
                    "`calendar_id` bigint(20) UNSIGNED NOT NULL, " .
                    "`ring_id` bigint(20) UNSIGNED NOT NULL, " .
                    "`auditorium_id` bigint(20) UNSIGNED NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "lessons (id, state, discipline_teacher_id, `calendar_id`, `Ring_Id`, `Auditorium_Id`) VALUES ( ? , ? , ? , ? , ? , ? )");
                foreach ($data as $lesson) {
                    $statement->execute(array($lesson["LessonId"], $lesson["IsActive"],
                        $lesson["TeacherForDisciplineId"], $lesson["CalendarId"], $lesson["RingId"], $lesson["AuditoriumId"]));
                }
                $pdo->commit();
                break;
            case "configs":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "config_options";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "config_options ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`value` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "config_options (id, `key`, `value`) VALUES ( ? , ? , ? )");
                foreach ($data as $configOption) {
                    $statement->execute(array($configOption["ConfigOptionId"], $configOption["Key"], $configOption["Value"]));
                }
                $pdo->commit();
                break;
            case "lessonLogEvents":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "lesson_log_events";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "lesson_log_events ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`old_lesson_id` bigint(20) UNSIGNED NOT NULL, " .
                    "`new_lesson_id` bigint(20) UNSIGNED NOT NULL, " .
                    "`date_time` datetime NOT NULL, " .
                    "`public_comment` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`hidden_comment` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "lesson_log_events (id, old_lesson_id, new_lesson_id, date_time, public_comment, hidden_comment) VALUES ( ? , ? , ? , ? , ? , ? )");
                foreach ($data as $lessonLogEvent) {
                    $statement->execute(array($lessonLogEvent["LessonLogEventId"],
                        $lessonLogEvent["OldLessonId"], $lessonLogEvent["NewLessonId"], $lessonLogEvent["DateTime"],
                        $lessonLogEvent["PublicComment"], $lessonLogEvent["HiddenComment"]));
                }
                $pdo->commit();
                break;
            case "auditoriumEvents":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "auditorium_events";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "auditorium_events ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`calendar_id` bigint(20) UNSIGNED NOT NULL, " .
                    "`ring_id` bigint(20) UNSIGNED NOT NULL, " .
                    "`auditorium_id` bigint(20) UNSIGNED NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "auditorium_events (`id`, `name`, `calendar_id`, `ring_id`, `auditorium_id`) VALUES ( ? , ? , ? , ? , ? )");
                foreach ($data as $AuditoriumEvent) {
                    $statement->execute(array($AuditoriumEvent["AuditoriumEventId"],
                        $AuditoriumEvent["Name"], $AuditoriumEvent["CalendarId"],
                        $AuditoriumEvent["RingId"], $AuditoriumEvent["AuditoriumId"]));
                }
                $pdo->commit();
                break;
            case "faculties":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "faculties";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "faculties ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`letter` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`sorting_order` int(11) NOT NULL, " .
                    "`schedule_signing_title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`dean_signing_schedule` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`session_signing_title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "`dean_signing_session_schedule` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "faculties (id, name, letter, sorting_order, schedule_signing_title, dean_signing_schedule, session_signing_title, dean_signing_session_schedule) VALUES ( ? , ? , ? , ? , ? , ? , ? , ? )");
                foreach ($data as $faculty) {
                    $statement->execute(array($faculty["FacultyId"], $faculty["Name"], $faculty["Letter"], $faculty["SortingOrder"], $faculty["ScheduleSigningTitle"], $faculty["DeanSigningSchedule"], $faculty["SessionSigningTitle"], $faculty["DeanSigningSessionSchedule"]));
                }
                $pdo->commit();
                break;
            case "GroupsInFaculties":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "faculty_student_group";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "faculty_student_group ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`faculty_id` bigint(20) UNSIGNED NOT NULL, " .
                    "`student_group_id` bigint(20) UNSIGNED NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "faculty_student_group (id, faculty_id, student_group_id) VALUES ( ? , ? , ? )");
                foreach ($data as $gif) {
                    $statement->execute(array($gif["GroupsInFacultyId"], $gif["FacultyId"], $gif["StudentGroupId"]));
                }
                $pdo->commit();
                break;
            case "exams":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "exams";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "exams ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`discipline_id` bigint(20) UNSIGNED NOT NULL, " .
                    "`is_active` tinyint(1) NOT NULL, " .
                    "`consultation_datetime` datetime NOT NULL, " .
                    "`consultation_auditorium_id` bigint(20) UNSIGNED NOT NULL, " .
                    "`exam_datetime` datetime NOT NULL, " .
                    "`exam_auditorium_id` bigint(20) UNSIGNED NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "exams (id, discipline_id, is_active, consultation_datetime, consultation_auditorium_id, exam_datetime, exam_auditorium_id) VALUES ( ? , ? , ? , ? , ? , ? , ? )");
                foreach ($data as $exam) {
                    $statement->execute(array($exam["ExamId"],
                        $exam["DisciplineId"],
                        $exam["IsActive"],
                        Carbon::createFromFormat('d.m.Y H:i', $exam["ConsultationDateTime"])->format('Y-m-d H:i:s'),
                        $exam["ConsultationAuditoriumId"],
                        Carbon::createFromFormat('d.m.Y H:i', $exam["ExamDateTime"])->format('Y-m-d H:i:s'),
                        $exam["ExamAuditoriumId"]));
                }
                $pdo->commit();
                break;
            case "examsLogEvents":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "exam_log_events";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "exam_log_events ( " .
                    "`id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, " .
                    "`old_exam_id` bigint(20) UNSIGNED NOT NULL, " .
                    "`new_exam_id` bigint(20) UNSIGNED NOT NULL, " .
                    "`datetime` datetime NOT NULL, " .
                    "PRIMARY KEY  (`id`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "exam_log_events (id, old_exam_id, new_exam_id, datetime)  VALUES ( ? , ? , ? , ? )");
                foreach ($data as $examLogEvent) {
                    $statement->execute(array($examLogEvent["LogEventId"],
                            $examLogEvent["OldExamId"],
                            $examLogEvent["NewExamId"],
                            Carbon::createFromFormat('d.m.Y H:i', $examLogEvent["DateTime"])->format('Y-m-d H:i:s'))
                    );
                }
                $pdo->commit();
                break;
            case "scheduleNotes":
                if ($append !== "1") {
                    $query = "DROP TABLE IF EXISTS " . $dbPrefix . "schedule_notes";
                    DB::statement($query);
                }
                $query  = "CREATE TABLE IF NOT EXISTS " . $dbPrefix . "schedule_notes ( " .
                    "`id` int(11) NOT NULL AUTO_INCREMENT, " .
                    "`text` varchar(100) NOT NULL, " .
                    "`is_lesson` BOOLEAN NOT NULL, " .
                    "`lesson_id` INT NULL, " .
                    "`late_amount` INT NOT NULL, " .
                    "PRIMARY KEY  (`ScheduleNoteId`)" .
                    ") ENGINE=InnoDB DEFAULT CHARSET=utf8;";
                DB::statement($query);
                $pdo = DB::getPdo();
                $pdo->beginTransaction();
                $statement = $pdo->prepare("INSERT INTO " . $dbPrefix . "schedule_notes (id, text, is_lesson, lesson_id, late_amount) VALUES ( ? , ? , ? , ?, ? )");
                foreach ($data as $note) {
                    $statement->execute(array($note["ScheduleNoteId"], $note["Text"], $note["IsLesson"],$note["LessonId"], $note["LateAmount"]));
                }
                $pdo->commit();
                break;
        }
        return response()->json(["success" => "OK"], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
