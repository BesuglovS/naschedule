@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <trello-teacher-day
                    :teachers="{{json_encode($teachers)}}"
                    :dates="{{json_encode($dates)}}"
                >
                </trello-teacher-day>
            </div>
        </div>
    </div>
@endsection
