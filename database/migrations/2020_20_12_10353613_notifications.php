<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Notifications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('notifications', function (Blueprint $table) {

        $table->bigIncrements('id');

        // USER FROM ID
        $table->unsignedBigInteger('user_sender_id')->nullable();
        $table->index('user_sender_id');
        $table->foreign('user_sender_id')->references('id')->on('users')->onDelete('cascade');

        // USER TO ID
        $table->unsignedBigInteger('user_receiver_id')->nullable();
        $table->index('user_receiver_id');
        $table->foreign('user_receiver_id')->references('id')->on('users')->onDelete('cascade');

        // NOTIFICATION TYPE
        $table->string('type')->nullable();

        // NOTIFICATION STATUS
        $table->enum('status', ['unread', 'read'])->default('unread');

        $table->string('info')->nullable();

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
       Schema::dropIfExists('notifications');
    }
}
