<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignSenderIdUserRating extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users_rating', function ($table) {
            $table->unsignedBigInteger('sender_user_id')->nullable();
            $table->index('sender_user_id');
            $table->foreign('sender_user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users_rating', function ($table) {
            $table->dropForeign(['sender_user_id']);
            $table->dropColumn('sender_user_id');
        });
    }
}
