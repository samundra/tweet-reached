@extends('layout.main')

@section('content')
    <div class="col-md-12 message-box">
        <div class="row">
            <div v-if="showMessage" class="alert alert-info">
                @{{ message }}
            </div>
        </div>
    </div>

    <div class="col-md-12 block-main">
        <div class="col-md-6">
            <div class="row">
                <h2>Enter Tweet URL: </h2>
                <form action="/tweet/reached" role="form">

                    <div class="form-group" id="searchForm">
                        <div class="controls">
                            <div class="input-group">
                                <input type="text"
                                    v-model="query"
                                    class="col-md-7 form-control"
                                    id="tweet_url"
                                    name="tweet_url"
                                    data-toggle="popover"
                                    data-placement="bottom"
                                    data-title="Missing Status"
                                    data-content="Please enter status"
                                    placeholder="Enter tweet URL" />
                                <span class="input-group-btn">
                                    <button
                                        class="btn btn-success"
                                        type="button"
                                        v-on:click="calculate"
                                        name="submit">Calculate</button>
                                </span>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-md-6">
            <div class="row">
                <ol>
                    <li>Copy and paste twitter full status link e.g. https://twitter.com/samushr/status/599063840362397696</li>
                    <li>Click on calculate, then wait for results to appear</li>
                </ol>
            </div>
        </div>
    </div>

    <div class="col-md-12 block-results">
        <div class="row">
            <div v-if="inProgress" class="col-md-6">
                <h2>Calculating ... </h2>
            </div>

            <div v-if="!inProgress" class="col-md-6">
                <div class="" v-if="!firstTime">
                    <h2>Total Retweets : <span class="count">@{{ tweet.retweetCount }}</span> Retweets done </h2>
                    <h2>Total Followers Reached : <span class="count">@{{ totalCount }}</span> Followers</h2>

                    <div class="information-block">
                        <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#retweeter-information" aria-expanded="false" aria-controls="retweeterInformation">
                            Toggle Userwise Follower Count
                        </button>
                        <br/>
                        <div id="retweeter-information">
                            <div class="well">
                                <ol>
                                    <li v-for="retweeter in tweet.retweeters">
                                        @{{ retweeter.name }} - @{{ retweeter.followersCount }}
                                    </li>
                                </ol>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6" v-if="!firstTime">
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
