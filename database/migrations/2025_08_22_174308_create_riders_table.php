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
        Schema::create('riders', function (Blueprint $table) {
            $table->id();
            $table->string('profile_photo')->nullable();
            $table->string('rider_id')->unique()->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('license_front')->nullable();
            $table->string('license_back')->nullable();
            $table->string('license_plate')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->string('vehicle_name')->nullable();
            $table->string('assigned_region')->nullable();
            $table->date('insurance_expire_date')->nullable();
            // $table->boolean('online')->default(false);
            $table->enum('availability_status', ['online','offline'])->default('offline');
            $table->enum('status', ['active','suspended'])->default('active');
            $table->integer('delivered_orders')->default(0);
            $table->float('average_rating', 3, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riders');
    }
};
