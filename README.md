
![Screenshot](https://user-images.githubusercontent.com/760855/40038695-349921fa-5833-11e8-96c6-96f61741a207.png)

## Twitter Retweet Follower Count Calculator
A demo application to calculate the people reached for the specific retweet.
This application has been built on [Laravel5.5](https://github.com/laravel/laravel/releases/tag/v5.5.0)
PHP Framework.

### Dependencies
- [Laravel PHP Framework](https://laravel.com/) - PHP Framework
- [Vue.js](https://vuejs.org) - Frontend JS Framework
- [PostgreSQL](https://www.postgresql.org/)- Database System.

## Development Dependencies
- [Phpunit](phpunit.de)
- [Mockery](https://github.com/mockery/mockery)
- [Twitter API for Laravel 4/5](https://github.com/thujohn/twitter)

## Installation 
The basic installation process of laravel should be followed.

Basically,

```bash
$ git clone git clone https://samundra@bitbucket.org/samundra/tweet-reached.git
$ cd tweet-reached && composer install
$ copy .env.example .env && php artisan key:generate
$ npm install && npm run prod
```

### Fix Permission Issues
```bash
$ sudo chgrp -R www-data storage bootstrap/cache
$ sudo chmod -R ug+rwx storage bootstrap/cache
```

## OPTIONAL
### Generate Assets
```bash
# Development Version
$ npm run dev

# On Production
$ npm run prod
```

### Configurations
- Copy ```.env.example``` and create new file ```.env```
- Update Twitter Configurations in ```.env``` with respective configurations
found in Twitter Application (https://apps.twitter.com/app/XXXXXX/keys) 
```bash
# Twitter Configuration
TWITTER_CONSUMER_KEY=
TWITTER_CONSUMER_SECRET=
TWITTER_ACCESS_TOKEN=
TWITTER_ACCESS_TOKEN_SECRET=
```
- Tweet are cached for 2 hour by default. To increase the cache expire time update
```APP_TWEET_CACHE_EXPIRE``` environment variable.

### Database
#### Run Migration
```bash
$ php artisan migrate:refresh
```

#### Run Seeder
```bash
$ php artisan db:seed
```

### Preview
Once all the setup are done, in project root directory execute 

```bash
$ php artisan serve
```
Then browse ```http://localhost:8000```. You should see a layout where you can enter tweet.

### Testing
All ```tests``` are in ```tests``` folder. To run test execute the below
command from project root directory.

### Troubleshooting
- Open the dev inspector and look in console. If there are 500 errors then you likely forgot to setup the twitter
credentials.
- You forgot to setup the database connection and run migrations

```bash
$ vendor/bin/phpunit --debug
```
#### Output
```bash
PHPUnit 6.3.1 by Sebastian Bergmann and contributors.

....................                                              20 / 20 (100%)

Time: 8.54 seconds, Memory: 18.00MB

OK (20 tests, 82 assertions)
```

### How to Contribute
- Fork it and send a PR with feature request.
- Find something that can be done better, create issue or send a PR directly :)
- Participate in the discussions

### Coding Standard
- PHP Coding Standard [PSR-2](http://www.php-fig.org/psr/psr-2/)

```bash
$ phpcs --standard=PSR2 --ignore=*/tests/*,*/node_modules/*,*/vendor/*,*/public/*,*/storage/*,*/resources/*,*/bootstrap/cache/* .
```

Happy Coding :)
