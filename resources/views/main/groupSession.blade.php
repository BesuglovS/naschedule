@extends('layouts.master')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <group-session :group-id="{{$group_id}}" :student-groups="{{json_encode($studentGroups)}}"></group-session>
            </div>
        </div>
    </div>
@endsection
