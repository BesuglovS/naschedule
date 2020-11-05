@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <teacher-week-schedule :week-count="{{$weekCount}}" :teachers="{{json_encode($teachers)}}" :teacher="{{json_encode($teacher)}}"></teacher-week-schedule>
            </div>
        </div>
    </div>
@endsection
