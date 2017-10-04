<?php
/**
 * @author Samundra Shrestha <samundra.shr@gmail.com>
 * @copyright Copyright (c) 2017
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

    // We'll manually update the timestamp fields
    public $timestamps = false;

    /** @var array Mass-assignable fields */
    protected $fillable = [
        'total_sum',
        'tweet_id',
        'updated_at',
        'info',
    ];
}
