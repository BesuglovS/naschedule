@extends('layouts.master')

@section('title')
    Количество часов в расписании по группам
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <discipline-hours
                    :week-count="{{$weekCount}}"
                    :group-id="{{$groupId}}"
                    :student-groups="{{json_encode($studentGroups)}}"></discipline-hours>
            </div>
        </div>
    </div>
@endsection
