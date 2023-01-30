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
        Schema::create('subreddits', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('about')->nullable();
            $table->integer('members')->default(0);
            $table->string('profile_image')->default('noprofile.jpg');
            $table->string('cover_image')->default('nocover.jpg');
            $table->string('type');
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
        Schema::dropIfExists('subreddits');
    }
};
