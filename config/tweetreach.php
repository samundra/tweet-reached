<?php
/**
 * @author Samundra Shrestha <samundra.shr@gmail.com>
 * @copyright Copyright (c) 2017
 */

return [
    'date_format' => env('APP_DATEFORMAT', 'Y-m-d H:i:sO'),

    // Cache will be valid for 2 hours, After this DB record will be refreshed
    // with new record
    'cache_expire' => env('APP_TWEET_CACHE_EXPIRE', 2),
];
