<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->string('email')->unique();
            $table->string('name');
            $table->string('password');
            $table->string('phone')->nullable(true);
            $table->string('site')->nullable(true);
            $table->string('location')->nullable(true);
            $table->string('recoverphrase')->nullable(true);
            $table->string('recoveranswer')->nullable(true);
            $table->boolean('admin')->nullable(true);
            $table->string('emailid')->nullable(true);
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
        Schema::dropIfExists('users');
    }
}
