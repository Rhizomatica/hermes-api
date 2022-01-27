<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCallerTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('caller', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->string('title')->nullable(true);
            $table->json('stations')->nullable(false);
            $table->time('starttime')->default(true);
            $table->time('stoptime')->default(false);
            $table->boolean('enable')->default(false);
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
        Schema::dropIfExists('caller');
    }
}

