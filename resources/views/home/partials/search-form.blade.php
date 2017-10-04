<div class="col-md-12">
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
                            data-title="{{ __('message.missing_url_title') }}"
                            data-content="{{ __('message.enter_valid_url') }}"
                            placeholder="{{ __('message.placeholder_enter_url') }}" />
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
