@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <teachers-collisions :week-count="{{$weekCount}}"></teachers-collisions>
            </div>
        </div>
    </div>
@endsection
