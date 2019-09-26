@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <faculty-teachers :faculties="{{json_encode($faculties)}}"></faculty-teachers>
            </div>
        </div>
    </div>
@endsection
