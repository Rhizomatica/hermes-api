<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomerrorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('custom_errors', function (Blueprint $table) {
            $table->increments('id')->unique();
            // $table->integer('user_id')->nullable(false);
            $table->string('controller')->nullable(false);
            $table->integer('error_code')->nullable(false);
            $table->string('error_message')->nullable(true);
            $table->string('stacktrace')->nullable(true);
            $table->integer('station')->nullable(false);
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
        Schema::dropIfExists('customerrors');
    }
}
