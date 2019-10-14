<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/api.php', 'OldApiController@api')
    ->name('api.old')->middleware('cors');

Route::post('/import.php', 'ApiController@import')
    ->name('api.import');

Route::get('/', 'MainController@index');

Auth::routes();
Route::group(['middleware' => ['auth']], function () {
    Route::get('/admin', 'MainController@root');

    Route::resource('auditoriums', 'AuditoriumController');
    Route::resource('auditoriumEvents', 'AuditoriumEventController');
    Route::resource('buildings', 'BuildingController');
    Route::resource('calendars', 'CalendarController');
    Route::post('/calendars/range', 'CalendarController@range');
    Route::resource('configOptions', 'ConfigOptionController');
    Route::resource('disciplines', 'DisciplineController');
    Route::resource('exams', 'ExamController');
    Route::resource('examLogEvents', 'ExamLogEventController');
    Route::resource('faculties', 'FacultyController');
    Route::resource('lessons', 'LessonController');
    Route::resource('lessonLogEvents', 'LessonLogEventController');
    Route::resource('rings', 'RingController');
    Route::resource('students', 'StudentController');
    Route::resource('studentGroups', 'StudentGroupController');
    Route::resource('teachers', 'TeacherController');
    Route::resource('teacherGroups', 'TeacherGroupController');

    Route::delete('/studentStudentGroups/{student_student_group_id}', 'StudentStudentGroupController@destroy');
    Route::post('/studentStudentGroups/store', 'StudentStudentGroupController@store');

    Route::delete('/facultyStudentGroups/{faculty_student_group_id}', 'FacultyStudentGroupController@destroy');
    Route::post('/facultyStudentGroups/store', 'FacultyStudentGroupController@store');

    Route::delete('/teacherDisciplines/{discipline_teacher_id}', 'DisciplineTeacherController@destroy');
    Route::post('/teacherDisciplines/store', 'DisciplineTeacherController@store');
    Route::post('/teacherDisciplines/updateTfd', 'DisciplineTeacherController@updateTfd');


    Route::get('/groupSchedule', 'MainController@groupSchedule');
    Route::get('/groupSchedule/{group_id}', 'MainController@groupScheduleWithId');

    Route::get('/facultySchedule', 'MainController@facultySchedule');
    Route::get('/facultySchedule/{faculty_id}', 'MainController@facultyScheduleWithId');

    Route::get('/teacherSchedule', 'MainController@teacherSchedule');

    Route::get('/groupSession', 'MainController@groupSession');
    Route::get('/groupSession/{group_id}', 'MainController@groupSessionWithId');

    Route::get('/buildingEvents', 'MainController@buildingEvents');

    // Lessons
    Route::post('/lessonsDestroyByIds', 'LessonController@DestroyByIds');
    Route::post('/lessonsWeeksAndAudsEdit', 'LessonController@WeeksAndAudsEdit');
    Route::get('/disciplinesByGroupInfo', 'DisciplineController@DisciplinesByGroupInfo');
    Route::post('/lessonsGroupScheduleAdd', 'LessonController@GroupScheduleAdd');
    Route::post('/switchLessons', 'LessonController@SwitchLessons');

    //Disciplines
    Route::post('/disciplinesCopyFromGroupToGroup', 'DisciplineController@CopyFromGroupToGroup');

    //FacultySchedule
    Route::get('/disciplinesByFacultyInfo', 'DisciplineController@DisciplinesByFacultyInfo');

    //teacherGroupSchedule
    Route::get('/teacherGroupSchedule', 'MainController@TeacherGroupSchedule');

    //teacherTeacherGroups
    Route::delete('/teacherTeacherGroups/{teacher_teacher_group_id}', 'TeacherTeacherGroupController@destroy');
    Route::post('/teacherTeacherGroups/store', 'TeacherTeacherGroupController@store');
    Route::get('/teacherTeacherGroups/teacherGroupTeachers/{teacher_group_id}', 'TeacherTeacherGroupController@get');

    //lessonLogEvents
    Route::get('/lessonLogEvents', 'MainController@lessonLogEvents');
    Route::get('/lessonLogEventsByGroup', 'LessonLogEventController@ByGroup');

    Route::get('/lle', 'MainController@lle');
    Route::get('/lleDateInfo', 'LessonLogEventController@ByDateInfo');
    Route::get('/lleEvents', 'LessonLogEventController@ByDate');

    //copy&Delete WeekSchedule
    Route::get('/weekSchedule', 'WeekController@index');
    Route::post('/copyWeekSchedule', 'WeekController@copyWeekSchedule');
    Route::post('/deleteWeekSchedule', 'WeekController@deleteWeekSchedule');

    // putAuds
    Route::get('/putAuds', 'MainController@putAudsIndex');
    Route::post('/putDailyAuds', 'MainController@putDailyAuds');

    // emptyAuds
    Route::get('/blankAuds', 'MainController@BlankAuds');
    Route::get('/getBlankAuds', 'MainController@GetBlankAuds');

    Route::get('/fillBlankAuds', 'MainController@fillBlankAuds');
    Route::post('/changeLessonAud', 'LessonController@changeLessonAud');
    Route::post('/changeLessonsAud', 'LessonController@changeLessonsAud');

    // newRings
    Route::get('/newRings', 'NewRingsController@index');
    Route::post('/changeRings', 'NewRingsController@ChangeRings');

    //teacherCollisions
    Route::get('/tc', 'MainController@teacherCollisions');
    Route::get('/tcs', 'MainController@teachersCollisions');
    Route::get('/tcl', 'MainController@CollisionsByTeacher');

    // Faculty Disciplines
    Route::get('/facTeachers', 'DisciplineController@facultyDisciplines');
    Route::get('/facultyTeachers', 'MainController@facultyDisciplinesIndex');

    // Teacher Got Ill
    Route::get('/teacherGotIll', 'TeacherGotIllController@index');
    Route::get('/loadIllInfo', 'TeacherGotIllController@loadIllInfo');

    // Teacher building transfers
    Route::get('/teacherBuildingTransfers', 'TeacherBuildingTransfersController@index');
    Route::get('/teacherBuildingDailyTransfers', 'TeacherBuildingTransfersController@DailyTransfers');
});

