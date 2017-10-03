<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Tweets Reached</title>
        <link rel="stylesheet" type="text/css" href="css/app.css"/>
    </head>
    <body>
        <div class="container-fluid">
            <div class="col-md-12 block-main">
                <h2>Enter Tweet URL: </h2>
                <form action="/tweet/reached" role="form">
                    <input type="text" class="input form-control" name="tweet_url" value="" placeholder="Enter tweet URL" />
                    <br/>
                    <button class="btn btn-lg btn-primary" type="button" name="submit" value="submit">Calculate Total Reach</button>
                </form>
            </div>

            <div class="col-md-12 block-results">
                <div class="row">
                    <div class="col-md-6">
                        <h2>Total Reached : <span class="count">1000</span> Followers</h2>
                    </div>
                    <div class="col-md-6">
                        <h2>Recently Searched</h2>
                        <ul>
                            <li><a href="javascript:void(0);" >http://www.twitter.com/status/10000</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
