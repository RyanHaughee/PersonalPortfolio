<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TrophyCase extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dynasty_awards', function (Blueprint $table) {
            $table->id();
            $table->string('award')->nullable();
            $table->string('fa_icon')->nullable();
            $table->timestamps();
        });
        Schema::create('dynasty_trophy_case', function (Blueprint $table) {
            $table->id();
            $table->integer('dynasty_award_id')->nullable();
            $table->integer('dynasty_team_id')->nullable();
            $table->integer('year')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
