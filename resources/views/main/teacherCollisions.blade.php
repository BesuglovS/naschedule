@extends('layouts.master')

@section('title')
    Коллизии преподавателей
@endsection

@section('content')
    <div style="text-align: center; font-size: 2em; font-weight: bold;">Коллизии преподавателей</div>
    <div class="container" style="align-items: center; display:flex; flex-direction:column; justify-content: center;">
        @foreach($collisions as $teacherCollisions)
            <div class="card" style="margin-bottom: 1em; width: 80%;">
                <div class="card-title" style="text-align: center; vertical-align: middle; font-weight: bold; ">
                    {{$teacherCollisions['fio']}}
                </div>
                <div class="card-body">
                    <table class="td-center" style="border-collapse:collapse; border: none; width: 100%;">
                        @foreach($teacherCollisions['collisions'] as $teacherDayCollisions)
                            @foreach($teacherDayCollisions as $teacherDayCollision)
                                <tr>
                                    <td>
                                        <table class="table td-center is-bordered" style="width: 100%">
                                            <tr>
                                                <td style="vertical-align: middle;">
                                                    {{substr($teacherDayCollision[0]->calendarDate, 8, 2)}}.<!--
                                                 -->{{substr($teacherDayCollision[0]->calendarDate, 5, 2)}}.<!--
                                                 -->{{substr($teacherDayCollision[0]->calendarDate, 0, 4)}}
                                                    <br />
                                                    {{substr($teacherDayCollision[0]->ringsTime,0,5)}}
                                                </td>
                                                @foreach($teacherDayCollision as $teacherDayCollisionLesson)
                                                    <td>
                                                        {{$teacherDayCollisionLesson->studentGroupName}} <br />
                                                        {{$teacherDayCollisionLesson->disciplineName}} <br />
                                                        {{$teacherDayCollisionLesson->auditoriumName}}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </table>
                </div>
            </div>
        @endforeach
    </div>

@endsection
