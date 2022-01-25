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
            $table->string('station')->nullable(true);
            $table->boolean('starttime')->default(true);
            $table->boolean('stoptime')->default(false);
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

