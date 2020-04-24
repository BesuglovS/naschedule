@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <trello-online
                    :week-count="{{$weekCount}}"
                    :weeks="{{json_encode($weeks)}}"
                    :current-week="{{$currentWeek}}">
                </trello-online>
            </div>
        </div>
    </div>
@endsection
