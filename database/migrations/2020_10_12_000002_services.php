<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Services extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('services', function (Blueprint $table) {
            $table->bigIncrements('id');
            
            $table->unsignedBigInteger('user_id');
            $table->index('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            $table->string('picture')->nullable();
            $table->integer('zip_code')->nullable();
            $table->string('service_name')->unique()->nullable();

            $table->unsignedBigInteger('services_category_id');
            $table->index('services_category_id');
            $table->foreign('services_category_id')->references('id')->on('services_category')->onDelete('cascade');

            // $table->string('services_location_id')->nullable();

            $table->string('willing_to_travel')->default('5');
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->string('geo_address')->nullable();
            $table->string('hourly_rate')->nullable();
            $table->enum('swap',['0','1'])->default('0');
            $table->enum('hire', ['0', '1'])->default('0');
            $table->string('cancellation_terms_hour')->default('24');
            $table->string('service_descp')->nullable();
            $table->string('video_url')->nullable();
            $table->enum('service_status', ['active', 'pause'])->default('active');
            $table->enum('featured', ['0', '1'])->default('0');
            $table->integer('views')->default('0');
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
        Schema::dropIfExists('services');
    }
}