//disciplineHours
Route::get('/disciplineHours', 'MainController@disciplineHours');

//teacherHours
Route::get('/teacherHours', 'MainController@teacherHours');

Route::get('/pdf-export', 'PdfController@facultyDowChoice');
Route::get('/print-pdf', 'PdfController@facultyDow');
Route::post('/download-pdf', 'PdfController@facultyDowDownload');
Route::post('/bulkDownload-pdf', 'PdfController@facultyBulkDownload');
Route::post('/bulkDowDownload-pdf', 'PdfController@DowBulkDownload');
Route::post('/bulkDowShow-pdf', 'PdfController@DowBulkShow');
Route::get('/print-group-week-pdf', 'PdfController@StudentGroupWeekIndex');
Route::get('/print-group-pdf', 'PdfController@StudentGroupWeek');
Route::get('/buildingEventsPdfChoice', 'PdfController@buildingEventsPdfChoice');
Route::get('/buildingEventsPdf', 'PdfController@buildingEventsPdf');
Route::get('/teacherPdf', 'PdfController@teacherPdf');
Route::get('/teacherPdfWeek', 'PdfController@teacherPdfWeek');



Route::get('/auds', 'MainController@auds');

// Teacher schedule changes
Route::get('/lleTeacher', 'MainController@lleTeacher');
Route::get('/lleByTeacher', 'LessonLogEventController@ByTeacher');

//BIG RED BUTTON
Route::get('/brb', 'BigRedButtonController@TMP');

Route::get('/rdl', 'BigRedButtonController@RemoveDuplicateLessons');

