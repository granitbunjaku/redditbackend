<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('subreddit_id');
            $table->string('content');
            $table->string('postfile')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->bigInteger('votes')->default(0)->nullable();
            $table->timestamps();

            $table->foreign('subreddit_id')->references('id')->on('subreddits');
            $table->foreign('user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('posts');
    }
};
