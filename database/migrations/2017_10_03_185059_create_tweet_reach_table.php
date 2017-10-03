<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class CreateTweetReachTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
$sql = <<<SQL
CREATE TABLE tweet_reach (
  id SERIAL NOT NULL PRIMARY KEY,
  tweet_id bigint NOT NULL UNIQUE,
  total_sum INTEGER NOT NULL DEFAULT 0,
  updated_at TIMESTAMP with time zone DEFAULT CURRENT_TIMESTAMP
);
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
        Schema::dropIfExists('tweet_reach');
    }
}
