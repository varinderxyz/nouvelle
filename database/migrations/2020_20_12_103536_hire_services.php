<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HireServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hire_services', function (Blueprint $table) {

            $table->bigIncrements('id');

            // HOW MANY HOURS
            $table->bigInteger('service_hours_want')->nullable();

            // USER FROM ID
            $table->unsignedBigInteger('user_sender_id')->nullable();
            $table->index('user_sender_id');
            $table->foreign('user_sender_id')->references('id')->on('users')->onDelete('cascade');

            // USER TO ID
            $table->unsignedBigInteger('user_receiver_id')->nullable();
            $table->index('user_receiver_id');
            $table->foreign('user_receiver_id')->references('id')->on('users')->onDelete('cascade');
            // USER TO SERVICE ID 
            $table->unsignedBigInteger('service_receiver_id');
            $table->index('service_receiver_id');
            $table->foreign('service_receiver_id')->references('id')->on('services')->onDelete('cascade');

            // ACCEPT OFFER INSTRUCTIONS
            $table->string('offer_instructions')->nullable();

            // SERVICE STATUS
            $table->enum('service_status', ['pending', 'active', 'completed', 'cancelled', 'declined'])->default('pending');

            // AMOUNT TO BE PAID 
            $table->float('amount_to_be_paid')->nullable();

            // PAYMENT STATUS
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');

            // TYPE FOR PAYMENT SETUP
            $table->string('type')->default('hire');

            // STATUS CONFIRMED
            $table->enum('sender_service_confirmed', ['0', '1'])->default('1');
            $table->enum('receiver_service_confirmed', ['0', '1'])->nullable();
            $table->enum('sender_service_completed', ['0', '1'])->nullable();


            // RECORD TIME STAMP 
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
        Schema::dropIfExists('hire_services');
    }
}
