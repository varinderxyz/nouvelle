<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SwapServices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('swap_services', function (Blueprint $table) {

            $table->bigIncrements('id');

            // HOW MANY HOURS
            $table->bigInteger('service_hours_swap')->nullable();
            
            $table->bigInteger('service_hours_want')->nullable();

            // MULTIPLE SERVICES ID DURING SWAP IN FIRST STEP
            $table->jsonb('multiple_service_sender_id')->nullable();

            // SWAP COUNT OF RECEIVER SERVICE
            $table->jsonb('receiver_swap_service_time_count')->nullable();

            // USER FROM ID
            $table->unsignedBigInteger('user_sender_id')->nullable();
            $table->index('user_sender_id');
            $table->foreign('user_sender_id')->references('id')->on('users')->onDelete('cascade');
            
            // USER FROM SERVICE ID 
            $table->unsignedBigInteger('service_sender_id')->nullable();
            $table->index('service_sender_id');
            $table->foreign('service_sender_id')->references('id')->on('services')->onDelete('cascade');

            // USER TO ID
            $table->unsignedBigInteger('user_receiver_id')->nullable();
            $table->index('user_receiver_id');
            $table->foreign('user_receiver_id')->references('id')->on('users')->onDelete('cascade');
            // USER TO SERVICE ID 
            $table->unsignedBigInteger('service_receiver_id')->nullable();
            $table->index('service_receiver_id');
            $table->foreign('service_receiver_id')->references('id')->on('services')->onDelete('cascade');

            // ACCEPT OFFER INSTRUCTIONS
            $table->string('offer_instructions')->nullable();
           
            // SERVICE STATUS
            $table->enum('service_status', ['pending', 'active', 'completed', 'cancelled','declined'])->default('pending');

            // PAYMENT STATUS
            $table->enum('payment_status', ['pending', 'paid'])->default('pending');

            // COST OF SERVICE
            $table->bigInteger('sender_amount_to_be_paid')->nullable();
            $table->bigInteger('receiver_amount_to_be_paid')->nullable();

            // BOOT ADDED 
            $table->bigInteger('boot_calculate')->nullable();

            // BOOT ASSIGN
            $table->unsignedBigInteger('boot_assign_person')->nullable();
            $table->index('boot_assign_person');
            $table->foreign('boot_assign_person')->references('id')->on('users')->onDelete('cascade');

            // STATUS CONFIRMED 
            $table->enum('sender_service_confirmed', ['0', '1'])->default('1');
            $table->enum('receiver_service_confirmed', ['0', '1'])->nullable();
            $table->enum('sender_service_completed', ['0', '1'])->nullable();
            $table->enum('receiver_service_completed', ['0', '1'])->nullable();

            // TYPE FOR PAYMENT SETUP
            $table->string('type')->default('swap');

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
        Schema::dropIfExists('swap_services');
    }
}
