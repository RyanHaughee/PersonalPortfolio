<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Players extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dynasty_players', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('pos')->nullable();
            $table->string('team_abr')->nullable();
            $table->integer('team_id')->nullable();
            $table->timestamps();
        });

        Schema::create('dynasty_player_teams', function (Blueprint $table) {
            $table->id();
            $table->integer('dynasty_player_id')->nullable();
            $table->integer('dynasty_team_id')->nullable();
            $table->timestamps();
        });

        Schema::create('dynasty_player_values', function (Blueprint $table) {
            $table->id();
            $table->integer('dynasty_player_id')->nullable();
            $table->string('name')->nullable();
            $table->integer('player_value')->nullable();
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
