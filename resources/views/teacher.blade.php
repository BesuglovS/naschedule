@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <div class="card-deck mb-2">
                    <div class="card tac">
                        <div class="card-body">
                            <a href="/teacherEdit">Редактировать описание занятий</a>
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
{{--                            <a href="/facultySchedule">Расписание параллели</a>--}}
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
{{--                            <a href="/teacherSchedule">Расписание преподавателя</a>--}}
                        </div>
                    </div>
                    <div class="card tac">
                        <div class="card-body">
{{--                            <a href="/groupSession">Расписание экзаменов по группам</a>--}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
