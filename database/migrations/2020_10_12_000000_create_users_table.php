<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::enableForeignKeyConstraints();
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->string('phone')->unique()->nullable();
            $table->string('picture')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('hourly_rate')->nullable();
            $table->string('payment_customer_id')->nullable();
            $table->string('willing_to_travel')->nullable();
            $table->string('about')->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->string('geo_address')->nullable();
            $table->enum('role',['admin','user'])->default('user');
            $table->enum('active',['1','0'])->default('1');
            $table->enum('phone_verified', ['0', '1'])->default('0');
            $table->enum('facebook_verified', ['0', '1'])->default('0');
            $table->enum('email_verified', ['0', '1'])->default('0');
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
