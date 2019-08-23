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

    Route::delete('/studentStudentGroups/{student_student_group_id}', 'StudentStudentGroupController@destroy');
    Route::post('/studentStudentGroups/store', 'StudentStudentGroupController@store');

    Route::delete('/facultyStudentGroups/{faculty_student_group_id}', 'FacultyStudentGroupController@destroy');
    Route::post('/facultyStudentGroups/store', 'FacultyStudentGroupController@store');

    Route::delete('/teacherDisciplines/{discipline_teacher_id}', 'DisciplineTeacherController@destroy');
    Route::post('/teacherDisciplines/store', 'DisciplineTeacherController@store');


    Route::get('/groupSchedule', 'MainController@groupSchedule');
    Route::get('/groupSchedule/{group_id}', 'MainController@groupScheduleWithId');

    Route::get('/facultySchedule', 'MainController@facultySchedule');
    Route::get('/facultySchedule/(faculty_id)', 'MainController@facultyScheduleWithId');

    Route::get('/teacherSchedule', 'MainController@teacherSchedule');

    Route::get('/groupSession', 'MainController@groupSession');
    Route::get('/groupSession/{group_id}', 'MainController@groupSessionWithId');

    Route::get('/buildingEvents', 'MainController@buildingEvents');

    // Lessons
    Route::post('/lessonsDestroyByIds', 'LessonController@DestroyByIds');
    Route::post('/lessonsWeeksAndAudsEdit', 'LessonController@WeeksAndAudsEdit');
    Route::get('/disciplinesByGroupInfo', 'DisciplineController@DisciplinesByGroupInfo');
    Route::post('/lessonsGroupScheduleAdd', 'LessonController@GroupScheduleAdd');

    //Disciplines
    Route::post('/disciplinesCopyFromGroupToGroup', 'DisciplineController@CopyFromGroupToGroup');

    //FacultySchedule
    Route::get('/disciplinesByFacultyInfo', 'DisciplineController@DisciplinesByFacultyInfo');

    //teacherGroupSchedule
    Route::get('/teacherGroupSchedule', 'MainController@TeacherGroupSchedule');
});
