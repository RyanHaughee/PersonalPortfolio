<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FuturePicks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dynasty_future_picks', function (Blueprint $table) {
            $table->id();
            $table->integer('original_owner_id')->nullable();
            $table->integer('round')->nullable();
            $table->integer('year')->nullable();
            $table->integer('current_owner_id')->nullable();
            $table->integer('estimated_pick_value')->nullable();
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
