<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UsersCategories extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users_categories', function (Blueprint $table) {

            $table->bigIncrements('id');

            $table->unsignedBigInteger('services_category_id');
            $table->index('services_category_id');
            $table->foreign('services_category_id')->references('id')->on('services_category')->onDelete('cascade');

            $table->unsignedBigInteger('service_id')->nullable();
            $table->index('service_id');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            
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
        Schema::dropIfExists('users_categories');
    }
}
