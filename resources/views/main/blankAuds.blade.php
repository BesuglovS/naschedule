@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <blank-auds :calendars="{{json_encode($calendars)}}"></blank-auds>
            </div>
        </div>
    </div>
@endsection
