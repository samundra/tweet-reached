<div class="row" v-if="!formError">
    <div class="col-md-12">
        <div class="row">
            <div v-if="inProgress" class="col-md-12">
                <h2>{{ __('message.calculating') }} ... </h2>
            </div>
            <div v-if="!inProgress" class="col-md-12">
                <div class="" v-if="!hideResults">
                    <h3>
                        <span class="count">@{{ tweet.retweetCount }} {{ __('app.retweets') }} | </span>
                        <span class="count">@{{ peopleReached }} {{ __('app.people_reached') }}</span>
                    </h3>

                    <div class="information-block" v-if="tweet.retweetCount">
                        <button class="btn btn-primary"
                            type="button"
                            data-toggle="collapse"
                            data-target="#retweeter-information"
                            aria-expanded="false"
                            aria-controls="retweeter-information">
                            {{ __('app.toggle_user_follower_count') }}
                        </button>
                        <br/>
                        <div id="retweeter-information" class="collapse">
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
        </div>
    </div>
</div>
