@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <teacher-got-ill
                    :teachers="{{json_encode($teachers)}}"
                    :calendars="{{json_encode($calendars)}}"
                    :week-count="{{$weekCount}}"
                    :current-week="{{$currentWeek}}"
                    :semester-starts="{{json_encode($semesterStarts)}}"
                ></teacher-got-ill>
            </div>
        </div>
    </div>
@endsection
