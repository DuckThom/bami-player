<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVoteskipColumnToNowPlayingTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('now_playing', function (Blueprint $table) {

            /*
             * -1: disabled
             *  0: inactive
             *  1: active
             *  2: failed
             */
            $table->enum('voteskip', [-1,0,1,2])->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('now_playing', function (Blueprint $table) {
            $table->dropColumn('voteskip');
        });
    }
}
