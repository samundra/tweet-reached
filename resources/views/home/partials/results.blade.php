<div v-if="inProgress" class="col-md-6">
    <h2>{{ __('message.calculating') }} ... </h2>
</div>

<div v-if="!inProgress" class="col-md-6">
    <div class="" v-if="!firstTime">
        <h3>
            <span class="count">@{{ tweet.retweetCount }} {{ __('app.retweets') }} | </span>
            <span class="count">@{{ totalCount }} {{ __('app.people_reached') }}</span>
        </h3>

        <div class="information-block">
            <button class="btn btn-primary" type="button" data-toggle="collapse"
                data-target="#retweeter-information" aria-expanded="false"
                aria-controls="retweeterInformation">
                {{ __('app.toggle_user_follower_count') }}
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
