@extends('layouts.master')

@section('title')
    Количество часов в расписании по преподавателям
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <teacher-hours
                    :week-count="{{$weekCount}}"
                    :teacher-id="{{$teacherId}}"
                    :teachers="{{json_encode($teachers)}}"
                    :student-groups="{{json_encode($studentGroups)}}"></teacher-hours>
            </div>
        </div>
    </div>
@endsection
