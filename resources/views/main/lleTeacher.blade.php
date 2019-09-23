@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <lle-teacher
                    :teachers="{{json_encode($teachers)}}"
                    :week-count="{{$weekCount}}"
                    :current-week="{{$currentWeek}}"
                    :semester-starts="{{json_encode($semesterStarts)}}"
                ></lle-teacher>
            </div>
        </div>
    </div>
@endsection
