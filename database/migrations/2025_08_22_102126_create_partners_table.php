<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->string('profile_photo')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('category')->nullable();
            $table->string('location')->nullable();
            $table->string('address')->nullable();
            $table->string('store_available_days')->nullable();
            $table->string('store_available_time')->nullable();
            $table->string('tax_id')->nullable();
            $table->string('business_name')->nullable();
            $table->string('username')->nullable();
            $table->enum('status', ['active', 'suspended', 'pending'])->default('pending');
            $table->float('average_rating', 3, 2)->default(0.00);

            $table->string('password')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();

            $table->enum('two_factor_method', ['none', 'totp', 'email'])->default('none');

            $table->string('two_factor_email_code')->nullable();
            $table->timestamp('two_factor_email_expires_at')->nullable();


            $table->string('provider')->nullable();
            $table->string('provider_id')->nullable();
            $table->string('provider_token')->nullable();
            $table->string('provider_refresh_token')->nullable();
            $table->string('avatar')->nullable();

            $table->unique(['provider', 'provider_id']);

            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
