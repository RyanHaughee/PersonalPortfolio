<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TournamentGames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournament_games', function (Blueprint $table) {
            $table->id();
            $table->integer('tournament_id');
            $table->integer('tournament_game_id');
            $table->integer('team_1_id');
            $table->integer('team_2_id');
            $table->integer('team_1_score');
            $table->integer('team_2_score');
            $table->integer('team_1_origin');
            $table->integer('team_2_origin');
            $table->integer('winner_to');
            $table->integer('winner_to_slot');
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
