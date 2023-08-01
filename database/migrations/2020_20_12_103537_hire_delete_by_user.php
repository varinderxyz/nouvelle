<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class HireDeleteByUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hire_services', function ($table) {
            $table->unsignedBigInteger('delete_by_user')->nullable();
            $table->index('delete_by_user');
            $table->foreign('delete_by_user')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hire_services', function ($table) {
            $table->dropForeign(['delete_by_user']);

            $table->dropColumn('delete_by_user');
        });
    }
}
