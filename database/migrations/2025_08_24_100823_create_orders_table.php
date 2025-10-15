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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('partner_id')->nullable();
            $table->unsignedBigInteger('traveler_id')->nullable();
            $table->unsignedBigInteger('rider_id')->nullable();
            $table->decimal('total_price', 10, 2)->default(0);
            $table->enum('status', ['pending', 'approved', 'processing', 'shipped', 'delivered', 'cancelled', 'returned', 'refunded'])->default('pending');
            $table->dateTime('dispatch_time')->nullable();
            $table->dateTime('delivery_time')->nullable();

            $table->dateTime('rent_start')->nullable();
            $table->dateTime('rent_due_back')->nullable();
            $table->dateTime('returned_at')->nullable();

            $table->nullableMorphs('canceled_by');
            $table->foreign('partner_id')->references('id')->on('partners')->onDelete('cascade');
            $table->foreign('traveler_id')->references('id')->on('travelers')->onDelete('cascade');
            $table->foreign('rider_id')->references('id')->on('riders')->onDelete('set null');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
