<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PendingTrades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dynasty_trades', function (Blueprint $table) {
            $table->id();
            $table->integer('league_id');
            $table->integer('team_1_id')->nullable();
            $table->integer('team_2_id')->nullable();
            $table->string('team_1_receives')->nullable();
            $table->string('team_2_receives')->nullable();
            $table->integer('verified')->nullable();
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
