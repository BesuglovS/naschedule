@extends('layouts.master')

@section('title')
    Расписание недели
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <week-schedule
                    :faculties="{{json_encode($faculties)}}"
                    :week-count="{{$weekCount}}"
                    :weeks="{{json_encode($weeks)}}"
                    :current-week="{{$currentWeek}}">
                </week-schedule>
            </div>
        </div>
    </div>
@endsection
