<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMessagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->increments('id')->unique();
            $table->string('name');
            $table->string('orig');
            $table->string('dest');
            $table->string('file')->nullable(true);
            $table->string('fileid')->nullable(true);
            $table->text('text');
            $table->string('sent_at')->nullable(true);
            $table->boolean('draft')->default(true);
            $table->boolean('inbox')->default(false);
            $table->boolean('secure')->default(false);
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
        Schema::dropIfExists('messages');
    }
}

