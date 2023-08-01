<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersRating extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_rating', function (Blueprint $table) {
          $table->bigIncrements('id');

        $table->unsignedBigInteger('user_id')->nullable();
        $table->index('user_id');
        $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        $table->integer('sender_user_id')->nullable();

        $table->float('star_rating')->default('0');

        // SWAP PROVIDER FEEDBACK
        $table->integer('time')->default('0');
        $table->integer('communication')->default('0');
        $table->integer('skills')->default('0');
        $table->integer('quality_of_work')->default('0');
        $table->integer('professionalism')->default('0');

        $table->timestamp('created_at')->useCurrent();
        $table->timestamp('updated_at')->useCurrent();

    });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users_rating');
    }
}
