@extends('layouts.master')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-12">
            <teacher-group-schedule
                :week-count="{{$weekCount}}"
                :teachers="{{json_encode($teachers)}}"
                :rings="{{json_encode($rings)}}">
            </teacher-group-schedule>
        </div>
    </div>
</div>
@endsection
