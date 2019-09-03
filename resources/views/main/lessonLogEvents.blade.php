@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <lesson-log-events
                    :auditoriums="{{json_encode($auditoriums)}}"
                    :group-id="{{$groupId}}"
                    :student-groups="{{json_encode($studentGroups)}}"
                    :week-count="{{$weekCount}}">
                </lesson-log-events>
            </div>
        </div>
    </div>
@endsection
