<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_locations', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->unsignedBigInteger('location_id');
            $table->index('location_id');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');

            $table->unsignedBigInteger('user_id')->nullable();
            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

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
        Schema::dropIfExists('users_locations');
    }
}
