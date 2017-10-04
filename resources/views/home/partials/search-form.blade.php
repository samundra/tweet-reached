<div class="col-md-6">
    <div class="row">
        <form action="/tweet/reached" role="form">
            <div class="form-group" id="searchForm">
                <div class="controls">
                    <div class="input-group">
                        <span class="input-group-addon">
                            {{ __('app.twitter_url') }}:
                        </span>
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
                            <button class="btn btn-success" type="button" v-on:click="calculate" name="submit">
                                {{ __('app.calculate') }}
                            </button>
                        </span>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
