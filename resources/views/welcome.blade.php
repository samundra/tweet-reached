@extends('layout.main')

@section('content')
    <h1>Twitter Follower Calculator</h1>

    @include('message')

    <div class="col-md-12 block-main">
        @include('home.partials.search-form')
        @include('home.partials.instructions')
    </div>

    <div class="col-md-12 block-results">
        <div class="row">
            @include('home.partials.results')

            @include('home.partials.search-query')
        </div>
    </div>
@endsection
