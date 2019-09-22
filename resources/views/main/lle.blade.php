@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <lle :dates="{{json_encode($dates)}}"></lle>
            </div>
        </div>
    </div>
@endsection
