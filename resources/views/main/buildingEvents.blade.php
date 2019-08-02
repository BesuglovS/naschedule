@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <building-events :week-count="{{$weekCount}}" :buildings="{{json_encode($buildings)}}"></building-events>
            </div>
        </div>
    </div>
@endsection
