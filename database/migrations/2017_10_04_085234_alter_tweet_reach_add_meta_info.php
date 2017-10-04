<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterTweetReachAddMetaInfo extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
$sql = <<<SQL
ALTER TABLE tweet_reach ADD COLUMN info JSON default NULL
SQL;
        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
$sql = <<<SQL
ALTER TABLE tweet_reach DROP COLUMN info
SQL;
        DB::statement($sql);
    }
}
