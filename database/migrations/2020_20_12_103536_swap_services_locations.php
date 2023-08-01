<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SwapServicesLocations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('swap_services_locations', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('location_id');
            $table->index('location_id');
            $table->foreign('location_id')->references('id')->on('locations')->onDelete('cascade');

            $table->unsignedBigInteger('swap_services_id')->nullable();
            $table->index('swap_services_id');
            $table->foreign('swap_services_id')->references('id')->on('swap_services')->onDelete('cascade');

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
        Schema::dropIfExists('swap_services_locations');
    }
}
