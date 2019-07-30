@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <teacher-schedule :week-count="{{$weekCount}}" :teachers="{{json_encode($teachers)}}"></teacher-schedule>
            </div>
        </div>
    </div>
@endsection
