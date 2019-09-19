@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <put-auds :calendars="{{json_encode($calendars)}}"></put-auds>
            </div>
        </div>
    </div>
@endsection
