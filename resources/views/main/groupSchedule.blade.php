@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <group-schedule
                    :auditoriums="{{json_encode($auditoriums)}}"
                    :group-id="{{$group_id}}"
                    :student-groups="{{json_encode($studentGroups)}}"
                    :week-count="{{$weekCount}}">
                </group-schedule>
            </div>
        </div>
    </div>
@endsection
