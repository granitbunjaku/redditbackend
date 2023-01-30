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
        Schema::create('categories_subreddit', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('subreddit_id');
            $table->unsignedBigInteger('categories_id');
            $table->timestamps();
            
            $table->foreign('subreddit_id')->references('id')->on('subreddits');
            $table->foreign('categories_id')->references('id')->on('categories');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('categories_subreddit');
    }
};
