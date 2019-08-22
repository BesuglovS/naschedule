@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <faculty-schedule
                    :faculty-id="{{$faculty_id}}"
                    :faculties="{{json_encode($faculties)}}"
                    :week-count="{{$weekCount}}"
                    :auditoriums="{{json_encode($auditoriums)}}">
                </faculty-schedule>
            </div>
        </div>
    </div>
@endsection
