@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <teacher-building-transfers
                    :calendars="{{json_encode($calendars)}}"
                ></teacher-building-transfers>
            </div>
        </div>
    </div>
@endsection
