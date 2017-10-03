<?php
/**
 * @author Zen eServices Pte Ltd
 * @copyright Copyright (c) 2017 Zen eServices Pte Ltd
 */

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TweetReachModel extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tweet_reach';

    // We don't have created_at columns
    public $timestamps = false;

    protected $fillable = [
        'total_sum',
        'tweet_id',
        'updated_at'
    ];
}
