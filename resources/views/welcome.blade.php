@extends('layout.main')

@section('content')
    <h1>{{ $title }}</h1>

    @include('message')

    <div class="col-md-12">
        <div class="col-md-6 block-main">
            @include('home.partials.search-form')
            @include('home.partials.results')
        </div>

        <div class="col-md-6 block-results">
            <div class="row">
                @include('home.partials.instructions')
                @include('home.partials.search-query')
            </div>
        </div>
    </div>
@endsection
