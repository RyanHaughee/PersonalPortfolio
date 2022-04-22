<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TournamentTableStructure extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tournament_styles', function (Blueprint $table) {
            $table->id();
            $table->string('style');
            $table->timestamps();
        });

        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('unique_id');
            $table->string('name');
            $table->integer('tournament_style_id');
            $table->timestamps();
        });

        Schema::create('tournament_teams', function (Blueprint $table) {
            $table->id();
            $table->string('tournament_id');
            $table->integer('seed');
            $table->string('name');
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
        Schema::dropIfExists('tournament_styles');
        Schema::dropIfExists('tournaments');
        Schema::dropIfExists('tournament_teams');
    }
}
