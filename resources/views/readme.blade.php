@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-10 offset-md-1">
            <div class="card card-body shadow-sm border-0">{!! $readme !!}</div>
        </div>
    </div>
</div>
@endsection
