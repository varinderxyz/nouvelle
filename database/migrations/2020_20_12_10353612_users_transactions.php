<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');

            // TRANSACTION FROM 
            $table->unsignedBigInteger('sender_user_id')->nullable();
            $table->index('sender_user_id');
            $table->foreign('sender_user_id')->references('id')->on('users')->onDelete('cascade');

            // TRANSACTION TO
            $table->unsignedBigInteger('receiver_user_id')->nullable();
            $table->index('receiver_user_id');
            $table->foreign('receiver_user_id')->references('id')->on('users')->onDelete('cascade');

            $table->float('amount_paid')->nullable();

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
        Schema::dropIfExists('users_transactions');
    }
}
