@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10 offset-md-1">
                <div class="card card-body shadow-sm border-0 text-justify">{!! $readme !!}</div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        h1, h2, h3, h4, h5 {
            margin-top: 1rem;
        }

        h5 {
            font-weight: bold;
        }
    </style>
@endsection
