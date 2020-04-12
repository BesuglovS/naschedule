@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <trello-day
                    :groups="{{json_encode($groups)}}"
                    :dates="{{json_encode($dates)}}"
                >
                </trello-day>
            </div>
        </div>
    </div>
@endsection
