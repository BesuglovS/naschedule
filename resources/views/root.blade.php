@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card-deck mb-2">
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/groupSchedule">Расписание группы</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/facultySchedule">Расписание параллели</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/teacherSchedule">Расписание преподавателя</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/groupSession">Расписание экзаменов по группам</a>
                        </div>
                    </div>
                </div>

                <div class="card-deck mb-2">
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/auditoriums">Аудитории</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/auditoriumEvents">Занятость аудиторий</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/buildings">Корпуса академии</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/calendars">Даты семестра</a>
                        </div>
                    </div>
                </div>

                <div class="card-deck mb-2">
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/configOptions">Опции</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/teachers">Учителя</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/disciplines">Дисциплины</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/exams">Экзамены</a>
                        </div>
                    </div>
                </div>

                <div class="card-deck mb-2">
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/rings">Звонки</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/faculties">Параллели</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/studentGroups">Классы</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/students">Ученики</a>
                        </div>
                    </div>
                </div>

                <div class="card-deck mb-2">
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/buildingEvents">Занятость аудиторий корпуса</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/teacherGroupSchedule">Расписание нескольких преподавателей</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/teacherGroups">Группы учителей</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
