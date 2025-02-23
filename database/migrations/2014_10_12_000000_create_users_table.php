<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migration.
     *
     * This method is used to define the structure of the database table to be created. 
     * In this case, the "users" table is being created with necessary fields and their constraints.
     *
     * @return void
     */
    public function up()
    {
        // Create the "users" table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable(false);
            $table->string('email')->unique()->nullable(false);
            $table->string('password')->nullable(false);
            $table->string('verification_code')->nullable();
            $table->timestamp('code_expires_at')->nullable();
            $table->boolean('email_verified')->default(false);
            $table->enum('role', ['user', 'admin'])->default('user');
            $table->timestamps();
        });
    }

    /**
     * Revertir migración
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
