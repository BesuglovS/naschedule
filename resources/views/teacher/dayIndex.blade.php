@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <group-day
                    :groups="{{json_encode($groups)}}"
                    :calendars="{{json_encode($dates)}}"
                >
                </group-day>
            </div>
        </div>
    </div>
@endsection
