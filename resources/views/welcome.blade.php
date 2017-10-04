<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Tweets Reached</title>
        <link rel="stylesheet" type="text/css" href="css/app.css"/>
    </head>
    <body>
        <div id="app" class="container-fluid">
            <div class="col-md-12 block-main">
                <h2>Enter Tweet URL: </h2>
                <form action="/tweet/reached" role="form">
                    <div class="form-group" id="searchForm">
                        {{--<label for="searchForm" class="control-label">Enter Twitter Status</label>--}}
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
            </div>

            <div class="col-md-12 block-results">
                <div class="row">
                    <div v-if="inProgress" class="col-md-6">
                        <h2>Calculating ... </h2>
                    </div>
                    <div v-if="!inProgress" class="col-md-6">
                        <h2>Total Reached : <span class="count">@{{ totalCount }}</span> Followers</h2>
                    </div>
                    <div class="col-md-6">
                        <h2>Recently Queries:</h2>
                        <ul>
                            <li v-for="link in links">
                                <a href="javascript:void(0);">@{{ link.text }}</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    <script src="js/app.js"></script>
    </body>
</html>
