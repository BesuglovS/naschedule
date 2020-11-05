@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <online
                    :faculties="{{json_encode($faculties)}}"
                    :week-count="{{$weekCount}}"
                    :weeks="{{json_encode($weeks)}}"
                    :current-week="{{$currentWeek}}">
                </online>
            </div>
        </div>
    </div>
@endsection
