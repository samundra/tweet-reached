@extends('layout.main')

@section('content')
    <div class="col-md-12 block-main">
        <h2>Enter Tweet URL: </h2>
        <form action="/tweet/reached" role="form">
            <div class="form-group" id="searchForm">
                <div class="controls">
                    <input type="text"
                        v-model="query"
                        class="input form-control"
                        id="tweet_url"
                        name="tweet_url"
                        data-toggle="popover"
                        data-placement="bottom"
                        data-title="Missing Status"
                        data-content="Please enter status"
                        placeholder="Enter tweet URL" />
                </div>
            </div>

            <button
                class="btn btn-lg btn-success"
                type="button"
                v-on:click="calculate"
                name="submit">Calculate</button>
        </form>
    </div><!-- / Form -->

    <div class="col-md-12 block-results">
        <div class="row">
            <div v-if="inProgress" class="col-md-6">
                <h2>Calculating ... </h2>
            </div>
            <div v-if="!inProgress" class="col-md-6">
                <h2>Total Reached : <span class="count">@{{ totalCount }}</span> Followers</h2>
            </div>
            <div class="col-md-6">
                <h2>Recent Queries:</h2>
                <ul>
                    <li v-for="link in links">
                        <a href="javascript:void(0);">@{{ link.text }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div> <!---- / Results -->
@endsection
