@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <fill-blank-auds
                    :week-count="{{$weekCount}}"
                    :buildings="{{json_encode($buildings)}}"
                    :auds-with-building="{{json_encode($audsWithBuilding)}}"
                    :rings="{{json_encode($rings)}}"
                    :semester-starts="{{json_encode($semesterStarts)}}"
                    :chap="{{$chap}}"
                >
                </fill-blank-auds>
            </div>
        </div>
    </div>
@endsection
