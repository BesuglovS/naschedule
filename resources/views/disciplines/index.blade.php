@extends('layouts.master')

@section('title')
    Список дисциплин
@endsection

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12">
                <discipline-list :group-id="{{$groupId}}" :student-groups="{{json_encode($studentGroups)}}"></discipline-list>
            </div>
        </div>
    </div>
@endsection
