<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFrequencyTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('frequency', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->string('alias')->nullable(false);
            $table->string('nickname')->nullable(false);
            $table->integer('frequency')->default(false);
            $table->string('mode')->default(false);
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
        Schema::dropIfExists('frequency');
    }
}
