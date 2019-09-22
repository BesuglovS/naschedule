@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <new-rings :calendars="{{json_encode($calendars)}}"
                           :rings="{{json_encode($rings)}}"
                           :ringidpairs1="{{json_encode($ringIdPairs1)}}"
                           :ringidpairs2="{{json_encode($ringIdPairs2)}}"
                ></new-rings>
            </div>
        </div>
    </div>
@endsection
